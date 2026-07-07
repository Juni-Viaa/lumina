<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Lumina') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
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
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-cover flex items-center justify-center"
             style="background-image: url('/images/Background.png');">
            <div class="w-full sm:max-w-md mt-6 px-6 py-4  overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
