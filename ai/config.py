"""
config.py — Central configuration for the RAG system.
 
All tuneable parameters live here. Both ingest.py and query_api.py import
this module; changing a value here applies everywhere.
"""
 
import os
from pathlib import Path
from dotenv import load_dotenv
 
# ── Load .env ──────────────────────────────────────────────────────────────────
_root_env  = Path(__file__).parent.parent / ".env"
_local_env = Path(__file__).parent / ".env"
 
if _root_env.exists():
    load_dotenv(dotenv_path=str(_root_env), override=True)
elif _local_env.exists():
    load_dotenv(dotenv_path=str(_local_env), override=True)
 
# ── Paths ──────────────────────────────────────────────────────────────────────
BASE_DIR        = Path(__file__).parent
DOCUMENTS_DIR   = BASE_DIR / "documents"
VECTORSTORE_DIR = BASE_DIR / "vectorstore"
 
DOCUMENTS_DIR.mkdir(exist_ok=True)
VECTORSTORE_DIR.mkdir(exist_ok=True)
 
# ── API Keys ───────────────────────────────────────────────────────────────────
GEMINI_API_KEY: str = os.getenv("GEMINI_API_KEY", "")
 
# ── MySQL ──────────────────────────────────────────────────────────────────────
DB_HOST     = os.getenv("DB_HOST",     "127.0.0.1")
DB_PORT     = int(os.getenv("DB_PORT", "3306"))
DB_USER     = os.getenv("DB_USERNAME", "root")
DB_PASSWORD = os.getenv("DB_PASSWORD", "")
DB_NAME     = os.getenv("DB_DATABASE", "lumina")

# ── Embedding ──────────────────────────────────────────────────────────────────
EMBEDDING_MODEL  = "intfloat/multilingual-e5-large"
EMBEDDING_DEVICE = "cpu"

# ── Chunking ───────────────────────────────────────────────────────────────────
CHUNK_SIZE    = 2560
CHUNK_OVERLAP = 256

# ── FAISS index ────────────────────────────────────────────────────────────────
FAISS_INDEX_PATH = str(VECTORSTORE_DIR / "faiss_index")

# ── Retrieval ──────────────────────────────────────────────────────────────────
TOP_K = 5

# ── Gemini LLM ─────────────────────────────────────────────────────────────────
GEMINI_MODEL       = "gemini-3.1-flash-lite"
GEMINI_TEMPERATURE = 0.2
GEMINI_MAX_TOKENS  = 1024

# ── RAG system prompt ──────────────────────────────────────────────────────────
RAG_SYSTEM_PROMPT = """\
You are a precise, helpful multilingual assistant.
Answer the user's question **only** using the provided context excerpts.
If the answer cannot be found in the context, state that clearly — do not guess.
When possible, mention which document section supports your answer.

Context:
{context}
"""
