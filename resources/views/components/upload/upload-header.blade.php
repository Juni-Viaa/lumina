<div class="px-5 pt-5 pb-3 border-b border-white/10 flex items-start justify-between gap-4 shrink-0">
    <div>
        <h3 class="text-[#1a3a52] text-lg font-semibold leading-tight"
            style="font-family: 'Space Grotesk', sans-serif;">
            Upload & Manage
        </h3>
    </div>

    <div class="glass-inner inline-flex p-1 rounded-2xl">
        <button
            @click="activeView='upload'"
            class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all"
            :class="activeView === 'upload'
                ? 'bg-white/35 text-[#1a3a52]'
                : 'text-[#1a3a52]/55 hover:text-[#1a3a52]'">
            Upload
        </button>

        <button
            @click="activeView='manage'; fetchDocuments()"
            class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all"
            :class="activeView === 'manage'
                ? 'bg-white/35 text-[#1a3a52]'
                : 'text-[#1a3a52]/55 hover:text-[#1a3a52]'">
            Documents
        </button>
    </div>
</div>