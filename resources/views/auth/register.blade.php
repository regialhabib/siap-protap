<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ config('app.name', 'SIAP-PROTAP') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-[#f4f7f6]">
    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.1)] flex flex-col md:flex-row overflow-hidden">
            <div class="md:w-1/2 relative p-8 md:p-12 flex flex-col justify-center text-center md:text-left">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-emerald-600 opacity-95"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center justify-center md:justify-start">
                        <img src="{{ asset('images/logo_final.jpg') }}" alt="Logo SIAP-PROTAP" class="w-12 h-12 rounded-full shadow-md border-2 border-white">
                        <span class="text-white font-extrabold text-3xl md:text-2xl ml-3 tracking-wide">SIAP-PROTAP</span>
                    </div>
                    <div class="hidden md:block md:my-auto md:py-12">
                        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 md:mb-4">Bergabung dengan kami</h1>
                        <p class="text-emerald-50 text-sm md:text-base font-medium leading-relaxed max-w-sm">
                            Buat akun petugas untuk mulai mencatat dan mengirimkan hasil pengamatan lapangan.
                        </p>
                    </div>
                </div>
            </div>

            <div class="md:w-1/2 p-6 sm:p-8 flex flex-col justify-center bg-white">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Buat Akun</h2>

                <form method="POST" action="{{ route('register') }}" class="space-y-3"
                    x-data="{
                        open: false,
                        search: '',
                        selectedId: @js((string) old('uppt_id', '')),
                        options: @js($uppts->map(fn ($uppt) => ['id' => (string) $uppt->id, 'name' => $uppt->nama_uppt])->values()),
                        get filteredOptions() {
                            return this.options.filter(option => option.name.toLowerCase().includes(this.search.toLowerCase()));
                        },
                        selectOption(option) {
                            this.selectedId = option.id;
                            this.search = option.name;
                            this.open = false;
                        }
                    }"
                    x-init="search = options.find(option => option.id === selectedId)?.name ?? ''"
                    @submit="if (!selectedId) { $event.preventDefault(); $refs.upptSearch.setCustomValidity('Pilih wilayah UPPT dari daftar.'); $refs.upptSearch.reportValidity(); }">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                            class="block w-full px-4 py-2.5 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-150"
                            placeholder="Masukkan nama lengkap">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                            class="block w-full px-4 py-2.5 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-150"
                            placeholder="Enter your Email">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="uppt_id" class="block text-sm font-semibold text-gray-700 mb-1">Wilayah Penugasan (UPPT)</label>
                        <div class="relative" @click.outside="open = false">
                            <input id="uppt_id" x-ref="upptSearch" type="text" x-model="search"
                                @focus="open = true"
                                @input="selectedId = ''; $refs.upptSearch.setCustomValidity('')"
                                @keydown.escape="open = false"
                                role="combobox" aria-autocomplete="list" aria-controls="uppt-options" :aria-expanded="open.toString()"
                                autocomplete="off" placeholder="Cari atau pilih wilayah UPPT" required
                                class="block w-full px-4 py-2.5 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-150">
                            <input type="hidden" name="uppt_id" :value="selectedId">

                            <div id="uppt-options" x-cloak x-show="open" role="listbox"
                                class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                                <template x-for="option in filteredOptions" :key="option.id">
                                    <button type="button" role="option" :aria-selected="selectedId === option.id"
                                        @click="selectOption(option)"
                                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 focus:bg-emerald-50 focus:outline-none"
                                        x-text="option.name"></button>
                                </template>
                                <p x-show="filteredOptions.length === 0" class="px-4 py-2 text-sm text-gray-500">
                                    Wilayah UPPT tidak ditemukan.
                                </p>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('uppt_id')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            class="block w-full px-4 py-2.5 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-150"
                            placeholder="Buat password">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="block w-full px-4 py-2.5 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-150"
                            placeholder="Ulangi password">
                    </div>

                    <div class="space-y-3 pt-1">
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-md text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150 ease-in-out">
                            Register
                        </button>
                        <p class="text-center text-sm text-gray-600">
                            Sudah punya akun?
                            <a class="font-medium text-emerald-500 hover:text-emerald-600" href="{{ route('login') }}">Login</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
