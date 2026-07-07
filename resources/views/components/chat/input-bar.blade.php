{{-- input-bar.blade.php --}}
<div class="glass-inner border-t border-white/10 px-4 py-4 mx-4 mt-4
            rounded-2xl backdrop-blur-md shrink-0">
    <div class="flex items-end gap-3">
        <textarea
            x-model="input"
            x-ref="inputBox"
            @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); sendMessage(); }"
            placeholder="Ketik pertanyaanmu disini..."
            rows="1"
            spellcheck="false"
            autocomplete="off"
            :disabled="loading"
            class="glass-inner flex-1 resize-none bg-transparent border border-white/20
                   focus:ring-0 focus:outline-none text-black placeholder-black/65 text-sm
                   rounded-xl px-4 py-3 backdrop-blur-sm disabled:opacity-50 transition-all"
            style="max-height: 160px;"
            @input="autoResize($el)">
        </textarea>

        <button
            @click.prevent="sendMessage()"
            :disabled="loading || !input.trim()"
            class="glass-inner w-12 h-12 rounded-xl flex items-center justify-center
                   shrink-0 text-[#1a6fa8] hover:bg-white/30 disabled:opacity-40
                   disabled:cursor-not-allowed transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                 stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77
                         59.77 0 013.269 20.876L5.999 12zm0 0h7.5"/>
            </svg>
        </button>
    </div>

    <p class="text-xs text-black/80 mt-2 text-center">
        Enter untuk kirim · Shift+Enter baris baru
    </p>
</div>