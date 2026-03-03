<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="min-h-screen bg-[#0B0F14] text-slate-100 antialiased">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-md">
                <div class="flex justify-center mb-6">
                    <a href="/">
                        <img src="{{ asset('images/logo.png') }}" class="h-14 w-auto" alt="Logo">
                    </a>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-2xl p-8">
                    {{ $slot }}
                </div>

                <div class="mt-6 text-center text-xs text-slate-400">
                    © {{ date('Y') }} {{ config('app.name') }}
                </div>
            </div>
        </div>
    </body>
</html>