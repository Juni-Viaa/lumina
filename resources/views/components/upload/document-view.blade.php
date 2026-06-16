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
            <button @click="fetchDocuments()" class="glass-inner p-2 rounded-xl text-[#1a3a52]/50 hover:text-[#1a3a52] transition-colors" :class="loadingDocs ? 'opacity-50 cursor-wait' : ''">
                <svg class="w-4 h-4" :class="loadingDocs ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0 0 4.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 0 1-15.357-2m15.357 2H15"/>
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
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v8H4z"/>
                    </svg>
                </div>
            </template>

            <template x-if="!loadingDocs && documents.length > 0">
                <div class="divide-y divide-white/10">
                    <template x-for="doc in documents" :key="doc.document_id">
                        <div class="p-4 flex items-center gap-4">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 bg-white/20">
                                <svg class="w-5 h-5 text-[#1a6fa8]/70" fill="none"
                                     stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium text-[#1a3a52] truncate max-w-60" x-text="doc.document_name"></p>
                                    <span class="px-2 py-1 rounded-full text-[10px] uppercase tracking-wide bg-white/15" :class="{ 'text-yellow-600': doc.status === 'processing', 'text-green-600':  doc.status === 'indexed', 'text-rose-500':   doc.status === 'failed', 'text-[#1a3a52]/60': !['processing','indexed','failed'].includes(doc.status) }" x-text="doc.status">
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
                                <button @click="confirmDelete(doc)" class="px-3 py-2 rounded-xl text-xs text-rose-500 hover:bg-rose-500/10 transition-all">
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