{{-- markdown-styles.blade.php --}}
<style>
/* ── Base ───────────────────────────────────────────────────────────────── */
.prose-chat {
    font-size: 15px;
    line-height: 1.75;
    color: #1e293b;
    max-width: none;
}

/* ── Paragraphs ─────────────────────────────────────────────────────────── */
.prose-chat p {
    margin: 0.55rem 0;
    color: #1e293b;
}
.prose-chat p:first-child { margin-top: 0; }
.prose-chat p:last-child  { margin-bottom: 0; }

/* ── Headings ─────────────────────────────────────────────────────────────── */
.prose-chat h1,
.prose-chat h2,
.prose-chat h3,
.prose-chat h4 {
    color: #0f172a;
    font-weight: 700;
    margin-top: 1.2rem;
    margin-bottom: 0.35rem;
    line-height: 1.4;
}
.prose-chat h1 { font-size: 1.15em; border-bottom: 1px solid rgba(0,0,0,0.08); padding-bottom: 0.2rem; }
.prose-chat h2 { font-size: 1.05em; }
.prose-chat h3 { font-size: 0.97em; }
.prose-chat h4 { font-size: 0.93em; }
.prose-chat h1:first-child,
.prose-chat h2:first-child,
.prose-chat h3:first-child { margin-top: 0; }

/* ── Bold ───────────────────────────────────────────────────────────────── */
.prose-chat strong {
    font-weight: 650;
    color: #0f172a;
}

/* ── Italic ──────────────────────────────────────────────────────────────── */
.prose-chat em {
    font-style: italic;
    font-weight: normal;
    color: inherit;
}

/* ── Lists ──────────────────────────────────────────────────────────────── */
.prose-chat ul,
.prose-chat ol {
    padding-left: 1.5rem;
    margin: 0.4rem 0;
    color: #1e293b;
}
.prose-chat ul { list-style-type: disc; }
.prose-chat ol { list-style-type: decimal; }

.prose-chat li {
    margin-bottom: 0.4rem;
    line-height: 1.7;
    color: #1e293b;
    padding-left: 0.15rem;
}
.prose-chat li:last-child { margin-bottom: 0; }

/* ── Nested lists ───────────────────────────────────────────────────────── */
.prose-chat li > ul,
.prose-chat li > ol {
    margin-top: 0.25rem;
    margin-bottom: 0.25rem;
}

/* ── Tables ──────────────────────────────────────────────────────────────── */
.prose-chat table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88em;
    margin: 0.75rem 0;
    color: #1e293b;
}
.prose-chat th {
    background: rgba(0,0,0,0.05);
    font-weight: 600;
    color: #0f172a;
    text-align: left;
    padding: 0.45rem 0.75rem;
    border: 1px solid rgba(0,0,0,0.1);
}
.prose-chat td {
    padding: 0.4rem 0.75rem;
    border: 1px solid rgba(0,0,0,0.08);
    vertical-align: top;
    color: #1e293b;
}
.prose-chat tr:nth-child(even) td { background: rgba(0,0,0,0.015); }

/* ── Code ────────────────────────────────────────────────────────────────── */
.prose-chat code {
    font-family: 'Fira Code', 'Cascadia Code', 'Consolas', monospace;
    font-size: 0.82em;
    background: rgba(0,0,0,0.06);
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
    border-left: 3px solid rgba(0,0,0,0.18);
    padding: 0.2rem 0.8rem;
    margin: 0.5rem 0;
    color: #475569;
    font-style: italic;
}

/* ── HR ──────────────────────────────────────────────────────────────────── */
.prose-chat hr {
    border: none;
    border-top: 1px solid rgba(0,0,0,0.09);
    margin: 1rem 0;
}

/* ── Citation — small, muted gray, superscript style ─────────────────────
   Rendered as <sup class="citation">[Doc, hal. N]</sup>              ── */
.prose-chat sup.citation {
    font-size: 0.72em;
    font-weight: 400;
    color: #94a3b8;
    font-style: normal;
    vertical-align: super;
    line-height: 1;
    margin-left: 1px;
    letter-spacing: 0;
}

/* ── Prevent Tailwind prose plugin from overriding colors ────────────────── */
.prose-chat a          { color: #2563eb; }
.prose-chat *          { color: inherit; }
.prose-chat strong     { color: #0f172a; }
.prose-chat em         { color: inherit; }
.prose-chat code       { color: #1e293b; }
</style>