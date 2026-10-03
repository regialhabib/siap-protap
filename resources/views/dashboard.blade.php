<x-app-layout>
    <!-- Header / Title -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Overview</h1>
            <p class="text-sm text-gray-500 mt-1">Pantau statistik dan riwayat data pengamatan lapangan.</p>
        </div>
        @can('popt')
            <a href="{{ route('pengamatan.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Data
            </a>
        @endcan
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
    @can('admin')
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
    @endcan

    @can('popt')
    <div x-data="{ showModal: false, modalData: {} }">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100 bg-white">
                <h3 class="text-lg font-bold text-gray-900">Riwayat Pengamatan Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[11px] font-bold text-gray-400 uppercase bg-gray-50/50 border-b border-gray-100 tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">TANGGAL</th>
                            <th scope="col" class="px-6 py-4">WILAYAH (UPPT)</th>
                            <th scope="col" class="px-6 py-4">KOMODITAS / OPT</th>
                            <th scope="col" class="px-6 py-4 text-center">LUAS KOMODITI</th>
                            <th scope="col" class="px-6 py-4 text-center">LUAS SERANGAN</th>
                            <th scope="col" class="px-6 py-4 text-center">KONDISI</th>
                            <th scope="col" class="px-6 py-4 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse($pengamatans as $item)
                            <tr class="hover:bg-gray-50/50 transition-colors bg-white">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($item->tanggal_pengamatan)->isoFormat('D MMM Y') }}
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    {{ $item->uppt->nama_uppt ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded text-xs font-medium">{{ $item->komoditas->nama_komoditas ?? '-' }}</span>
                                        <span class="bg-red-50 text-red-500 px-2 py-1 rounded text-xs font-medium">{{ $item->opt->nama_opt ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-medium">
                                    {{ number_format($item->luas_komoditi_ha, 2) }} Ha
                                </td>
                                <td class="px-6 py-4 text-center font-medium text-gray-500">
                                    {{ number_format($item->serangan_jumlah, 2) }} Ha
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium lowercase">
                                        {{ $item->kondisi_serangan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button type="button" 
                                            data-item="{{ json_encode([
                                                'id' => $item->id,
                                                'tanggal_raw' => \Carbon\Carbon::parse($item->tanggal_pengamatan)->format('Y-m-d'),
                                                'tanggal' => \Carbon\Carbon::parse($item->tanggal_pengamatan)->isoFormat('D MMMM Y'),
                                                'uppt' => $item->uppt->nama_uppt ?? '-',
                                                'kecamatan' => $item->kecamatan->nama_kecamatan ?? '-',
                                                'komoditas' => $item->komoditas->nama_komoditas ?? '-',
                                                'opt' => $item->opt->nama_opt ?? '-',
                                                'luas_komoditi_num' => number_format($item->luas_komoditi_ha, 2),
                                                'kondisi' => $item->kondisi_serangan,
                                                'serangan_ringan_num' => number_format($item->serangan_ringan, 2),
                                                'serangan_sedang_num' => number_format($item->serangan_sedang, 2),
                                                'serangan_berat_num' => number_format($item->serangan_berat, 2),
                                                'serangan_total_num' => number_format($item->serangan_jumlah, 2),
                                                'kendali_apbd_kab' => number_format($item->kendali_apbd_kab, 2),
                                                'kendali_apbd_prov' => number_format($item->kendali_apbd_prov, 2),
                                                'kendali_apbn' => number_format($item->kendali_apbn, 2),
                                                'kendali_masyarakat' => number_format($item->kendali_masyarakat, 2)
                                            ]) }}"
                                            @click.prevent="modalData = JSON.parse($event.currentTarget.dataset.item); showModal = true" 
                                            title="Lihat" class="bg-blue-500 hover:bg-blue-600 text-white p-1.5 rounded-md transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        
                                        @can('update', $item)
                                        <a href="{{ route('pengamatan.edit', $item->id) }}" title="Edit" class="bg-amber-500 hover:bg-amber-600 text-white p-1.5 rounded-md transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                        
                                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-deletion-{{ $item->id }}')" type="button" title="Hapus" class="bg-red-500 hover:bg-red-600 text-white p-1.5 rounded-md transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>

                                        <x-modal name="confirm-deletion-{{ $item->id }}" maxWidth="md" focusable>
                                            <form method="post" action="{{ route('pengamatan.destroy', $item->id) }}" class="p-6 text-left whitespace-normal">
                                                @csrf
                                                @method('delete')
                            
                                                <h2 class="text-lg font-bold text-gray-900">
                                                    Konfirmasi Penghapusan
                                                </h2>
                            
                                                <p class="mt-2 text-sm text-gray-600">
                                                    Apakah Anda yakin ingin menghapus data pengamatan ini? Data yang dihapus tidak dapat dikembalikan.
                                                </p>
                            
                                                <div class="mt-6 flex justify-end">
                                                    <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150 mr-3">
                                                        Batal
                                                    </button>
                            
                                                    <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-red-700 transition-colors transition ease-in-out duration-150">
                                                        Ya, Hapus Data
                                                    </button>
                                                </div>
                                            </form>
                                        </x-modal>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 bg-gray-50/50">
                                    <svg class="w-10 h-10 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-base font-medium text-gray-600 mb-1">Belum ada data pengamatan</p>
                                    <p class="text-sm">Anda belum melaporkan data pengamatan bulan ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pengamatans->hasPages())
            <div class="p-4 border-t border-gray-100 bg-white">
                {{ $pengamatans->links() }}
            </div>
            @endif
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
                    <div class="bg-gray-50 rounded-xl p-5 mb-6 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">WILAYAH UPPT</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="modalData.uppt"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">TANGGAL</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="modalData.tanggal_raw"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">KOMODITAS</p>
                            <p class="text-sm font-semibold text-emerald-600" x-text="modalData.komoditas"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">JENIS OPT</p>
                            <p class="text-sm font-semibold text-red-600" x-text="modalData.opt"></p>
                        </div>
                    </div>

                    <!-- Luas Tanam & Intensitas Serangan -->
                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-gray-900 mb-3">Luas Tanam & Intensitas Serangan</h4>
                        <div class="grid grid-cols-4 gap-3 text-center">
                            <div class="border border-gray-200 rounded-lg p-3">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">LUAS KOMODITI</p>
                                <p class="text-lg font-bold text-gray-900"><span x-text="modalData.luas_komoditi_num"></span> <span class="text-xs font-medium text-gray-500">Ha</span></p>
                            </div>
                            <div class="bg-red-50 border border-red-100 rounded-lg p-3">
                                <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mb-1">RINGAN</p>
                                <p class="text-lg font-bold text-red-600"><span x-text="modalData.serangan_ringan_num"></span> <span class="text-xs font-medium text-red-400">Ha</span></p>
                            </div>
                            <div class="bg-red-50 border border-red-100 rounded-lg p-3">
                                <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mb-1">SEDANG</p>
                                <p class="text-lg font-bold text-red-600"><span x-text="modalData.serangan_sedang_num"></span> <span class="text-xs font-medium text-red-400">Ha</span></p>
                            </div>
                            <div class="bg-red-50 border border-red-100 rounded-lg p-3">
                                <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mb-1">BERAT</p>
                                <p class="text-lg font-bold text-red-600"><span x-text="modalData.serangan_berat_num"></span> <span class="text-xs font-medium text-red-400">Ha</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Tindakan Pengendalian -->
                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-gray-900 mb-3">Tindakan Pengendalian (Sumber Dana)</h4>
                        <div class="grid grid-cols-4 gap-3 border-b border-gray-100 pb-5">
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 mb-1">APBD Kab/Kota</p>
                                <p class="text-sm font-bold text-gray-900" x-text="modalData.kendali_apbd_kab"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 mb-1">APBD Provinsi</p>
                                <p class="text-sm font-bold text-gray-900" x-text="modalData.kendali_apbd_prov"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 mb-1">Swadaya</p>
                                <p class="text-sm font-bold text-gray-900" x-text="modalData.kendali_masyarakat"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 mb-1">APBN</p>
                                <p class="text-sm font-bold text-gray-900" x-text="modalData.kendali_apbn"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Kondisi Serangan Box -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                        <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-1">KONDISI SERANGAN</p>
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
