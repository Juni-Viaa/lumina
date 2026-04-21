{{--
    Component: header
    Usage: @include('components.header', ['title' => 'Selamat datang, User123'])
--}}
<header class="glass-panel header flex items-center justify-between px-6 py-4">

    <span class="text-[#1a3a52] font-medium text-base tracking-tight" style="font-family: 'Space Grotesk', sans-serif;">
        {{ $title ?? 'Selamat datang, ' . (auth()->user()->name ?? 'User') }}
    </span>

    {{-- Profile icon --}}
    <button class="glass-inner w-9 h-9 rounded-full flex items-center justify-center hover:scale-105 transition-transform">
        <svg class="w-5 h-5 text-[#1a6fa8]/70" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
        </svg>
    </button>

</header>
