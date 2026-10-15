<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password - {{ config('app.name', 'SIAP-PROTAP') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-[#f4f7f6]">
    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.1)] flex flex-col md:flex-row overflow-hidden min-h-[550px]">
            <div class="md:w-1/2 relative p-8 md:p-12 flex flex-col justify-center text-center md:text-left">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-emerald-600 opacity-95"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center justify-center md:justify-start">
                        <img src="{{ asset('images/logo_final.jpg') }}" alt="Logo SIAP-PROTAP" class="w-12 h-12 rounded-full shadow-md border-2 border-white">
                        <span class="text-white font-extrabold text-3xl md:text-2xl ml-3 tracking-wide">SIAP-PROTAP</span>
                    </div>
                    <div class="hidden md:block md:my-auto">
                        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 md:mb-4">Lupa password?</h1>
                        <p class="text-emerald-50 text-sm md:text-base font-medium leading-relaxed max-w-sm">
                            Masukkan email akun Anda. Kami akan mengirimkan tautan untuk membuat password baru.
                        </p>
                    </div>
                </div>
            </div>

            <div class="md:w-1/2 p-8 sm:p-12 flex flex-col justify-center bg-white">
                <h2 class="text-2xl font-bold text-gray-900 mb-3">Lupa Password</h2>
                <p class="text-sm text-gray-600 mb-6">
                    Lupa password? Masukkan alamat email Anda dan kami akan mengirimkan tautan reset password.
                </p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-150"
                            placeholder="Enter your Email">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150 ease-in-out">
                            Kirim Tautan Reset Password
                        </button>
                    </div>

                    <p class="text-center text-sm text-gray-600">
                        <a class="font-medium text-emerald-500 hover:text-emerald-600" href="{{ route('login') }}">Kembali ke Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
