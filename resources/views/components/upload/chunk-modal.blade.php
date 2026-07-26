<template x-if="chunkModalDoc">
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-[#C9DCE4]/80 backdrop-blur-sm">
        <div class="glass-panel rounded-3xl p-6 max-w-2xl w-full mx-4 shadow-2xl max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-[#1a3a52] font-semibold text-base">
                    Chunk untuk "<span x-text="chunkModalDoc.document_name"></span>"
                </h4>
                <button @click="closeChunkModal()" class="text-[#1a3a52]/50 hover:text-[#1a3a52]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto">
                <template x-if="loadingChunks">
                    <div class="flex justify-center py-10">
                        <svg class="w-5 h-5 animate-spin text-[#1a6fa8]/50" ...>...</svg>
                    </div>
                </template>
                <template x-if="!loadingChunks && chunkList.length === 0">
                    <div class="text-center text-[#1a3a52]/50 text-sm">Tidak ada chunk.</div>
                </template>
                <template x-for="(chunk, idx) in chunkList" :key="chunk.chunk_id">
                    <div class="border-b border-white/10 py-3 last:border-0">
                        <p class="text-xs text-[#1a3a52]/40">Chunk #<span x-text="idx+1"></span></p>
                        <p class="text-sm text-[#1a3a52] mt-1 break-words" x-text="chunk.chunk_text"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>