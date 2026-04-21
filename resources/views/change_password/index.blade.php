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
<div
    class="flex flex-col h-full"
    x-data="chatApp()"
    x-on:clear-chat.window="clearChat()"
>
    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6" x-ref="messages">

        {{-- Empty state --}}
        <template x-if="messages.length === 0">
            <div class="flex flex-col items-center justify-center h-full text-center py-20">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                    </svg>
                </div>
                <h2 class="text-stone-300 font-medium mb-1">Ask anything about your documents</h2>
                <p class="text-sm text-stone-500 max-w-xs">LuminaRAG will search through your uploaded documents to find the most relevant answers.</p>
            </div>
        </template>

        {{-- Message list --}}
        <template x-for="(msg, index) in messages" :key="index">
            <div class="flex gap-3" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">

                {{-- AI Avatar --}}
                <template x-if="msg.role === 'assistant'">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        </svg>
                    </div>
                </template>

                {{-- Bubble --}}
                <div
                    class="max-w-xl px-4 py-3 rounded-2xl text-sm leading-relaxed"
                    :class="msg.role === 'user'
                        ? 'bg-amber-500/10 text-amber-100 rounded-tr-sm'
                        : 'bg-stone-800 text-stone-200 rounded-tl-sm'"
                    x-text="msg.content"
                ></div>
            </div>
        </template>

        {{-- Typing indicator --}}
        <template x-if="loading">
            <div class="flex gap-3 justify-start">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                    </svg>
                </div>
                <div class="bg-stone-800 px-4 py-3 rounded-2xl rounded-tl-sm flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-stone-500 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 bg-stone-500 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 bg-stone-500 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                </div>
            </div>
        </template>
    </div>

    {{-- Input area --}}
    <div class="border-t border-stone-800 px-4 py-4 bg-stone-950">
        <form @submit.prevent="sendMessage()" class="flex items-end gap-3">
            <textarea
                x-model="input"
                x-ref="inputBox"
                @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                placeholder="Ask a question about your documents…"
                rows="1"
                :disabled="loading"
                class="flex-1 resize-none bg-stone-900 border border-stone-700 focus:border-amber-500/50 focus:ring-0 focus:outline-none
                       text-stone-200 placeholder-stone-600 text-sm rounded-xl px-4 py-3
                       disabled:opacity-50 transition-colors"
                style="max-height: 160px;"
                @input="autoResize($el)"
            ></textarea>
            <button
                type="submit"
                :disabled="loading || !input.trim()"
                class="w-10 h-10 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-40 disabled:cursor-not-allowed
                       flex items-center justify-center transition-colors shrink-0"
            >
                <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.269 20.876L5.999 12zm0 0h7.5"/>
                </svg>
            </button>
        </form>
        <p class="text-xs text-stone-600 mt-2 text-center">Press Enter to send · Shift+Enter for new line</p>
    </div>
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
