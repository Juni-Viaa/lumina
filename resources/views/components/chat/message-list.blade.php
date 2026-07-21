{{-- message-list.blade.php --}}
<div class="flex-1 overflow-y-auto px-3 md:px-4 py-4 md:py-6 space-y-4 md:space-y-6 scrollable"
     x-ref="messages">

    {{-- Empty / idle state --}}
    <template x-if="messages.length === 0">
        <div class="flex flex-col items-center justify-center h-full text-center py-12 md:py-20 gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white/80 flex items-center justify-center shadow-sm">
                <img src="{{ asset('images/icons/Logo.png') }}" class="w-10 h-7 opacity-70" alt="Lumina">
            </div>
            <p class="text-[#1a3a52]/80 text-base leading-relaxed"
               style="font-family: 'Space Grotesk', sans-serif;">
                Halo! Saya <strong>Lumina</strong>, asisten akademikmu.<br>
                Ajukan pertanyaan dan aku akan menjawabnya<br>
                sesuai pengetahuanku.
            </p>
        </div>
    </template>

    {{-- Message list --}}
    <template x-for="(msg, index) in messages" :key="index">
        <div class="flex gap-2 md:gap-3"
             :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">

            {{-- AI avatar --}}
            <template x-if="msg.role === 'assistant'">
                <div class="w-7 h-7 md:w-8 md:h-8 rounded-xl bg-white border border-slate-200
                            shadow-sm flex items-center justify-center shrink-0 mt-1">
                    <img src="{{ asset('images/icons/Logo.png') }}"
                         class="w-9 h-6 md:w-10 md:h-7 opacity-70" alt="Lumina">
                </div>
            </template>

            {{-- Bubble --}}
            <div class="rounded-2xl text-sm"
                 :class="msg.role === 'user'
                     ? 'bg-white border border-slate-200 shadow-sm rounded-tr-sm px-3 py-2.5 md:px-4 md:py-3 max-w-[85%] md:max-w-2xl'
                     : 'bg-white border border-slate-100 shadow-sm rounded-tl-sm px-3 py-3 md:px-5 md:py-4 max-w-[92%] md:max-w-2xl'">

                {{-- Assistant: rendered markdown --}}
                <template x-if="msg.role === 'assistant'">
                    <div class="prose-chat" x-html="renderMarkdown(msg.content)"></div>
                </template>

                {{-- User: plain text --}}
                <template x-if="msg.role === 'user'">
                    <span class="text-[#0f172a]"
                          style="white-space: pre-wrap; word-break: break-word; font-size: 14px; line-height: 1.65;"
                          x-text="msg.content"></span>
                </template>
            </div>
        </div>
    </template>

    {{-- Typing indicator --}}
    <template x-if="loading">
        <div class="flex gap-2 md:gap-3 justify-start">
            <div class="w-7 h-7 md:w-8 md:h-8 rounded-xl bg-white border border-slate-200
                        shadow-sm flex items-center justify-center shrink-0">
                <img src="{{ asset('images/icons/Logo.png') }}"
                     class="w-9 h-6 opacity-70" alt="Lumina">
            </div>
            <div class="bg-white border border-slate-200 shadow-sm
                        px-4 py-3 rounded-2xl rounded-tl-sm flex items-center gap-1.5">
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