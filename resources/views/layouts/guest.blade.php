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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    @if (session('success'))
    <div style="position:fixed; top:20px; right:20px; z-index:99999; background:#16a34a; color:white; padding:14px 18px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.2);">
        {{ session('success') }}
    </div>
@endif
    {{-- Toast success untuk halaman guest (login/register) --}}
   @if (session('success'))
        <div style="position:fixed; top:20px; right:20px; z-index:99999; background:#16a34a; color:white; padding:14px 18px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.2);">
            {{ session('success') }}
        </div>
    @endif
    <div class="min-h-screen bg-cover flex items-center justify-center"
         style="background-image: url('/images/Background.png');">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>
</html>