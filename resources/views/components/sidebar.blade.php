{{--
    Component: sidebar
    Usage: @include('components.sidebar', ['chatHistory' => $chatHistory])
--}}
<aside class="glass-panel sidebar flex flex-col h-full w-55 shrink-0 px-4 py-5 gap-2">

    {{-- Logo --}}
    <div class="flex items-center gap-2.5 px-2 mb-3">
        <div class="w-full h-8 rounded-lg glass-inner flex items-center text-lg leading-none">
            <img src="{{ asset('images/icons/Logo.png') }}" class="w-12 h-8 opacity-70" alt="Logo">
            <span class="text-[#1a3a52] font-semibold text-base tracking-tight"
                  style="font-family: 'Space Grotesk', sans-serif;">Lumina</span>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex flex-col gap-1">
<<<<<<< HEAD
        {{-- New Chat --}}
        {{-- route('dashboard.index') --}}
        <a href="{{ route('dashboard.index') }}"
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all">
            <img src="{{ asset('images/icons/NewChatIcon.png') }}" class="w-5 h-5 opacity-70" alt="New Chat">
            <span class="text-sm text-[#1a3a52]/80">New Chat</span>
=======

        <a href="{{ route('dashboard.index') }}"
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  {{ request()->routeIs('dashboard.index') ? 'sidebar-nav-active' : '' }}">
            <img src="{{ asset('images/icons/NewChatIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
            <span class="text-sm text-black/80">New Chat</span>
>>>>>>> de4cb21bd0c63cd3c2cc214cd7a0314d39fa4161
        </a>

        <a href="{{ route('uploads.index') }}"
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  {{ request()->routeIs('uploads.*') ? 'sidebar-nav-active' : '' }}">
            <img src="{{ asset('images/icons/UploadIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
            <span class="text-sm text-black/80">Upload Dokumen</span>
        </a>

        <div x-data="{ open: true }">
            <button @click="open = !open"
                    class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all">
                <img src="{{ asset('images/icons/HistoryIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
                <span class="text-sm text-black/80 flex-1 text-left">Riwayat Chat</span>
                <img src="{{ asset('images/icons/DropDownIcon.png') }}"
                     class="w-3.5 h-3.5 opacity-50 transition-transform duration-200"
                     :class="open ? 'rotate-180' : ''" alt="">
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex flex-col gap-0.5 pl-10 mt-0.5"
                 id="sidebar-history">

                @forelse($chatHistory ?? [] as $chat)
<<<<<<< HEAD
                    <a href="{{ route('chat.show', $chat->id) }}" class="text-sm text-[#1a3a52]/60 hover:text-[#1a3a52]/90 py-1.5 px-2 rounded-lg hover:bg-white/20 transition-all truncate">
                        {{ $chat->title ?? 'Chat ' . $loop->iteration }}
=======
                    <a href="{{ route('dashboard.show', $chat->query_id) }}"
                       class="text-sm text-[#1a3a52]/60 hover:text-[#1a3a52]/90 py-1.5 px-2
                              rounded-lg hover:bg-white/20 transition-all truncate block"
                       title="{{ $chat->query_text }}">
                        {{-- FIX: use display_title accessor instead of ->title --}}
                        {{ $chat->display_title }}
>>>>>>> de4cb21bd0c63cd3c2cc214cd7a0314d39fa4161
                    </a>
                @empty
                    <span class="text-xs text-[#1a3a52]/40 py-1 px-2 italic">
                        Belum ada riwayat
                    </span>
                @endforelse

                <a href="{{ route('history.index') }}" class="flex justify-end px-4 pt-1">
                    <button class="text-xs text-[#1a3a52]/35 hover:text-[#1a3a52]/60 transition-colors">
                        Lihat semua
                    </button>
                </a>
            </div>
        </div>
    </nav>

    <div class="flex-1"></div>

    <form method="POST" action="{{ route('logout') }}" class="px-1">
        @csrf
        <div class="w-full h-8 rounded-lg glass-inner flex items-center text-lg leading-none">
            <button type="submit"
                    class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all">
                <img src="{{ asset('images/icons/LogOutIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
                <span class="text-sm text-black/80">Log Out</span>
            </button>
        </div>
    </form>

</aside>