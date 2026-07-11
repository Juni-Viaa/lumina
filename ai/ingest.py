"""
ingest.py — Upload, preprocess, chunk, and embed PDF/DOCX/TXT documents.
           Persists document metadata and every chunk to MySQL (via PyMySQL).
"""

from __future__ import annotations

import re
import shutil
import argparse
import sys
from pathlib import Path
from typing import Any

# ── Force UTF-8 before anything touches stdio ─────────────────────────────────
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

# ── Load Laravel root .env (one level above ai/) ──────────────────────────────
_laravel_env = Path(__file__).parent.parent / ".env"
if _laravel_env.exists():
    from dotenv import load_dotenv
    load_dotenv(dotenv_path=str(_laravel_env), override=True)

import pymysql
import pymysql.cursors
from rich.console import Console
from rich.progress import Progress, SpinnerColumn, TextColumn

from langchain_core.runnables import RunnableLambda
from langchain_core.documents import Document
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_community.document_loaders import (
    PyPDFLoader,
    Docx2txtLoader
)
from langchain_huggingface import HuggingFaceEmbeddings

try:
    from langchain_community.vectorstores import FAISS
except Exception:
    from langchain_faiss import FAISS  # type: ignore

import config

console = Console(file=sys.stdout, highlight=False)


# ── MySQL helpers ──────────────────────────────────────────────────────────────

def _get_db_connection() -> pymysql.connections.Connection:
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


# ── Step functions ─────────────────────────────────────────────────────────────

def _copy_to_documents(payload: dict) -> dict:
    file_path: Path = payload["file_path"]
    dest = config.DOCUMENTS_DIR / file_path.name
    if dest.resolve() != file_path.resolve():
        shutil.copy2(file_path, dest)
        console.print(f"    [dim]Copied to documents/{file_path.name}[/dim]")
    else:
        console.print("    [dim]Already in documents/[/dim]")
    return {**payload, "file_path": dest}


def _load_document(payload: dict) -> dict:
    file_path: Path = payload["file_path"]
    suffix = file_path.suffix.lower()

    if suffix == ".pdf":
        loader = PyPDFLoader(str(file_path))
    elif suffix in (".docx", ".doc"):
        loader = Docx2txtLoader(str(file_path))
    else:
        raise ValueError(f"Unsupported extension '{suffix}'.")

    docs = loader.load()
    for doc in docs:
        doc.metadata.setdefault("source_file", file_path.name)

    console.print(f"    [dim]Loaded {len(docs)} page(s)/section(s)[/dim]")
    return {**payload, "docs": docs}


def _preprocess_documents(payload: dict) -> dict:
    def clean(text: str) -> str:
        text = re.sub(r"\n{3,}", "\n\n", text)
        text = re.sub(r"[ \t]{2,}", " ", text)
        return text.strip()

    cleaned = [
        Document(page_content=clean(d.page_content), metadata=d.metadata)
        for d in payload["docs"]
        if len(clean(d.page_content)) > 50
    ]
    console.print(f"    [dim]{len(cleaned)} non-empty page(s) after cleaning[/dim]")
    return {**payload, "docs": cleaned}


def _chunk_documents(payload: dict) -> dict:
    splitter = RecursiveCharacterTextSplitter(
        chunk_size=config.CHUNK_SIZE,
        chunk_overlap=config.CHUNK_OVERLAP,
        separators=["\n\n", "\n", ". ", " ", ""],
        length_function=len,
        add_start_index=True,
    )
    chunks = splitter.split_documents(payload["docs"])
    console.print(f"    [dim]{len(chunks)} chunks (size={config.CHUNK_SIZE}, overlap={config.CHUNK_OVERLAP})[/dim]")
    return {**payload, "chunks": chunks}


def _persist_to_mysql(payload: dict) -> dict:
    file_path: Path         = payload["file_path"]
    chunks: list[Document]  = payload["chunks"]
    document_id: int | None = payload.get("document_id")
    user_id: int            = payload.get("user_id", 1)

    conn = _get_db_connection()
    try:
        with conn.cursor() as cur:
            if document_id is None:
                cur.execute(
                    "INSERT INTO documents (user_id, document_name, path_file, file_type, status) "
                    "VALUES (%s, %s, %s, %s, 'processing')",
                    (user_id, file_path.name, str(file_path), file_path.suffix.lstrip(".").lower()),
                )
                document_id = cur.lastrowid
                console.print(f"    [dim]Inserted documents row id={document_id}[/dim]")
            else:
                cur.execute(
                    "UPDATE documents SET path_file = %s WHERE document_id = %s",
                    (str(file_path), document_id),
                )
                console.print(f"    [dim]Reusing documents row id={document_id}[/dim]")

            cur.execute("DELETE FROM chunks WHERE document_id = %s", (document_id,))

            chunk_rows = [(document_id, chunk.page_content) for chunk in chunks]
            cur.executemany(
                "INSERT INTO chunks (document_id, chunk_text) VALUES (%s, %s)",
                chunk_rows,
            )
            cur.execute(
                "UPDATE documents SET status = 'indexed' WHERE document_id = %s",
                (document_id,),
            )

        conn.commit()
        console.print(f"    [dim]{len(chunk_rows)} chunk(s) saved, status=indexed[/dim]")

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

    return {**payload, "document_id": document_id}


