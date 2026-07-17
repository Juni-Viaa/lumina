"""
query_api.py — Laravel-callable wrapper around the RAG pipeline.
"""

from __future__ import annotations

import argparse
import json
import os
import sys
import time
from pathlib import Path

# ── Force UTF-8 before anything touches stdio ─────────────────────────────────
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

# ── Imports ────────────────────────────────────────────────────────────────────
import pymysql
import pymysql.cursors

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


# ── DB helpers ─────────────────────────────────────────────────────────────────

def _get_db() -> pymysql.connections.Connection:
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

            cur.execute(
                "SELECT user_id FROM queries WHERE query_id = %s",
                (query_id,),
            )
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


def _update_query_status(query_id: int, status: str, response_time_ms: int | None = None) -> None:
    conn = _get_db()
    try:
        with conn.cursor() as cur:
            if response_time_ms is not None:
                cur.execute(
                    "UPDATE queries SET status = %s, response_time_ms = %s WHERE query_id = %s",
                    (status, response_time_ms, query_id),
                )
            else:
                cur.execute(
                    "UPDATE queries SET status = %s WHERE query_id = %s",
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


def _build_chain(vectorstore, top_k: int):
    retriever = vectorstore.as_retriever(
        search_type="similarity",
        search_kwargs={"k": top_k},
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

    chain = (
        RunnableParallel(
            context=retriever | RunnableLambda(_format_context),
            question=RunnablePassthrough(),
        )
        | prompt
        | llm
        | StrOutputParser()
    )

    return chain, retriever


def _get_sources(retriever, question: str) -> list[dict]:
    try:
        docs = retriever.vectorstore.similarity_search_with_score(
            question, k=retriever.search_kwargs.get("k", config.TOP_K)
        )
        return [
            {
                "source":  d.metadata.get("source_file", "unknown"),
                "page":    d.metadata.get("page", None),
                "score":   round(float(s), 4),
                "excerpt": d.page_content[:200],
            }
            for d, s in docs
        ]
    except Exception:
        return []


# ── Main ───────────────────────────────────────────────────────────────────────

def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--question", required=True)
    parser.add_argument("--query-id", type=int, required=True)
    parser.add_argument("--top-k",    type=int, default=config.TOP_K)
    args = parser.parse_args()

    query_id = args.query_id
    question = args.question

    def fail(msg: str) -> None:
        print(json.dumps({"success": False, "query_id": query_id, "error": msg}, ensure_ascii=False))
        sys.exit(0)

    if not Path(config.FAISS_INDEX_PATH).exists():
        fail("FAISS index not found. Please ingest at least one document first.")

    if not config.GEMINI_API_KEY:
        fail("GEMINI_API_KEY is not set. Add it to your .env file.")

    try:
        start = time.time()

        embeddings = HuggingFaceEmbeddings(
            model_name=config.EMBEDDING_MODEL,
            model_kwargs={"device": config.EMBEDDING_DEVICE},
            encode_kwargs={"normalize_embeddings": True},
        )

        vectorstore = FAISS.load_local(
            config.FAISS_INDEX_PATH,
            embeddings,
            allow_dangerous_deserialization=True,
        )

        chain, retriever = _build_chain(vectorstore, args.top_k)
        answer           = chain.invoke(question)
        elapsed          = round((time.time() - start) * 1000)
        sources          = _get_sources(retriever, question)
        answer_id        = _save_answer(query_id, answer)

        _update_query_status(query_id, "answered", elapsed)

        print(json.dumps({
            "success":          True,
            "query_id":         query_id,
            "answer_id":        answer_id,
            "answer":           answer,
            "response_time_ms": elapsed,
            "sources":          sources,
        }, ensure_ascii=False))

    except Exception as exc:
        _update_query_status(query_id, "failed")
        print(json.dumps({
            "success":  False,
            "query_id": query_id,
            "error":    str(exc),
        }, ensure_ascii=False))


if __name__ == "__main__":
    main()