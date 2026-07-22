"""
flask_api.py — Persistent Flask API server for the RAG pipeline.
Start once, keep running:
    python flask_api.py
"""

from __future__ import annotations

import os
import re
import shutil
import sys
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

from langchain_community.document_loaders import PyPDFLoader, Docx2txtLoader, TextLoader
from langchain_huggingface import HuggingFaceEmbeddings
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_core.runnables import RunnableLambda, RunnableParallel, RunnablePassthrough
from langchain_core.prompts import ChatPromptTemplate
from langchain_core.output_parsers import StrOutputParser
from langchain_core.documents import Document
from langchain_google_genai import ChatGoogleGenerativeAI

import config
from rebuild_faiss import rebuild as rebuild_faiss_index, get_all_active_chunks

import threading
import json as _json

app = Flask(__name__)

# ── Globals — loaded once on startup ──────────────────────────────────────────
_embeddings  = None
_vectorstore = None
_rag_chain   = None
_retriever   = None

# ── Rebuild debounce state ────────────────────────────────────────────────────
REBUILD_DEBOUNCE_SECONDS = 60

_rebuild_lock      = threading.Lock()
_rebuild_timer     = None
_rebuild_running   = False
_rebuild_requested_again = False


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


def _persist_chunks(document_id: int, file_path: Path, chunks: list[Document]) -> None:
    """Save chunks to MySQL and mark document as indexed."""
    conn = _get_db()
    try:
        with conn.cursor() as cur:
            cur.execute("DELETE FROM chunks WHERE document_id = %s", (document_id,))

            cur.executemany(
                "INSERT INTO chunks (document_id, chunk_text) VALUES (%s, %s)",
                [(document_id, chunk.page_content) for chunk in chunks],
            )

            cur.execute(
                "UPDATE documents SET status = 'indexed', path_file = %s WHERE document_id = %s",
                (str(file_path), document_id),
            )
        conn.commit()
    except Exception:
        conn.rollback()
        try:
            with conn.cursor() as cur:
                cur.execute(
                    "UPDATE documents SET status = 'failed' WHERE document_id = %s",
                    (document_id,),
                )
            conn.commit()
        except Exception:
            pass
        raise
    finally:
        conn.close()


# ── Ingest helpers ─────────────────────────────────────────────────────────────

def _load_file(file_path: Path) -> list[Document]:
    suffix = file_path.suffix.lower()
    if suffix == ".pdf":
        loader = PyPDFLoader(str(file_path))
    elif suffix in (".docx", ".doc"):
        loader = Docx2txtLoader(str(file_path))
    elif suffix == ".txt":
        try:
            loader = TextLoader(str(file_path), encoding="utf-8")
        except Exception:
            loader = TextLoader(str(file_path), encoding="latin-1")
    else:
        raise ValueError(f"Unsupported file type: {suffix}")

    docs = loader.load()
    for doc in docs:
        doc.metadata.setdefault("source_file", file_path.name)
    return docs


def _clean_docs(docs: list[Document]) -> list[Document]:
    cleaned = []
    for doc in docs:
        text = re.sub(r"\n{3,}", "\n\n", doc.page_content)
        text = re.sub(r"[ \t]{2,}", " ", text).strip()
        if len(text) > 50:
            cleaned.append(Document(page_content=text, metadata=doc.metadata))
    return cleaned


def _chunk_docs(docs: list[Document]) -> list[Document]:
    splitter = RecursiveCharacterTextSplitter(
        chunk_size=config.CHUNK_SIZE,
        chunk_overlap=config.CHUNK_OVERLAP,
        separators=["\n\n", "\n", ". ", " ", ""],
        length_function=len,
        add_start_index=True,
    )
    return splitter.split_documents(docs)


def _upsert_faiss(chunks: list[Document], document_id: int) -> int:
    """Add chunks to FAISS index. Returns number of chunks added."""
    global _vectorstore

    for chunk in chunks:
        chunk.metadata["document_id"] = document_id

    if _vectorstore is not None:
        _vectorstore.add_documents(chunks)
    else:
        _vectorstore = FAISS.from_documents(chunks, _embeddings)

    _vectorstore.save_local(config.FAISS_INDEX_PATH)
    return len(chunks)


# ── RAG helpers ────────────────────────────────────────────────────────────────

