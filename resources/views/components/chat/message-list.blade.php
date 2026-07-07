{{-- message-list.blade.php --}}
<div class="flex-1 overflow-y-auto px-4 py-6 space-y-6 scrollable" x-ref="messages">

    {{-- Empty / idle state --}}
    <template x-if="messages.length === 0">
        <div class="flex flex-col items-center justify-center h-full text-center py-20">
            <p class="text-lg leading-relaxed" style="color:  black; font-family: 'Space Grotesk', sans-serif;">
                Halo, Lumina disini siap membantu.<br>
                Tanyakan pertanyaanmu dan Lumina<br>
                akan membantu menjawabnya berdasarkan<br>
                pengetahuan Lumina
            </p>
        </div>
    </template>

    {{-- Message list --}}
    <template x-for="(msg, index) in messages" :key="index">
        <div class="flex gap-3"
            :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">

            {{-- AI avatar --}}
            <template x-if="msg.role === 'assistant'">
                <div class="w-10 h-10 rounded-lg bg-white flex items-center
                            justify-center shrink-0 mt-0.5 border border-slate-200 shadow-sm">
                    <img src="{{ asset('images/icons/Logo.png') }}"
                        class="w-12 h-8 opacity-70" alt="Lumina">
                </div>
            </template>

            {{-- Bubble --}}
            <div class="max-w-2xl px-4 py-3 rounded-2xl text-base leading-relaxed"
                :class="msg.role === 'user'
                     ? 'bg-[#5BB7EC]/40 border border-slate-200 shadow-sm rounded-tr-sm'
                     : 'bg-white/60 border border-slate-200 shadow-sm rounded-tl-sm'"
                :style="msg.role === 'user'
                     ? 'color: #000000;'
                     : 'color: #000000;'">

                {{-- Assistant: render markdown --}}
                <template x-if="msg.role === 'assistant'">
                    <div
                        class="prose prose-slate max-w-none prose-headings:font-semibold prose-p:leading-7 prose-li:leading-7 prose-pre:bg-slate-900 prose-pre:text-white prose-chat"
                        x-html="renderMarkdown(msg.content)">
                    </div>
                </template>

                {{-- User: preserve newlines --}}
                <template x-if="msg.role === 'user'">
                    <span style="white-space: pre-wrap; word-break: break-word;"
                        x-text="msg.content"></span>
                </template>
            </div>
        </div>
    </template>

    {{-- Typing indicator --}}
    <template x-if="loading">
        <div class="flex gap-3 justify-start">
            <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 shadow-sm flex items-center
                        justify-center shrink-0">
                <img src="{{ asset('images/icons/Logo.png') }}"
                    class="w-12 h-8 opacity-70" alt="Lumina">
            </div>
            <div class="bg-white/60 border border-slate-200 shadow-sm px-4 py-3 rounded-2xl rounded-tl-sm
                        flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full animate-bounce bg-[#1a3a52]/60"
                    style="animation-delay:0ms"></span>
                <span class="w-1.5 h-1.5 rounded-full animate-bounce bg-[#1a3a52]/60"
                    style="animation-delay:150ms"></span>
                <span class="w-1.5 h-1.5 rounded-full animate-bounce bg-[#1a3a52]/60"
                    style="animation-delay:300ms"></span>
            </div>
        </div>
    </template>

</div>