<div x-show="activeView === 'ingesting'" x-cloak class="flex-1 min-h-0 flex flex-col p-5 gap-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-[#1a3a52]" style="font-family: 'Space Grotesk', sans-serif;">
                Proses Ingesting
            </p>
            <p class="text-xs text-[#1a3a52]/50">Status: <span x-text="ingestStatus"></span></p>
        </div>
        <div class="glass-inner px-3 py-2 rounded-2xl text-xs text-[#1a3a52]/60">
            <span x-text="ingestLogs.length"></span> log
        </div>
    </div>

    <div class="flex-1 min-h-0 overflow-hidden rounded-3xl glass-inner flex flex-col">
        {{-- Table header --}}
        <div class="grid grid-cols-[84px_120px_1fr] gap-3 px-4 py-2 border-b border-white/10 shrink-0">
            <span class="text-[10px] font-medium uppercase tracking-wide text-[#1a3a52]/40 text-center">Waktu</span>
            <span class="text-[10px] font-medium uppercase tracking-wide text-[#1a3a52]/40 text-center">Tahap</span>
            <span class="text-[10px] font-medium uppercase tracking-wide text-[#1a3a52]/40 text-center">Detail</span>
        </div>

        <div class="flex-1 overflow-y-auto px-4" x-ref="logContainer">
            <template x-if="ingestLogs.length === 0">
                <div class="text-center text-[#1a3a52]/50 text-sm py-10">Menunggu log pertama...</div>
            </template>
            <template x-for="log in ingestLogs" :key="log.id">
                <div class="grid grid-cols-[84px_120px_1fr] gap-3 items-center py-2 border-b border-white/10 last:border-0">
                    <span class="text-[10px] text-[#1a3a52]/40 tabular-nums" x-text="new Date(log.created_at).toLocaleTimeString()"></span>
                    <span>
                        <span class="inline-flex justify-center items-center w-full px-2 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wide bg-white/15 text-[#1a3a52]/70" x-text="log.step"></span>
                    </span>
                    <span class="text-sm text-[#1a3a52]/80 truncate" x-text="log.message" :title="log.message"></span>
                </div>
            </template>
        </div>
    </div>

    <div class="flex justify-end">
        <button @click="activeView = 'manage'; fetchDocuments()" class="glass-inner px-4 py-2 rounded-xl text-sm text-[#1a6fa8] hover:bg-white/30 transition-all">
            Lihat Dokumen
        </button>
    </div>
</div>