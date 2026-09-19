<!-- Mobile Sidebar Backdrop -->
<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-20 bg-gray-900/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 bg-[#0f172a] text-gray-300 transition-transform duration-300 transform lg:translate-x-0 lg:static lg:inset-auto flex flex-col shadow-2xl">
    
    <!-- Branding -->
    <div class="flex items-center justify-center h-20 border-b border-gray-800">
        <div class="text-white font-bold text-lg tracking-widest flex items-center">
            SIAP-PROTAP
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <!-- Dashboard Link -->
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            Dashboard
        </a>

        @if(auth()->user()->role === 'popt')
            <!-- Menu Khusus POPT -->
            <div class="pt-6 pb-2">
                <p class="px-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Operasional</p>
            </div>
            <a href="{{ route('pengamatan.create') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('pengamatan.create') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('pengamatan.create') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Input Pengamatan
            </a>
        @endif

        @if(auth()->user()->role === 'admin')
            <!-- Menu Khusus Admin -->
            <div class="pt-6 pb-2">
                <p class="px-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Master Data</p>
            </div>
            <a href="{{ route('komoditas.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('komoditas.*') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('komoditas.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Master Komoditas
            </a>
            <a href="{{ route('opt.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('opt.*') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('opt.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Master OPT
            </a>
            <a href="{{ route('uppt.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('uppt.*') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('uppt.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Wilayah & UPPT
            </a>
        @endif

        <div class="pt-6 pb-2">
            <p class="px-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Pelaporan</p>
        </div>
        <a href="#" class="flex items-center px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium transition-colors">
            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Rekap Ekspor
        </a>
    </nav>

    <!-- User Profile Snippet at bottom -->
    <div class="p-5 border-t border-gray-800 bg-[#0b1221]">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-emerald-600/20 border border-emerald-600/30 text-emerald-500 flex items-center justify-center font-bold">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="ml-3">
                <p class="text-sm font-semibold text-gray-200 truncate w-40">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 capitalize font-medium">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </div>
</aside>
