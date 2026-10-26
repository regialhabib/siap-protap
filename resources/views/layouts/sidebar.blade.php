<!-- Mobile Sidebar Backdrop -->
<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-20 bg-gray-900/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 bg-[#0f172a] text-gray-300 transition-transform duration-300 transform lg:translate-x-0 lg:static lg:inset-auto flex flex-col shadow-2xl">
    
    <!-- Branding -->
    <div class="flex shrink-0 items-center justify-center h-16 border-b border-gray-800">
        <div class="text-white font-bold text-lg tracking-widest flex items-center">
            <img src="{{ asset('images/logo_160.jpg') }}" alt="Logo" class="w-8 h-8 rounded-full mr-3 border border-emerald-500">
            SIAP-PROTAP
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-scroll flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
        <!-- Dashboard Link -->
        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            Dashboard
        </a>

        @can('popt')
            <!-- Menu Khusus POPT -->
            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-widest">Operasional</p>
            </div>
            <a href="{{ route('pengamatan.create') }}" class="flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('pengamatan.create') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('pengamatan.create') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Input Pengamatan
            </a>
            <a href="{{ route('pengamatan.riwayat.index') }}" @if(request()->routeIs('pengamatan.riwayat.index')) aria-current="page" @endif class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 {{ request()->routeIs('pengamatan.riwayat.index') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('pengamatan.riwayat.index') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 12h12M8 17h12M4 7h.01M4 12h.01M4 17h.01"></path></svg>
                Riwayat Pengamatan
            </a>
            <a href="{{ route('opt.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('opt.*') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('opt.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Master OPT
            </a>
        @endcan

        @can('admin')
            <!-- Menu Khusus Admin -->
            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-widest">Pelaporan</p>
            </div>
            @php
                $reportsActive = request()->routeIs('laporan.index', 'laporan.triwulan', 'laporan.tahunan', 'laporan.export.excel', 'laporan.export.pdf', 'laporan.export.triwulan.*', 'laporan.export.tahunan.*');
                $monthlyReportActive = request()->routeIs('laporan.index', 'laporan.export.excel', 'laporan.export.pdf');
                $quarterlyReportActive = request()->routeIs('laporan.triwulan', 'laporan.export.triwulan.*');
                $annualReportActive = request()->routeIs('laporan.tahunan', 'laporan.export.tahunan.*');
                $observationHistoryActive = request()->routeIs('pengamatan.riwayat.index');
            @endphp
            <div x-data="{ reportsOpen: {{ $reportsActive ? 'true' : 'false' }} }" class="space-y-1">
                <button type="button" @click="reportsOpen = !reportsOpen" :aria-expanded="reportsOpen.toString()" aria-controls="report-submenu"
                    class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium transition-all duration-200 {{ $reportsActive ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100' }}">
                    <svg class="mr-3 h-5 w-5 {{ $reportsActive ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="flex-1">Laporan</span>
                    <svg class="h-4 w-4 transition-transform" :class="reportsOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"></path></svg>
                </button>
                <div id="report-submenu" x-cloak x-show="reportsOpen" x-transition class="ml-5 space-y-0.5 border-l border-gray-700 pl-2" style="display: none;">
                    <a href="{{ route('laporan.index') }}" @if($monthlyReportActive) aria-current="page" @endif class="block rounded-lg px-3 py-1.5 text-sm transition-all duration-200 {{ $monthlyReportActive ? 'bg-gray-800 font-semibold text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100' }}">Laporan Bulanan</a>
                    <a href="{{ route('laporan.triwulan') }}" @if($quarterlyReportActive) aria-current="page" @endif class="block rounded-lg px-3 py-1.5 text-sm transition-all duration-200 {{ $quarterlyReportActive ? 'bg-gray-800 font-semibold text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100' }}">Laporan Triwulan</a>
                    <a href="{{ route('laporan.tahunan') }}" @if($annualReportActive) aria-current="page" @endif class="block rounded-lg px-3 py-1.5 text-sm transition-all duration-200 {{ $annualReportActive ? 'bg-gray-800 font-semibold text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100' }}">Laporan Tahunan</a>
                </div>
            </div>
            <a href="{{ route('pengamatan.riwayat.index') }}" @if($observationHistoryActive) aria-current="page" @endif class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 {{ $observationHistoryActive ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100' }}">
                <svg class="mr-3 h-5 w-5 {{ $observationHistoryActive ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 12h12M8 17h12M4 7h.01M4 12h.01M4 17h.01"></path></svg>
                Riwayat Pengamatan
            </a>

            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-widest">Master Data</p>
            </div>
            <a href="{{ route('komoditas.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('komoditas.*') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('komoditas.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Master Komoditas
            </a>
            <a href="{{ route('opt.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('opt.*') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('opt.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Master OPT
            </a>
            <a href="{{ route('uppt.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('uppt.*') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('uppt.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Wilayah & UPPT
            </a>
            <a href="{{ route('pengguna.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('pengguna.*') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('pengguna.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Manajemen Petugas
            </a>
        @endcan

        <!-- Menu Pengaturan / Akun -->
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-widest">Pengaturan</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('profile.*') ? 'bg-emerald-600 text-white font-medium shadow-md shadow-emerald-900/20' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100 font-medium' }}">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('profile.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Profil Akun
        </a>
    </nav>
</aside>
