"""
rag_server.py — Persistent Flask API server for the RAG pipeline.

Loads the embedding model and FAISS index ONCE on startup, then serves
queries instantly without the 30-60s model reload penalty.

Start it once (runs in background):
    python rag_server.py

Laravel calls:
    POST http://127.0.0.1:5001/ask   { "question": "...", "query_id": N }
    GET  http://127.0.0.1:5001/health
"""

from __future__ import annotations

import sys
import json
import time
from pathlib import Path

# ── Force UTF-8 ───────────────────────────────────────────────────────────────
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

# ── Load Laravel root .env ────────────────────────────────────────────────────
_root_env = Path(__file__).parent.parent / ".env"
if _root_env.exists():
    from dotenv import load_dotenv
    load_dotenv(dotenv_path=str(_root_env), override=True)

import pymysql
import pymysql.cursors
from flask import Flask, request, jsonify

try:
    from langchain_community.vectorstores import FAISS
except Exception:
    from langchain_faiss import FAISS  # type: ignore

from langchain_huggingface import HuggingFaceEmbeddings
from langchain_core.runnables import RunnableLambda, RunnableParallel, RunnablePassthrough
from langchain_core.prompts import ChatPromptTemplate
from langchain_core.output_parsers import StrOutputParser
from langchain_core.documents import Document
from langchain_google_genai import ChatGoogleGenerativeAI

import config

app = Flask(__name__)

# ── Globals — loaded once on startup ──────────────────────────────────────────
_embeddings  = None
_vectorstore = None
_rag_chain   = None
_retriever   = None


# ── DB helpers ─────────────────────────────────────────────────────────────────

def _get_db():
    return pymysql.connect(
        host=config.DB_HOST,
        port=config.DB_PORT,
        user=config.DB_USER,
        password=config.DB_PASSWORD,
        database=config.DB_NAME,
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=False,
    )


def _save_answer(query_id: int, answer_text: str) -> int:
    conn = _get_db()
    try:
        with conn.cursor() as cur:
            cur.execute(
                "INSERT INTO answers (query_id, answer_text) VALUES (%s, %s)",
                (query_id, answer_text),
            )
            answer_id = cur.lastrowid
            cur.execute("SELECT user_id FROM queries WHERE query_id = %s", (query_id,))
            row = cur.fetchone()
            if row:
                cur.execute(
                    "INSERT INTO histories (user_id, query_id, answer_id) VALUES (%s, %s, %s)",
                    (row["user_id"], query_id, answer_id),
                )
        conn.commit()
        return answer_id
    except Exception:
        conn.rollback()
        raise
    finally:
        conn.close()


def _update_query_status(query_id: int, status: str, ms: int | None = None) -> None:
    conn = _get_db()
    try:
        with conn.cursor() as cur:
            if ms is not None:
                cur.execute(
                    "UPDATE queries SET status=%s, response_time_ms=%s WHERE query_id=%s",
                    (status, ms, query_id),
                )
            else:
                cur.execute(
                    "UPDATE queries SET status=%s WHERE query_id=%s",
                    (status, query_id),
                )
        conn.commit()
    except Exception:
        conn.rollback()
    finally:
        conn.close()


# ── RAG helpers ────────────────────────────────────────────────────────────────

def _format_context(docs: list[Document]) -> str:
    parts = []
    for i, doc in enumerate(docs, 1):
        src  = doc.metadata.get("source_file", "unknown")
        page = doc.metadata.get("page", "")
        loc  = src + (f", p.{int(page) + 1}" if page != "" else "")
        parts.append(f"[Excerpt {i} — {loc}]\n{doc.page_content}")
    return "\n\n---\n\n".join(parts)


def _load_components():
    """Load embedding model + FAISS + RAG chain once on startup."""
    global _embeddings, _vectorstore, _rag_chain, _retriever

    print("Loading embedding model...", flush=True)
    _embeddings = HuggingFaceEmbeddings(
        model_name=config.EMBEDDING_MODEL,
        model_kwargs={"device": config.EMBEDDING_DEVICE},
        encode_kwargs={"normalize_embeddings": True},
    )

    print("Loading FAISS index...", flush=True)
    _vectorstore = FAISS.load_local(
        config.FAISS_INDEX_PATH,
        _embeddings,
        allow_dangerous_deserialization=True,
    )

    _retriever = _vectorstore.as_retriever(
        search_type="similarity",
        search_kwargs={"k": config.TOP_K},
    )

    llm = ChatGoogleGenerativeAI(
        model=config.GEMINI_MODEL,
        google_api_key=config.GEMINI_API_KEY,
        temperature=config.GEMINI_TEMPERATURE,
        max_output_tokens=config.GEMINI_MAX_TOKENS,
        streaming=False,
    )

    prompt = ChatPromptTemplate.from_messages([
        ("system", config.RAG_SYSTEM_PROMPT),
        ("human", "{question}"),
    ])

    _rag_chain = (
        RunnableParallel(
            context=_retriever | RunnableLambda(_format_context),
            question=RunnablePassthrough(),
        )
        | prompt
        | llm
        | StrOutputParser()
    )

    print("RAG server ready.", flush=True)


