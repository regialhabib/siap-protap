<x-app-layout>
    <div class="mb-6 mt-6 overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="rounded-t-xl border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-800">Riwayat Pengamatan Tahunan</h1>
            <p class="mt-1 text-sm text-gray-500">Bandingkan luas serangan setiap bulan selama tahun {{ $tahun }}.</p>
        </div>

        <form method="GET" action="{{ route('pengamatan.riwayat.index') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-3 xl:grid-cols-4"
            x-data="{
                kabupatenId: @js($filters['kabupaten_id']),
                upptId: @js($filters['uppt_id']),
                kecamatanId: @js($filters['kecamatan_id']),
                uppts: @js($uppts->map(fn ($uppt) => ['id' => (string) $uppt->id, 'kabupaten_id' => (string) $uppt->kabupaten_id, 'nama' => $uppt->nama_uppt])->values()),
                kecamatans: @js($kecamatans->map(fn ($kecamatan) => ['id' => (string) $kecamatan->id, 'nama' => $kecamatan->nama_kecamatan])->values()),
                kecamatanEndpoint: @js(route('api.uppt.kecamatans', ['uppt' => '__UPPT__'])),
                get filteredUppts() {
                    return this.uppts.filter((uppt) => !this.kabupatenId || uppt.kabupaten_id === this.kabupatenId);
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
                <label for="tahun" class="mb-2 block text-xs font-bold uppercase text-gray-500">Tahun</label>
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
                        <option value="{{ $kabupaten->id }}">{{ $kabupaten->nama_kabupaten }}</option>
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
                        <option :value="uppt.id" x-text="uppt.nama"></option>
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
                        <option :value="kecamatan.id" x-text="kecamatan.nama"></option>
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
                <label for="jenis_opt_id" class="mb-2 block text-xs font-bold uppercase text-gray-500">Jenis OPT</label>
                <select id="jenis_opt_id" name="jenis_opt_id" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Jenis OPT</option>
                    @foreach($jenisOpts as $jenisOpt)
                        <option value="{{ $jenisOpt->id }}" @selected($filters['jenis_opt_id'] == $jenisOpt->id)>
                            {{ $jenisOpt->opt?->nama_opt }} — {{ $jenisOpt->nama_jenis_opt }}
                        </option>
                    @endforeach
                </select>
                @error('jenis_opt_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-bold text-white hover:bg-emerald-700">Tampilkan Riwayat</button>
            </div>
        </form>
    </div>

    <div class="mb-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <h2 class="font-bold text-gray-900">Data Pengamatan {{ $tahun }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $recordCount }} baris perbandingan · nilai luas serangan dan sisa serangan dalam hektare.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1350px] text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-100/70 text-xs uppercase text-gray-700">
                    <tr>
                        <th rowspan="2" class="border-r px-3 py-3">Bulan</th>
                        <th rowspan="2" class="border-r px-3 py-3">Kabupaten/Kota</th>
                        <th rowspan="2" class="border-r px-3 py-3">UPPT</th>
                        <th rowspan="2" class="border-r px-3 py-3">Kecamatan</th>
                        <th rowspan="2" class="border-r px-3 py-3">Komoditas</th>
                        <th rowspan="2" class="border-r px-3 py-3">Jenis OPT</th>
                        <th colspan="4" class="border-r border-b px-3 py-2 text-center">Periode Sebelumnya (Ha)</th>
                        <th colspan="4" class="border-b px-3 py-2 text-center">Periode Sekarang (Ha)</th>
                    </tr>
                    <tr>
                        <th class="border-r px-3 py-2 text-center">Ringan</th>
                        <th class="border-r px-3 py-2 text-center">Berat</th>
                        <th class="border-r border-b px-3 py-2 text-center">Jumlah</th>
                        <th class="border-r border-b px-3 py-2 text-center">Sisa Serangan</th>
                        <th class="border-r px-3 py-2 text-center">Ringan</th>
                        <th class="border-r px-3 py-2 text-center">Berat</th>
                        <th class="border-r px-3 py-2 text-center">Jumlah</th>
                        <th class="px-3 py-2 text-center">Sisa Serangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $row)
                        @if($row->is_empty)
                            <tr class="border-b border-gray-100 bg-gray-50/70">
                                <td class="whitespace-nowrap border-r px-3 py-3 font-semibold text-gray-700">{{ \Carbon\Carbon::create($tahun, $row->bulan, 1)->locale('id')->translatedFormat('F') }}</td>
                                <td colspan="13" class="px-3 py-3 text-center text-gray-500">Belum ada data pengamatan pada bulan ini.</td>
                            </tr>
                        @else
                            <tr class="border-b border-gray-100 hover:bg-gray-50/70">
                                <td class="whitespace-nowrap border-r px-3 py-3 font-semibold text-gray-700">{{ \Carbon\Carbon::create($tahun, $row->bulan, 1)->locale('id')->translatedFormat('F') }}</td>
                                <td class="border-r px-3 py-3">{{ $row->kabupaten }}</td>
                                <td class="border-r px-3 py-3">{{ $row->uppt }}</td>
                                <td class="border-r px-3 py-3">{{ $row->kecamatan }}</td>
                                <td class="border-r px-3 py-3">{{ $row->komoditas }}</td>
                                <td class="border-r px-3 py-3">{{ $row->opt }}{{ $row->jenis_opt ? ' — '.$row->jenis_opt : '' }}</td>
                                @foreach(['previous' => $row->previous, 'current' => $row->current] as $period => $snapshot)
                                    @foreach(['ringan', 'berat', 'jumlah'] as $field)
                                        <td class="border-r px-3 py-3 text-center {{ $field === 'jumlah' ? 'font-bold' : '' }}">
                                            {{ $snapshot === null ? '—' : number_format($snapshot[$field], 2) }}
                                        </td>
                                    @endforeach
                                    <td class="{{ $period === 'previous' ? 'border-r' : '' }} px-3 py-3 text-center font-semibold">
                                        {{ $snapshot === null ? '—' : number_format($snapshot['kondisi_serangan'], 2) }}
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($data->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $data->links() }}</div>
        @endif
    </div>
</x-app-layout>
