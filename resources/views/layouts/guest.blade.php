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
                    <img src="{{ asset('images/logo_final.jpg') }}" alt="Logo SIAP-PROTAP" class="w-16 h-16 rounded-full shadow-lg border-2 border-emerald-100">
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
