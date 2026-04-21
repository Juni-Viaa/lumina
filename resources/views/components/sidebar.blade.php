{{--
    Component: sidebar
    Usage: @include('components.sidebar', ['chatHistory' => $chatHistory])
--}}
<aside class="glass-panel sidebar flex flex-col h-full w-[220px] shrink-0 px-4 py-5 gap-2">

    {{-- Logo --}}
    <div class="flex items-center gap-2.5 px-2 mb-3">
        <div class="w-8 h-8 rounded-lg glass-inner flex items-center justify-center font-bold text-[#1a6fa8] text-lg leading-none" style="font-family: 'Space Grotesk', sans-serif;">
            &#x2112;
        </div>
        <span class="text-[#1a3a52] font-semibold text-base tracking-tight" style="font-family: 'Space Grotesk', sans-serif;">Lumina</span>
    </div>

    {{-- Nav items --}}
    <nav class="flex flex-col gap-1">
        {{-- New Chat --}}
        {{-- route('chat.index') --}}
        <a href="#"
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all">
            <img src="{{ asset('images/icons/NewChatIcon.png') }}" class="w-5 h-5 opacity-70" alt="New Chat">
            <span class="text-sm text-[#1a3a52]/80">New Chat</span>
        </a>

        {{-- Upload Dokumen --}}
        <a href="{{ route('uploads.index') }}"
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  {{ request()->routeIs('uploads.*') ? 'sidebar-nav-active' : '' }}">
            <img src="{{ asset('images/icons/UploadIcon.png') }}" class="w-5 h-5 opacity-70" alt="Upload">
            <span class="text-sm text-[#1a3a52]/80">Upload Dokumen</span>
        </a>

        {{-- Riwayat Chat (collapsible) --}}
        <div x-data="{ open: true }">
            <button @click="open = !open"
                    class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all">
                <img src="{{ asset('images/icons/HistoryIcon.png') }}" class="w-5 h-5 opacity-70" alt="History">
                <span class="text-sm text-[#1a3a52]/80 flex-1 text-left">Riwayat Chat</span>
                <img src="{{ asset('images/icons/DropDownIcon.png') }}"
                     class="w-3.5 h-3.5 opacity-50 transition-transform duration-200"
                     :class="open ? 'rotate-180' : ''"
                     alt="">
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex flex-col gap-0.5 pl-10 mt-0.5">
                @forelse($chatHistory ?? [] as $chat)
                    <a href="#" class="text-sm text-[#1a3a52]/60 hover:text-[#1a3a52]/90 py-1.5 px-2 rounded-lg hover:bg-white/20 transition-all truncate">
                        {{ $chat->title ?? 'Chat ' . $loop->iteration }}
                    </a>
                @empty
                    <span class="text-xs text-[#1a3a52]/40 py-1 px-2">Chat 1</span>
                    <span class="text-xs text-[#1a3a52]/40 py-1 px-2">Chat 2</span>
                    <span class="text-xs text-[#1a3a52]/40 py-1 px-2">Chat 3</span>
                @endforelse
            </div>
        </div>
    </nav>

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Log Out 
    <form method="POST" action="{{ route('logout') }}" class="px-1">
        @csrf
        <button type="submit"
                class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all">
            <img src="{{ asset('images/icons/LogOutIcon.png') }}" class="w-5 h-5 opacity-70" alt="Log Out">
            <span class="text-sm text-[#1a3a52]/80">Log Out</span>
        </button>
    </form>
    --}}

</aside>
