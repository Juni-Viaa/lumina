<div align="center">

# 🔍 Lumina
### RAG-Based Intelligent Document Analysis System

*Sistem analisis dokumen cerdas berbasis Retrieval-Augmented Generation*

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![Python](https://img.shields.io/badge/Python-3.11-3776AB?style=flat&logo=python&logoColor=white)](https://python.org)
[![Flask](https://img.shields.io/badge/Flask-3.x-000000?style=flat&logo=flask&logoColor=white)](https://flask.palletsprojects.com)
[![LangChain](https://img.shields.io/badge/LangChain-0.3-1C3C3C?style=flat)](https://langchain.com)
[![FAISS](https://img.shields.io/badge/FAISS-Vector_DB-0464A4?style=flat)](https://faiss.ai)
[![Gemini](https://img.shields.io/badge/Gemini-3.1_Flash--Lite-4285F4?style=flat&logo=google&logoColor=white)](https://deepmind.google/models/gemini/flash-lite/)

**Project:** IF-4MD-08 · **Institution:** Politeknik Negeri Batam · **Year:** 2026

</div>

---

## 📖 Overview

**Lumina** is an intelligent document analysis system that enables users to ask questions in natural language and receive accurate, contextual answers sourced directly from uploaded documents. Built on the **Retrieval-Augmented Generation (RAG)** approach, it combines semantic vector search with a Large Language Model (LLM) to overcome the limitations of conventional keyword-based search.

### The Problem It Solves

Traditional document search requires users to:
- Manually open and read documents one by one
- Rely on keyword matching that doesn't understand context
- Spend excessive time finding relevant information in large document collections

### The Solution

Lumina processes uploaded documents into a semantic vector index, enabling users to ask questions like *"What are the graduation requirements for semester 4?"* and receive precise, referenced answers — without reading the entire document.

---

## ✨ Features

| Feature | Description |
|---|---|
| 📤 **Document Upload** | Upload PDF and DOCX documents via web UI |
| 🔄 **Automatic Ingestion** | Documents are automatically chunked, embedded, and indexed into FAISS |
| 💬 **Natural Language Query** | Ask questions in Indonesian or English via chat interface |
| 🤖 **RAG-Powered Answers** | Gemini LLM generates answers grounded in your documents |
| 📚 **Source Attribution** | Every answer includes document source, page number, and similarity score |
| 🗂️ **Query History** | All questions and answers are saved and browsable |
| 🔐 **Role-Based Access** | Separate access control for Dosen (lecturer) and Mahasiswa (student) |
| 🗑️ **Smart Delete** | Deleting a document removes its chunks from MySQL and rebuilds FAISS with debouncing |

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        User Browser                          │
│              Alpine.js + Tailwind CSS Frontend               │
└──────────────────────┬──────────────────────────────────────┘
                       │ HTTP
┌──────────────────────▼──────────────────────────────────────┐
│                   Laravel 12 (PHP)                           │
│    UploadController │ DashboardController │ Auth             │
└──────────┬──────────────────────┬──────────────────────────-┘
           │ Store file           │ POST /ask or /ingest
           │                      │
    ┌──────▼──────┐    ┌──────────▼────────────────────────┐
    │  MySQL DB   │    │     Flask RAG Server (Port 5001)   │
    │  documents  │    │  Loaded once, stays in memory:     │
    │  chunks     │    │  • HuggingFace Embeddings (E5-L)   │
    │  queries    │    │  • FAISS Vector Index              │
    │  answers    │    │  • LangChain RAG Chain             │
    │  histories  │    │  • Gemini 3.1 Flash Lite LLM       │
    └─────────────┘    └───────────────────────────────────-┘
```

### RAG Pipeline

```
User Question
     │
     ▼
Vector Embedding (multilingual-e5-large)
     │
     ▼
Similarity Search on FAISS Index
     │
     ▼
Top-K Relevant Chunks Retrieved
     │
     ▼
Prompt Construction (System Prompt + Context + Question)
     │
     ▼
Gemini 3.1 Flash Lite (LLM Generation)
     │
     ▼
Answer + Source References → Saved to MySQL → Displayed to User
```

### Document Ingestion Pipeline

```
Upload (PDF / DOCX / TXT)
     │
     ├─ Copy to ai/documents/
     │
     ├─ Document Parsing (PyPDF / Docx2txt / TextLoader)
     │
     ├─ Text Cleaning (remove excess whitespace, blank pages)
     │
     ├─ Chunking (RecursiveCharacterTextSplitter, 2560 chars, 256 overlap)
     │
     ├─ Persist Chunks → MySQL chunks table
     │
     └─ Embed + Upsert → FAISS vectorstore/faiss_index/
```

---

## 🛠️ Tech Stack

### Backend
| Component | Technology |
|---|---|
| Web Framework | Laravel 12 (PHP 8.2) |
| AI Server | Flask 3.x (Python 3.11) |
| RAG Framework | LangChain 0.3 |
| Embedding Model | `intfloat/multilingual-e5-large` (HuggingFace) |
| Vector Database | FAISS (Facebook AI Similarity Search) |
| LLM | Google Gemini 3.1 Flash Lite |
| Database | MySQL (XAMPP / MariaDB) |

### Frontend
| Component | Technology |
|---|---|
| CSS Framework | Tailwind CSS |
| Interactivity | Alpine.js |
| Design Style | Glassmorphism |

---

## 📋 Prerequisites

Before running Lumina, ensure you have the following installed:

- **PHP** 8.2+ with extensions: `pdo_mysql`, `fileinfo`, `mbstring`, `openssl`
- **Composer** 2.x
- **Node.js** 18+ and **npm**
- **Python** 3.11 (⚠️ Not 3.12+ — LangChain incompatibility)
- **MySQL** (via XAMPP or standalone)
- **Git**
- **Google Gemini API Key** — get one free at [aistudio.google.com](https://aistudio.google.com)

---

## 🚀 Installation & Setup

### Step 1 — Clone the repository

```bash
git clone https://github.com/your-username/lumina.git
cd lumina
```

### Step 2 — Install PHP dependencies

```bash
composer install
```

### Step 3 — Install Node dependencies and build assets

```bash
npm install
npm run build
```

### Step 4 — Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure the following:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lumina
DB_USERNAME=root
DB_PASSWORD=

# Gemini API
GEMINI_API_KEY=your_gemini_api_key_here

# RAG Server paths (update to your actual paths)
RAG_PYTHON_PATH=C:\laravel\lumina\ai\.venv\Scripts\python.exe
RAG_INGEST_SCRIPT=C:\laravel\lumina\ai\ingest.py
```

### Step 5 — Set up the database

Create a MySQL database named `lumina`, then run migrations:

```bash
php artisan migrate
```

### Step 6 — Set up the Python environment

```bash
cd ai

# Create virtual environment with Python 3.11 specifically
py -3.11 -m venv .venv          # Windows
python3.11 -m venv .venv        # Linux/macOS

# Activate the virtual environment
.venv\Scripts\activate          # Windows
source .venv/bin/activate       # Linux/macOS

# Install Python dependencies
pip install -r requirements.txt
```

### Step 7 — Create `config/rag.php`

Create the file `config/rag.php` in your Laravel project:

```php
<?php
return [
    'python_path'   => env('RAG_PYTHON_PATH', 'python'),
    'ingest_script' => env('RAG_INGEST_SCRIPT', base_path('ai/ingest.py')),
];
```

---

## ▶️ Running the Application

Lumina requires **two servers** running simultaneously — the Laravel web server and the Flask RAG server.

### Terminal 1 — Start the Flask RAG Server

```bash
cd ai
.venv\Scripts\python.exe rag_server.py       # Windows
source .venv/bin/activate && python rag_server.py  # Linux/macOS
```

You should see:
```
Loading embedding model...
Loading FAISS index...
RAG server ready.
 * Running on http://127.0.0.1:5001
```

> ⚠️ **Keep this terminal open.** The embedding model (`multilingual-e5-large`, ~1.3 GB) loads once and stays in memory. If you close this terminal, the query and upload features will stop working.

> 💡 If no FAISS index exists yet (first run), you'll see:
> `No FAISS index found yet — ingest a document first.`
> This is normal. Upload a document first, then queries will work.

### Terminal 2 — Start the Laravel Dev Server

```bash
php artisan serve
```

Access the application at **http://127.0.0.1:8000**

---

## 📖 How to Use

### 1. Register an Account

Navigate to `/register` and create an account. Roles:
- **Dosen** — can upload, manage, and delete documents
- **Mahasiswa** — can only ask questions

### 2. Upload Documents (Dosen only)

1. Click **"Upload Dokumen"** in the sidebar
2. Switch to the **Upload** tab
3. Drag & drop or click to select a PDF, DOCX, or TXT file (max 100 MB)
4. Click **"Kirim"**
5. Watch the process tracker — steps animate while Flask ingests the document
6. When status shows **"indexed"** in the Documents tab, the document is ready to query

### 3. Ask Questions

1. Click **"New Chat"** in the sidebar
2. Type your question in the input box (Indonesian or English)
3. Press **Enter** to send, **Shift+Enter** for a new line
4. Lumina will search the document index and generate a contextual answer
5. The answer includes **source references** showing which document and page was used

### 4. View History

- The sidebar shows your **5 most recent** queries
- Click **"Lihat semua"** to see the full history page at `/history`
- Click any history item to re-read the full question and answer

### 5. Manage Documents (Dosen only)

1. Go to **"Upload Dokumen"** → **"Documents"** tab
2. View all uploaded documents with their status (Processing / Indexed / Failed)
3. Click **"Delete"** to remove a document — this also removes its chunks from MySQL and schedules a FAISS index rebuild (60-second debounce, so rapid deletions trigger only one rebuild)

---

## ⚙️ Configuration Reference

### `ai/config.py`

| Parameter | Default | Description |
|---|---|---|
| `EMBEDDING_MODEL` | `intfloat/multilingual-e5-large` | HuggingFace embedding model |
| `EMBEDDING_DEVICE` | `cpu` | Change to `cuda` for GPU |
| `CHUNK_SIZE` | `2560` | Characters per chunk |
| `CHUNK_OVERLAP` | `256` | Overlap between chunks (~10%) |
| `TOP_K` | `5` | Number of chunks retrieved per query |
| `GEMINI_MODEL` | `gemini-3.1-flash-lite` | Gemini model name |
| `GEMINI_TEMPERATURE` | `0.2` | LLM creativity (0 = deterministic) |
| `GEMINI_MAX_TOKENS` | `1024` | Max answer length |

### Flask Server Endpoints

| Endpoint | Method | Description |
|---|---|---|
| `/health` | GET | Check server and model status |
| `/ask` | POST | Run a RAG query |
| `/ingest` | POST | Ingest a document (called by Laravel) |
| `/rebuild-index` | POST | Schedule/trigger FAISS rebuild |
| `/rebuild-status` | GET | Check rebuild scheduler state |
| `/reload` | POST | Reload FAISS from disk |

---

## 🗄️ Database Schema

```
documents   — uploaded file metadata (document_id, user_id, document_name, path_file, file_type, size, status)
chunks      — text chunks from documents (chunk_id, document_id, chunk_text)
queries     — user questions (query_id, user_id, query_text, query_title, status, response_time_ms)
answers     — AI-generated answers (answer_id, query_id, answer_text)
histories   — links query ↔ answer per user (history_id, user_id, query_id, answer_id)
users       — authentication (id, name, email, password, role)
```

---

## 🔧 Troubleshooting

### "RAG server is not running"
Start the Flask server: `python ai/rag_server.py`

### "FAISS index not found"
Upload at least one document first before querying.

### "GEMINI_API_KEY is not set"
Add `GEMINI_API_KEY=your_key` to your `.env` file in the Laravel root.

### Python import error / asyncio crash
You are likely using Python 3.12+. Recreate the venv with Python 3.11:
```bash
py -3.11 -m venv ai/.venv
ai/.venv/Scripts/python.exe -m pip install -r ai/requirements.txt
```

### Document stuck on "Processing"
Check Flask server logs and `storage/logs/ingest_err.log` for errors.

### Slow first query after server start
The embedding model takes 20-40 seconds to load on first startup. Subsequent queries respond in 2-5 seconds.

---

## 👥 Team

| Name | NIM | Role |
|---|---|---|
| Junior Dirgantara Betan | 3312411002 | Document Management, RAG Integration, Frontend |
| Ferdian Baihaqi | 3312411029 | Authentication, User Management, RAG Processing |

**Supervisor:** Miratul Khusna Mufida, S.ST, M.Sc, PhD
**Institution:** Politeknik Negeri Batam — Program Studi Teknik Informatika

---

## 📄 License

This project was developed as part of the Project-Based Learning (PBL) program at Politeknik Negeri Batam (IF-4MD-08, 2026).

---

<div align="center">
Made with ❤️ by Team IF-4MD-08 · Politeknik Negeri Batam 2026
</div>
