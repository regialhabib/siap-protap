<x-app-layout title="Laporan Triwulan">
    @php
        $hektare = fn ($value) => \App\Services\PengamatanHistoryService::hektare((float) $value);
        $months = \App\Services\PeriodePengamatan::quarterMonths($filters['tahun'], $filters['triwulan']);
        $rentang = $months[0]->translatedFormat('F Y').' – '.$months[2]->translatedFormat('F Y');
        $label = \App\Services\PeriodePengamatan::quarterLabel($filters['triwulan']);
    @endphp

    <x-filter-errors />

    <div class="mb-6 overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="rounded-t-xl border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <h2 class="text-xl font-bold leading-tight tracking-tight text-gray-800">Filter Laporan Triwulan</h2>
        </div>
        <form method="GET" action="{{ route('laporan.triwulan') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <x-tahun-select :selected="$filters['tahun']" />
                @error('tahun') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="triwulan" class="mb-2 block text-sm font-semibold text-gray-700">Triwulan</label>
                <select id="triwulan" name="triwulan" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach([1 => 'TW I (Februari – April)', 2 => 'TW II (Mei – Juli)', 3 => 'TW III (Agustus – Oktober)', 4 => 'TW IV (November – Januari)'] as $value => $text)
                        <option value="{{ $value }}" @selected($filters['triwulan'] === $value)>{{ $text }}</option>
                    @endforeach
                </select>
                @error('triwulan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kabupaten_id" class="mb-2 block text-sm font-semibold text-gray-700">Kabupaten/Kota</label>
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
                <h3 class="font-bold text-gray-900">Laporan Triwulan {{ $label }} Tahun {{ $filters['tahun'] }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $rentang }}</p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <a href="{{ route('laporan.export.triwulan.pdf', request()->query()) }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 sm:w-auto">Cetak PDF</a>
                <a href="{{ route('laporan.export.triwulan.excel', request()->query()) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700 sm:w-auto">Unduh Excel</a>
            </div>
        </div>
        <x-keterangan-singkatan />
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="whitespace-nowrap border-b border-gray-200 bg-gray-100/50 text-xs uppercase text-gray-700">
                    <tr>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">No</th>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">Kabupaten/Kota</th>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">Komoditas</th>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">Luas (Ha)</th>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">OPT</th>
                        <th colspan="3" class="border-b border-r border-gray-200 px-4 py-2 text-center">Luas Serangan (Ha)</th>
                        <th colspan="5" class="border-b border-r border-gray-200 px-4 py-2 text-center">Luas Pengendalian (Ha)</th>
                        <th rowspan="2" class="px-4 py-3">Kondisi Serangan</th>
                    </tr>
                    <tr>
                        <th class="border-r border-gray-200 bg-yellow-50/50 px-3 py-2 text-center">R</th>
                        <th class="border-r border-gray-200 bg-red-50/50 px-3 py-2 text-center">B</th>
                        <th class="border-r border-gray-200 px-3 py-2 text-center font-bold">JML</th>
                        <th class="border-r border-gray-200 bg-blue-50/50 px-3 py-2 text-center">APBD I</th>
                        <th class="border-r border-gray-200 bg-blue-50/50 px-3 py-2 text-center">APBD II</th>
                        <th class="border-r border-gray-200 bg-blue-50/50 px-3 py-2 text-center">Masy</th>
                        <th class="border-r border-gray-200 bg-blue-50/50 px-3 py-2 text-center">APBN</th>
                        <th class="border-r border-gray-200 px-3 py-2 text-center font-bold">JML</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <td class="border-r border-gray-100 px-4 py-3 text-center">{{ $data->firstItem() + $loop->index }}</td>
                            <td class="border-r border-gray-100 px-4 py-3 font-medium text-gray-900">{{ $row->kabupaten }}</td>
                            <td class="whitespace-nowrap border-r border-gray-100 px-4 py-3">{{ $row->komoditas }}</td>
                            <td class="border-r border-gray-100 px-4 py-3 text-center font-bold">{{ $hektare($row->luas_komoditi_ha) }}</td>
                            <td class="min-w-[12rem] border-r border-gray-100 px-4 py-3">{{ $row->opt }}{{ $row->jenis_opt !== '' ? ' — '.$row->jenis_opt : '' }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ $hektare($row->serangan_ringan) }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ $hektare($row->serangan_berat) }}</td>
                            <td class="border-r border-gray-200 bg-gray-50 px-3 py-3 text-center font-bold">{{ $hektare($row->serangan_jumlah) }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ $hektare($row->kendali_apbd_prov) }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ $hektare($row->kendali_apbd_kab) }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ $hektare($row->kendali_masyarakat) }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ $hektare($row->kendali_apbn) }}</td>
                            <td class="border-r border-gray-200 bg-blue-50/50 px-3 py-3 text-center font-bold">{{ $hektare($row->kendali_jumlah) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $row->kondisi_label }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="14" class="px-6 py-12 text-center text-gray-500">Belum ada data pengamatan pada triwulan dan filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-4 py-3">{{ $data->links() }}</div>
    </div>
</x-app-layout>
