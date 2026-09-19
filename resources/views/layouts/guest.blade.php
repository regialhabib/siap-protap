<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIAP-PROTAP') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
            <div class="w-full sm:max-w-md px-6 text-center">
                <a href="/" class="flex flex-col items-center justify-center space-y-2">
                    <div class="w-16 h-16 bg-emerald-600 text-white flex items-center justify-center rounded-full shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-emerald-700 tracking-tight">SIAP-PROTAP</h1>
                    <p class="text-sm text-gray-500 font-medium">Sistem Informasi Administrasi Pelaporan Proteksi Tanaman Perkebunan</p>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-8 px-6 py-8 bg-white shadow-xl overflow-hidden sm:rounded-xl border border-gray-100">
                {{ $slot }}
            </div>
            
            <div class="mt-8 text-xs text-gray-400">
                &copy; {{ date('Y') }} Dinas Perkebunan Provinsi Jambi
            </div>
        </div>
    </body>
</html>
