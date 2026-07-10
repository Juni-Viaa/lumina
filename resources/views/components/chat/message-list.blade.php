{{-- message-list.blade.php --}}
<div class="flex-1 overflow-y-auto px-4 py-6 space-y-6 scrollable" x-ref="messages">

    {{-- Empty / idle state --}}
    <template x-if="messages.length === 0">
        <div class="flex flex-col items-center justify-center h-full text-center py-20 gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-sm">
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
        <div class="flex gap-3"
             :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">

            {{-- AI avatar --}}
            <template x-if="msg.role === 'assistant'">
                <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 shadow-sm
                            flex items-center justify-center shrink-0 mt-1">
                    <img src="{{ asset('images/icons/Logo.png') }}"
                         class="w-10 h-7 opacity-70" alt="Lumina">
                </div>
            </template>

            {{-- Bubble --}}
            <div class="max-w-2xl rounded-2xl text-md"
                 :class="msg.role === 'user'
                     ? 'bg-white border border-slate-200 shadow-sm rounded-tr-sm px-4 py-3'
                     : 'bg-white border border-slate-200 shadow-sm rounded-tl-sm px-5 py-4'">

                {{-- Assistant: rendered markdown — color is set by .prose-chat, NOT inherited from bubble --}}
                <template x-if="msg.role === 'assistant'">
                    <div class="prose-chat" x-html="renderMarkdown(msg.content)"></div>
                </template>

                {{-- User: plain text, preserve line breaks --}}
                <template x-if="msg.role === 'user'">
                    <span class="text-[#0f172a]"
                          style="white-space: pre-wrap; word-break: break-word; font-size: 15px; line-height: 1.7;"
                          x-text="msg.content"></span>
                </template>
            </div>
        </div>
    </template>

    {{-- Typing indicator --}}
    <template x-if="loading">
        <div class="flex gap-3 justify-start">
            <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 shadow-sm
                        flex items-center justify-center shrink-0">
                <img src="{{ asset('images/icons/Logo.png') }}"
                     class="w-10 h-7 opacity-70" alt="Lumina">
            </div>
            <div class="bg-white border border-slate-200 shadow-sm
                        px-5 py-4 rounded-2xl rounded-tl-sm flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full animate-bounce bg-[#1a3a52]/60"
                      style="animation-delay:0ms"></span>
                <span class="w-2 h-2 rounded-full animate-bounce bg-[#1a3a52]/60"
                      style="animation-delay:150ms"></span>
                <span class="w-2 h-2 rounded-full animate-bounce bg-[#1a3a52]/60"
                      style="animation-delay:300ms"></span>
            </div>
        </div>
    </template>

</div>