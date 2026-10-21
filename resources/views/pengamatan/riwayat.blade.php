<x-app-layout>
    <div class="mb-6 mt-6 overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="rounded-t-xl border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-800">Riwayat Pengamatan Periode</h1>
        </div>

        <form method="GET" action="{{ route('pengamatan.riwayat.index') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-3 xl:grid-cols-4"
            x-data="{
                kabupatenId: @js($filters['kabupaten_id']),
                upptId: @js($filters['uppt_id']),
                kecamatanId: @js($filters['kecamatan_id']),
                optId: @js($filters['opt_id']),
                jenisOptId: @js($filters['jenis_opt_id']),
                uppts: @js($uppts->map(fn ($uppt) => ['id' => (string) $uppt->id, 'kabupaten_id' => (string) $uppt->kabupaten_id, 'nama' => $uppt->nama_uppt])->values()),
                kecamatans: @js($kecamatans->map(fn ($kecamatan) => ['id' => (string) $kecamatan->id, 'nama' => $kecamatan->nama_kecamatan])->values()),
                opts: @js($opts->map(fn ($opt) => ['id' => (string) $opt->id, 'jenis' => $opt->jenisOpts->map(fn ($jenis) => ['id' => (string) $jenis->id, 'nama' => $jenis->nama_jenis_opt])->values()])->values()),
                kecamatanEndpoint: @js(route('api.uppt.kecamatans', ['uppt' => '__UPPT__'])),
                get filteredUppts() {
                    return this.uppts.filter((uppt) => !this.kabupatenId || uppt.kabupaten_id === this.kabupatenId);
                },
                get jenisOptions() {
                    return this.opts.find((opt) => opt.id === this.optId)?.jenis ?? [];
                },
                onKabupatenChanged() {
                    this.upptId = '';
                    this.kecamatanId = '';
                    this.kecamatans = [];
                },
                async onUpptChanged() {
                    this.kecamatanId = '';
                    this.kecamatans = [];
                    await this.loadKecamatans(false);
                },
                onOptChanged() {
                    this.jenisOptId = '';
                },
                async loadKecamatans(preserveSelection) {
                    if (!this.upptId) {
                        this.kecamatans = [];
                        this.kecamatanId = '';
                        return;
                    }

                    const selectedKecamatan = preserveSelection ? this.kecamatanId : '';
                    const response = await fetch(this.kecamatanEndpoint.replace('__UPPT__', this.upptId));
                    if (!response.ok) {
                        this.kecamatans = [];
                        this.kecamatanId = '';
                        return;
                    }

                    const results = await response.json();
                    this.kecamatans = results.map((kecamatan) => ({ id: String(kecamatan.id), nama: kecamatan.nama_kecamatan }));
                    this.kecamatanId = this.kecamatans.some((kecamatan) => kecamatan.id === selectedKecamatan) ? selectedKecamatan : '';
                },
                init() {
                    this.loadKecamatans(true);
                }
            }">
            <div>
                <label for="tahun" class="mb-2 block text-xs font-bold uppercase text-gray-500">Tahun (Periode Feb–Jan)</label>
                <input id="tahun" type="number" name="tahun" min="2000" max="2100" required value="{{ $tahun }}"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('tahun') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kabupaten_id" class="mb-2 block text-xs font-bold uppercase text-gray-500">Kabupaten/Kota</label>
                <select id="kabupaten_id" name="kabupaten_id" x-model="kabupatenId" @change="onKabupatenChanged()"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Kabupaten/Kota</option>
                    @foreach($kabupatens as $kabupaten)
                        <option value="{{ $kabupaten->id }}" @selected($filters['kabupaten_id'] == $kabupaten->id)>{{ $kabupaten->nama_kabupaten }}</option>
                    @endforeach
                </select>
                @error('kabupaten_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="uppt_id" class="mb-2 block text-xs font-bold uppercase text-gray-500">UPPT</label>
                <select id="uppt_id" name="uppt_id" x-model="upptId" @change="onUpptChanged()"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua UPPT</option>
                    <template x-for="uppt in filteredUppts" :key="uppt.id">
                        <option :value="uppt.id" :selected="uppt.id === upptId" x-text="uppt.nama"></option>
                    </template>
                </select>
                @error('uppt_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kecamatan_id" class="mb-2 block text-xs font-bold uppercase text-gray-500">Kecamatan</label>
                <select id="kecamatan_id" name="kecamatan_id" x-model="kecamatanId" :disabled="kecamatans.length === 0"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500">
                    <option value="" x-text="kecamatans.length ? 'Semua Kecamatan' : 'Pilih UPPT terlebih dahulu'"></option>
                    <template x-for="kecamatan in kecamatans" :key="kecamatan.id">
                        <option :value="kecamatan.id" :selected="kecamatan.id === kecamatanId" x-text="kecamatan.nama"></option>
                    </template>
                </select>
                @error('kecamatan_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="komoditas_id" class="mb-2 block text-xs font-bold uppercase text-gray-500">Komoditas</label>
                <select id="komoditas_id" name="komoditas_id" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Komoditas</option>
                    @foreach($komoditasList as $komoditas)
                        <option value="{{ $komoditas->id }}" @selected($filters['komoditas_id'] == $komoditas->id)>{{ $komoditas->nama_komoditas }}</option>
                    @endforeach
                </select>
                @error('komoditas_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="opt_id" class="mb-2 block text-xs font-bold uppercase text-gray-500">OPT</label>
                <select id="opt_id" name="opt_id" x-model="optId" @change="onOptChanged()"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua OPT</option>
                    @foreach($opts as $opt)
                        <option value="{{ $opt->id }}" @selected($filters['opt_id'] == $opt->id)>{{ $opt->nama_opt }}</option>
                    @endforeach
                </select>
                @error('opt_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jenis_opt_id" class="mb-2 block text-xs font-bold uppercase text-gray-500">Jenis OPT</label>
                <select id="jenis_opt_id" name="jenis_opt_id" x-model="jenisOptId" :disabled="jenisOptions.length === 0"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500">
                    <option value="" x-text="jenisOptions.length ? 'Semua Jenis OPT' : (optId ? 'Tidak ada jenis turunan' : 'Pilih OPT terlebih dahulu')"></option>
                    <template x-for="jenis in jenisOptions" :key="jenis.id">
                        <option :value="jenis.id" :selected="jenis.id === jenisOptId" x-text="jenis.nama"></option>
                    </template>
                </select>
                @error('jenis_opt_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-bold text-white hover:bg-emerald-700">Tampilkan</button>
                <a href="{{ route('pengamatan.riwayat.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-bold text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    @php
        $hektare = fn ($value) => \App\Services\PengamatanHistoryService::hektare((float) $value);
        $attackFields = ['tambah_ringan', 'tambah_berat'];
        $controlFields = \App\Services\PengamatanHistoryService::CONTROL_FIELDS;
    @endphp
    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <h2 class="font-bold text-gray-900">Data Pengamatan Periode {{ $tahun }} (Februari {{ $tahun }} – Januari {{ $tahun + 1 }})</h2>
        </div>
        <x-keterangan-singkatan :bordered="false" />
    </div>

    @if($data->total() > 1)
        <div class="mb-4 flex flex-col gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm md:flex-row md:items-center" x-data>
            <label for="chain-select" class="shrink-0 text-xs font-bold uppercase text-gray-500">Data Pengamatan</label>
            <select id="chain-select" @change="window.location.href = $event.target.value"
                class="block w-full flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                @foreach($chainLabels as $index => $label)
                    <option value="{{ $data->url($index + 1) }}" @selected($data->currentPage() === $index + 1)>{{ $index + 1 }}. {{ $label }}</option>
                @endforeach
            </select>
            <div class="flex shrink-0 items-center gap-2">
                @if($data->onFirstPage())
                    <span class="inline-flex h-10 cursor-not-allowed items-center rounded-lg border border-gray-200 px-3 text-sm font-semibold text-gray-300">‹ Sebelumnya</span>
                @else
                    <a href="{{ $data->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-lg border border-gray-300 px-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">‹ Sebelumnya</a>
                @endif
                <span class="px-1 text-sm text-gray-500">{{ $data->currentPage() }} / {{ $data->total() }}</span>
                @if($data->hasMorePages())
                    <a href="{{ $data->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-lg border border-gray-300 px-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Berikutnya ›</a>
                @else
                    <span class="inline-flex h-10 cursor-not-allowed items-center rounded-lg border border-gray-200 px-3 text-sm font-semibold text-gray-300">Berikutnya ›</span>
                @endif
            </div>
        </div>
    @endif

    @forelse($data as $chain)
        <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-1 border-b border-gray-100 bg-gray-50/50 px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="font-bold text-gray-900">{{ $chain->uppt }} · Kec. {{ $chain->kecamatan }} · {{ $chain->kabupaten }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ $chain->komoditas }} — {{ $chain->opt }}</p>
                </div>
                <p class="text-sm text-gray-500">Luas komoditi <span class="font-bold text-gray-900">{{ $hektare($chain->luas_komoditi_ha) }} Ha</span></p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-100/50 text-xs uppercase text-gray-700">
                        <tr>
                            <th rowspan="2" class="border-r border-gray-200 px-4 py-3">Bulan</th>
                            <th colspan="3" class="border-b border-r border-gray-200 px-4 py-2 text-center">Serangan Bulan Ini (Ha)</th>
                            <th colspan="5" class="border-b border-r border-gray-200 px-4 py-2 text-center">Pengendalian Bulan Ini (Ha)</th>
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
                        @foreach($chain->months as $month)
                            @if($month->is_blank)
                                <tr class="border-b border-gray-100 bg-gray-50 text-gray-400">
                                    <td class="whitespace-nowrap border-r border-gray-100 px-4 py-2.5">{{ $month->bulan_label }}</td>
                                    @foreach(range(1, 8) as $column)
                                        <td class="border-r border-gray-100 px-3 py-2.5 text-center">—</td>
                                    @endforeach
                                    <td class="px-4 py-2.5">—</td>
                                </tr>
                            @else
                                <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                                    <td class="whitespace-nowrap border-r border-gray-100 px-4 py-2.5 font-semibold text-gray-700">
                                        {{ $month->bulan_label }}
                                        @unless($month->ada_input)
                                            <span class="block text-xs font-normal text-amber-700">Tidak ada pengamatan</span>
                                        @endunless
                                    </td>
                                    @foreach($attackFields as $field)
                                        <td class="border-r border-gray-100 px-3 py-2.5 text-center">{{ $hektare($month->values[$field]) }}</td>
                                    @endforeach
                                    <td class="border-r border-gray-200 bg-gray-50 px-3 py-2.5 text-center font-bold">{{ $hektare($month->values['tambah_jumlah']) }}</td>
                                    @foreach($controlFields as $field)
                                        <td class="border-r border-gray-100 px-3 py-2.5 text-center">{{ $hektare($month->values[$field]) }}</td>
                                    @endforeach
                                    <td class="border-r border-gray-200 bg-blue-50/50 px-3 py-2.5 text-center font-bold">{{ $hektare($month->values['kendali_jumlah']) }}</td>
                                    <td class="whitespace-nowrap px-4 py-2.5 font-semibold">{{ $month->kondisi_label }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 bg-gray-100/50 font-bold text-gray-900">
                        <tr>
                            <td class="border-r border-gray-200 px-4 py-3">Total Periode</td>
                            @foreach($attackFields as $field)
                                <td class="border-r border-gray-200 px-3 py-3 text-center">{{ $hektare($chain->total[$field]) }}</td>
                            @endforeach
                            <td class="border-r border-gray-200 px-3 py-3 text-center">{{ $hektare($chain->total['tambah_jumlah']) }}</td>
                            @foreach($controlFields as $field)
                                <td class="border-r border-gray-200 px-3 py-3 text-center">{{ $hektare($chain->total[$field]) }}</td>
                            @endforeach
                            <td class="border-r border-gray-200 px-3 py-3 text-center">{{ $hektare($chain->total['kendali_jumlah']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">Akhir: {{ $hektare($chain->total['kondisi_akhir']) }} Ha</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @empty
        <div class="mb-6 rounded-xl border border-gray-200 bg-white px-6 py-12 text-center text-sm text-gray-500 shadow-sm">
            {{ $hasFilters ? 'Belum ada data pengamatan pada periode dan filter ini.' : 'Pilih filter lalu tampilkan untuk membaca riwayat pengamatan.' }}
        </div>
    @endforelse
</x-app-layout>
