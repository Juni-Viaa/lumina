@extends('layouts.app')
@section('title', 'Chat')

@push('topbar-actions')
    <button
        @click="clearChat()"
        class="text-xs text-stone-500 hover:text-stone-300 border border-stone-800
               hover:border-stone-700 px-3 py-1.5 rounded-lg transition-colors">
        Clear chat
    </button>
@endpush

@push('styles')
<style>
/* Markdown rendered inside assistant bubbles */
.prose-chat { line-height: 1.6; }
.prose-chat p          { margin-bottom: 0.4rem; }
.prose-chat p:last-child { margin-bottom: 0; }
.prose-chat h1,
.prose-chat h2,
.prose-chat h3         { color: inherit; line-height: 1.3; }
.prose-chat ul,
.prose-chat ol         { padding-left: 1.25rem; margin: 0.4rem 0; }
.prose-chat li         { margin-bottom: 0.15rem; }
.prose-chat strong     { font-weight: 600; color: inherit; }
.prose-chat em         { font-style: italic; }
.prose-chat code       { font-size: 0.8em; }
.prose-chat pre        { font-size: 0.8em; }
.prose-chat blockquote { margin: 0.5rem 0; }
.prose-chat hr         { margin: 0.75rem 0; }
</style>
@endpush

@section('content')
<div class="flex flex-col h-full" x-data="chatApp()">

    {{-- Messages area --}}
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6" x-ref="messages">

        {{-- Empty / idle state --}}
        <template x-if="messages.length === 0">
            <div class="flex flex-col items-center justify-center h-full text-center py-20">
                <p class="text-[#1a3a52]/70 text-lg leading-relaxed"
                   style="font-family: 'Space Grotesk', sans-serif;">
                    Halo, Lumina disini siap membantu.<br>
                    Tanyakan pertanyaanmu dan Lumina<br>
                    akan membantu menjawabnya berdasarkan<br>
                    pengetahuan Lumina
                </p>
            </div>
        </template>

        {{-- Message list --}}
        <template x-for="(msg, index) in messages" :key="index">
            <div class="flex gap-3"
                 :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">

                {{-- AI avatar --}}
                <template x-if="msg.role === 'assistant'">
                    <div class="w-8 h-8 rounded-lg bg-[#C9DCE4] flex items-center
                                justify-center shrink-0 mt-0.5">
                        <img src="{{ asset('images/icons/Logo.png') }}"
                             class="w-12 h-8 opacity-70" alt="Lumina">
                    </div>
                </template>

                {{-- Bubble --}}
                <div class="max-w-xl px-4 py-3 rounded-2xl text-sm leading-relaxed"
                     :class="msg.role === 'user'
                         ? 'bg-[#5BB7EC] text-white rounded-tr-sm'
                         : 'bg-[#92C7DD] text-white rounded-tl-sm'">
                    {{-- Assistant: render markdown --}}
                    <template x-if="msg.role === 'assistant'">
                        <div class="prose-chat" x-html="renderMarkdown(msg.content)"></div>
                    </template>
                    {{-- User: preserve newlines with pre-wrap --}}
                    <template x-if="msg.role === 'user'">
                        <span style="white-space: pre-wrap; word-break: break-word;"
                              x-text="msg.content"></span>
                    </template>
                </div>
            </div>
        </template>

        {{-- Typing indicator --}}
        <template x-if="loading">
            <div class="flex gap-3 justify-start">
                <div class="w-8 h-8 rounded-lg bg-[#C9DCE4] flex items-center
                            justify-center shrink-0">
                    <img src="{{ asset('images/icons/Logo.png') }}"
                         class="w-12 h-8 opacity-70" alt="Lumina">
                </div>
                <div class="bg-[#92C7DD] px-4 py-3 rounded-2xl rounded-tl-sm
                            flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce"
                          style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce"
                          style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce"
                          style="animation-delay:300ms"></span>
                </div>
            </div>
        </template>
    </div>

    {{-- Input area --}}
    <div class="glass-inner border-t border-white/10 px-4 py-4 mx-4 mt-4
                rounded-2xl backdrop-blur-md shrink-0">
        <div class="flex items-end gap-3">
            <textarea
                x-model="input"
                x-ref="inputBox"
                @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); sendMessage(); }"
                placeholder="Ketik pertanyaanmu disini..."
                rows="1"
                spellcheck="false"
                autocomplete="off"
                :disabled="loading"
                class="glass-inner flex-1 resize-none bg-transparent border border-white/20
                       focus:ring-0 focus:outline-none text-black placeholder-black/65 text-sm
                       rounded-xl px-4 py-3 backdrop-blur-sm disabled:opacity-50 transition-all"
                style="max-height: 160px;"
                @input="autoResize($el)">
            </textarea>

            <button
                @click="sendMessage()"
                :disabled="loading || !input.trim()"
                class="glass-inner w-12 h-12 rounded-xl flex items-center justify-center
                       shrink-0 text-[#1a6fa8] hover:bg-white/30 disabled:opacity-40
                       disabled:cursor-not-allowed transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                     stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77
                             59.77 0 013.269 20.876L5.999 12zm0 0h7.5"/>
                </svg>
            </button>
        </div>

        <p class="text-xs text-[#1a3a52]/50 mt-2 text-center">
            Enter untuk kirim · Shift+Enter baris baru
        </p>
    </div>

</div>
@endsection

@push('scripts')
<script>
function chatApp() {
    return {
        {{--
            FIX: $initialMessages is computed in PHP (DashboardController) and
            passed as a plain array. json_encode() is safe here — it produces
            valid JS array literal with no Blade/PHP ternary syntax issues.
        --}}
        messages: {!! json_encode($initialMessages ?? []) !!},
        input:    '',
        loading:  false,

        async sendMessage() {
            const question = this.input.trim();
            if (!question || this.loading) return;

            this.messages.push({ role: 'user', content: question });
            this.input   = '';
            this.loading = true;

            this.$nextTick(() => {
                this.scrollToBottom();
                if (this.$refs.inputBox) {
                    this.$refs.inputBox.style.height = 'auto';
                }
            });

            try {
                const res  = await fetch('{{ route("dashboard.ask") }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ question }),
                });

                // ── Read raw text first so we never lose the body ─────────
                const rawText = await res.text();
                console.log('[Lumina] HTTP status:', res.status);
                console.log('[Lumina] Raw response:', rawText);

                let data = {};
                try {
                    data = JSON.parse(rawText);
                } catch (parseErr) {
                    // Response wasn't JSON — show raw HTML/text (e.g. Laravel error page)
                    this.messages.push({
                        role:    'assistant',
                        content: '❌ Server returned non-JSON response (HTTP ' + res.status + '):\n\n'
                                 + rawText.substring(0, 500),
                    });
                    return;
                }

                if (!res.ok || data.error) {
                    // ── Show full debug info in the chat bubble ────────────
                    const debug  = data.debug  ?? {};
                    const lines  = [
                        '❌ HTTP ' + res.status + ' — ' + (data.error ?? data.message ?? 'Unknown error'),
                        '',
                        '— Laravel Response —',
                        JSON.stringify(data, null, 2),
                        '',
                        '— Python Debug —',
                        'python:        ' + (debug.python_path    ?? '?'),
                        'script exists: ' + (debug.script_exists  ?? '?'),
                        'faiss exists:  ' + (debug.faiss_exists   ?? '?'),
                        'gemini key:    ' + (debug.gemini_key_set ?? '?'),
                        'exit code:     ' + (debug.exit_code      ?? '?'),
                        'stderr: ' + (debug.stderr_raw  ?? '(empty)'),
                        'stdout: ' + (debug.stdout_raw  ?? '(empty)'),
                    ];
                    this.messages.push({ role: 'assistant', content: lines.join('\n') });
                    console.error('[Lumina debug]', data);
                    return;
                }

                this.messages.push({
                    role:    'assistant',
                    content: data.answer ?? 'Tidak ada jawaban.',
                });

                // Refresh sidebar — uses the global function defined in sidebar.blade.php
                if (typeof window.refreshSidebar === 'function') {
                    window.refreshSidebar();
                }

            } catch (err) {
                this.messages.push({
                    role:    'assistant',
                    content: '❌ Fetch error: ' + err.message,
                });
                console.error('[Lumina fetch error]', err);
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        clearChat() {
            this.messages = [];
            this.input    = '';
        },

        scrollToBottom() {
            const el = this.$refs.messages;
            if (el) el.scrollTop = el.scrollHeight;
        },

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 160) + 'px';
        },

        renderMarkdown(text) {
            if (!text) return '';

            // Escape HTML first to prevent XSS
            let html = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // ── Block elements (process top-down) ─────────────────────────

            // Headings: ### h3, ## h2, # h1
            html = html.replace(/^### (.+)$/gm, '<h3 class="font-bold text-base mt-3 mb-1">$1</h3>');
            html = html.replace(/^## (.+)$/gm,  '<h2 class="font-bold text-lg mt-4 mb-1">$1</h2>');
            html = html.replace(/^# (.+)$/gm,   '<h1 class="font-bold text-xl mt-4 mb-2">$1</h1>');

            // Horizontal rule
            html = html.replace(/^---+$/gm, '<hr class="border-white/30 my-3">');

            // Unordered lists: lines starting with * or - (not bold/italic)
            html = html.replace(/^[\*\-] (.+)$/gm, '<li class="ml-4 list-disc">$1</li>');
            html = html.replace(/(<li[\s\S]*?<\/li>)(\n<li)/g, '$1$2'); // group consecutive
            html = html.replace(/(<li[^>]*>[\s\S]*?<\/li>\n?)+/g,
                match => `<ul class="my-2 space-y-0.5">${match}</ul>`);

            // Ordered lists: lines starting with 1. 2. etc.
            html = html.replace(/^\d+\. (.+)$/gm, '<li class="ml-4 list-decimal">$1</li>');
            html = html.replace(/(<li class="ml-4 list-decimal">[\s\S]*?<\/li>\n?)+/g,
                match => `<ol class="my-2 space-y-0.5">${match}</ol>`);

            // Blockquote
            html = html.replace(/^&gt; (.+)$/gm,
                '<blockquote class="border-l-2 border-white/50 pl-3 italic opacity-80 my-1">$1</blockquote>');

            // Code block (triple backtick)
            html = html.replace(/```[\w]*\n?([\s\S]*?)```/g,
                '<pre class="bg-black/20 rounded-lg p-3 my-2 text-xs overflow-x-auto whitespace-pre"><code>$1</code></pre>');

            // ── Inline elements ────────────────────────────────────────────

            // Bold+italic ***text***
            html = html.replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>');

            // Bold **text**
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>');

            // Italic *text* (single asterisk, not inside words)
            html = html.replace(/(?<![*\w])\*(?![*\s])(.+?)(?<![*\s])\*(?![*\w])/g, '<em>$1</em>');

            // Inline code `code`
            html = html.replace(/`([^`]+)`/g,
                '<code class="bg-black/20 rounded px-1 py-0.5 text-xs font-mono">$1</code>');

            // ── Paragraphs & line breaks ───────────────────────────────────

            // Double newline → paragraph break
            html = html.replace(/\n{2,}/g, '</p><p class="mt-2">');

            // Single newline → <br> (inside paragraphs)
            html = html.replace(/\n/g, '<br>');

            // Wrap in paragraph if doesn't start with a block element
            if (! /^<(h[1-6]|ul|ol|pre|blockquote|hr)/.test(html)) {
                html = `<p>${html}</p>`;
            }

            return html;
        },
    };
}
</script>
@endpush