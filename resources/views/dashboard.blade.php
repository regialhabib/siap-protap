<x-app-layout>
    <!-- Header / Title -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Overview</h1>
            <p class="text-sm text-gray-500 mt-1">Pantau statistik dan riwayat data pengamatan lapangan.</p>
        </div>
        @if(auth()->user()->role === 'popt')
            <a href="{{ route('pengamatan.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Data
            </a>
        @endif
    </div>

    <!-- Stats Cards (Solid Background Accents) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card 1 (Emerald) -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-md p-6 relative overflow-hidden text-white border border-emerald-700/20">
            <div class="flex items-center justify-between mb-4 relative z-10">
                <h3 class="text-sm font-semibold text-emerald-100">Total Pengamatan</h3>
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <div class="text-3xl font-bold relative z-10">{{ $stats['total_pengamatan'] }}</div>
            <p class="text-xs text-emerald-100 mt-1 relative z-10">Data terkumpul bulan ini</p>
            
            <!-- Decorative circle -->
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        </div>

        <!-- Card 2 (Red) -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-md p-6 relative overflow-hidden text-white border border-red-700/20">
            <div class="flex items-center justify-between mb-4 relative z-10">
                <h3 class="text-sm font-semibold text-red-100">Luas Serangan (Ha)</h3>
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div class="text-3xl font-bold relative z-10">{{ number_format($stats['total_luas_serangan'], 2) }}</div>
            <p class="text-xs text-red-100 mt-1 font-medium flex items-center relative z-10">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Total luasan terdampak
            </p>
            
            <!-- Decorative circle -->
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        </div>

        <!-- Card 3 (Amber) -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-md p-6 relative overflow-hidden text-white border border-amber-700/20">
            <div class="flex items-center justify-between mb-4 relative z-10">
                <h3 class="text-sm font-semibold text-amber-100">Komoditas Terdampak</h3>
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </div>
            </div>
            <div class="text-3xl font-bold relative z-10">{{ $stats['komoditas_terdampak'] }}</div>
            <p class="text-xs text-amber-100 mt-1 relative z-10">Jenis tanaman terserang</p>
            
            <!-- Decorative circle -->
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Riwayat Pengamatan Terbaru</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="text-xs text-gray-400 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Kecamatan (UPPT)</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Komoditas / OPT</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-right">Luas Lahan</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-right">Luas Serangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengamatans as $p)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 text-gray-600">
                            {{ \Carbon\Carbon::parse($p->tanggal_pengamatan)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $p->kecamatan }}</div>
                            <div class="text-xs text-gray-500">{{ $p->nama_uppt }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700">
                                    {{ $p->nama_komoditas }}
                                </span>
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700">
                                    {{ $p->nama_opt }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-gray-600 font-mono">
                            {{ number_format($p->luas_komoditi_ha, 2) }} Ha
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-gray-900 font-mono">{{ number_format($p->serangan_jumlah, 2) }}</span>
                            <span class="text-gray-500 text-xs">Ha</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">Tidak ada data</h3>
                            <p class="text-xs text-gray-500 mt-1">Belum ada data pengamatan yang masuk.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pengamatans->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $pengamatans->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
