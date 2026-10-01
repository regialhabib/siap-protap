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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <div class="text-3xl font-bold relative z-10">{{ $stats['komoditas_terdampak'] }}</div>
            <p class="text-xs text-amber-100 mt-1 relative z-10">Jenis tanaman terserang</p>
            
            <!-- Decorative circle -->
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        </div>
    </div>

    <!-- UPPT Compliance Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6 mb-8"
        x-data="{
            sudahList: {{ Js::from($chartData['sudah']) }},
            belumList: {{ Js::from($chartData['belum']) }},
            sudahPage: 1,
            belumPage: 1,
            perPage: 5,
            
            get pagedSudah() {
                let start = (this.sudahPage - 1) * this.perPage;
                return this.sudahList.slice(start, start + this.perPage);
            },
            get totalSudahPages() {
                return Math.ceil(this.sudahList.length / this.perPage) || 1;
            },
            get pagedBelum() {
                let start = (this.belumPage - 1) * this.perPage;
                return this.belumList.slice(start, start + this.perPage);
            },
            get totalBelumPages() {
                return Math.ceil(this.belumList.length / this.perPage) || 1;
            }
        }"
    >
        <h3 class="text-lg font-bold text-gray-900 mb-6">Status Pengamatan UPPT ({{ $chartData['bulan_ini'] }})</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            <!-- Chart Container -->
            <div class="md:col-span-4 flex flex-col items-center justify-center">
                <div class="relative w-full max-w-[250px] aspect-square">
                    <canvas id="upptChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-3xl font-bold text-gray-800">{{ $chartData['persentase_sudah'] }}%</span>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sudah Lapor</span>
                    </div>
                </div>
                <div class="mt-6 flex justify-center space-x-6 w-full">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></div>
                        <span class="text-sm font-medium text-gray-700">Sudah ({{ $chartData['total_sudah'] }})</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-gray-200 mr-2"></div>
                        <span class="text-sm font-medium text-gray-700">Belum ({{ $chartData['total_belum'] }})</span>
                    </div>
                </div>
            </div>

            <!-- List Container -->
            <div class="md:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sudah Pengamatan -->
                <div class="bg-emerald-50/50 rounded-xl border border-emerald-100 p-5 flex flex-col">
                    <h4 class="text-sm font-bold text-emerald-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Sudah Melakukan Pengamatan
                    </h4>
                    
                    <ul class="space-y-2 flex-grow">
                        <template x-for="(uppt, index) in pagedSudah" :key="uppt.id">
                            <li class="bg-white rounded-lg px-3 py-2 text-sm font-medium text-gray-700 shadow-sm border border-gray-100 flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="w-5 h-5 rounded bg-emerald-100 text-emerald-700 text-[10px] flex items-center justify-center font-bold mr-2" x-text="(sudahPage - 1) * perPage + index + 1"></span>
                                    <span x-text="uppt.nama_uppt"></span>
                                </div>
                                <span class="flex w-2 h-2 rounded-full bg-emerald-500"></span>
                            </li>
                        </template>
                        <template x-if="sudahList.length === 0">
                            <li class="text-sm text-gray-500 italic text-center py-4">Belum ada data</li>
                        </template>
                    </ul>
                    
                    <!-- Alpine Pagination Sudah -->
                    <div class="mt-4 flex items-center justify-between border-t border-emerald-200/50 pt-4" x-show="totalSudahPages > 1" x-cloak>
                        <button @click="if(sudahPage > 1) sudahPage--" :disabled="sudahPage === 1" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-emerald-200 text-emerald-700 disabled:opacity-50 hover:bg-emerald-100 transition-colors">Sebelumnya</button>
                        <span class="text-xs text-gray-600 font-medium"><span x-text="sudahPage"></span> / <span x-text="totalSudahPages"></span></span>
                        <button @click="if(sudahPage < totalSudahPages) sudahPage++" :disabled="sudahPage === totalSudahPages" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-emerald-200 text-emerald-700 disabled:opacity-50 hover:bg-emerald-100 transition-colors">Selanjutnya</button>
                    </div>
                </div>

                <!-- Belum Pengamatan -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 flex flex-col">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Belum Melakukan Pengamatan
                    </h4>
                    
                    <ul class="space-y-2 flex-grow">
                        <template x-for="(uppt, index) in pagedBelum" :key="uppt.id">
                            <li class="bg-white rounded-lg px-3 py-2 text-sm font-medium text-gray-600 shadow-sm border border-gray-100 flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="w-5 h-5 rounded bg-gray-200 text-gray-600 text-[10px] flex items-center justify-center font-bold mr-2" x-text="(belumPage - 1) * perPage + index + 1"></span>
                                    <span x-text="uppt.nama_uppt"></span>
                                </div>
                                <span class="flex w-2 h-2 rounded-full bg-gray-300"></span>
                            </li>
                        </template>
                        <template x-if="belumList.length === 0">
                            <li class="text-sm text-gray-500 italic text-center py-4">Semua UPPT sudah melapor!</li>
                        </template>
                    </ul>
                    
                    <!-- Alpine Pagination Belum -->
                    <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4" x-show="totalBelumPages > 1" x-cloak>
                        <button @click="if(belumPage > 1) belumPage--" :disabled="belumPage === 1" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-gray-300 text-gray-700 disabled:opacity-50 hover:bg-gray-100 transition-colors">Sebelumnya</button>
                        <span class="text-xs text-gray-600 font-medium"><span x-text="belumPage"></span> / <span x-text="totalBelumPages"></span></span>
                        <button @click="if(belumPage < totalBelumPages) belumPage++" :disabled="belumPage === totalBelumPages" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-gray-300 text-gray-700 disabled:opacity-50 hover:bg-gray-100 transition-colors">Selanjutnya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Load Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('upptChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Sudah Pengamatan', 'Belum Pengamatan'],
                        datasets: [{
                            data: [{{ $chartData['total_sudah'] }}, {{ $chartData['total_belum'] }}],
                            backgroundColor: [
                                '#10b981', // emerald-500
                                '#e5e7eb'  // gray-200
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        cutout: '75%',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            label += context.parsed + ' UPPT';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
