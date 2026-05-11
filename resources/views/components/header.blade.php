{{--
    Component: header
    Usage: @include('components.header', ['title' => 'Selamat datang, User123'])
--}}
<header class="glass-panel header flex items-center justify-between px-6 py-4">

    <span class="text-[#1a3a52] font-medium text-base tracking-tight" style="font-family: 'Space Grotesk', sans-serif;">
        {{ $title ?? 'Selamat datang, ' . (auth()->user()->name ?? 'User') }}
    </span>

    {{-- Profile icon with dropdown --}}
    <div class="relative" x-data="{ open: false }">

        <button
            @click="open = !open"
            @click.outside="open = false"
            class="glass-inner w-9 h-9 rounded-full flex items-center justify-center hover:scale-105 transition-transform focus:outline-none">
            <svg class="w-5 h-5 text-[#1a6fa8]/70" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
        </button>

        {{-- Dropdown menu --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
            class="absolute right-0 mt-2 w-48 rounded-xl shadow-lg border border-white/30 bg-white/70 backdrop-blur-md z-50"
            style="display: none;">

            <div class="py-1">
                {{-- route('change-password.index') --}}
                <a href="#"
                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-[#1a3a52] hover:bg-[#1a6fa8]/10 transition-colors"
                   style="font-family: 'Space Grotesk', sans-serif;">
                    <svg class="w-4 h-4 text-[#1a6fa8]/70" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                    </svg>
                    Ganti Password
                </a>
            </div>

        </div>
    </div>

</header>