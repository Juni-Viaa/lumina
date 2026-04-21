<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lumina — @yield('title', 'RAG System')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Space Grotesk', sans-serif; }

        body {
            background-color: #c9e8f7;
            background-image: url('{{ asset('images/Background.png') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* Glass panel — 15% white bg, 30% white stroke, 25% black shadow blur 4, 30 blur */
        .glass-panel {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.30);
            box-shadow: 0 4px 4px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 20px;
        }

        /* Inner glass elements — slightly more opaque */
        .glass-inner {
            background: rgba(255, 255, 255, 0.20);
            border: 1px solid rgba(255, 255, 255, 0.30);
            box-shadow: 0 4px 4px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
        }

        /* Sidebar nav items */
        .sidebar-nav-item:hover {
            background: rgba(255, 255, 255, 0.20);
        }
        .sidebar-nav-active {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Sidebar: no top border radius on right side to blend into layout */
        .sidebar {
            border-radius: 20px;
        }

        /* Header: full-width pill shape */
        .header {
            border-radius: 20px;
        }

        /* Upload bar */
        .upload-bar {
            border-radius: 16px;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(26,111,168,0.2); border-radius: 4px; }
    </style>
    @stack('head')
</head>
<body class="h-full overflow-hidden">

    <div class="flex h-full p-4 gap-3">

        {{-- Sidebar --}}
        @include('components.sidebar', ['chatHistory' => $chatHistory ?? []])

        {{-- Right column --}}
        <div class="flex flex-col flex-1 min-w-0 gap-3">

            {{-- Header --}}
            @include('components.header', ['title' => $pageTitle ?? ('Selamat datang, ' . (auth()->user()->name ?? 'User123'))])

            {{-- Main content --}}
            <main class="flex-1 min-h-0">
                @yield('content')
            </main>

        </div>
    </div>

    @stack('scripts')
</body>
</html>
