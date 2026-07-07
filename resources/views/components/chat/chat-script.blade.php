{{-- chat-script.blade.php --}}
<script>
    function chatApp() {
        return {
            messages: {!! json_encode($initialMessages ?? []) !!},
            input:    '',
            loading:  false,

            async sendMessage() {
                const question = this.input.trim();
                if (!question || this.loading) return;

                this.loading = true;
                this.messages.push({ role: 'user', content: question });
                this.input   = '';

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
                            content: 'Server returned non-JSON (HTTP ' + res.status + '):\n\n'
                                     + rawText.substring(0, 500),
                        });
                        return;
                    }

                    if (!res.ok || data.error) {
                        this.messages.push({
                            role:    'assistant',
                            content: '' + (data.error ?? data.message ?? 'Terjadi kesalahan.'),
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
                        content: 'Fetch error: ' + err.message,
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
                if (!text) return "";

                // Pastikan marked dan DOMPurify sudah dimuat
                if (typeof marked === 'undefined' || typeof DOMPurify === 'undefined') {
                    return text; // fallback
                }

                marked.setOptions({
                    gfm: true,
                    breaks: true,
                });

                let html = DOMPurify.sanitize(
                    marked.parse(text)
                );

                html = html.replace(
                    /<strong>\((.+?,\s*hal\.\s*\d+.*?)\)<\/strong>/gi,
                    '<sup class="citation">[$1]</sup>'
                );

                return html;
            },
        };
    }

    // Daftarkan ke Alpine agar bisa digunakan dengan x-data="chatApp()"
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatApp', chatApp);
    });
</script>