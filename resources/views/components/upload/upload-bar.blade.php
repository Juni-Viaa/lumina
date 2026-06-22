<div class="p-5 shrink-0">
    <template x-if="!uploading && !uploaded">
        <div>
            <div
                class="glass-inner upload-bar flex items-center gap-3 px-4 py-3 rounded-2xl cursor-pointer hover:bg-white/20 transition-all"
                @click="$refs.fileInput.click()"
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="handleDrop($event)"
                :class="dragging ? 'ring-2 ring-[#1a6fa8]/40' : ''">

                <div class="glass-inner w-9 h-9 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[#1a6fa8]/70" fill="none" stroke="currentColor"
                        stroke-width="1.6" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 16V8m0 0-3 3m3-3 3 3"/>
                    </svg>
                </div>

                <span class="text-sm text-[#1a3a52]/80 flex-1">
                    Upload dokumen untuk diproses ke database Lumina.
                </span>

                <template x-if="selectedFile">
                    <button
                        @click.stop="submitUpload()"
                        class="glass-inner px-4 py-1.5 rounded-xl text-xs font-medium text-[#1a6fa8]">
                        Kirim
                    </button>
                </template>

                <input
                    type="file"
                    x-ref="fileInput"
                    class="hidden"
                    accept=".pdf,.doc,.docx"
                    @change="handleFile($event)">
            </div>

            <p class="mt-3 text-[11px] text-[#1a3a52]/80 px-1">
                PDF, DOC, dan DOCX didukung · Maks. 100 MB
            </p>
        </div>
    </template>
</div>