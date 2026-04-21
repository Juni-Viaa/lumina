{{--
    Component: upload-form
    Usage: @include('components.upload-form')
--}}
<div class="glass-panel upload-form flex flex-col h-full" x-data="uploadForm()">

    {{-- Drop zone / content area --}}
    <div class="flex-1 flex items-center justify-center p-8">

        {{-- Idle state --}}
        <template x-if="!selectedFile && !uploading && !uploaded">
            <div class="text-center max-w-sm">
                <p class="text-[#1a3a52]/70 text-lg leading-relaxed" style="font-family: 'Space Grotesk', sans-serif;">
                    Halo, Lumina disini siap membantu.<br>
                    Upload dokumenmu dan Lumina<br>
                    akan memprosesnya ke database
                </p>
            </div>
        </template>

        {{-- File selected state --}}
        <template x-if="selectedFile && !uploading && !uploaded">
            <div class="text-center">
                <div class="glass-inner w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#1a6fa8]/70" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-[#1a3a52] font-medium text-sm mb-1" x-text="selectedFile.name"></p>
                <p class="text-[#1a3a52]/50 text-xs" x-text="formatSize(selectedFile.size)"></p>
                <button @click="selectedFile = null" class="mt-3 text-xs text-[#1a6fa8]/60 hover:text-[#1a6fa8] transition-colors">
                    Remove
                </button>
            </div>
        </template>

        {{-- Uploading state --}}
        <template x-if="uploading">
            <div class="text-center">
                <div class="w-12 h-12 mx-auto mb-4 relative">
                    <svg class="animate-spin w-12 h-12 text-[#1a6fa8]/40" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
                <p class="text-[#1a3a52]/70 text-sm" style="font-family: 'Space Grotesk', sans-serif;">Memproses dokumen…</p>
            </div>
        </template>

        {{-- Success state --}}
        <template x-if="uploaded">
            <div class="text-center">
                <div class="glass-inner w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-500/80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </div>
                <p class="text-[#1a3a52] font-medium text-sm mb-1">Dokumen berhasil diupload!</p>
                <button @click="reset()" class="mt-2 text-xs text-[#1a6fa8]/60 hover:text-[#1a6fa8] transition-colors">
                    Upload lagi
                </button>
            </div>
        </template>

    </div>

    {{-- Bottom upload bar --}}
    <div class="glass-inner upload-bar flex items-center gap-3 mx-4 mb-4 px-4 py-3 rounded-2xl cursor-pointer hover:bg-white/20 transition-all"
         @click="$refs.fileInput.click()"
         @dragover.prevent="dragging = true"
         @dragleave.prevent="dragging = false"
         @drop.prevent="handleDrop($event)"
         :class="dragging ? 'ring-2 ring-[#1a6fa8]/40' : ''">

        <div class="glass-inner w-8 h-8 rounded-xl flex items-center justify-center shrink-0">
            <img src="{{ asset('images/icons/UploadIcon.png') }}" class="w-4 h-4 opacity-60" alt="Upload">
        </div>

        <span class="text-sm text-[#1a3a52]/50 flex-1 select-none" style="font-family: 'Space Grotesk', sans-serif;">
            Upload Dokumen Disini
        </span>

        <template x-if="selectedFile && !uploading">
            <button
                @click.stop="submitUpload()"
                class="glass-inner px-4 py-1.5 rounded-xl text-xs font-medium text-[#1a6fa8] hover:bg-white/30 transition-all">
                Kirim
            </button>
        </template>

        <input
            type="file"
            x-ref="fileInput"
            class="hidden"
            accept=".pdf,.txt,.docx"
            @change="handleFile($event)">
    </div>

</div>

<script>
function uploadForm() {
    return {
        selectedFile: null,
        uploading: false,
        uploaded: false,
        dragging: false,

        handleFile(e) {
            this.selectedFile = e.target.files[0] ?? null;
        },

        handleDrop(e) {
            this.dragging = false;
            this.selectedFile = e.dataTransfer.files[0] ?? null;
        },

        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },

        async submitUpload() {
            if (!this.selectedFile) return;
            this.uploading = true;
            const form = new FormData();
            form.append('document', this.selectedFile);
            form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            try {
                const res = await fetch('{{ route('uploads.store') }}', { method: 'POST', body: form });
                if (res.ok) this.uploaded = true;
            } catch {
                alert('Upload gagal. Coba lagi.');
            } finally {
                this.uploading = false;
            }
        },

        reset() {
            this.selectedFile = null;
            this.uploaded = false;
            this.uploading = false;
        }
    }
}
</script>
