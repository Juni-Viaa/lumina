<style>
/* ── Base prose ────────────────────────────────────────────────────────── */
.prose-chat {
    font-size: 15px;
    line-height: 1.8;
    color: #1e293b;
    max-width: none;
}

/* ── Paragraphs ────────────────────────────────────────────────────────── */
.prose-chat p {
    margin: 0.6rem 0;
}
.prose-chat p:first-child { margin-top: 0; }
.prose-chat p:last-child  { margin-bottom: 0; }

/* ── Headings — only these get highlighted, nothing else ──────────────── */
.prose-chat h1 {
    font-size: 1.2em;
    font-weight: 700;
    color: #0f172a;
    margin: 1.4rem 0 0.5rem;
    padding-bottom: 0.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.08);
}
.prose-chat h2 {
    font-size: 1.05em;
    font-weight: 700;
    color: #0f172a;
    margin: 1.2rem 0 0.4rem;
}
.prose-chat h3 {
    font-size: 0.97em;
    font-weight: 600;
    color: #1e293b;
    margin: 1rem 0 0.3rem;
}

/* ── Bold — only for truly important terms, not foreign words ──────────── */
.prose-chat strong {
    font-weight: 600;
    color: #0f172a;
}

/* ── Italic — foreign terms should be italic, NOT bold ────────────────── */
.prose-chat em {
    font-style: italic;
    color: inherit;        /* same colour as body — no extra emphasis */
    font-weight: normal;
}

/* ── Lists ─────────────────────────────────────────────────────────────── */
.prose-chat ul,
.prose-chat ol {
    padding-left: 1.4rem;
    margin: 0.5rem 0;
}
.prose-chat ul { list-style-type: disc; }
.prose-chat ol { list-style-type: decimal; }
.prose-chat li {
    margin-bottom: 0.15rem;
    line-height: 1.7;
}
/* No gap between tight list items */
.prose-chat li + li { margin-top: 0; }

/* ── Tables ─────────────────────────────────────────────────────────────── */
.prose-chat table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9em;
    margin: 0.75rem 0;
}
.prose-chat th {
    background: rgba(0,0,0,0.05);
    font-weight: 600;
    text-align: left;
    padding: 0.45rem 0.75rem;
    border: 1px solid rgba(0,0,0,0.1);
}
.prose-chat td {
    padding: 0.4rem 0.75rem;
    border: 1px solid rgba(0,0,0,0.08);
    vertical-align: top;
}
.prose-chat tr:nth-child(even) td {
    background: rgba(0,0,0,0.02);
}

/* ── Code ───────────────────────────────────────────────────────────────── */
.prose-chat code {
    font-family: 'Fira Code', 'Cascadia Code', monospace;
    font-size: 0.82em;
    background: rgba(0,0,0,0.07);
    border-radius: 4px;
    padding: 0.1em 0.35em;
    color: #1e293b;
}
.prose-chat pre {
    background: #1e293b;
    border-radius: 10px;
    padding: 1rem 1.2rem;
    overflow-x: auto;
    margin: 0.75rem 0;
}
.prose-chat pre code {
    background: none;
    color: #e2e8f0;
    font-size: 0.85em;
    padding: 0;
}

/* ── Blockquote ──────────────────────────────────────────────────────────── */
.prose-chat blockquote {
    border-left: 3px solid rgba(0,0,0,0.2);
    padding: 0.25rem 0.75rem;
    margin: 0.5rem 0;
    color: #475569;
    font-style: italic;
}

/* ── Horizontal rule ─────────────────────────────────────────────────────── */
.prose-chat hr {
    border: none;
    border-top: 1px solid rgba(0,0,0,0.1);
    margin: 1rem 0;
}

/* ── Citation — smaller, muted gray, no bold ──────────────────────────── */
.citation {
    display: inline;
    font-size: 0.78em;
    font-weight: 400;
    color: #94a3b8;
    font-style: normal;
    letter-spacing: 0.01em;
    margin-left: 2px;
}
/* Override if citation lands inside a <strong> */
.prose-chat strong .citation,
.prose-chat .citation strong {
    font-weight: 400;
    color: #94a3b8;
}
</style>