<script>
window.uploadForm = function () {
    return {

        activeView: 'upload', // 'upload' | 'manage' | 'ingesting'

        // Untuk ingesting
        ingestingDocId: null,
        ingestSessionId: null,
        ingestPollTimer: null,
        ingestLogs: [],
        ingestStatus: null, // 'processing', 'indexed', 'failed'

        // Untuk chunk modal
        chunkModalDoc: null,
        chunkList: [],
        loadingChunks: false,

        selectedFile: null,
        uploading: false,
        uploaded: false,
        dragging: false,
        uploadError: null,

        currentStep: 0,

        steps: [
            {
                label: 'Mengunggah file',
                desc: 'Mentransfer dokumen ke server'
            }
        ],

        documents: [],
        loadingDocs: false,
        deletingDoc: null,
        deleting: false,

        handleFile(e) {
            this.uploadError = null;
            this.uploaded = false;
            this.selectedFile = e.target.files[0] ?? null;
        },

        handleDrop(e) {
            this.dragging = false;
            this.uploadError = null;
            this.uploaded = false;
            this.selectedFile = e.dataTransfer.files[0] ?? null;
        },

        formatSize(bytes) {
            if (!bytes) return '';

            bytes = Number(bytes);

            if (bytes < 1024)
                return bytes + ' B';

            if (bytes < 1048576)
                return (bytes / 1024).toFixed(1) + ' KB';

            return (bytes / 1048576).toFixed(1) + ' MB';
        },

        formatDate(dateStr) {
            if (!dateStr) return 'Unknown date';

            return new Date(dateStr)
                .toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
        },

        // ── Pewarnaan step ingest ──────────────────────────────────────────
        stepBadgeClass(step) {
            if (step === 'error' || step === 'failed') {
                return 'bg-rose-500/15 text-rose-600';
            }
            if (step === 'complete') {
                return 'bg-green-500/15 text-green-600';
            }
            return 'bg-orange-500/15 text-orange-600';
        },

        stepTextClass(step) {
            if (step === 'error' || step === 'failed') return 'text-rose-600';
            if (step === 'complete') return 'text-green-600';
            return 'text-[#1a3a52]/80';
        },

        stepLabel(step) {
            if (step === 'complete') return 'Ready';
            return step;
        },

        statusColorClass() {
            if (this.ingestStatus === 'failed') return 'text-rose-500';
            if (this.ingestStatus === 'indexed') return 'text-green-600';
            return 'text-[#1a3a52]/50';
        },

        async startIngesting(docId, sessionId, autoRedirect = true) {
            this.activeView = 'ingesting';
            this.ingestingDocId = docId;
            this.ingestSessionId = sessionId;
            this.ingestLogs = [];
            this.ingestStatus = 'processing';

            if (this.ingestPollTimer) {
                clearTimeout(this.ingestPollTimer);
                this.ingestPollTimer = null;
            }

            let lastId = 0;
            let failCount = 0;
            const maxAttempts = 1800; // ~30 menit di siklus 1 detik — jaring pengaman
            let attempts = 0;

            const poll = async () => {
                attempts++;

                try {
                    const res = await fetch(
                        `/ingest-logs-status/${docId}?session=${sessionId ?? ''}&after=${lastId}`
                    );
                    const data = await res.json();
                    failCount = 0; // request ini berhasil, reset penghitung gagal

                    if (data.logs && data.logs.length) {
                        this.ingestLogs.push(...data.logs);
                        lastId = data.logs[data.logs.length - 1].id;
                        this.$nextTick(() => {
                            const container = this.$refs.logContainer;
                            if (container) container.scrollTop = container.scrollHeight;
                        });
                    }

                    this.ingestStatus = data.status;

                    if (['indexed', 'failed'].includes(data.status)) {
                        if (autoRedirect) {
                            setTimeout(() => {
                                this.activeView = 'manage';
                                this.fetchDocuments();
                            }, 2000);
                        }
                        return;
                    }

                } catch (err) {
                    // Satu request gagal (mis. hiccup jaringan sesaat) BUKAN
                    // berarti proses ingest-nya gagal — coba lagi beberapa kali
                    // sebelum menyerah, jangan langsung tandai 'failed'.
                    failCount++;
                    if (failCount >= 5) {
                        return;
                    }
                }

                if (attempts >= maxAttempts) {
                    return; // biarkan status terakhir yang diketahui, jangan menebak
                }

                this.ingestPollTimer = setTimeout(poll, 1000);
            };

            poll();
        },

        // Dipanggil dari tombol "Ingest" di daftar dokumen — melihat log
        // sesi ingest terakhir untuk dokumen tersebut. autoRedirect=false
        // karena ini cuma untuk ditinjau, bukan proses yang baru dimulai.
        viewIngest(doc) {
            if (!doc.ingest_session_id) return;
            this.startIngesting(doc.document_id, doc.ingest_session_id, false);
        },

        stopIngesting() {
            if (this.ingestPollTimer) {
                clearTimeout(this.ingestPollTimer);
                this.ingestPollTimer = null;
            }
        },

        viewChunks(doc) {
            this.chunkModalDoc = doc;
            this.loadingChunks = true;
            this.chunkList = [];
            fetch(`/chunks/${doc.document_id}`)
                .then(res => res.json())
                .then(data => {
                    this.chunkList = data;
                    this.loadingChunks = false;
                })
                .catch(() => {
                    this.loadingChunks = false;
                });
        },

        closeChunkModal() {
            this.chunkModalDoc = null;
            this.chunkList = [];
        },

        async submitUpload() {

            if (!this.selectedFile) return;

            this.uploading = true;
            this.uploadError = null;
            this.currentStep = 0;

            const form = new FormData();

            form.append(
                'document',
                this.selectedFile
            );

            form.append(
                '_token',
                document.querySelector(
                    'meta[name="csrf-token"]'
                ).content
            );

            try {

                const res = await fetch(
                    "{{ route('uploads.store') }}",
                    {
                        method: 'POST',
                        body: form,
                        headers: {
                            Accept: 'application/json'
                        }
                    }
                );

                const data = await res.json();

                if (!res.ok)
                    throw data;

                this.currentStep =
                    this.steps.length;

                this.uploaded = true;
                this.uploading = false;
                this.startIngesting(data.document_id, data.session_id);

                await this.fetchDocuments();

            } catch (err) {

                this.uploading = false;

                this.uploadError = err;

            }

        },

        async fetchDocuments() {

            this.loadingDocs = true;

            try {

                const res =
                    await fetch(
                        "{{ route('uploads.list') }}"
                    );

                this.documents =
                    await res.json();

            } finally {

                this.loadingDocs = false;

            }

        },

        confirmDelete(doc) {
            this.deletingDoc = doc;
        },

        async deleteDocument() {

            if (!this.deletingDoc)
                return;

            this.deleting = true;

            try {

                await fetch(
                    `/upload/${this.deletingDoc.document_id}`,
                    {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content
                        }
                    }
                );

                this.documents =
                    this.documents.filter(
                        d =>
                        d.document_id !==
                        this.deletingDoc.document_id
                    );

                this.deletingDoc = null;

            } finally {

                this.deleting = false;

            }

        },

        reset() {
            this.selectedFile = null;
            this.uploaded = false;
            this.uploading = false;
            this.uploadError = null;
            this.currentStep = 0;
        }
    };
};
</script>