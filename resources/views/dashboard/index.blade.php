@extends('layouts.app')
@section('title', 'Chat')

@push('topbar-actions')
    <button
        @click="$dispatch('clear-chat')"
        class="text-xs text-stone-500 hover:text-stone-300 border border-stone-800 hover:border-stone-700 px-3 py-1.5 rounded-lg transition-colors">
        Clear chat
    </button>
@endpush

@section('content')
<div class="flex flex-col h-full" x-data="chatApp()" x-on:clear-chat.window="clearChat()">
    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6" x-ref="messages">

        {{-- Empty state --}}
        <template x-if="messages.length === 0">
            <div class="flex flex-col items-center justify-center h-full text-center py-20">
                <p class="text-[#1a3a52]/70 text-lg leading-relaxed" style="font-family: 'Space Grotesk', sans-serif;">
                    Halo, Lumina disini siap membantu.<br>
                    Tanyakan pertanyaanmu dan Lumina<br>
                    akan membantu menjawabnya berdasarkan<br>
                    pengetahuan Lumina
                </p>
            </div>
        </template>

        {{-- Message list --}}
        <template x-for="(msg, index) in messages" :key="index">
            <div class="flex gap-3" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">

                {{-- AI Avatar --}}
                <template x-if="msg.role === 'assistant'">
                    <div class="w-8 h-8 rounded-lg bg-[#C9DCE4] flex items-center justify-center shrink-0 mt-0.5">
                        <img src="{{ asset('images/icons/Logo.png') }}" class="w-12 h-8 opacity-70" alt="Logo">
                    </div>
                </template>

                {{-- Bubble --}}
                <div
                    class="max-w-xl px-4 py-3 rounded-2xl text-sm leading-relaxed"
                    :class="msg.role === 'user'
                        ? 'bg-[#5BB7EC] text-white rounded-tr-sm'
                        : 'bg-[#92C7DD] text-white rounded-tl-sm'"
                    x-text="msg.content"
                ></div>
            </div>
        </template>

        {{-- Typing indicator --}}
        <template x-if="loading">
            <div class="flex gap-3 justify-start">
                <div class="w-8 h-8 rounded-lg bg-[#C9DCE4] flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/icons/Logo.png') }}" class="w-12 h-8 opacity-70" alt="Logo">
                </div>
                <div class="bg-[#92C7DD] px-4 py-3 rounded-2xl rounded-tl-sm flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-bounce" style="animation-delay:300ms"></span>
                </div>
            </div>
        </template>
    </div>

{{-- Input area --}}
<div class="glass-inner border-t border-white/10 px-4 py-4 mx-4 mb-4 rounded-2xl backdrop-blur-md">

    <form @submit.prevent="sendMessage()" class="flex items-end gap-3">

        <textarea
            x-model="input"
            x-ref="inputBox"
            @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
            placeholder="Ketik pertanyaanmu disini..."
            rows="1"
            :disabled="loading"
            class="glass-inner flex-1 resize-none bg-transparent border border-white/20 focus:ring-0 focus:outline-none text-black placeholder-black/65 text-sm rounded-xl px-4 py-3 backdrop-blur-sm disabled:opacity-50 transition-all"
            style="max-height: 160px;"
            @input="autoResize($el)"
        ></textarea>

        <button
            type="submit"
            :disabled="loading || !input.trim()"
            class="glass-inner w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-[#1a6fa8] hover:bg-white/30 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.269 20.876L5.999 12zm0 0h7.5"/>
            </svg>
        </button>

    </form>

    <p class="text-xs text-[#1a3a52]/50 mt-2 text-center">
        Press Enter to send · Shift+Enter for new line
    </p>

</div>
@endsection

@push('scripts')
<script>
function chatApp() {
    return {
        messages: [],
        input: '',
        loading: false,

        async sendMessage() {
            const question = this.input.trim();
            if (!question || this.loading) return;

            this.messages.push({ role: 'user', content: question });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => {
                this.scrollToBottom();
                this.$refs.inputBox.style.height = 'auto';
            });

            try {
                const res = await fetch('/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ question }),
                });
                const data = await res.json();
                this.messages.push({ role: 'assistant', content: data.answer ?? 'No response.' });
            } catch {
                this.messages.push({ role: 'assistant', content: 'Something went wrong. Please try again.' });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        clearChat() {
            this.messages = [];
            this.input = '';
        },

        scrollToBottom() {
            const el = this.$refs.messages;
            if (el) el.scrollTop = el.scrollHeight;
        },

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 160) + 'px';
        },
    }
}
</script>
@endpush
