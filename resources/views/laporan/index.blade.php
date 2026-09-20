<x-app-layout>
    <!-- Filter Form -->
    <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm mt-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h2 class="font-bold text-xl text-gray-800 leading-tight tracking-tight">
                Rekap Laporan Pengamatan
            </h2>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('laporan.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4" x-data="{ jenis: '{{ $jenis }}' }">
            
            <!-- Jenis Laporan -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jenis Laporan</label>
                <select name="jenis" x-model="jenis" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="bulanan">Laporan Bulanan</option>
                    <option value="triwulan">Laporan Triwulan</option>
                    <option value="tahunan">Laporan Tahunan</option>
                </select>
            </div>

            <!-- Filter Bulan (muncul jika Bulanan) -->
            <div x-show="jenis === 'bulanan'" x-cloak>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Bulan</label>
                <select name="bulan" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Triwulan (muncul jika Triwulan) -->
            <div x-show="jenis === 'triwulan'" x-cloak style="display: none;">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Triwulan</label>
                <select name="triwulan" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="1" {{ $triwulan == '1' ? 'selected' : '' }}>Kuartal I (Jan-Mar)</option>
                    <option value="2" {{ $triwulan == '2' ? 'selected' : '' }}>Kuartal II (Apr-Jun)</option>
                    <option value="3" {{ $triwulan == '3' ? 'selected' : '' }}>Kuartal III (Jul-Sep)</option>
                    <option value="4" {{ $triwulan == '4' ? 'selected' : '' }}>Kuartal IV (Okt-Des)</option>
                </select>
            </div>

            <!-- Filter Tahun (Selalu Muncul) -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tahun</label>
                <select name="tahun" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    @foreach(range(date('Y'), date('Y') - 5) as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter UPPT -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Wilayah / UPPT</label>
                <select name="uppt_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    @foreach($uppts as $u)
                        <option value="{{ $u->id }}" {{ $uppt_id == $u->id ? 'selected' : '' }}>{{ $u->nama_uppt }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full flex justify-center items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors h-10">
                    Tampilkan
                </button>
            </div>
        </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Header Tabel & Export -->
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-gray-900">
                    Laporan {{ ucfirst($jenis) }} 
                    @if($jenis == 'bulanan') 
                        {{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} 
                    @elseif($jenis == 'triwulan')
                        Kuartal {{ $triwulan }}
                    @endif
                    {{ $tahun }}
                </h3>
                <p class="text-sm text-gray-500 mt-1">Ditemukan {{ $data->count() }} data pengamatan.</p>
            </div>
            
            <div class="flex gap-2">
                <!-- Export PDF -->
                <a href="{{ route('laporan.export.pdf', request()->all()) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-bold rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 384 512"><path d="M369.9 97.9L286 14C277 5 264.8-.1 252.1-.1H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V131.9c0-12.7-5.1-25-14.1-34zM332.1 128H256V51.9l76.1 76.1zM48 464V48h160v104c0 13.3 10.7 24 24 24h104v288H48zm250.2-143.7c-12.2-12-47-8.7-64.4-6.5-17.2-10.5-28.7-25-36.8-46.3 3.9-16.1 10.1-40.6 5.4-56-4.2-13.6-21.6-13.6-24-11.3-4.3 4.3-8.8 15.6-8.8 35 0 17 6.1 36.4 12.8 52.8-15.5 35-37.1 66.8-59.5 83.1-23.7 17.3-43 27-46.9 29.8-13.6 9.8-12.5 30.2 4.1 30.2 11 0 28.5-8.8 46.9-29.2 19.3-21.4 39.5-62 55.4-106.6 22 2.9 50.8 7.4 69 7.4 20.3 0 36.1-5.1 43.1-13.1 7.2-8.3 7.5-22.3 3.7-29.3zm-192.4 56.6c10.8-9 22.9-21.7 32.7-36.5-19.1 17.6-32.9 31.9-32.7 36.5zm82.8-135.5c2.3-15.1 6.5-24.1 9.7-24.1 3.2 0 5.4 6.9 5.4 17.3 0 6.6-1.5 13.8-3.4 20.8-3.9-5.3-8.1-10.2-11.7-14zM218.4 290c-9.5 21.6-21 41.8-34 58.7 8.1-18.7 18.2-37.7 29.1-52.9 2-2.7 3.9-5.1 4.9-5.8zm73.9 3.2c-5 4.8-16.8 5.7-34.9 5.7-9.5 0-21.4-.7-32.5-2.2 17.9-10.4 31.3-17.6 39-17.6 6 0 10.2 3.4 10.2 7.7 0 1.9-4.8 5.4-21.8 6.4z"/></svg>
                    Cetak PDF
                </a>
                
                <!-- Export Excel -->
                <a href="{{ route('laporan.export.excel', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-bold rounded-lg shadow-sm transition-colors shadow-emerald-500/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Unduh Excel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100/50 border-b border-gray-200">
                    <tr>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">No</th>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">Wilayah / UPPT</th>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">Komoditas</th>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">OPT</th>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">Luas (Ha)</th>
                        <th colspan="4" class="px-4 py-2 border-r border-gray-200 text-center border-b">Luas Serangan (Ha)</th>
                        <th colspan="4" class="px-4 py-2 border-r border-gray-200 text-center border-b">Pengendalian (Ha)</th>
                        <th rowspan="2" class="px-4 py-3">Kondisi Serangan</th>
                    </tr>
                    <tr>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-yellow-50/50">R</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-orange-50/50">S</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-red-50/50">B</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center font-bold">JML</th>
                        
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-blue-50/50">Kab</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-blue-50/50">Prov</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-blue-50/50">Masy</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-blue-50/50">APBN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $d)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 border-r border-gray-100 text-center">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 border-r border-gray-100 font-medium text-gray-900">{{ $d->uppt->nama_uppt ?? '-' }}</td>
                            <td class="px-4 py-3 border-r border-gray-100">{{ $d->komoditas->nama_komoditas ?? '-' }}</td>
                            <td class="px-4 py-3 border-r border-gray-100">{{ $d->opt->nama_opt ?? '-' }}</td>
                            <td class="px-4 py-3 border-r border-gray-100 font-bold text-center">{{ floatval($d->luas_komoditi_ha) }}</td>
                            
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ floatval($d->serangan_ringan) }}</td>
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ floatval($d->serangan_sedang) }}</td>
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ floatval($d->serangan_berat) }}</td>
                            <td class="px-3 py-3 border-r border-gray-200 font-bold text-center bg-gray-50">{{ floatval($d->serangan_jumlah) }}</td>
                            
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ floatval($d->kendali_apbd_kab) }}</td>
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ floatval($d->kendali_apbd_prov) }}</td>
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ floatval($d->kendali_masyarakat) }}</td>
                            <td class="px-3 py-3 border-r border-gray-200 text-center">{{ floatval($d->kendali_apbn) }}</td>
                            
                            <td class="px-4 py-3 text-sm italic">{{ $d->kondisi_serangan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Tidak ada data pengamatan yang ditemukan pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
