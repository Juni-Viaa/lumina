{{-- chat-script.blade.php --}}
<script>
    function chatApp() {
        return {
            messages:    {!! json_encode($initialMessages ?? []) !!},
            input:         '',
            loading:       false,
            processingMsg: 'Lumina sedang memulai',

            stepLabels: {
                embedding:          'Mengubah pertanyaan menjadi embedding',
                similarity_search:  'Mencari potongan dokumen yang relevan',
                top_k:              'Mengambil potongan dokumen paling relevan',
                context:            'Menyusun konteks dari dokumen',
                generate:           'Menghasilkan jawaban dengan AI',
                done:               'Jawaban siap',
            },

            async sendMessage() {
                const question = this.input.trim();
                if (!question || this.loading) return;

                this.loading       = true;
                this.processingMsg = 'Lumina sedang memulai';
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
                        this.loading = false;
                        return;
                    }

                    if (!res.ok || data.error) {
                        this.messages.push({
                            role:    'assistant',
                            content: '' + (data.error ?? data.message ?? 'Terjadi kesalahan.'),
                        });
                        console.error('[Lumina]', data);
                        this.loading = false;
                        return;
                    }

                    // Pertanyaan sudah diterima & sedang diproses di Flask —
                    // dengarkan progres realtime-nya lewat SSE.
                    this.listenForAnswer(data.query_id);

                } catch (err) {
                    this.messages.push({
                        role:    'assistant',
                        content: 'Fetch error: ' + err.message,
                    });
                    console.error('[Lumina fetch error]', err);
                    this.loading = false;
                }
            },

            // Poll status pertanyaan via fetch biasa (bukan SSE) — lebih
            // tahan banting lintas hosting/proxy. Berhenti begitu status
            // answered/failed, atau setelah ~2 menit tanpa respons.
            async listenForAnswer(queryId) {
                const maxAttempts = 120; // 120 x 1s ≈ 2 menit
                let attempts = 0;

                const finish = (message) => {
                    this.loading = false;
                    this.messages.push({ role: 'assistant', content: message });
                    this.$nextTick(() => this.scrollToBottom());
                };

                const poll = async () => {
                    attempts++;

                    let data;
                    try {
                        const res = await fetch(`/queries/${queryId}/status`);
                        data = await res.json();
                    } catch (err) {
                        finish('Gagal memeriksa status jawaban: ' + err.message);
                        return;
                    }

                    if (data.step && this.stepLabels[data.step]) {
                        this.processingMsg = this.stepLabels[data.step];
                    }

                    if (data.status === 'answered') {
                        this.loading = false;
                        this.messages.push({
                            role:    'assistant',
                            content: data.answer ?? 'Tidak ada jawaban.',
                        });
                        if (typeof window.refreshSidebar === 'function') {
                            window.refreshSidebar();
                        }
                        this.$nextTick(() => this.scrollToBottom());
                        return;
                    }

                    if (data.status === 'failed') {
                        finish('Lumina gagal memproses pertanyaan ini. Silakan coba lagi.');
                        return;
                    }

                    if (data.status === 'not_found') {
                        finish('Pertanyaan tidak ditemukan. Silakan coba lagi.');
                        return;
                    }

                    if (attempts >= maxAttempts) {
                        finish('Waktu tunggu jawaban habis. Silakan coba lagi.');
                        return;
                    }

                    setTimeout(poll, 1000);
                };

                poll();
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