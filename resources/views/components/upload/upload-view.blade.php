<div x-show="activeView === 'upload'" x-cloak class="flex flex-col flex-1 min-h-0">

    <div class="flex-1 flex items-center justify-center px-5 py-4 min-h-0">

        {{-- Idle --}}
        <template x-if="!selectedFile && !uploading && !uploaded && !uploadError">
            <div class="text-center max-w-sm">
                <p class="text-[#1a3a52]/80 text-lg leading-relaxed"
                   style="font-family: 'Space Grotesk', sans-serif;">
                    Halo, Lumina disini siap membantu.<br>
                    Upload dokumenmu dan Lumina<br>
                    akan memprosesnya ke database.
                </p>
            </div>
        </template>

        {{-- Selected File --}}
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

        {{-- Uploading --}}
        <template x-if="uploading">
            <div class="w-full max-w-sm">
                <p class="text-[#1a3a52]/50 text-xs uppercase tracking-widest mb-4 text-center" style="font-family: 'Space Grotesk', sans-serif;">
                    Memproses dokumen
                </p>
                <div class="space-y-2">
                    <template x-for="(step, i) in steps" :key="i">
                        <div class="glass-inner rounded-2xl px-4 py-3 flex items-center gap-3 transition-all duration-500" :class="currentStep === i ? 'bg-white/25' : currentStep > i ? 'opacity-50' : 'opacity-30'">
                            <div class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0" :class="currentStep > i ? 'bg-green-500/20' : currentStep === i ? 'bg-[#1a6fa8]/15' : 'bg-white/10'">
                                <template x-if="currentStep > i">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="currentStep === i">
                                    <svg class="w-4 h-4 text-[#1a6fa8] animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                                    </svg>
                                </template>
                                <template x-if="currentStep < i">
                                    <div class="w-2 h-2 rounded-full bg-[#1a3a52]/25"></div>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[#1a3a52]" :class="currentStep === i ? 'opacity-100' : 'opacity-50'" x-text="step.label"></p>
                                <p class="text-xs text-[#1a3a52]/40" x-text="step.desc"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- Success --}}
        <template x-if="uploaded && !uploading">
            <div class="w-full max-w-sm text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-green-500/15">
                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-[#1a3a52] font-semibold text-base mb-1" style="font-family: 'Space Grotesk', sans-serif;">
                    Upload berhasil!
                </p>
                <p class="text-[#1a3a52]/50 text-sm mb-5">
                    Dokumen sedang diproses oleh Lumina.
                </p>
                <button @click="reset()" class="glass-inner px-5 py-2 rounded-xl text-sm text-[#1a6fa8] hover:bg-white/30 transition-all">
                    Upload lagi
                </button>
            </div>
        </template>

        {{-- Error --}}
        <template x-if="uploadError && !uploading">
            <div class="w-full max-w-sm text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-rose-500/15">
                    <svg class="w-7 h-7 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    </svg>
                </div>
                <p class="text-[#1a3a52] font-semibold text-base mb-1" style="font-family: 'Space Grotesk', sans-serif;">
                    Upload gagal.
                </p>
                <p class="text-[#1a3a52]/50 text-sm mb-5" x-text="uploadError"></p>
                <div class="space-y-2">
                    <p class="text-red-500 font-medium" x-text="uploadError.message">
                    </p>   
                    <p class="text-xs text-[#1a3a52]/60" x-show="uploadError.status">
                        Error Code:
                        <span x-text="uploadError.status"></span>
                    </p>
                    <p class="text-xs text-[#1a3a52]/50 wrap-break-words" x-show="uploadError.detail" x-text="uploadError.detail">
                    </p>
                </div>
            </div>
        </template>

    </div>

    @include('components.upload.upload-bar')

</div>