def _make_upsert_faiss_fn(embeddings: HuggingFaceEmbeddings):
    def _upsert_to_faiss(payload: dict) -> dict:
        chunks: list[Document] = payload["chunks"]
        index_path = config.FAISS_INDEX_PATH
        doc_id = payload.get("document_id")

        if doc_id is not None:
            for chunk in chunks:
                chunk.metadata["document_id"] = doc_id

        if Path(index_path).exists():
            console.print("    [dim]Merging into existing FAISS index...[/dim]")
            vectorstore = FAISS.load_local(
                index_path, embeddings, allow_dangerous_deserialization=True
            )
            vectorstore.add_documents(chunks)
        else:
            console.print("    [dim]Creating new FAISS index...[/dim]")
            vectorstore = FAISS.from_documents(chunks, embeddings)

        vectorstore.save_local(index_path)
        console.print(f"    [dim]FAISS index saved to {index_path}[/dim]")
        return {"chunks_added": len(chunks), "index_path": index_path, "document_id": doc_id}

    return _upsert_to_faiss


# ── Pipeline ───────────────────────────────────────────────────────────────────

def build_ingest_pipeline(embeddings: HuggingFaceEmbeddings):
    return (
        RunnableLambda(_copy_to_documents)
        | RunnableLambda(_load_document)
        | RunnableLambda(_preprocess_documents)
        | RunnableLambda(_chunk_documents)
        | RunnableLambda(_persist_to_mysql)
        | RunnableLambda(_make_upsert_faiss_fn(embeddings))
    )


def _load_embeddings() -> HuggingFaceEmbeddings:
    console.print(f"\n[cyan]Loading embedding model:[/cyan] {config.EMBEDDING_MODEL}")
    return HuggingFaceEmbeddings(
        model_name=config.EMBEDDING_MODEL,
        model_kwargs={"device": config.EMBEDDING_DEVICE},
        encode_kwargs={"normalize_embeddings": True},
    )


# ── CLI ────────────────────────────────────────────────────────────────────────

def main() -> None:
    parser = argparse.ArgumentParser(description="Ingest PDF/DOCX/TXT into RAG vectorstore + MySQL.")
    parser.add_argument("paths", nargs="+")
    parser.add_argument("--document-id", type=int, default=None)
    parser.add_argument("--user-id",     type=int, default=1)
    args = parser.parse_args()

    supported = {".pdf", ".docx"}
    files: list[Path] = []
    for raw in args.paths:
        p = Path(raw).expanduser().resolve()
        if p.is_dir():
            found = sorted(f for f in p.rglob("*") if f.suffix.lower() in supported)
            files.extend(found)
        elif p.is_file():
            if p.suffix.lower() in supported:
                files.append(p)
        else:
            console.print(f"[red]Path not found: {raw}[/red]")

    if not files:
        console.print("[red]No files to ingest.[/red]")
        sys.exit(1)

    console.rule("[bold blue]RAG Ingest Pipeline")
    console.print(f"[bold]{len(files)} file(s) queued[/bold]\n")

    with Progress(SpinnerColumn(), TextColumn("{task.description}"), transient=True) as p:
        p.add_task("Loading multilingual-e5-large...", total=None)
        embeddings = _load_embeddings()

    pipeline = build_ingest_pipeline(embeddings)

    success, failed = 0, 0
    for file_path in files:
        console.rule(f"[blue]{file_path.name}")
        console.print("  [bold]1/6[/bold] copy -> [bold]2/6[/bold] load -> [bold]3/6[/bold] clean -> [bold]4/6[/bold] chunk -> [bold]5/6[/bold] MySQL -> [bold]6/6[/bold] FAISS")
        try:
            result = pipeline.invoke({
                "file_path":   file_path,
                "document_id": args.document_id,
                "user_id":     args.user_id,
            })
            console.print(
                f"\n  [bold green]Done![/bold green] "
                f"{result['chunks_added']} chunks, document_id={result['document_id']}, file={file_path.name}\n"
            )
            success += 1
        except Exception as exc:
            console.print(f"\n  [bold red]Failed:[/bold red] {exc}\n")
            failed += 1

    console.rule()
    console.print(f"[bold]Summary:[/bold] [green]{success} succeeded[/green]  [dim]{failed} failed[/dim]")


if __name__ == "__main__":
    main()