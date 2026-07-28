<script>
window.uploadForm = function () {
    return {

        activeView: 'upload', // 'upload' | 'manage' | 'ingesting'

        // Untuk ingesting
        ingestingDocId: null,
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

        startIngesting(docId) {
            this.activeView = 'ingesting';
            this.ingestingDocId = docId;
            this.ingestLogs = [];
            this.ingestStatus = 'processing';

            if (this.eventSource) {
                this.eventSource.close();
            }

            const url = `/ingest-logs/${docId}`;
            this.eventSource = new EventSource(url);

            this.eventSource.addEventListener('log', (e) => {
                const data = JSON.parse(e.data);
                this.ingestLogs.push(data);
                // scroll to bottom
                this.$nextTick(() => {
                    const container = this.$refs.logContainer;
                    if (container) container.scrollTop = container.scrollHeight;
                });
            });

            this.eventSource.addEventListener('done', (e) => {
                const data = JSON.parse(e.data);
                this.ingestStatus = data.status;
                this.eventSource.close();
                // optional: after 2s switch to manage
                setTimeout(() => {
                    this.activeView = 'manage';
                    this.fetchDocuments();
                }, 2000);
            });

            this.eventSource.onerror = () => {
                // handle error
                this.ingestStatus = 'failed';
                this.eventSource.close();
            };
        },

        stopIngesting() {
            if (this.eventSource) {
                this.eventSource.close();
                this.eventSource = null;
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
                this.startIngesting(data.document_id);

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