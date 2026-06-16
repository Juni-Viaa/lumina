<script>
window.uploadForm = function () {
    return {

        activeView: 'upload',

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
            },
            {
                label: 'Membaca dokumen',
                desc: 'Mengekstrak teks dari file'
            },
            {
                label: 'Membersihkan konten',
                desc: 'Memfilter teks yang tidak relevan'
            },
            {
                label: 'Memotong ke chunks',
                desc: 'Membagi teks menjadi potongan kecil'
            },
            {
                label: 'Menyimpan ke database',
                desc: 'Memasukkan data ke MySQL'
            },
            {
                label: 'Membuat vector index',
                desc: 'Menghasilkan embedding FAISS'
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

        _startStepTicker() {
            this.currentStep = 0;

            const delays = [700,1100,900,1300,1100];

            let elapsed = 0;

            delays.forEach((delay, i) => {

                elapsed += delay;

                setTimeout(() => {

                    if (
                        this.uploading &&
                        this.currentStep === i
                    ) {
                        this.currentStep = i + 1;
                    }

                }, elapsed);

            });
        },

        async submitUpload() {

            if (!this.selectedFile) return;

            this.uploading = true;
            this.uploadError = null;
            this.currentStep = 0;

            this._startStepTicker();

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