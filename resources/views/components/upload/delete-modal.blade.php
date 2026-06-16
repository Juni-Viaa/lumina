<template x-if="deletingDoc">
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm">
        <div class="glass-panel rounded-3xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <h4 class="text-[#1a3a52] font-semibold text-base mb-1">
                Hapus dokumen?
            </h4>
            <p class="text-[#1a3a52]/60 text-sm mb-5">
                "<span x-text="deletingDoc.document_name"></span>"
                akan dihapus permanen beserta semua chunk-nya.
            </p>
            <div class="flex gap-3 justify-end">
                <button
                    @click="deletingDoc=null"
                    class="px-4 py-2 rounded-xl text-sm text-[#1a3a52]/70">
                    Batal
                </button>
                <button
                    @click="deleteDocument()" :disabled="deleting" class="px-4 py-2 rounded-xl text-sm bg-rose-500 text-white">
                    <span
                        x-text="deleting ? 'Menghapus...' : 'Ya, Hapus'">
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>