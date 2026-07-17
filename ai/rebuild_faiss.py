"""
rebuild_faiss.py — Rebuild the FAISS index from chunks still in MySQL.
"""

from __future__ import annotations

import sys
from pathlib import Path

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

_root_env = Path(__file__).parent.parent / ".env"
if _root_env.exists():
    from dotenv import load_dotenv
    load_dotenv(dotenv_path=str(_root_env), override=True)

import pymysql
import pymysql.cursors

try:
    from langchain_community.vectorstores import FAISS
except Exception:
    from langchain_faiss import FAISS  # type: ignore

from langchain_core.documents import Document
from langchain_huggingface import HuggingFaceEmbeddings

import config


def get_all_active_chunks() -> list[Document]:
    """
    Fetch all chunks whose parent document is NOT soft-deleted.
    Returns LangChain Document objects with metadata.
    """
    conn = pymysql.connect(
        host=config.DB_HOST,
        port=config.DB_PORT,
        user=config.DB_USER,
        password=config.DB_PASSWORD,
        database=config.DB_NAME,
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=True,
    )
    try:
        with conn.cursor() as cur:
            cur.execute("""
                SELECT
                    c.chunk_id,
                    c.document_id,
                    c.chunk_text,
                    d.document_name
                FROM chunks c
                INNER JOIN documents d ON d.document_id = c.document_id
                WHERE d.deleted_at IS NULL
                  AND c.deleted_at IS NULL
            """)
            rows = cur.fetchall()
    finally:
        conn.close()

    docs = []
    for row in rows:
        docs.append(Document(
            page_content=row["chunk_text"],
            metadata={
                "document_id":   row["document_id"],
                "chunk_id":      row["chunk_id"],
                "source_file":   row["document_name"],
            },
        ))
    return docs


def rebuild(embeddings: HuggingFaceEmbeddings) -> dict:
    """
    Rebuild FAISS index from all active chunks in MySQL.
    Returns a summary dict.
    """
    index_path = config.FAISS_INDEX_PATH

    chunks = get_all_active_chunks()

    if not chunks:
        import shutil
        if Path(index_path).exists():
            shutil.rmtree(index_path)
        return {"chunks_indexed": 0, "status": "index_cleared"}

    vectorstore = FAISS.from_documents(chunks, embeddings)
    vectorstore.save_local(index_path)

    return {
        "chunks_indexed": len(chunks),
        "status":         "rebuilt",
        "index_path":     index_path,
    }


if __name__ == "__main__":
    print("Loading embedding model...", flush=True)
    embeddings = HuggingFaceEmbeddings(
        model_name=config.EMBEDDING_MODEL,
        model_kwargs={"device": config.EMBEDDING_DEVICE},
        encode_kwargs={"normalize_embeddings": True},
    )
    result = rebuild(embeddings)
    print(f"Done: {result}", flush=True)