def _format_context(docs: list[Document]) -> str:
    parts = []
    for i, doc in enumerate(docs, 1):
        source = doc.metadata.get("source_file", "Dokumen tidak diketahui")
        doc_name = source.rsplit(".", 1)[0].replace("_", " ")
        page = doc.metadata.get("page", "")
        page_str = f", hal. {int(page) + 1}" if page != "" else ""
        header = f"[Sumber: {doc_name}{page_str}]"
        parts.append(f"{header}\n{doc.page_content}")
    return "\n\n---\n\n".join(parts)


def _build_rag_chain():
    global _rag_chain, _retriever

    if _vectorstore is None:
        _rag_chain = None
        _retriever = None
        return

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


def _load_components():
    """Load embedding model + FAISS index (if exists) once on startup."""
    global _embeddings, _vectorstore

    print("Loading embedding model...", flush=True)
    _embeddings = HuggingFaceEmbeddings(
        model_name=config.EMBEDDING_MODEL,
        model_kwargs={"device": config.EMBEDDING_DEVICE},
        encode_kwargs={"normalize_embeddings": True},
    )

    if Path(config.FAISS_INDEX_PATH).exists():
        print("Loading FAISS index...", flush=True)
        _vectorstore = FAISS.load_local(
            config.FAISS_INDEX_PATH,
            _embeddings,
            allow_dangerous_deserialization=True,
        )
        _build_rag_chain()
        print("RAG server ready.", flush=True)
    else:
        print("No FAISS index found yet — ingest a document first.", flush=True)
        print("RAG server ready (query disabled until first ingest).", flush=True)


# ── Routes ─────────────────────────────────────────────────────────────────────

