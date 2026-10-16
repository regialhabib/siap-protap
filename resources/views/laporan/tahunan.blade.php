<x-app-layout>
    <div class="mb-6 mt-6 overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="rounded-t-xl border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <h2 class="text-xl font-bold leading-tight tracking-tight text-gray-800">Filter Laporan Tahunan</h2>
            <p class="mt-1 text-sm text-gray-500">Rekapitulasi serangan dan pengendalian per Kabupaten/Kota selama tahun {{ $tahun }}.</p>
        </div>
        <form method="GET" action="{{ route('laporan.tahunan') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <label for="tahun" class="mb-2 block text-xs font-bold uppercase text-gray-500">Tahun</label>
                <input id="tahun" type="number" name="tahun" min="2000" max="2100" value="{{ $tahun }}" required class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label for="kabupaten_id" class="mb-2 block text-xs font-bold uppercase text-gray-500">Kabupaten/Kota</label>
                <select id="kabupaten_id" name="kabupaten_id" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Kabupaten/Kota</option>
                    @foreach($kabupatens as $kabupaten)
                        <option value="{{ $kabupaten->id }}" @selected($filters['kabupaten_id'] == $kabupaten->id)>{{ $kabupaten->nama_kabupaten }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-bold text-white hover:bg-emerald-700">Tampilkan Laporan</button>
            </div>
        </form>
    </div>

    <div class="mb-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col items-start justify-between gap-4 border-b border-gray-100 bg-gray-50/50 px-6 py-4 md:flex-row md:items-center">
            <div>
                <h3 class="font-bold text-gray-900">Laporan Tahunan {{ $tahun }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $data->total() }} kelompok data · Provinsi Jambi</p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <a href="{{ route('laporan.export.tahunan.pdf', request()->query()) }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 sm:w-auto">Cetak PDF</a>
                <a href="{{ route('laporan.export.tahunan.excel', request()->query()) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700 sm:w-auto">Unduh Excel</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[2000px] text-left text-xs">
                <thead class="border-b border-gray-200 bg-gray-100/50 text-xs uppercase text-gray-700">
                    <tr>
                        <th rowspan="2" class="border-r px-3 py-3">No</th><th rowspan="2" class="border-r px-3 py-3">Kabupaten/Kota</th><th rowspan="2" class="border-r px-3 py-3">Jenis Komoditas</th><th rowspan="2" class="border-r px-3 py-3">Luas Komoditas (Ha)</th><th rowspan="2" class="border-r px-3 py-3">Jenis OPT</th><th rowspan="2" class="border-r px-3 py-3">Luas Pengendalian (Ha)</th>
                        <th colspan="3" class="border-b border-r px-3 py-2 text-center">Luas Serangan (Ha)</th><th colspan="5" class="border-b border-r px-3 py-2 text-center">Luas Pengendalian (Ha)</th><th rowspan="2" class="border-r px-3 py-3">Cara Pengendalian</th><th rowspan="2" class="px-3 py-3">Kerugian Hasil (Rp 000)</th>
                    </tr>
                    <tr>
                        <th class="border-r px-3 py-2 text-center">Ringan</th><th class="border-r px-3 py-2 text-center">Berat</th><th class="border-r px-3 py-2 text-center">Jumlah</th>
                        <th class="border-r px-3 py-2 text-center">APBD I</th><th class="border-r px-3 py-2 text-center">APBD II</th><th class="border-r px-3 py-2 text-center">Masyarakat</th><th class="border-r px-3 py-2 text-center">APBN</th><th class="border-r px-3 py-2 text-center">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="border-r px-3 py-3 text-center">{{ $loop->iteration }}</td><td class="border-r px-3 py-3">{{ $row->kabupaten }}</td><td class="border-r px-3 py-3">{{ $row->komoditas }}</td><td class="border-r px-3 py-3 text-center">{{ rtrim(rtrim(number_format($row->luas_komoditi_ha, 2), '0'), '.') }}</td><td class="border-r px-3 py-3">{{ $row->opt }}{{ $row->jenis_opt !== '' ? ' — '.$row->jenis_opt : '' }}</td><td class="border-r px-3 py-3 text-center">{{ rtrim(rtrim(number_format($row->kendali_jumlah, 2), '0'), '.') }}</td>
                            <td class="border-r px-3 py-3 text-center">{{ rtrim(rtrim(number_format($row->serangan_ringan, 2), '0'), '.') }}</td><td class="border-r px-3 py-3 text-center">{{ rtrim(rtrim(number_format($row->serangan_berat, 2), '0'), '.') }}</td><td class="border-r px-3 py-3 text-center font-bold">{{ rtrim(rtrim(number_format($row->serangan_jumlah, 2), '0'), '.') }}</td>
                            <td class="border-r px-3 py-3 text-center">{{ rtrim(rtrim(number_format($row->kendali_apbd_prov, 2), '0'), '.') }}</td><td class="border-r px-3 py-3 text-center">{{ rtrim(rtrim(number_format($row->kendali_apbd_kab, 2), '0'), '.') }}</td><td class="border-r px-3 py-3 text-center">{{ rtrim(rtrim(number_format($row->kendali_masyarakat, 2), '0'), '.') }}</td><td class="border-r px-3 py-3 text-center">{{ rtrim(rtrim(number_format($row->kendali_apbn, 2), '0'), '.') }}</td><td class="border-r px-3 py-3 text-center font-bold">{{ rtrim(rtrim(number_format($row->kendali_jumlah, 2), '0'), '.') }}</td>
                            <td class="border-r px-3 py-3">{{ $row->cara_pengendalian }}</td><td class="px-3 py-3"></td>
                        </tr>
                    @empty
                        <tr><td colspan="16" class="px-6 py-12 text-center text-gray-500">Belum ada data pengamatan pada tahun dan filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-4 py-3">{{ $data->links() }}</div>
    </div>
</x-app-layout>
