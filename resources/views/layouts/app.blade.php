<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lumina — @yield('title', 'Lumina')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Space Grotesk', sans-serif; }

        body {
            background-color: #C9E8F7;
            background-image: url("{{ asset('images/Background.png') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            box-shadow: 0 4px 4px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 20px;
        }

        .glass-inner {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .sidebar-nav-item:hover  { background: rgba(255, 255, 255, 0.12); }
        .sidebar-nav-active      { background: rgba(255, 255, 255, 0.16); }
        .sidebar                 { border-radius: 20px; }
        .header                  { border-radius: 20px; }
        .upload-bar              { border-radius: 16px; }

        ::-webkit-scrollbar       { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(26,111,168,0.2); border-radius: 4px; }

        /* ── Mobile drawer overlay ─────────────────────────────────────── */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 40;
        }
        #sidebar-overlay.active { display: block; }

        /* ── Mobile sidebar drawer ─────────────────────────────────────── */
        @media (max-width: 767px) {
            #app-sidebar {
                position: fixed;
                top: 0; left: 0; bottom: 0;
                z-index: 50;
                width: 72vw;
                max-width: 280px;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                border-radius: 0 20px 20px 0;
            }
            #app-sidebar.open { transform: translateX(0); }

            /* Extra bottom padding so content clears the bottom nav + shadow */
            #main-content { padding-bottom: 80px; }

            /* Also pad the main yield area so shadow isn't clipped */
            #main-content > main { overflow: visible; }
        }

        /* ── Bottom navigation bar — mobile only ────────────────────────── */
        #bottom-nav {
            display: none;
        }
        @media (max-width: 767px) {
            #bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0; left: 0; right: 0;
                height: 64px;
                z-index: 40;
                background: rgba(255,255,255,0.55);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border-top: 1px solid rgba(255,255,255,0.4);
                align-items: center;
                justify-content: space-around;
                padding: 0 8px;
            }
        }

        /* ── Tablet: narrower sidebar, no bottom nav ─────────────────────── */
        @media (min-width: 768px) and (max-width: 1023px) {
            #app-sidebar { width: 56px; }
            #app-sidebar .nav-label,
            #app-sidebar .sidebar-brand-name,
            #app-sidebar .history-section,
            #app-sidebar .logout-label { display: none; }
            #app-sidebar .sidebar-logo-wrap { justify-content: center; }
        }
    </style>
    @stack('head')
</head>
<body class="h-full" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">

    {{-- Mobile overlay --}}
    <div id="sidebar-overlay"
         :class="sidebarOpen ? 'active' : ''"
         @click="sidebarOpen = false"
         class="md:hidden"></div>

    <div class="flex h-full p-2 md:p-4 gap-2 md:gap-3">

        {{-- Sidebar --}}
        <div id="app-sidebar"
             :class="sidebarOpen ? 'open' : ''"
             class="glass-panel sidebar shrink-0 md:w-56 lg:w-60 h-full">
            @include('components.sidebar')
        </div>

        {{-- Right column --}}
        <div id="main-content" class="flex flex-col flex-1 min-w-0 gap-2 md:gap-3">

            {{-- Header --}}
            @include('components.header')

            {{-- Main content --}}
            <main class="flex-1 min-h-0 overflow-hidden">
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Bottom nav — mobile only --}}
    <nav id="bottom-nav" class="md:hidden glass-panel">

        {{-- New Chat --}}
        <a href="{{ route('dashboard.index') }}"
           class="flex flex-col items-center gap-1 px-3 py-1 rounded-xl transition-all
                  {{ request()->routeIs('dashboard.index') ? 'text-[#1a6fa8]' : 'text-[#1a3a52]/70 hover:text-[#1a3a52]' }}">
            <img src="{{ asset('images/icons/NewChatIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
            <span class="text-[10px] font-medium">Chat</span>
        </a>

        {{-- Upload — admin only --}}
        @auth
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('uploads.index') }}"
               class="flex flex-col items-center gap-1 px-3 py-1 rounded-xl transition-all
                      {{ request()->routeIs('uploads.*') ? 'text-[#1a6fa8]' : 'text-[#1a3a52]/70 hover:text-[#1a3a52]' }}">
                <img src="{{ asset('images/icons/UploadIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
                <span class="text-[10px] font-medium">Upload</span>
            </a>
            @endif
        @endauth

        {{-- History --}}
        <a href="{{ route('history.index') }}"
           class="flex flex-col items-center gap-1 px-3 py-1 rounded-xl transition-all
                  {{ request()->routeIs('history.*') ? 'text-[#1a6fa8]' : 'text-[#1a3a52]/70 hover:text-[#1a3a52]' }}">
            <img src="{{ asset('images/icons/HistoryIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
            <span class="text-[10px] font-medium">Riwayat</span>
        </a>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex flex-col items-center gap-1 px-3 py-1 rounded-xl transition-all
                           text-[#1a3a52]/70 hover:text-[#1a3a52]">
                <img src="{{ asset('images/icons/LogOutIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
                <span class="text-[10px] font-medium">Keluar</span>
            </button>
        </form>

    </nav>

    @stack('scripts')
</body>
</html>