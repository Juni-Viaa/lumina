{{--
    @include('components.header', [
    'title' => 'Selamat datang, ' . auth()->user()->username
])
--}}
<header class="glass-panel header relative z-50 flex items-center justify-between px-6 py-4">
    <span class="text-[#1a3a52] font-medium text-base tracking-tight"
          style="font-family: 'Space Grotesk', sans-serif;">
        {{ $title ?? 'Selamat datang, ' . (auth()->user()->username) }}
    </span>

    {{-- Profile Dropdown --}}
    <div class="relative" x-data="{ open: false }">

        {{-- Profile Button --}}
        <button
            @click="open = !open"
            class="glass-inner w-9 h-9 rounded-full flex items-center justify-center hover:scale-105 transition-transform"
        >
            <svg class="w-5 h-5 text-[#1a6fa8]/70"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.75"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
        </button>

        {{-- Dropdown --}}
        <div
            x-show="open"
            @click.away="open = false"
            x-transition
           class="absolute right-0 top-full mt-4 w-48 glass-panel rounded-2xl shadow-lg border border-white/10 py-2 z-[9999]"
        >
            {{-- Change Password --}}
            <a href="{{ route('profile.password') }}"
               class="flex items-center gap-2 px-4 py-2.5 text-sm text-[#1a3a52] hover:bg-[#1a6fa8]/10 transition-colors"
                   style="font-family: 'Space Grotesk', sans-serif;">
                    <svg class="w-4 h-4 text-[#1a6fa8]/70" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                    </svg>
                    Ganti Password
            </a>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-[#1a6fa8]/10 transition-colors">
                        Logout
                </button>
            </form>

        </div>
    </div>

</header>