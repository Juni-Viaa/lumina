# gunicorn.conf.py — Lumina RAG Server configuration

import os

# ── Bind ──────────────────────────────────────────────────────────────────────
bind = f"0.0.0.0:{os.environ.get('PORT', '10000')}"

# ── Workers ───────────────────────────────────────────────────────────────────
workers = 1
threads = 4

# ── Timeouts ──────────────────────────────────────────────────────────────────
timeout         = 300
graceful_timeout = 60
keepalive       = 5

# ── Preload ───────────────────────────────────────────────────────────────────
preload_app = True

# ── Process ───────────────────────────────────────────────────────────────────
proc_name  = "lumina-rag"
worker_class = "gthread"

# ── Logging ───────────────────────────────────────────────────────────────────
loglevel       = "info"
accesslog      = "ai/logs/gunicorn_access.log"
errorlog       = "ai/logs/gunicorn_error.log"
capture_output = True       # redirects flask print() statements to errorlog
access_log_format = '%(h)s %(l)s %(u)s %(t)s "%(r)s" %(s)s %(b)s %(D)sμs'