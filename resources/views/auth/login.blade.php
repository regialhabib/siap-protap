<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'SIAP-PROTAP') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-[#f4f7f6]">
    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
        
        <!-- The Floating Card -->
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.1)] flex flex-col md:flex-row overflow-hidden min-h-[550px]">
            
            <!-- Left Side: Green Background -->
            <div class="md:w-1/2 relative p-8 md:p-12 flex flex-col justify-center text-center md:text-left">
                <!-- Solid green base + slight gradient simulating the image overlay -->
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-emerald-600 opacity-95"></div>
                
                <div class="relative z-10 flex flex-col h-full">
                    <!-- Top Logo/Name -->
                    <div class="text-white font-extrabold text-2xl md:text-xl italic tracking-wide mb-6 md:mb-0">
                        SIAP-PROTAP
                    </div>

                    <!-- Center Content (Hidden on mobile) -->
                    <div class="hidden md:block md:mt-auto md:mb-auto">
                        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 md:mb-4">Selamat datang!</h1>
                        <p class="text-emerald-50 text-sm md:text-base font-medium leading-relaxed max-w-sm mx-auto md:mx-0">
                            Sistem Informasi Administrasi Pelaporan Proteksi Tanaman Perkebunan. Silakan masuk untuk mengelola data pengamatan Anda.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="md:w-1/2 p-10 sm:p-12 flex flex-col justify-center bg-white">
                
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Log In</h2>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />



                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                            class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-150" 
                            placeholder="Enter your Email">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password" 
                            class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-150" 
                            placeholder="Enter Password">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-2">
                        <label for="remember_me" class="flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span class="ml-2 text-sm text-gray-600">Remember Me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-emerald-500 hover:text-emerald-600" href="{{ route('password.request') }}">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150 ease-in-out">
                            Login
                        </button>
                    </div>
                </form>

            </div>
        </div>
        
    </div>
</body>
</html>