# ── Routes ─────────────────────────────────────────────────────────────────────

@app.route("/health", methods=["GET"])
def health():
    return jsonify({
        "status":       "ok",
        "model_loaded": _rag_chain is not None,
        "faiss_loaded": _vectorstore is not None,
    })


@app.route("/ask", methods=["POST"])
def ask():
    data     = request.get_json(force=True)
    question = (data.get("question") or "").strip()
    query_id = data.get("query_id")

    if not question:
        return jsonify({"success": False, "error": "question is required"}), 400
    if not query_id:
        return jsonify({"success": False, "error": "query_id is required"}), 400

    if _rag_chain is None:
        return jsonify({"success": False, "error": "Model not loaded yet. Try again in a moment."}), 503

    try:
        start     = time.time()
        answer    = _rag_chain.invoke(question)
        elapsed   = round((time.time() - start) * 1000)
        answer_id = _save_answer(query_id, answer)
        _update_query_status(query_id, "answered", elapsed)

        # Sources
        sources = []
        try:
            scored = _vectorstore.similarity_search_with_score(question, k=config.TOP_K)
            sources = [
                {
                    "source":  d.metadata.get("source_file", "unknown"),
                    "page":    d.metadata.get("page", None),
                    "score":   round(float(s), 4),
                    "excerpt": d.page_content[:200],
                }
                for d, s in scored
            ]
        except Exception:
            pass

        return jsonify({
            "success":          True,
            "query_id":         query_id,
            "answer_id":        answer_id,
            "answer":           answer,
            "response_time_ms": elapsed,
            "sources":          sources,
        })

    except Exception as exc:
        _update_query_status(query_id, "failed")
        return jsonify({"success": False, "query_id": query_id, "error": str(exc)}), 500


@app.route("/reload", methods=["POST"])
def reload_index():
    """
    Call this after ingesting new documents so the server picks up
    the updated FAISS index without restarting.
    """
    global _vectorstore, _retriever, _rag_chain
    try:
        _vectorstore = FAISS.load_local(
            config.FAISS_INDEX_PATH,
            _embeddings,
            allow_dangerous_deserialization=True,
        )
        _retriever = _vectorstore.as_retriever(
            search_type="similarity",
            search_kwargs={"k": config.TOP_K},
        )
        # Rebuild chain with new retriever
        llm = ChatGoogleGenerativeAI(
            model=config.GEMINI_MODEL,
            google_api_key=config.GEMINI_API_KEY,
            temperature=config.GEMINI_TEMPERATURE,
            max_output_tokens=config.GEMINI_MAX_TOKENS,
            streaming=False,
        )
        prompt = ChatPromptTemplate.from_messages([
            ("system", config.RAG_SYSTEM_PROMPT),
            ("human", "{question}"),
        ])
        _rag_chain = (
            RunnableParallel(
                context=_retriever | RunnableLambda(_format_context),
                question=RunnablePassthrough(),
            )
            | prompt
            | llm
            | StrOutputParser()
        )
        return jsonify({"success": True, "message": "FAISS index reloaded."})
    except Exception as exc:
        return jsonify({"success": False, "error": str(exc)}), 500


# ── Entry point ────────────────────────────────────────────────────────────────

if __name__ == "__main__":
    if not config.GEMINI_API_KEY:
        print("ERROR: GEMINI_API_KEY not set in .env", flush=True)
        sys.exit(1)

    if not Path(config.FAISS_INDEX_PATH).exists():
        print("ERROR: FAISS index not found. Run ingest.py first.", flush=True)
        sys.exit(1)

    _load_components()

    # Host 127.0.0.1 — only accessible from localhost (Laravel on same machine)
    app.run(host="127.0.0.1", port=5001, debug=False, threaded=True)