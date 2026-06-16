<script>
function chatApp() {
    return {
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
                const res = await fetch('{{ route("dashboard.ask") }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ question }),
                });

                const rawText = await res.text();
                let data = {};

                try {
                    data = JSON.parse(rawText);
                } catch {
                    this.messages.push({
                        role:    'assistant',
                        content: '❌ Server returned non-JSON (HTTP ' + res.status + '):\n\n'
                                 + rawText.substring(0, 500),
                    });
                    return;
                }

                if (!res.ok || data.error) {
                    this.messages.push({
                        role:    'assistant',
                        content: '❌ ' + (data.error ?? data.message ?? 'Terjadi kesalahan.'),
                    });
                    console.error('[Lumina]', data);
                    return;
                }

                this.messages.push({
                    role:    'assistant',
                    content: data.answer ?? 'Tidak ada jawaban.',
                });

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

            let html = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // Block: headings
            html = html.replace(/^### (.+)$/gm, '<h3 class="font-bold text-base mt-3 mb-1">$1</h3>');
            html = html.replace(/^## (.+)$/gm,  '<h2 class="font-bold text-lg mt-4 mb-1">$1</h2>');
            html = html.replace(/^# (.+)$/gm,   '<h1 class="font-bold text-xl mt-4 mb-2">$1</h1>');

            // Block: horizontal rule
            html = html.replace(/^---+$/gm, '<hr class="border-white/30 my-3">');

            // Block: unordered list
            html = html.replace(/^[\*\-] (.+)$/gm, '<li class="ml-4 list-disc">$1</li>');
            html = html.replace(/(<li[^>]*>[\s\S]*?<\/li>\n?)+/g,
                m => `<ul class="my-2 space-y-0.5">${m}</ul>`);

            // Block: ordered list
            html = html.replace(/^\d+\. (.+)$/gm, '<li class="ml-4 list-decimal">$1</li>');
            html = html.replace(/(<li class="ml-4 list-decimal">[\s\S]*?<\/li>\n?)+/g,
                m => `<ol class="my-2 space-y-0.5">${m}</ol>`);

            // Block: blockquote
            html = html.replace(/^&gt; (.+)$/gm,
                '<blockquote class="border-l-2 border-white/50 pl-3 italic opacity-80 my-1">$1</blockquote>');

            // Block: code block
            html = html.replace(/```[\w]*\n?([\s\S]*?)```/g,
                '<pre class="bg-black/20 rounded-lg p-3 my-2 text-xs overflow-x-auto whitespace-pre"><code>$1</code></pre>');

            // Inline: bold+italic, bold, italic
            html = html.replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>');
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>');
            html = html.replace(/(?<![*\w])\*(?![*\s])(.+?)(?<![*\s])\*(?![*\w])/g, '<em>$1</em>');

            // Inline: code
            html = html.replace(/`([^`]+)`/g,
                '<code class="bg-black/20 rounded px-1 py-0.5 text-xs font-mono">$1</code>');

            // Paragraphs & line breaks
            html = html.replace(/\n{2,}/g, '</p><p class="mt-2">');
            html = html.replace(/\n/g, '<br>');

            if (!/^<(h[1-6]|ul|ol|pre|blockquote|hr)/.test(html)) {
                html = `<p>${html}</p>`;
            }

            return html;
        },
    };
}
</script>