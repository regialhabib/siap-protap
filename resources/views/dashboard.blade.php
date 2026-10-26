<x-app-layout title="Dashboard">
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms class="mb-6 flex items-center justify-between bg-emerald-100 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg shadow-sm font-medium" role="alert">
            <span>{{ session('success') }}</span>
            <button @click="show = false" type="button" class="text-emerald-600 hover:text-emerald-800 focus:outline-none ml-4 transition-colors" aria-label="Tutup">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms class="mb-6 flex items-center justify-between bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg shadow-sm font-medium" role="alert">
            <span>{{ session('error') }}</span>
            <button @click="show = false" type="button" class="text-red-600 hover:text-red-800 focus:outline-none ml-4 transition-colors" aria-label="Tutup">&times;</button>
        </div>
    @endif

    <div x-data="dashboardFilter()" x-init="initChart()">
    <!-- Header / Title -->
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ringkasan Pengamatan</h1>
            <p class="text-sm text-gray-500 mt-1">Pantau statistik dan riwayat data pengamatan lapangan.</p>
        </div>
        <div class="flex items-center space-x-3">
            @can('admin')
            <div class="flex items-center space-x-2 bg-white p-1.5 rounded-lg border border-gray-200 shadow-sm">
                <select x-model="bulan" @change="fetchData()" :disabled="isLoading" class="text-sm border-transparent focus:border-emerald-500 focus:ring focus:ring-emerald-200 rounded-md py-1.5 pl-3 pr-8">
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
                <div class="h-4 w-px bg-gray-300"></div>
                <select x-model="tahun" @change="fetchData()" :disabled="isLoading" class="text-sm border-transparent focus:border-emerald-500 focus:ring focus:ring-emerald-200 rounded-md py-1.5 pl-3 pr-8">
                    @for($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
                <div class="px-2" x-show="isLoading" x-cloak>
                    <svg class="animate-spin w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                </div>
            </div>
            @endcan

            @can('popt')
                <a href="{{ route('pengamatan.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Input Data
                </a>
            @endcan
        </div>
    </div>

    <!-- Stats Cards (Solid Background Accents) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-6 mb-8">
        <!-- Card 1 (Emerald) -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-md grid grid-cols-[1fr_auto] items-center gap-x-4 p-4 sm:block sm:p-5 lg:p-6 text-white border border-emerald-700/20">
            <div class="flex items-center justify-between sm:mb-3">
                <h3 class="text-sm font-semibold text-emerald-100">Total Pengamatan</h3>
                <div class="hidden sm:block p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <div class="col-start-2 row-span-2 row-start-1 text-2xl font-bold sm:text-3xl" x-text="stats.total_pengamatan">{{ $stats['total_pengamatan'] }}</div>
            <p class="text-xs text-emerald-100 mt-1">Data terkumpul bulan ini</p>
        </div>

        <!-- Card 2 (Red) -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-md grid grid-cols-[1fr_auto] items-center gap-x-4 p-4 sm:block sm:p-5 lg:p-6 text-white border border-red-700/20">
            <div class="flex items-center justify-between sm:mb-3">
                <h3 class="text-sm font-semibold text-red-100">@can('popt') Kondisi Serangan Saat Ini @else Luas Serangan (Ha) @endcan</h3>
                <div class="hidden sm:block p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            @can('popt')
                <div class="col-start-2 row-span-2 row-start-1 text-2xl font-bold sm:text-3xl"><span x-text="formatNumber(stats.kondisi_serangan)">{{ \App\Services\PengamatanHistoryService::hektare((float) $stats['kondisi_serangan']) }}</span> Ha</div>
                <p class="text-xs text-red-100 mt-1 font-medium">Sisa serangan setelah pengendalian</p>
            @else
                <div class="col-start-2 row-span-2 row-start-1 text-2xl font-bold sm:text-3xl" x-text="formatNumber(stats.total_luas_serangan)">{{ \App\Services\PengamatanHistoryService::hektare((float) $stats['total_luas_serangan']) }}</div>
                <p class="text-xs text-red-100 mt-1 font-medium flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Total luasan terdampak
                </p>
            @endcan
        </div>

        <!-- Card 3 (Amber) -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-md grid grid-cols-[1fr_auto] items-center gap-x-4 p-4 sm:block sm:p-5 lg:p-6 text-white border border-amber-700/20">
            <div class="flex items-center justify-between sm:mb-3">
                <h3 class="text-sm font-semibold text-amber-100">Komoditas Terdampak</h3>
                <div class="hidden sm:block p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <div class="col-start-2 row-span-2 row-start-1 text-2xl font-bold sm:text-3xl" x-text="stats.komoditas_terdampak">{{ $stats['komoditas_terdampak'] }}</div>
            <p class="text-xs text-amber-100 mt-1">Jenis tanaman terserang</p>
        </div>
    </div>

    <!-- UPPT Compliance Section -->
    @can('admin')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6 mb-8 relative">
        <div x-show="isLoading" class="absolute inset-0 bg-white/50 z-20 flex items-center justify-center backdrop-blur-[1px]" x-cloak></div>
        <h3 class="text-lg font-bold text-gray-900 mb-6">Status Pengamatan UPPT (<span x-text="chartData.bulan_ini">{{ $chartData['bulan_ini'] }}</span>)</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            <!-- Chart Container -->
            <div class="md:col-span-4 flex flex-col items-center justify-center">
                <div class="relative w-full max-w-[250px] aspect-square">
                    <canvas id="upptChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-3xl font-bold text-gray-800"><span x-text="chartData.persentase_sudah">{{ $chartData['persentase_sudah'] }}</span>%</span>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sudah Lapor</span>
                    </div>
                </div>
                <div class="mt-6 flex justify-center space-x-6 w-full">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></div>
                        <span class="text-sm font-medium text-gray-700">Sudah (<span x-text="chartData.total_sudah">{{ $chartData['total_sudah'] }}</span>)</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-gray-200 mr-2"></div>
                        <span class="text-sm font-medium text-gray-700">Belum (<span x-text="chartData.total_belum">{{ $chartData['total_belum'] }}</span>)</span>
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
                                    <span class="w-6 h-6 shrink-0 rounded bg-emerald-100 text-emerald-700 text-xs flex items-center justify-center font-bold mr-2" x-text="(sudahPage - 1) * perPage + index + 1"></span>
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
                    <x-client-pager class="mt-4 border-t border-emerald-200/50 pt-4" x-show="totalSudahPages > 1" x-cloak page="sudahPage" pages="totalSudahPages" prev="sudahPage--" next="sudahPage++" :summary="false" />
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
                                    <span class="w-6 h-6 shrink-0 rounded bg-gray-200 text-gray-600 text-xs flex items-center justify-center font-bold mr-2" x-text="(belumPage - 1) * perPage + index + 1"></span>
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
                    <x-client-pager class="mt-4 border-t border-gray-200 pt-4" x-show="totalBelumPages > 1" x-cloak page="belumPage" pages="totalBelumPages" prev="belumPage--" next="belumPage++" :summary="false" />
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('popt')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Laporan Bulan Ini ({{ now()->isoFormat('MMMM Y') }})</h3>
        </div>
        <ul class="divide-y divide-gray-100 md:hidden">
            @forelse($activeChains as $chain)
                <li class="space-y-3 px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $chain->komoditas }}</p>
                            <p class="text-sm text-gray-600">{{ $chain->opt }}</p>
                            <p class="text-sm text-gray-600">Kec. {{ $chain->kecamatan }}</p>
                        </div>
                        @if($chain->sudah_lapor)
                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Sudah dilaporkan</span>
                        @else
                            <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">Belum dilaporkan</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm text-gray-600">Kondisi serangan <span class="font-semibold text-gray-900">{{ \App\Services\PengamatanHistoryService::hektare((float) $chain->kondisi_serangan_ha) }} Ha</span></p>
                        <a href="{{ $chain->input_url }}" class="inline-flex shrink-0 items-center rounded-md border border-emerald-200 bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm transition-colors hover:bg-emerald-50">Input Data</a>
                    </div>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-gray-500">Belum ada data pengamatan pada periode ini.</li>
            @endforelse
        </ul>
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-sm text-left">
                <thead class="text-xs font-bold text-gray-600 uppercase bg-gray-50 border-b border-gray-100 tracking-wider">
                    <tr>
                        <th class="whitespace-nowrap px-6 py-3">Kecamatan</th>
                        <th class="whitespace-nowrap px-6 py-3">Komoditas / OPT</th>
                        <th class="whitespace-nowrap px-6 py-3 text-right">Kondisi Serangan</th>
                        <th class="whitespace-nowrap px-6 py-3 text-center">Status Bulan Ini</th>
                        <th class="whitespace-nowrap px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($activeChains as $chain)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-3 font-medium text-gray-900">{{ $chain->kecamatan }}</td>
                            <td class="min-w-[14rem] px-6 py-3"><span class="font-medium text-gray-900">{{ $chain->komoditas }}</span><span class="block text-sm text-gray-600">{{ $chain->opt }}</span></td>
                            <td class="whitespace-nowrap px-6 py-3 text-right">{{ \App\Services\PengamatanHistoryService::hektare((float) $chain->kondisi_serangan_ha) }} Ha</td>
                            <td class="whitespace-nowrap px-6 py-3 text-center">
                                @if($chain->sudah_lapor)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Sudah dilaporkan</span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">Belum dilaporkan</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-center">
                                <a href="{{ $chain->input_url }}" class="inline-flex items-center rounded-md border border-emerald-200 bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm transition-colors hover:bg-emerald-50">Input Data</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-gray-500">Belum ada data pengamatan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endcan

    @can('popt')
    <div x-data="{ showModal: false, modalData: {} }">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8 relative min-h-[200px]">
            <div x-show="isLoading" class="absolute inset-0 bg-white/50 z-20 flex items-center justify-center backdrop-blur-[1px]" x-cloak></div>
            <div class="p-6 border-b border-gray-100 bg-white">
                <h3 class="text-lg font-bold text-gray-900">Riwayat Pengamatan Terbaru</h3>
            </div>
            
            <div id="table-container">
                @include('partials.dashboard-table')
            </div>
        </div>

        <!-- Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full p-6">
                    
                    <!-- Header Modal -->
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 leading-tight">Detail Pengamatan</h3>
                            </div>
                        </div>
                        <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- General Info Box -->
                    <div class="bg-gray-50 rounded-xl p-5 mb-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Wilayah UPPT</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="modalData.uppt"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Kecamatan</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="modalData.kecamatan"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="modalData.tanggal"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Komoditas</p>
                            <p class="text-sm font-semibold text-emerald-600" x-text="modalData.komoditas"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">OPT</p>
                            <p class="text-sm font-semibold text-red-600" x-text="modalData.opt"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Luas Komoditi</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="modalData.luas_komoditi"></p>
                        </div>
                    </div>

                    <!-- Serangan -->
                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-gray-900 mb-3">Serangan (Ha)</h4>
                        <table class="w-full text-sm">
                            <thead class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                <tr><th class="py-2 text-left"></th><th class="py-2 text-right">Ringan</th><th class="py-2 text-right">Berat</th><th class="py-2 text-right">Jumlah</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                <tr><td class="py-2">Tambah serangan</td><td class="py-2 text-right" x-text="modalData.tambah_ringan"></td><td class="py-2 text-right" x-text="modalData.tambah_berat"></td><td class="py-2 text-right font-semibold" x-text="modalData.tambah_jumlah"></td></tr>
                                <tr class="bg-red-50/50"><td class="py-2">Serangan kumulatif</td><td class="py-2 text-right" x-text="modalData.serangan_ringan_num"></td><td class="py-2 text-right" x-text="modalData.serangan_berat_num"></td><td class="py-2 text-right font-bold text-red-600" x-text="modalData.serangan_total_num"></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tindakan Pengendalian -->
                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-gray-900 mb-3">Pengendalian per Sumber Dana (Ha)</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 border-b border-gray-100 pb-5">
                            <div>
                                <p class="text-xs font-bold text-gray-500 mb-1">APBD I (Prov)</p>
                                <p class="text-sm font-bold text-gray-900" x-text="modalData.kendali_apbd_prov"></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 mb-1">APBD II (Kab)</p>
                                <p class="text-sm font-bold text-gray-900" x-text="modalData.kendali_apbd_kab"></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 mb-1">Masyarakat</p>
                                <p class="text-sm font-bold text-gray-900" x-text="modalData.kendali_masyarakat"></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 mb-1">APBN</p>
                                <p class="text-sm font-bold text-gray-900" x-text="modalData.kendali_apbn"></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-blue-600 mb-1">Jumlah</p>
                                <p class="text-sm font-bold text-blue-700" x-text="modalData.kendali_jumlah"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Kondisi Serangan Box -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                        <p class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-1">Kondisi Serangan</p>
                        <p class="text-sm font-medium text-amber-900" x-text="modalData.kondisi"></p>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="button" @click="showModal = false" class="px-6 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('admin')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
    @endcan
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardFilter', () => ({
                isLoading: false,
                bulan: '{{ request("bulan", now()->month) }}',
                tahun: '{{ request("tahun", now()->year) }}',
                stats: {
                    total_pengamatan: {{ $stats['total_pengamatan'] }},
                    total_luas_serangan: {{ $stats['total_luas_serangan'] }},
                    komoditas_terdampak: {{ $stats['komoditas_terdampak'] }}
                    kondisi_serangan: {{ $stats['kondisi_serangan'] }},
                },
                
                chartData: @json($chartData),
                sudahList: @json($chartData['sudah'] ?? []),
                belumList: @json($chartData['belum'] ?? []),
                sudahPage: 1,
                belumPage: 1,
                perPage: 5,
                chartInstance: null,
                
                tableHtml: '',

                initChart() {
                    const ctx = document.getElementById('upptChart');
                    if (ctx && !this.chartInstance) {
                        this.chartInstance = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Sudah Pengamatan', 'Belum Pengamatan'],
                                datasets: [{
                                    data: [this.chartData.total_sudah, this.chartData.total_belum],
                                    backgroundColor: ['#10b981', '#e5e7eb'],
                                    borderWidth: 0,
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                cutout: '75%',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return (context.label ? context.label + ': ' : '') + context.parsed + ' UPPT';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                    this.$nextTick(() => {
                        this.attachPaginationListeners();
                    });
                },

                updateChart(newData) {
                    if (this.chartInstance) {
                        this.chartInstance.data.datasets[0].data = [newData.total_sudah, newData.total_belum];
                        this.chartInstance.update();
                    }
                },
                
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
                },

                formatNumber(num) {
                    return (parseFloat(num) || 0).toLocaleString('id-ID', {maximumFractionDigits: 2});
                },

                async fetchData(url = null) {
                    this.isLoading = true;
                    try {
                        let fetchUrl = url;
                        if (!fetchUrl) {
                            fetchUrl = new URL(window.location.href);
                            fetchUrl.searchParams.set('bulan', this.bulan);
                            fetchUrl.searchParams.set('tahun', this.tahun);
                        } else {
                            const parsedUrl = new URL(url);
                            this.bulan = parsedUrl.searchParams.get('bulan') || this.bulan;
                            this.tahun = parsedUrl.searchParams.get('tahun') || this.tahun;
                        }

                        const response = await fetch(fetchUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }

                        const data = await response.json();
                        
                        this.stats = data.stats;
                        if (data.table_html) {
                            const container = document.getElementById('table-container');
                            if (container) {
                                container.innerHTML = data.table_html;
                            }
                            this.$nextTick(() => {
                                this.attachPaginationListeners();
                            });
                        }
                        
                        if (data.chartData) {
                            this.chartData = data.chartData;
                            this.sudahList = data.chartData.sudah;
                            this.belumList = data.chartData.belum;
                            this.sudahPage = 1;
                            this.belumPage = 1;
                            this.updateChart(data.chartData);
                        }
                        
                        // Update URL without reload
                        window.history.pushState({}, '', fetchUrl);
                        
                    } catch (error) {
                        console.error("Gagal mengambil data", error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                attachPaginationListeners() {
                    const container = document.getElementById('table-container');
                    if (!container) return;
                    
                    const links = container.querySelectorAll('nav[role="navigation"] a');
                    links.forEach(link => {
                        // avoid multiple listeners
                        const newLink = link.cloneNode(true);
                        link.parentNode.replaceChild(newLink, link);
                        newLink.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.fetchData(newLink.href);
                        });
                    });
                }
            }))
        });
    </script>
    </div>
</x-app-layout>
