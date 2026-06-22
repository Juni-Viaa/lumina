# RAG-Based Intelligent Document Analysis System

An intelligent document analysis system based on **Retrieval-Augmented Generation (RAG)** that enables users to query information from uploaded documents using natural language. The system combines semantic search using vector databases and Large Language Models (LLMs) to generate accurate and contextual answers.

## 📖 Overview

Traditional keyword-based document search often fails to understand the context behind user questions. This project addresses that limitation by implementing a **Retrieval-Augmented Generation (RAG)** architecture that combines:

* Semantic document retrieval
* Vector embeddings
* Large Language Models (LLM)
* Context-aware answer generation

The system is designed for academic environments where users need to search information from:

* Journals
* Learning modules
* Reports
* Administrative documents
* Internal campus documents

---

# ✨ Features

## User Management

### Student

* Register account
* Login & Logout
* Change password
* Ask questions using natural language
* View query history

### Lecturer

* All student features
* Upload PDF/DOCX documents
* Manage documents (CRUD)
* Maintain knowledge base

---

# 🏗 System Architecture

The application uses a client-server architecture consisting of two main services:

```text
┌──────────────────────────┐
│      Laravel Web App     │
│──────────────────────────│
│ Authentication           │
│ User Management          │
│ Document Management      │
│ Query Interface          │
└────────────┬─────────────┘
             │ HTTP API
             ▼
┌──────────────────────────┐
│      Flask AI Server     │
│      (rag_server.py)     │
└────────────┬─────────────┘
             │
     ┌───────┼────────┐
     ▼                ▼
 ingest.py      query_api.py
(Document       Retrieval &
 Indexing)      Generation
     │                │
     ▼                ▼
 Embeddings      FAISS Search
     │                │
     └──────┬─────────┘
            ▼
      Vector Store
         (FAISS)
```

---

# 🧠 AI Pipeline

The system implements a complete Retrieval-Augmented Generation (RAG) workflow.

## 1. Document Upload

Lecturers upload documents in:

* PDF
* DOCX

formats.

---

## 2. Data Preprocessing

Uploaded documents go through several preprocessing stages:

### Document Parsing

Extract text from PDF and DOCX files.

### Text Cleaning

* Remove unnecessary characters
* Normalize spacing
* Clean formatting noise

### Content Filtering

Remove pages or sections containing insufficient information.

### Text Chunking

Split large documents into smaller chunks.

### Chunk Overlap

Preserve context between chunks by overlapping adjacent text segments.

---

## 3. Embedding Generation

Each chunk is transformed into vector representations using HuggingFace embedding models.

Models used:

```text

intfloat/multilingual-e5-large

```

---

## 4. Vector Indexing

Embeddings are stored in:

* FAISS

for semantic similarity search.

Additional metadata stored in MySQL:

* Document name
* File type
* Storage location
* Indexing status
* Chunk references

---

## 5. Retrieval Process

When a user submits a question:

### Query Embedding

The query is converted into a vector.

### Similarity Search

The system searches the vector database for semantically similar chunks.

### Top-K Retrieval

The most relevant chunks are selected.

```text
Top-K = 3
Top-K = 5
Top-K = 10
```

---

## 6. Context Construction

Retrieved chunks are combined into a structured context.

Example:

```text
Context:
[Document A - Page 3]
...

[Document B - Page 5]
...
```

---

## 7. Answer Generation

The context and user question are sent to the LLM.

The model generates:

* Contextual answers
* Source references
* Reduced hallucinations

---

# 🛠 Technology Stack

## Frontend

* Tailwind CSS
* Alpine.js

## Backend

* Laravel 12
* PHP 8+

## AI Service

* Python
* Flask
* LangChain

## Vector Database

* FAISS

## Embedding Models

* HuggingFace Embeddings
* intfloat/multilingual-e5-large

## Database

* MySQL

## LLM

* Gemini-3.1-flash-lite

---

# 📂 Project Structure

