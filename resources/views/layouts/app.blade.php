<!DOCTYPE html>
<html lang="id" class="h-full">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;
        }

        /* Glass panel — soft white frost over the light bg */
        .glass-panel {
            background: rgba(255, 255, 255, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.55);
            box-shadow:
                0 4px 24px rgba(100, 160, 200, 0.15),
                0 1px 4px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border-radius: 20px;
        }

        /* Inner glass elements — white tint with top-light inner glow */
        .glass-inner {
            background: rgba(255, 255, 255, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.50);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.70),
                inset 0 -1px 0 rgba(180, 210, 230, 0.20),
                0 2px 8px rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        /* Sidebar nav items */
        .sidebar-nav-item:hover {
            background: rgba(255, 255, 255, 0.35);
        }
        .sidebar-nav-active {
            background: rgba(255, 255, 255, 0.45);
        }

        /* ── Font colour tokens ──────────────────────────────────────────────
           Background is light blue-gray (~#D8E8F0) so dark navy/slate reads
           best. Avoid pure black — it feels harsh on frosted glass.
        ─────────────────────────────────────────────────────────────────── */

        /* Primary text — dark navy, high contrast */
        :root {
            --text-primary:   #1a3a52;   /* headings, labels               */
            --text-secondary: #2e5f7e;   /* body, descriptions             */
            --text-muted:     #5a8aa8;   /* placeholders, timestamps       */
            --text-disabled:  #8aafc8;   /* disabled states                */
            --text-on-blue:   #ffffff;   /* text on coloured bubbles/btns  */
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
@if (session('success'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-2"
        x-init="setTimeout(() => show = false, 3500)"
        class="fixed top-5 right-5 z-[9999]"
        style="display: none;"
    >
        <div class="w-[360px] overflow-hidden rounded-2xl border border-emerald-300/40 bg-emerald-400/20 backdrop-blur-xl shadow-2xl">
            <div class="flex items-start gap-3 p-4">
                <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <div class="flex-1 pr-2">
                    <h4 class="text-sm font-semibold tracking-wide text-emerald-950">
                        Berhasil
                    </h4>
                    <p class="mt-1 text-sm leading-5 text-emerald-900">
                        {{ session('success') }}
                    </p>
                </div>

                <button
                    @click="show = false"
                    class="rounded-lg p-1 text-emerald-900/70 transition hover:bg-emerald-500/10 hover:text-emerald-950"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="h-[3px] w-full bg-emerald-900/10">
                <div
                    class="h-[3px] bg-emerald-600"
                    x-init="$el.style.width = '100%'; setTimeout(() => $el.style.width = '0%', 50)"
                    style="transition: width 3.5s linear;"
                ></div>
            </div>
        </div>
    </div>
@endif
<body class="h-full">

    <div class="flex h-full p-4 gap-3">

        {{-- Sidebar --}}
        @include('components.sidebar', ['chatHistory' => $chatHistory ?? []])

        {{-- Right column --}}
        <div class="flex flex-col flex-1 min-w-0 gap-3">

            {{-- Header --}}
            @include('components.header')

            {{-- Main content --}}
            <main class="flex-1 min-h-0">
                @yield('content')
            </main>

        </div>
    </div>

    @stack('scripts')
</body>
</html>