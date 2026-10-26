<header class="bg-white border-b border-gray-200">
    <div class="flex items-center gap-3 px-4 sm:px-6 lg:px-8 h-16">

        <!-- Mobile menu button -->
        <button @click="sidebarOpen = true" type="button" aria-label="Buka menu" class="-ml-1 shrink-0 rounded-md p-1 text-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 lg:hidden">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Page Title -->
        <div class="min-w-0 flex-1">
            <p class="truncate text-base font-bold text-gray-900 sm:text-lg">{{ $title ?? config('app.name', 'SIAP-PROTAP') }}</p>
            @cannot('admin')
                <p class="truncate text-xs font-medium text-emerald-700 sm:text-sm">
                    {{ Auth::user()->uppt->nama_uppt ?? 'Wilayah Tidak Diketahui' }}
                </p>
            @endcannot
        </div>

        <!-- Right Side User Menu -->
        <div class="flex shrink-0 items-center">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button" aria-label="Menu akun" class="inline-flex items-center px-2 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition ease-in-out duration-150 sm:px-3">
                        <svg class="mr-1 h-7 w-7 text-gray-400 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div class="hidden flex-col text-right mr-2 sm:flex">
                            <span class="font-bold text-gray-800">{{ Auth::user()->name }}</span>
                            <span class="text-xs text-emerald-700 uppercase">{{ Auth::user()->role->value }}</span>
                        </div>

                        <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        Profil Akun
                    </x-dropdown-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            Keluar
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>
