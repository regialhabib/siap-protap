<header class="bg-white border-b border-gray-200">
    <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-20">
        
        <!-- Mobile menu button -->
        <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none lg:hidden">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Dynamic Area Breadcrumbs -->
        <div class="hidden lg:flex items-center space-x-2 text-sm font-medium text-gray-500">
            <span>Aplikasi SIAP-PROTAP</span>
            @if(Auth::user()->role !== 'admin')
                <span>/</span>
                <span class="text-emerald-600">
                    {{ Auth::user()->uppt->nama_uppt ?? 'Wilayah Tidak Diketahui' }}
                </span>
            @endif
        </div>

        <!-- Right Side User Menu -->
        <div class="flex items-center">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                        <div class="flex flex-col text-right mr-2">
                            <span class="font-bold text-gray-800">{{ Auth::user()->name }}</span>
                            <span class="text-xs text-emerald-600 uppercase">{{ Auth::user()->role }}</span>
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
                            Keluar (Logout)
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>