@app.route("/health", methods=["GET"])
def health():
    return jsonify({
        "status":       "ok",
        "model_loaded": _embeddings  is not None,
        "index_loaded": _vectorstore is not None,
        "query_ready":  _rag_chain   is not None,
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
        return jsonify({"success": False, "error": "No documents indexed yet. Please upload a document first."}), 503

    try:
        start     = time.time()
        answer    = _rag_chain.invoke(question)
        elapsed   = round((time.time() - start) * 1000)
        answer_id = _save_answer(query_id, answer)
        _update_query_status(query_id, "answered", elapsed)

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


@app.route("/ingest", methods=["POST"])
def ingest():
    """
    Ingest a document: load → clean → chunk → MySQL → FAISS.
    Body: { "file_path": "...", "document_id": N, "user_id": N }
    """
    data        = request.get_json(force=True)
    file_path   = data.get("file_path", "").strip()
    document_id = data.get("document_id")
    user_id     = data.get("user_id", 1)

    if not file_path:
        return jsonify({"success": False, "error": "file_path is required"}), 400
    if not document_id:
        return jsonify({"success": False, "error": "document_id is required"}), 400
    if _embeddings is None:
        return jsonify({"success": False, "error": "Embedding model not loaded"}), 503

    path = Path(file_path)
    if not path.exists():
        return jsonify({"success": False, "error": f"File not found: {file_path}"}), 400

    try:
        start = time.time()

        dest = config.DOCUMENTS_DIR / path.name
        if dest.resolve() != path.resolve():
            shutil.copy2(path, dest)

        docs   = _load_file(dest)
        docs   = _clean_docs(docs)
        chunks = _chunk_docs(docs)

        _persist_chunks(document_id, dest, chunks)

        chunks_added = _upsert_faiss(chunks, document_id)

        _build_rag_chain()

        elapsed = round((time.time() - start) * 1000)

        return jsonify({
            "success":      True,
            "document_id":  document_id,
            "chunks_added": chunks_added,
            "elapsed_ms":   elapsed,
        })

    except Exception as exc:
        return jsonify({"success": False, "error": str(exc)}), 500


def _execute_rebuild() -> None:
    """
    Actually perform the FAISS rebuild. Runs in a background thread.
    Handles the 'rebuild requested again while running' case by looping.
    """
    global _vectorstore, _rebuild_running, _rebuild_requested_again

    with _rebuild_lock:
        _rebuild_running = True
        _rebuild_requested_again = False

    while True:
        print("[rebuild] Starting FAISS rebuild...", flush=True)
        try:
            result = rebuild_faiss_index(_embeddings)

            if result["status"] == "index_cleared":
                _vectorstore = None
            else:
                _vectorstore = FAISS.load_local(
                    config.FAISS_INDEX_PATH,
                    _embeddings,
                    allow_dangerous_deserialization=True,
                )
            _build_rag_chain()
            print(f"[rebuild] Done: {result}", flush=True)

        except Exception as exc:
            print(f"[rebuild] FAILED: {exc}", flush=True)

        with _rebuild_lock:
            if _rebuild_requested_again:
                _rebuild_requested_again = False
                continue
            else:
                _rebuild_running = False
                break


def schedule_rebuild() -> str:
    """
    Called on every document delete. Implements the debounce + queue logic:
    """
    global _rebuild_timer, _rebuild_requested_again

    with _rebuild_lock:
        if _rebuild_running:
            _rebuild_requested_again = True
            return "queued_after_current_rebuild"

        if _rebuild_timer is not None:
            _rebuild_timer.cancel()

        _rebuild_timer = threading.Timer(
            REBUILD_DEBOUNCE_SECONDS,
            lambda: threading.Thread(target=_execute_rebuild, daemon=True).start(),
        )
        _rebuild_timer.daemon = True
        _rebuild_timer.start()

        return "scheduled"


@app.route("/reload", methods=["POST"])
def reload_index():
    """
    Reload FAISS index from disk without restarting the server.
    """
    global _vectorstore
    try:
        if not Path(config.FAISS_INDEX_PATH).exists():
            _vectorstore = None
            _build_rag_chain()
            return jsonify({"success": True, "message": "No index on disk — query disabled."})

        _vectorstore = FAISS.load_local(
            config.FAISS_INDEX_PATH,
            _embeddings,
            allow_dangerous_deserialization=True,
        )
        _build_rag_chain()
        return jsonify({"success": True, "message": "FAISS index reloaded."})
    except Exception as exc:
        return jsonify({"success": False, "error": str(exc)}), 500


@app.route("/rebuild-index", methods=["POST"])
def rebuild_index():
    """
    Schedule a debounced FAISS rebuild after a document deletion.
    """
    data      = request.get_json(silent=True) or {}
    immediate = bool(data.get("immediate", False))

    if immediate:
        try:
            result = rebuild_faiss_index(_embeddings)
            global _vectorstore
            if result["status"] == "index_cleared":
                _vectorstore = None
            else:
                _vectorstore = FAISS.load_local(
                    config.FAISS_INDEX_PATH, _embeddings,
                    allow_dangerous_deserialization=True,
                )
            _build_rag_chain()
            return jsonify({"success": True, "mode": "immediate", **result})
        except Exception as exc:
            return jsonify({"success": False, "error": str(exc)}), 500

    status = schedule_rebuild()
    return jsonify({
        "success": True,
        "mode":    "debounced",
        "status":  status,
        "debounce_seconds": REBUILD_DEBOUNCE_SECONDS,
    })


@app.route("/rebuild-status", methods=["GET"])
def rebuild_status():
    """Inspect current rebuild scheduler state — useful for debugging/UI."""
    with _rebuild_lock:
        return jsonify({
            "rebuild_running":   _rebuild_running,
            "rebuild_pending":   _rebuild_timer is not None and not _rebuild_running,
            "queued_after_current": _rebuild_requested_again,
        })


# ── Entry point ────────────────────────────────────────────────────────────────
# RENDER FIX: Render scans for an open port within ~60s of startup.
# The embedding model takes 60-120s to load, so if we block on
# _load_components() before the server binds, Render never sees the port
# and kills the process with "No open ports detected".
#
# Solution: start the server FIRST (port binds immediately), then load
# the model in a background thread. During model loading, /health returns
# {"model_loaded": false} and /ask returns 503 — both are handled
# gracefully by the Laravel DashboardController.

if not config.GEMINI_API_KEY:
    print("ERROR: GEMINI_API_KEY not set in .env", flush=True)
    raise RuntimeError("GEMINI_API_KEY is required")

def _load_in_background():
    """Load model in a thread so Gunicorn can bind the port first."""
    print("[startup] Loading model in background thread...", flush=True)
    _load_components()
    print("[startup] Model ready.", flush=True)

# When running under Gunicorn with preload_app=True, the module is
# imported in the master process before workers fork. We start the
# background thread here so the model loads once and is shared.
import threading as _threading
_bg = _threading.Thread(target=_load_in_background, daemon=True)
_bg.start()

if __name__ == "__main__":
    # Direct python run — wait for model before accepting requests
    _bg.join()
    app.run(host="0.0.0.0", port=int(os.environ.get("PORT", 5001)),
            debug=False, threaded=True)