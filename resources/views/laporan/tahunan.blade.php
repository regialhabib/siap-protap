<x-app-layout>
    <div class="mt-6 mb-6 overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="rounded-t-xl border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <h2 class="text-xl font-bold leading-tight tracking-tight text-gray-800">Histori Pengamatan Tahunan</h2>
            <p class="mt-1 text-sm text-gray-500">Semua catatan ditampilkan berurutan selama satu tahun.</p>
        </div>

        <div class="p-6">
            <form method="GET" action="{{ route('laporan.tahunan') }}" autocomplete="off"
                class="grid grid-cols-1 gap-4 md:grid-cols-4"
                x-data="{
                    upptId: @js($upptId),
                    upptSearch: '',
                    openOptions: false,
                    upptOptions: @js($uppts->map(fn ($uppt) => ['id' => (string) $uppt->id, 'name' => $uppt->nama_uppt])->values()),
                    kecamatans: @js($kecamatans->map(fn ($kecamatan) => ['id' => (string) $kecamatan->id, 'name' => $kecamatan->nama_kecamatan])->values()),
                    kecamatanId: @js($kecamatanId),
                    get filteredUppts() {
                        return this.upptOptions.filter(option => option.name.toLowerCase().includes(this.upptSearch.toLowerCase()));
                    },
                    init() {
                        this.upptSearch = this.upptOptions.find(option => option.id === this.upptId)?.name ?? '';
                    },
                    async selectUppt(option) {
                        this.upptId = option.id;
                        this.upptSearch = option.name;
                        this.openOptions = false;
                        this.kecamatanId = '';
                        const response = await fetch(`/api/uppt/${option.id}/kecamatans`);
                        this.kecamatans = (await response.json()).map(kecamatan => ({ id: String(kecamatan.id), name: kecamatan.nama_kecamatan }));
                    },
                }"
                @submit="if (!upptId) { $event.preventDefault(); $refs.upptSearch.setCustomValidity('Pilih wilayah UPPT dari daftar.'); $refs.upptSearch.reportValidity(); }">
                <div>
                    <label for="tahun" class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500">Tahun</label>
                    <input id="tahun" type="number" name="tahun" min="2000" max="2100" value="{{ $tahun }}" required
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="histori-uppt" class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500">Wilayah / UPPT</label>
                    <div class="relative" @click.outside="openOptions = false">
                        <input id="histori-uppt" x-ref="upptSearch" type="text" x-model="upptSearch"
                            @focus="openOptions = true" @input="upptId = ''; kecamatans = []; kecamatanId = ''; $refs.upptSearch.setCustomValidity('')"
                            @keydown.escape="openOptions = false" role="combobox" aria-autocomplete="list"
                            aria-controls="histori-uppt-options" :aria-expanded="openOptions.toString()"
                            placeholder="Cari atau pilih wilayah UPPT" required autocomplete="off"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <input type="hidden" name="uppt_id" :value="upptId">
                        <div id="histori-uppt-options" x-cloak x-show="openOptions" role="listbox"
                            class="absolute z-30 mt-1 max-h-56 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                            <template x-for="option in filteredUppts" :key="option.id">
                                <button type="button" role="option" :aria-selected="upptId === option.id" @click="selectUppt(option)"
                                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 focus:bg-emerald-50 focus:outline-none"
                                    x-text="option.name"></button>
                            </template>
                            <p x-show="filteredUppts.length === 0" class="px-4 py-2 text-sm text-gray-500">Wilayah UPPT tidak ditemukan.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="histori-kecamatan" class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500">Kecamatan</label>
                    <select id="histori-kecamatan" name="kecamatan_id" x-model="kecamatanId" :disabled="kecamatans.length === 0"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500">
                        <option value="" x-text="kecamatans.length > 0 ? 'Semua Kecamatan' : 'Pilih UPPT terlebih dahulu'"></option>
                        <template x-for="kecamatan in kecamatans" :key="kecamatan.id">
                            <option :value="kecamatan.id" x-text="kecamatan.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label for="histori-search" class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500">Cari</label>
                    <input id="histori-search" type="search" name="search" value="{{ $search }}" placeholder="Komoditas, OPT, kecamatan..."
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <div class="md:col-span-4">
                    <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700 sm:w-auto">
                        Tampilkan Histori
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mb-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-gray-100 bg-gray-50/50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-bold text-gray-900">Histori Tahun {{ $tahun }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $data->count() }} catatan, diurutkan dari tanggal terlama. Luas serangan ditampilkan per catatan.</p>
            </div>
            <a href="{{ route('laporan.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Buka laporan bulanan / triwulan</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-100/50 text-xs uppercase text-gray-700">
                    <tr>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">No</th>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">Tanggal Catatan</th>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">Wilayah Pengamatan / Kecamatan</th>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">Komoditas</th>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">OPT</th>
                        <th rowspan="2" class="border-r border-gray-200 px-4 py-3">Luas (Ha)</th>
                        <th colspan="3" class="border-b border-r border-gray-200 px-4 py-2 text-center">Luas Serangan per Catatan (Ha)</th>
                        <th colspan="5" class="border-b border-r border-gray-200 px-4 py-2 text-center">Luas Pengendalian (Ha)</th>
                        <th rowspan="2" class="px-4 py-3">Kondisi Serangan Berjalan</th>
                    </tr>
                    <tr>
                        <th class="border-r border-gray-200 bg-yellow-50/50 px-3 py-2 text-center">R</th>
                        <th class="border-r border-gray-200 bg-red-50/50 px-3 py-2 text-center">B</th>
                        <th class="border-r border-gray-200 px-3 py-2 text-center font-bold">JML</th>
                        <th class="border-r border-gray-200 bg-blue-50/50 px-3 py-2 text-center">Kab</th>
                        <th class="border-r border-gray-200 bg-blue-50/50 px-3 py-2 text-center">Prov</th>
                        <th class="border-r border-gray-200 bg-blue-50/50 px-3 py-2 text-center">Masy</th>
                        <th class="border-r border-gray-200 bg-blue-50/50 px-3 py-2 text-center">APBN</th>
                        <th class="border-r border-gray-200 px-3 py-2 text-center font-bold">JML</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                            <td class="border-r border-gray-100 px-4 py-3 text-center">{{ $loop->iteration }}</td>
                            <td class="whitespace-nowrap border-r border-gray-100 px-4 py-3">{{ $row->tanggal_pengamatan->format('d/m/Y') }}</td>
                            <td class="border-r border-gray-100 px-4 py-3 font-medium text-gray-900">{{ $row->uppt->nama_uppt ?? '-' }}{{ $row->kecamatan ? ' / '.$row->kecamatan->nama_kecamatan : '' }}</td>
                            <td class="border-r border-gray-100 px-4 py-3">{{ $row->komoditas->nama_komoditas ?? '-' }}</td>
                            <td class="border-r border-gray-100 px-4 py-3">{{ $row->opt->nama_opt ?? '-' }}{{ $row->jenisOpt ? ' — '.$row->jenisOpt->nama_jenis_opt : '' }}</td>
                            <td class="border-r border-gray-100 px-4 py-3 text-center font-bold">{{ (float) $row->luas_komoditi_ha }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ (float) $row->serangan_ringan }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ (float) $row->serangan_berat }}</td>
                            <td class="border-r border-gray-200 bg-gray-50 text-center font-bold px-3 py-3">{{ (float) $row->serangan_jumlah }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ (float) $row->kendali_apbd_kab }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ (float) $row->kendali_apbd_prov }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ (float) $row->kendali_masyarakat }}</td>
                            <td class="border-r border-gray-100 px-3 py-3 text-center">{{ (float) $row->kendali_apbn }}</td>
                            <td class="border-r border-gray-200 bg-blue-50/50 px-3 py-3 text-center font-bold">{{ (float) $row->kendali_jumlah }}</td>
                            <td class="whitespace-nowrap px-4 py-3 italic">{{ number_format($row->kondisi_serangan_ha, 2) }} Ha</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-6 py-12 text-center text-gray-500">Belum ada catatan pengamatan pada periode dan filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
