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
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ detailModalOpen: false, detail: {} }">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Riwayat Pengamatan Terbaru</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="text-xs text-gray-400 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Wilayah (UPPT)</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Komoditas / OPT</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-right">Luas Komoditi</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-right">Luas Serangan</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-center">Kondisi</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengamatans as $p)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 text-gray-600">
                            {{ \Carbon\Carbon::parse($p->tanggal_pengamatan)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $p->uppt->nama_uppt ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700">
                                    {{ $p->komoditas->nama_komoditas ?? '-' }}
                                </span>
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700">
                                    {{ $p->opt->nama_opt ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-gray-600 font-mono">
                            {{ number_format($p->luas_komoditi_ha, 2) }} Ha
                        </td>
                        <td class="px-6 py-4 text-right text-gray-600 font-mono">
                            {{ number_format($p->serangan_jumlah, 2) }} Ha
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($p->kondisi_serangan)
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full font-medium">{{ $p->kondisi_serangan }}</span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <!-- Tombol Detail -->
                                <button type="button" @click='detail = @json($p); detailModalOpen = true' class="p-2 text-white bg-blue-500 hover:bg-blue-600 rounded-lg shadow-sm shadow-blue-500/30 transition-all tooltip-trigger" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                
                                @if(Auth::user()->role === 'popt')
                                <!-- Tombol Edit -->
                                <a href="{{ route('pengamatan.edit', $p->id) }}" class="p-2 text-white bg-amber-500 hover:bg-amber-600 rounded-lg shadow-sm shadow-amber-500/30 transition-all tooltip-trigger" title="Edit Data">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>

                                <!-- Tombol Hapus -->
                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'hapus-pengamatan-{{ $p->id }}')" class="p-2 text-white bg-red-500 hover:bg-red-600 rounded-lg shadow-sm shadow-red-500/30 transition-all tooltip-trigger" title="Hapus Data">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                @endif
                            </div>

                            @if(Auth::user()->role === 'popt')
                            <!-- Modal Konfirmasi Hapus -->
                            <x-modal name="hapus-pengamatan-{{ $p->id }}" focusable maxWidth="md">
                                <form method="POST" action="{{ route('pengamatan.destroy', $p->id) }}" class="p-6 text-left whitespace-normal">
                                    @csrf
                                    @method('DELETE')
                                    <h2 class="text-lg font-bold text-gray-900">Konfirmasi Penghapusan</h2>
                                    <p class="mt-3 text-sm text-gray-600">
                                        Apakah Anda yakin ingin menghapus data pengamatan komoditas <strong>{{ $p->komoditas->nama_komoditas ?? '-' }}</strong> tanggal <strong>{{ \Carbon\Carbon::parse($p->tanggal_pengamatan)->format('d M Y') }}</strong>?
                                        <br><br>
                                        Tindakan ini permanen dan tidak dapat dibatalkan.
                                    </p>
                                    <div class="mt-6 flex justify-end gap-3">
                                        <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2.5 bg-white border border-gray-300 rounded-md font-bold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition-colors">
                                            Batal
                                        </button>
                                        <button type="submit" class="px-4 py-2.5 bg-red-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-red-700 transition-colors">
                                            Ya, Hapus Data
                                        </button>
                                    </div>
                                </form>
                            </x-modal>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
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

        <!-- Modal Detail -->
        <div x-show="detailModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="detailModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 backdrop-blur-sm" aria-hidden="true" @click="detailModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="detailModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6 border border-gray-100">
                    
                    <div class="flex justify-between items-start mb-5 pb-4 border-b border-gray-100">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-50 text-blue-600 mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900" id="modal-title">Detail Pengamatan</h3>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">ID Ref: <span x-text="detail.id"></span></p>
                            </div>
                        </div>
                        <button type="button" @click="detailModalOpen = false" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <!-- Info Utama -->
                        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Wilayah UPPT</p>
                                <p class="text-sm font-semibold text-gray-900" x-text="detail.uppt?.nama_uppt"></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal</p>
                                <p class="text-sm font-semibold text-gray-900" x-text="detail.tanggal_pengamatan ? detail.tanggal_pengamatan.substring(0,10) : '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Komoditas</p>
                                <p class="text-sm font-semibold text-emerald-600" x-text="detail.komoditas?.nama_komoditas"></p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Jenis OPT</p>
                                <p class="text-sm font-semibold text-red-600" x-text="detail.opt?.nama_opt"></p>
                            </div>
                        </div>

                        <!-- Luas & Serangan -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-2 mb-3">Luas Tanam & Intensitas Serangan</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Luas Komoditi</p>
                                    <p class="text-lg font-bold text-gray-900 mt-1"><span x-text="detail.luas_komoditi_ha"></span><span class="text-xs text-gray-500 ml-1">Ha</span></p>
                                </div>
                                <div class="bg-red-50 border border-red-100 rounded-lg p-3 text-center">
                                    <p class="text-[10px] font-bold text-red-600 uppercase">Ringan</p>
                                    <p class="text-lg font-bold text-red-700 mt-1"><span x-text="detail.serangan_ringan"></span><span class="text-xs opacity-70 ml-1">Ha</span></p>
                                </div>
                                <div class="bg-red-50 border border-red-100 rounded-lg p-3 text-center">
                                    <p class="text-[10px] font-bold text-red-600 uppercase">Sedang</p>
                                    <p class="text-lg font-bold text-red-700 mt-1"><span x-text="detail.serangan_sedang"></span><span class="text-xs opacity-70 ml-1">Ha</span></p>
                                </div>
                                <div class="bg-red-50 border border-red-100 rounded-lg p-3 text-center">
                                    <p class="text-[10px] font-bold text-red-600 uppercase">Berat</p>
                                    <p class="text-lg font-bold text-red-700 mt-1"><span x-text="detail.serangan_berat"></span><span class="text-xs opacity-70 ml-1">Ha</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Pengendalian -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-2 mb-3">Tindakan Pengendalian (Sumber Dana)</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">APBD Kab/Kota</p>
                                    <p class="text-sm font-semibold text-gray-900"><span x-text="detail.kendali_apbd_kab"></span></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">APBD Provinsi</p>
                                    <p class="text-sm font-semibold text-gray-900"><span x-text="detail.kendali_apbd_prov"></span></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Swadaya</p>
                                    <p class="text-sm font-semibold text-gray-900"><span x-text="detail.kendali_masyarakat"></span></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">APBN</p>
                                    <p class="text-sm font-semibold text-gray-900"><span x-text="detail.kendali_apbn"></span></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kondisi -->
                        <div x-show="detail.kondisi_serangan" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-1">Kondisi Serangan</p>
                            <p class="text-sm font-medium text-amber-900" x-text="detail.kondisi_serangan"></p>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-4 border-t border-gray-100 text-right">
                        <button type="button" @click="detailModalOpen = false" class="inline-flex justify-center px-6 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