```text
lumina/
│
├── ai/                        # AI Service (Python + Flask + RAG)
│   ├── vectorstore/          # FAISS indexes
│   ├── config.py
│   ├── ingest.py             # Document preprocessing & indexing
│   ├── query_api.py          # RAG query pipeline
│   ├── rag_server.py         # Flask API server
│   ├── rebuild_faiss.py      # Rebuild vector index
│   ├── requirements.txt
│   └── README.md
│
├── app/                      # Laravel Application
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
│
├── artisan
├── composer.json
├── package.json
├── vite.config.js
├── .env
└── README.md
```

---

# 🚀 Installation Guide

## Prerequisites

Install:

* PHP 8.2+
* Composer
* Python 3.10+
* MySQL
* Git

---

# 1. Clone Repository

```bash
git clone https://github.com/Juni-Viaa/lumina.git

cd lumina
```

---

# 2. Setup Laravel Backend

```bash
cd laravel

composer install

cp .env.example .env

php artisan key:generate
```

Configure database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lumina
DB_USERNAME=root
DB_PASSWORD=
```

Run migration:

```bash
php artisan migrate
```

Create storage link:

```bash
php artisan storage:link
```

Start Laravel server:

```bash
php artisan serve
```

Default:

```text
http://127.0.0.1:8000
```

Start Nodejs:

```bash
npm run dev
```

---

# 3. Setup AI Service

Navigate to AI directory:

```bash
cd ai
```

Create virtual environment:

```bash
python -m venv .venv
```

Activate:

### Windows

```bash
.venv\Scripts\activate
```

### Linux

```bash
source .venv/bin/activate
```

Install dependencies:

```bash
pip install -r requirements.txt
```

---

# 4. Configure Environment Variables

Create:

```env
GEMINI_API_KEY=YOUR_API_KEY
RAG_PYTHON_PATH="C:\laravel\lumina\ai\.venv\Scripts\python.exe"
RAG_INGEST_SCRIPT="C:\laravel\lumina\ai\ingest.py"
```

---

# 5. Start Flask AI Server

```bash
python rag_server.py
```

Default:

```text
http://127.0.0.1:5000
```

---

# ▶ Running the System

Ensure both services are running:

### Terminal 1

```bash
php artisan serve
```

### Terminal 2

```
npm run dev
```

### Terminal 3

```bash
python rag_server.py
```

Open:

```text
http://127.0.0.1:8000
```

---

# 👨‍🏫 How to Use

## Lecturer Workflow

### Login

Login using lecturer account.

### Upload Documents

Navigate to:

```text
Document Management
```

Upload:

* PDF
* DOCX

### Automatic Processing

The system automatically performs:

```text
Parsing
↓
Cleaning
↓
Chunking
↓
Embedding
↓
Vector Indexing
```

### Verify Indexing

Ensure document status becomes:

```text
Indexed
```

---

## Student Workflow

### Login

Login using student account.

### Ask Questions

Example:

```text
Apa tujuan dari penelitian ini?
```

```text
Apa isi modul pembelajaran pada bab 3?
```

```text
Bagaimana prosedur administrasi akademik?
```

### Receive Results

The system returns:

* Generated answer
* Source document references
* Relevant document chunks

---

# 📊 Evaluation

The project evaluates:

## Retrieval Performance

* Recall@K
* Precision@K
* Accuracy
* Response Time

## Answer Quality

* Correctness
* Relevance
* Completeness
* Faithfulness

## Vector Database Comparison

* FAISS
* ChromaDB

## Top-K Analysis

Performance comparison using different retrieval values:

```text
K = 3
K = 5
K = 10
```

---

# 🔒 Access Control

| Feature          | Student | Lecturer |
| ---------------- | ------- | -------- |
| Register         | ✅       | ❌        |
| Login            | ✅       | ✅        |
| Ask Question     | ✅       | ✅        |
| Query History    | ✅       | ✅        |
| Upload Document  | ❌       | ✅        |
| Manage Documents | ❌       | ✅        |

---

# 🎯 Future Improvements

* Hybrid Search (BM25 + Vector Search)
* OCR Support
* Multi-file Citation
* Streaming Responses
* Reranking Models
* Role-based Administration
* Cloud Deployment

---

# 👥 Authors

### Junior Dirgantara Betan

Team Leader

### Ferdian Baihaqi

Member

---

# 📜 License

This project was developed as a Project-Based Learning (PBL) Final Project at Politeknik Negeri Batam.

Academic use only.
