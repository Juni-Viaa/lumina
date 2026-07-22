# gunicorn.conf.py — Lumina RAG Server configuration

import os

# ── Bind ──────────────────────────────────────────────────────────────────────
bind = f"0.0.0.0:{os.environ.get('PORT', '10000')}"

# ── Workers ───────────────────────────────────────────────────────────────────
workers = 1
threads = 1

# ── Timeouts ──────────────────────────────────────────────────────────────────
timeout         = 300
graceful_timeout = 60
keepalive       = 5

# ── Preload ───────────────────────────────────────────────────────────────────
preload_app = False

# ── Process ───────────────────────────────────────────────────────────────────
proc_name  = "lumina-rag"
worker_class = "sync"

# ── Logging ───────────────────────────────────────────────────────────────────
loglevel       = "info"
accesslog      = "-"
errorlog       = "-"
capture_output = True
access_log_format = '%(h)s "%(r)s" %(s)s %(b)s %(D)sμs'