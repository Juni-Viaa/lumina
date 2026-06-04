{{-- 
    Component: upload-form
    Usage: @include('components.upload-form')
--}}
<div class="glass-panel upload-form flex flex-col h-full overflow-hidden"
     x-data="uploadForm()"
     x-init="fetchDocuments()">

    {{-- Header / view switch --}}
    <div class="px-5 pt-5 pb-3 border-b border-white/10 flex items-start justify-between gap-4 shrink-0">
        <div>
            <p class="text-xs uppercase tracking-[0.24em] text-[#1a3a52]/45 mb-1"
               style="font-family: 'Space Grotesk', sans-serif;">
                Lumina Documents
            </p>
            <h3 class="text-[#1a3a52] text-lg font-semibold leading-tight"
                style="font-family: 'Space Grotesk', sans-serif;">
                Upload & Manage
            </h3>
        </div>

        <div class="glass-inner inline-flex p-1 rounded-2xl">
            <button
                @click="activeView = 'upload'"
                class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all"
                :class="activeView === 'upload'
                    ? 'bg-white/35 text-[#1a3a52]'
                    : 'text-[#1a3a52]/55 hover:text-[#1a3a52]'">
                Upload
            </button>
            <button
                @click="activeView = 'manage'; fetchDocuments()"
                class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all"
                :class="activeView === 'manage'
                    ? 'bg-white/35 text-[#1a3a52]'
                    : 'text-[#1a3a52]/55 hover:text-[#1a3a52]'">
                Documents
            </button>
        </div>
    </div>

    {{-- ── Upload view ────────────────────────────────────────────────────── --}}
    <div x-show="activeView === 'upload'" x-cloak class="flex flex-col flex-1 min-h-0">

        {{-- Middle area — switches between states --}}
        <div class="flex-1 flex items-center justify-center px-5 py-4 min-h-0">

            {{-- ① IDLE --}}
            <template x-if="!selectedFile && !uploading && !uploaded && !uploadError">
                <div class="text-center max-w-sm">
                    <p class="text-[#1a3a52]/70 text-lg leading-relaxed"
                       style="font-family: 'Space Grotesk', sans-serif;">
                        Halo, Lumina disini siap membantu.<br>
                        Upload dokumenmu dan Lumina<br>
                        akan memprosesnya ke database.
                    </p>
                </div>
            </template>

            {{-- ② FILE SELECTED --}}
            <template x-if="selectedFile && !uploading && !uploaded && !uploadError">
                <div class="w-full max-w-sm">
                    <div class="glass-inner rounded-2xl px-4 py-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 bg-white/20">
                            <svg class="w-5 h-5 text-[#1a6fa8]/75" fill="none" stroke="currentColor"
                                 stroke-width="1.6" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0
                                         1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1
                                         0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[#1a3a52] font-medium text-sm truncate" x-text="selectedFile.name"></p>
                            <p class="text-[#1a3a52]/50 text-xs" x-text="formatSize(selectedFile.size)"></p>
                        </div>
                        <button @click="selectedFile = null; $refs.fileInput.value = ''"
                                class="text-xs text-[#1a6fa8]/60 hover:text-[#1a6fa8] transition-colors shrink-0">
                            Remove
                        </button>
                    </div>
                </div>
            </template>

            {{-- ③ UPLOADING — live process steps --}}
            <template x-if="uploading">
                <div class="w-full max-w-sm">
                    <p class="text-[#1a3a52]/50 text-xs uppercase tracking-widest mb-4 text-center"
                       style="font-family: 'Space Grotesk', sans-serif;">
                        Memproses dokumen
                    </p>
                    <div class="space-y-2">
                        <template x-for="(step, i) in steps" :key="i">
                            <div class="glass-inner rounded-2xl px-4 py-3 flex items-center gap-3 transition-all duration-500"
                                 :class="currentStep === i
                                     ? 'bg-white/25'
                                     : currentStep > i ? 'opacity-50' : 'opacity-30'">

                                <div class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0"
                                     :class="currentStep > i
                                         ? 'bg-green-500/20'
                                         : currentStep === i ? 'bg-[#1a6fa8]/15' : 'bg-white/10'">

                                    <template x-if="currentStep > i">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                             stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>

                                    <template x-if="currentStep === i">
                                        <svg class="w-4 h-4 text-[#1a6fa8] animate-spin" fill="none"
                                             viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                                        </svg>
                                    </template>

                                    <template x-if="currentStep < i">
                                        <div class="w-2 h-2 rounded-full bg-[#1a3a52]/25"></div>
                                    </template>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-[#1a3a52]"
                                       :class="currentStep === i ? 'opacity-100' : 'opacity-50'"
                                       x-text="step.label"></p>
                                    <p class="text-xs text-[#1a3a52]/40" x-text="step.desc"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- ④ SUCCESS --}}
            <template x-if="uploaded && !uploading">
                <div class="w-full max-w-sm text-center">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-green-500/15">
                        <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-[#1a3a52] font-semibold text-base mb-1"
                       style="font-family: 'Space Grotesk', sans-serif;">
                        Upload berhasil!
                    </p>
                    <p class="text-[#1a3a52]/50 text-sm mb-5">
                        Dokumen sedang diproses oleh Lumina.
                    </p>
                    <button @click="reset()"
                            class="glass-inner px-5 py-2 rounded-xl text-sm text-[#1a6fa8]
                                   hover:bg-white/30 transition-all">
                        Upload lagi
                    </button>
                </div>
            </template>

            {{-- ⑤ ERROR --}}
            <template x-if="uploadError && !uploading">
                <div class="w-full max-w-sm text-center">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-rose-500/15">
                        <svg class="w-7 h-7 text-rose-500" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0
                                     1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        </svg>
                    </div>
                    <p class="text-[#1a3a52] font-semibold text-base mb-1"
                       style="font-family: 'Space Grotesk', sans-serif;">
                        Upload gagal.
                    </p>
                    <p class="text-[#1a3a52]/50 text-sm mb-5" x-text="uploadError"></p>
                    <div class="space-y-2">
                        <p class="text-red-500 font-medium"
                           x-text="uploadError.message">
                        </p>
                    
                        <p class="text-xs text-[#1a3a52]/60"
                           x-show="uploadError.status">
                            Error Code:
                            <span x-text="uploadError.status"></span>
                        </p>
                    
                        <p class="text-xs text-[#1a3a52]/50 wrap-break-words"
                           x-show="uploadError.detail"
                           x-text="uploadError.detail">
                        </p>
                    </div>
                </div>
            </template>

        </div>

        {{-- Bottom upload bar — hidden while uploading or after done --}}
        <div class="p-5 shrink-0">
            <template x-if="!uploading && !uploaded">
                <div>
                    <div
                        class="glass-inner upload-bar flex items-center gap-3 px-4 py-3 rounded-2xl
                               cursor-pointer hover:bg-white/20 transition-all"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="handleDrop($event)"
                        :class="dragging ? 'ring-2 ring-[#1a6fa8]/40' : ''">

                        <div class="glass-inner w-9 h-9 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-[#1a6fa8]/70" fill="none" stroke="currentColor"
                                 stroke-width="1.6" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 16V8m0 0-3 3m3-3 3 3m-9 5a4 4 0 0 1
                                         .88-7.902A5 5 0 0 1 18.9 9.2 3.5 3.5 0 0 1
                                         18 16H7.5z"/>
                            </svg>
                        </div>

                        <span class="text-sm text-[#1a3a52]/50 flex-1 select-none"
                              style="font-family: 'Space Grotesk', sans-serif;">
                            Upload dokumen untuk diproses ke database Lumina.
                        </span>

                        <template x-if="selectedFile">
                            <button
                                @click.stop="submitUpload()"
                                class="glass-inner px-4 py-1.5 rounded-xl text-xs font-medium
                                       text-[#1a6fa8] hover:bg-white/30 transition-all shrink-0">
                                Kirim
                            </button>
                        </template>

                        <input
                            type="file"
                            x-ref="fileInput"
                            class="hidden"
                            accept=".pdf,.txt,.docx,.doc"
                            @change="handleFile($event)">
                    </div>

                    <p class="mt-3 text-[11px] text-[#1a3a52]/40 px-1"
                       style="font-family: 'Space Grotesk', sans-serif;">
                        PDF, TXT, DOC, dan DOCX didukung · Maks. 10 MB
                    </p>
                </div>
            </template>
        </div>
    </div>

    {{-- ── Manage documents view ──────────────────────────────────────────── --}}
    <div x-show="activeView === 'manage'" x-cloak class="flex-1 min-h-0 flex flex-col p-5 gap-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-[#1a3a52]"
                   style="font-family: 'Space Grotesk', sans-serif;">
                    Documents in Database
                </p>
                <p class="text-xs text-[#1a3a52]/50">Review or remove stored files.</p>
            </div>

            <div class="flex items-center gap-2">
                <button @click="fetchDocuments()"
                        class="glass-inner p-2 rounded-xl text-[#1a3a52]/50
                               hover:text-[#1a3a52] transition-colors"
                        :class="loadingDocs ? 'opacity-50 cursor-wait' : ''">
                    <svg class="w-4 h-4" :class="loadingDocs ? 'animate-spin' : ''"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 0 0 4.582 9m0
                                 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 0 1-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
                <div class="glass-inner px-3 py-2 rounded-2xl text-xs text-[#1a3a52]/60">
                    Total: <span x-text="documents.length"></span>
                </div>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-hidden rounded-3xl glass-inner">
            <div class="h-full overflow-y-auto">

                <template x-if="loadingDocs">
                    <div class="p-6 flex items-center justify-center">
                        <svg class="w-5 h-5 animate-spin text-[#1a6fa8]/50" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                        </svg>
                    </div>
                </template>

                <template x-if="!loadingDocs && documents.length > 0">
                    <div class="divide-y divide-white/10">
                        <template x-for="doc in documents" :key="doc.document_id">
                            <div class="p-4 flex items-center gap-4">
                                <div class="w-11 h-11 rounded-2xl flex items-center justify-center
                                            shrink-0 bg-white/20">
                                    <svg class="w-5 h-5 text-[#1a6fa8]/70" fill="none"
                                         stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2
                                                 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414
                                                 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-medium text-[#1a3a52] truncate max-w-60"
                                           x-text="doc.document_name"></p>
                                        <span class="px-2 py-1 rounded-full text-[10px] uppercase
                                                     tracking-wide bg-white/15"
                                              :class="{
                                                  'text-yellow-600': doc.status === 'processing',
                                                  'text-green-600':  doc.status === 'indexed',
                                                  'text-rose-500':   doc.status === 'failed',
                                                  'text-[#1a3a52]/60': !['processing','indexed','failed'].includes(doc.status)
                                              }"
                                              x-text="doc.status">
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#1a3a52]/45 mt-1">
                                        <span x-text="formatDate(doc.created_at)"></span>
                                        <template x-if="doc.size">
                                            <span x-text="' · ' + formatSize(doc.size)"></span>
                                        </template>
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <button @click="confirmDelete(doc)"
                                            class="px-3 py-2 rounded-xl text-xs text-rose-500
                                                   hover:bg-rose-500/10 transition-all">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!loadingDocs && documents.length === 0">
                    <div class="h-full min-h-64 flex items-center justify-center p-6 text-center">
                        <div class="max-w-sm">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center
                                        mx-auto mb-4 bg-white/15">
                                <svg class="w-7 h-7 text-[#1a6fa8]/60" fill="none"
                                     stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M20 13V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10a2
                                             2 0 0 0 2 2h4"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 18h8m-4-4v8m8-8-4 4-4-4"/>
                                </svg>
                            </div>
                            <p class="text-[#1a3a52] font-medium text-sm">Belum ada dokumen tersimpan.</p>
                            <p class="text-[#1a3a52]/50 text-xs mt-1">
                                Upload dokumen untuk mulai mengisi database.
                            </p>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>

    {{-- ── Delete confirmation modal ───────────────────────────────────────── --}}
    <template x-if="deletingDoc">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm">
            <div class="glass-panel rounded-3xl p-6 max-w-sm w-full mx-4 shadow-2xl">
                <h4 class="text-[#1a3a52] font-semibold text-base mb-1"
                    style="font-family: 'Space Grotesk', sans-serif;">
                    Hapus dokumen?
                </h4>
                <p class="text-[#1a3a52]/60 text-sm mb-5">
                    "<span x-text="deletingDoc.document_name"></span>" akan dihapus permanen
                    beserta semua chunk-nya.
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="deletingDoc = null"
                            class="px-4 py-2 rounded-xl text-sm text-[#1a3a52]/70
                                   hover:bg-white/20 transition-all">
                        Batal
                    </button>
                    <button @click="deleteDocument()"
                            :disabled="deleting"
                            class="px-4 py-2 rounded-xl text-sm bg-rose-500 text-white
                                   hover:bg-rose-600 transition-all disabled:opacity-50">
                        <span x-text="deleting ? 'Menghapus...' : 'Ya, Hapus'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
function uploadForm() {
    return {
        activeView:   'upload',
        selectedFile: null,
        uploading:    false,
        uploaded:     false,
        dragging:     false,
        uploadError:  null,

        // Process tracker
        currentStep: 0,
        steps: [
            { label: 'Mengunggah file',       desc: 'Mentransfer dokumen ke server'       },
            { label: 'Membaca dokumen',        desc: 'Mengekstrak teks dari file'          },
            { label: 'Membersihkan konten',    desc: 'Memfilter teks yang tidak relevan'   },
            { label: 'Memotong ke chunks',     desc: 'Membagi teks menjadi potongan kecil' },
            { label: 'Menyimpan ke database',  desc: 'Memasukkan data ke MySQL'            },
            { label: 'Membuat vector index',   desc: 'Menghasilkan embedding FAISS'        },
        ],

        // Manage view
        documents:   [],
        loadingDocs: false,
        deletingDoc: null,
        deleting:    false,

        // ── File selection ─────────────────────────────────────────────────
        handleFile(e) {
            this.uploadError  = null;
            this.uploaded     = false;
            this.selectedFile = e.target.files[0] ?? null;
        },

        handleDrop(e) {
            this.dragging     = false;
            this.uploadError  = null;
            this.uploaded     = false;
            this.selectedFile = e.dataTransfer.files[0] ?? null;
        },

        // ── Formatting helpers ─────────────────────────────────────────────
        formatSize(bytes) {
            if (!bytes) return '';
            bytes = Number(bytes);
            if (bytes < 1024)    return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },

        formatDate(dateStr) {
            if (!dateStr) return 'Unknown date';
            return new Date(dateStr).toLocaleDateString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },

        // ── Step ticker — simulates pipeline progress during upload ────────
        // Each delay roughly mirrors the real step duration.
        // Step 5 (FAISS) completes only after the HTTP response arrives.
        _startStepTicker() {
            this.currentStep = 0;
            const delays = [700, 1100, 900, 1300, 1100]; // ms; 5 transitions → step 0–4
            let elapsed = 0;
            delays.forEach((delay, i) => {
                elapsed += delay;
                setTimeout(() => {
                    if (this.uploading && this.currentStep === i) {
                        this.currentStep = i + 1;
                    }
                }, elapsed);
            });
        },

        // ── Upload ─────────────────────────────────────────────────────────
        async submitUpload() {
            if (!this.selectedFile) return;

            this.uploading   = true;
            this.uploadError = null;
            this.currentStep = 0;
            this._startStepTicker();

            const form = new FormData();
            form.append('document', this.selectedFile);
            form.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const res = await fetch("{{ route('uploads.store') }}", {
                    method: 'POST',
                    body: form,
                    headers: {
                        Accept: 'application/json'
                    }
                });

                let data = {};

                try {
                    data = await res.json();
                } catch {
                    data = {
                        message: await res.text()
                    };
                }

                if (!res.ok) {
                    throw {
                        status: res.status,
                        message: data.message || 'Unknown error',
                        detail: data.error || data.exception || null
                    };
                }

                this.currentStep = this.steps.length;

                await new Promise(r => setTimeout(r, 500));

                this.uploaded = true;
                this.uploading = false;

                await this.fetchDocuments();

            } catch (err) {

                this.uploading = false;

                this.uploadError = {
                    title: 'Upload Gagal',
                    status: err.status || 'NETWORK',
                    message: err.message || 'Unknown error',
                    detail: err.detail || ''
                };
            }
        },

        // ── Document list ──────────────────────────────────────────────────
        async fetchDocuments() {
            this.loadingDocs = true;
            try {
                const res  = await fetch("{{ route('uploads.list') }}");
                const data = await res.json();
                this.documents = Array.isArray(data) ? data : [];
            } catch {
                // Silently fail
            } finally {
                this.loadingDocs = false;
            }
        },

        // ── Delete ─────────────────────────────────────────────────────────
        confirmDelete(doc) {
            this.deletingDoc = doc;
        },

        async deleteDocument() {
            if (!this.deletingDoc) return;
            this.deleting = true;
            try {
                const res = await fetch(`/upload/${this.deletingDoc.document_id}`, {
                    method:  'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                });
                if (res.ok) {
                    this.documents   = this.documents.filter(
                        d => d.document_id !== this.deletingDoc.document_id
                    );
                    this.deletingDoc = null;
                } else {
                    alert('Gagal menghapus dokumen. Coba lagi.');
                }
            } catch {
                alert('Koneksi bermasalah. Coba lagi.');
            } finally {
                this.deleting = false;
            }
        },

        // ── Reset ──────────────────────────────────────────────────────────
        reset() {
            this.selectedFile = null;
            this.uploaded     = false;
            this.uploading    = false;
            this.uploadError  = null;
            this.currentStep  = 0;
        },
    };
}
</script>