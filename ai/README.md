# RAG System — multilingual-e5 + FAISS + Gemini

A local Retrieval-Augmented Generation (RAG) system that runs entirely in VS Code.  
Built with **LangChain**, **multilingual-e5-large** embeddings, **FAISS** vector store, and **Gemini Flash** as the LLM generator.

---

## Project Structure

```
rag_system/
├── documents/          ← uploaded PDF/DOCX files land here
├── vectorstore/        ← FAISS index (auto-created)
├── config.py           ← all tuneable parameters
├── ingest.py           ← upload → preprocess → chunk → embed
├── query.py            ← retrieve → generate → answer
├── requirements.txt
├── .env.example
└── README.md
```

---

## Quick Start

### 1. Clone / open the folder in VS Code

```bash
cd rag_system
```

### 2. Create a virtual environment

```bash
python -m venv .venv
# Windows
.venv\Scripts\activate
# macOS / Linux
source .venv/bin/activate
```

### 3. Install dependencies

```bash
pip install -r requirements.txt
```

> **Note:** The first run downloads `multilingual-e5-large` (~1.1 GB) from HuggingFace automatically.

### 4. Set your Gemini API key

```bash
cp .env.example .env
# then edit .env and paste your key:
# GEMINI_API_KEY=AIza...
```

Get a free key at <https://aistudio.google.com>.

---

## Usage

### Ingest a document

```bash
# Single PDF
python ingest.py path/to/document.pdf

# Single DOCX
python ingest.py path/to/report.docx

# Multiple files at once
python ingest.py file1.pdf file2.docx

# Entire folder
python ingest.py path/to/folder/
```

The pipeline runs 5 steps automatically:
1. **Copy** the file into `documents/`
2. **Load** text from PDF (page-by-page) or DOCX
3. **Clean** whitespace / empty pages
4. **Chunk** into 512-character overlapping segments
5. **Embed** with `multilingual-e5-large` and **upsert** into FAISS

You can ingest more files later — they are **merged** into the existing index.

---

### Query the system

**Single question (CLI):**
```bash
python query.py "What is the main topic of this document?"
```

**Interactive REPL (recommended):**
```bash
python query.py
```

Then type questions in any language (Indonesian, English, etc.):
```
❯ Apa itu machine learning?
❯ Jelaskan konsep RAG dalam 3 kalimat
❯ What are the key findings?
❯ sources       ← list all indexed documents
❯ quit          ← exit
```

**Override top-K at runtime:**
```bash
python query.py --top-k 8 "Summarize the methodology"
```

---

## Configuration

Edit `config.py` to tune the system:

| Parameter | Default | Description |
|---|---|---|
| `EMBEDDING_MODEL` | `intfloat/multilingual-e5-large` | Supports 100+ languages |
| `EMBEDDING_DEVICE` | `cpu` | Change to `cuda` for GPU |
| `CHUNK_SIZE` | `512` | Characters per chunk |
| `CHUNK_OVERLAP` | `64` | Overlap between chunks |
| `TOP_K` | `5` | Chunks retrieved per query |
| `GEMINI_MODEL` | `gemini-2.0-flash` | LLM generator |
| `GEMINI_TEMPERATURE` | `0.2` | Lower = more factual |
| `GEMINI_MAX_TOKENS` | `1024` | Max answer length |

---

## How it works

```
┌─────────────┐    ingest.py     ┌──────────────────────────────┐
│  PDF / DOCX │ ──────────────▶  │  1. Load  (PyPDF / Docx2txt) │
└─────────────┘                  │  2. Clean (whitespace)        │
                                 │  3. Chunk (512 chars, ov=64)  │
                                 │  4. Embed (multilingual-e5)   │
                                 │  5. Store (FAISS index)       │
                                 └──────────────────────────────┘

┌───────────┐     query.py      ┌───────────────────────────────┐
│   Query   │ ───────────────▶  │  1. Embed query (e5)          │
│ (any lang)│                   │  2. Similarity search (FAISS) │
└───────────┘                   │  3. Format context            │
                                │  4. Call Gemini Flash API     │
     ┌──────────────────────────│  5. Return answer + sources   │
     ▼                          └───────────────────────────────┘
┌──────────┐
│  Answer  │
│ + Source │
│   Refs   │
└──────────┘
```

---

## Tips

- **Multilingual:** Both ingestion and querying support all languages `multilingual-e5-large` covers (100+, including Bahasa Indonesia).
- **Incremental ingestion:** Run `ingest.py` multiple times — new chunks are merged into the existing FAISS index without re-processing old files.
- **GPU acceleration:** Set `EMBEDDING_DEVICE = "cuda"` in `config.py` if you have a CUDA-capable GPU for much faster embedding.
- **Larger context:** Increase `TOP_K` or `GEMINI_MAX_TOKENS` for more detailed answers.
