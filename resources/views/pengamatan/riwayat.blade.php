<x-app-layout title="Riwayat Pengamatan">
    <div class="mb-6 overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm">
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
                    this.kabupatenId = this.uppts.find((uppt) => uppt.id === this.upptId)?.kabupaten_id ?? this.kabupatenId;
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
                <x-tahun-select :selected="$tahun" />
                @error('tahun') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kabupaten_id" class="mb-2 block text-sm font-semibold text-gray-700">Kabupaten/Kota <span class="text-red-500">*</span></label>
                <select id="kabupaten_id" name="kabupaten_id" x-model="kabupatenId" @change="onKabupatenChanged()" required
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">-- Pilih Kabupaten/Kota --</option>
                    @foreach($kabupatens as $kabupaten)
                        <option value="{{ $kabupaten->id }}" @selected($filters['kabupaten_id'] == $kabupaten->id)>{{ $kabupaten->nama_kabupaten }}</option>
                    @endforeach
                </select>
                @error('kabupaten_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="uppt_id" class="mb-2 block text-sm font-semibold text-gray-700">UPPT <span class="text-red-500">*</span></label>
                <select id="uppt_id" name="uppt_id" x-model="upptId" @change="onUpptChanged()" required
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">-- Pilih UPPT --</option>
                    <template x-for="uppt in filteredUppts" :key="uppt.id">
                        <option :value="uppt.id" :selected="uppt.id === upptId" x-text="uppt.nama"></option>
                    </template>
                </select>
                @error('uppt_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kecamatan_id" class="mb-2 block text-sm font-semibold text-gray-700">Kecamatan <span class="text-red-500">*</span></label>
                <select id="kecamatan_id" name="kecamatan_id" x-model="kecamatanId" :disabled="kecamatans.length === 0" :required="kecamatans.length > 0"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500">
                    <option value="" x-text="kecamatans.length ? '-- Pilih Kecamatan --' : (upptId ? 'UPPT ini tidak punya kecamatan' : 'Pilih UPPT dulu')"></option>
                    <template x-for="kecamatan in kecamatans" :key="kecamatan.id">
                        <option :value="kecamatan.id" :selected="kecamatan.id === kecamatanId" x-text="kecamatan.nama"></option>
                    </template>
                </select>
                @error('kecamatan_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="komoditas_id" class="mb-2 block text-sm font-semibold text-gray-700">Komoditas <span class="text-red-500">*</span></label>
                <select id="komoditas_id" name="komoditas_id" required class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">-- Pilih Komoditas --</option>
                    @foreach($komoditasList as $komoditas)
                        <option value="{{ $komoditas->id }}" @selected($filters['komoditas_id'] == $komoditas->id)>{{ $komoditas->nama_komoditas }}</option>
                    @endforeach
                </select>
                @error('komoditas_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="opt_id" class="mb-2 block text-sm font-semibold text-gray-700">OPT <span class="text-red-500">*</span></label>
                <select id="opt_id" name="opt_id" x-model="optId" @change="onOptChanged()" required
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">-- Pilih OPT --</option>
                    @foreach($opts as $opt)
                        <option value="{{ $opt->id }}" @selected($filters['opt_id'] == $opt->id)>{{ $opt->nama_opt }}</option>
                    @endforeach
                </select>
                @error('opt_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jenis_opt_id" class="mb-2 block text-sm font-semibold text-gray-700">Jenis OPT <span class="text-red-500">*</span></label>
                <select id="jenis_opt_id" name="jenis_opt_id" x-model="jenisOptId" :disabled="jenisOptions.length === 0" :required="jenisOptions.length > 0"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500">
                    <option value="" x-text="jenisOptions.length ? '-- Pilih Jenis OPT --' : (optId ? 'OPT ini tidak punya jenis' : 'Pilih OPT dulu')"></option>
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
        $attackFields = ['tambah_ringan' => 'R', 'tambah_berat' => 'B'];
        $controlFields = ['kendali_apbd_prov' => 'APBD I', 'kendali_apbd_kab' => 'APBD II', 'kendali_masyarakat' => 'Masy', 'kendali_apbn' => 'APBN'];
        $perubahan = function (float $awal, float $akhir) use ($hektare): object {
            $selisih = round($akhir - $awal, 2);

            return match (true) {
                $selisih > 0 => (object) ['ikon' => '▲', 'teks' => 'Bertambah '.$hektare($selisih).' Ha', 'warna' => 'bg-red-50 text-red-700'],
                $selisih < 0 => (object) ['ikon' => '▼', 'teks' => 'Berkurang '.$hektare(abs($selisih)).' Ha', 'warna' => 'bg-emerald-50 text-emerald-700'],
                default => (object) ['ikon' => '━', 'teks' => 'Tetap', 'warna' => 'bg-gray-100 text-gray-600'],
            };
        };
    @endphp
    @if($chain)
        <h2 class="mb-4 text-lg font-bold text-gray-900">Periode {{ $tahun }} <span class="font-medium text-gray-600">(Februari {{ $tahun }} – Januari {{ $tahun + 1 }})</span></h2>
    @endif


    @if($chain)
        @php
            $totalPerubahan = $perubahan($chain->total['kondisi_awal'], $chain->total['kondisi_akhir']);
            $monthGroups = collect($chain->months)->chunkWhile(fn ($month, $key, $chunk) => $month->is_blank && $chunk->last()->is_blank);
        @endphp
        <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
            x-data="{
                rincian: false,
                init() {
                    try { this.rincian = localStorage.getItem('riwayat.rincian') === '1'; } catch (e) {}
                    this.$watch('rincian', (value) => { try { localStorage.setItem('riwayat.rincian', value ? '1' : '0'); } catch (e) {} });
                },
            }">
            <div class="flex flex-col gap-1 border-b border-gray-100 bg-gray-50/50 px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="font-bold text-gray-900">{{ $chain->uppt }} · Kec. {{ $chain->kecamatan }} · {{ $chain->kabupaten }}</h3>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-200">
                            <span class="font-normal text-emerald-700">Komoditas</span> {{ $chain->komoditas }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 font-semibold text-amber-900 ring-1 ring-inset ring-amber-200">
                            <span class="font-normal text-amber-800">OPT</span> {{ $chain->opt }}
                        </span>
                        @if ($chain->jenis_opt)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1 font-semibold text-orange-900 ring-1 ring-inset ring-orange-200">
                                <span class="font-normal text-orange-800">Jenis OPT</span> {{ $chain->jenis_opt }}
                            </span>
                        @endif
                    </div>
                </div>
                <p class="text-sm text-gray-600">Luas komoditi <span class="font-bold text-gray-900">{{ $hektare($chain->luas_komoditi_ha) }} Ha</span></p>
            </div>

            {{-- Ringkasan periode: awal + serangan − pengendalian = akhir --}}
            <div class="grid grid-cols-2 gap-3 border-b border-gray-100 p-4 sm:p-6 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-sm text-gray-600">Kondisi awal periode</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $hektare($chain->total['kondisi_awal']) }} Ha</p>
                </div>
                <div class="rounded-lg border border-red-100 bg-red-50/60 p-4">
                    <p class="text-sm text-red-800">+ Serangan baru</p>
                    <p class="mt-1 text-2xl font-bold text-red-700">{{ $hektare($chain->total['tambah_jumlah']) }} Ha</p>
                </div>
                <div class="rounded-lg border border-blue-100 bg-blue-50/60 p-4">
                    <p class="text-sm text-blue-800">− Pengendalian</p>
                    <p class="mt-1 text-2xl font-bold text-blue-700">{{ $hektare($chain->total['kendali_jumlah']) }} Ha</p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm text-emerald-900">= Kondisi akhir</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-800">{{ $hektare($chain->total['kondisi_akhir']) }} Ha</p>
                    <span class="mt-2 inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-0.5 text-sm font-semibold {{ $totalPerubahan->warna }}"><span aria-hidden="true">{{ $totalPerubahan->ikon }}</span>{{ $totalPerubahan->teks }}</span>
                </div>
            </div>

            <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p class="text-sm text-gray-600">Kondisi akhir setiap bulan menjadi kondisi awal bulan berikutnya.</p>
                <button type="button" @click="rincian = !rincian" :aria-pressed="rincian.toString()"
                    class="inline-flex shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50"
                    x-text="rincian ? 'Sembunyikan rincian' : 'Tampilkan rincian (R/B & sumber dana)'">Tampilkan rincian (R/B &amp; sumber dana)</button>
            </div>
            <x-keterangan-singkatan class="border-t" x-show="rincian" x-cloak />

            {{-- Mobile: satu kartu per bulan --}}
            <ul class="divide-y divide-gray-100 border-t border-gray-100 md:hidden">
                @foreach($monthGroups as $group)
                    @php $month = $group->first(); @endphp
                    @if($month->is_blank)
                        <li class="bg-gray-50 px-4 py-3 text-sm text-gray-500">
                            <span class="font-medium">{{ $group->count() > 1 ? $month->bulan_label.' – '.$group->last()->bulan_label : $month->bulan_label }}</span> · belum ada data
                        </li>
                    @else
                        @php $bulanPerubahan = $perubahan($month->kondisi_awal, $month->kondisi_akhir); @endphp
                        <li class="space-y-3 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $month->bulan_label }}</p>
                                    @unless($month->ada_input)
                                        <p class="text-sm text-amber-800">Tidak ada pengamatan, kondisi dibawa</p>
                                    @endunless
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-0.5 text-sm font-semibold {{ $bulanPerubahan->warna }}"><span aria-hidden="true">{{ $bulanPerubahan->ikon }}</span>{{ $bulanPerubahan->teks }}</span>
                            </div>
                            <dl class="grid grid-cols-4 gap-2 text-center">
                                <div class="rounded-md bg-gray-50 px-1 py-2"><dt class="text-xs text-gray-600">Awal</dt><dd class="text-sm font-semibold text-gray-900">{{ $hektare($month->kondisi_awal) }}</dd></div>
                                <div class="rounded-md bg-red-50/60 px-1 py-2"><dt class="text-xs text-red-800">+ Serangan</dt><dd class="text-sm font-semibold text-red-700">{{ $month->ada_input ? $hektare($month->values['tambah_jumlah']) : '—' }}</dd></div>
                                <div class="rounded-md bg-blue-50/60 px-1 py-2"><dt class="text-xs text-blue-800">− Kendali</dt><dd class="text-sm font-semibold text-blue-700">{{ $month->ada_input ? $hektare($month->values['kendali_jumlah']) : '—' }}</dd></div>
                                <div class="rounded-md bg-emerald-50 px-1 py-2"><dt class="text-xs text-emerald-900">= Akhir</dt><dd class="text-sm font-bold text-gray-900">{{ $hektare($month->kondisi_akhir) }}</dd></div>
                            </dl>
                            @if($month->ada_input)
                                <div x-show="rincian" x-cloak class="space-y-1 text-sm text-gray-600">
                                    <p>Serangan: R {{ $hektare($month->values['tambah_ringan']) }} · B {{ $hektare($month->values['tambah_berat']) }}</p>
                                    <p>Pengendalian: @foreach($controlFields as $field => $label){{ $label }} {{ $hektare($month->values[$field]) }}{{ $loop->last ? '' : ' · ' }}@endforeach</p>
                                </div>
                            @endif
                        </li>
                    @endif
                @endforeach
                <li class="bg-gray-100/60 px-4 py-3 text-sm font-semibold text-gray-900">
                    Total periode: {{ $hektare($chain->total['kondisi_awal']) }} + {{ $hektare($chain->total['tambah_jumlah']) }} − {{ $hektare($chain->total['kendali_jumlah']) }} = {{ $hektare($chain->total['kondisi_akhir']) }} Ha
                </li>
            </ul>

            <div class="hidden overflow-x-auto border-t border-gray-100 md:block">
                <table class="w-full text-left text-sm">
                    <thead class="whitespace-nowrap border-b border-gray-200 bg-gray-100/50 text-xs uppercase text-gray-700">
                        <tr>
                            <th rowspan="2" class="border-r border-gray-200 px-4 py-3">Bulan</th>
                            <th rowspan="2" class="border-r border-gray-200 px-4 py-3 text-right">Kondisi Awal</th>
                            <th :colspan="rincian ? 3 : 1" class="border-r border-gray-200 bg-red-50/60 px-4 py-3 text-center text-red-800" :class="rincian && 'border-b'">+ Serangan Baru</th>
                            <th :colspan="rincian ? 5 : 1" class="border-r border-gray-200 bg-blue-50/60 px-4 py-3 text-center text-blue-800" :class="rincian && 'border-b'">− Pengendalian</th>
                            <th rowspan="2" class="border-r border-gray-200 bg-emerald-50/60 px-4 py-3 text-right text-emerald-900">= Kondisi Akhir</th>
                            <th rowspan="2" class="px-4 py-3">Perubahan</th>
                        </tr>
                        <tr x-show="rincian" x-cloak>
                            @foreach($attackFields as $label)
                                <th class="border-r border-gray-200 px-3 py-2 text-right">{{ $label }}</th>
                            @endforeach
                            <th class="border-r border-gray-200 px-3 py-2 text-right">JML</th>
                            @foreach($controlFields as $label)
                                <th class="border-r border-gray-200 px-3 py-2 text-right">{{ $label }}</th>
                            @endforeach
                            <th class="border-r border-gray-200 px-3 py-2 text-right">JML</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach($monthGroups as $group)
                            @php $month = $group->first(); @endphp
                            @if($month->is_blank)
                                <tr class="border-b border-gray-100 bg-gray-50 text-gray-500">
                                    <td :colspan="rincian ? 13 : 6" class="px-4 py-2.5">
                                        <span class="font-medium">{{ $group->count() > 1 ? $month->bulan_label.' – '.$group->last()->bulan_label : $month->bulan_label }}</span>
                                        · belum ada data
                                    </td>
                                </tr>
                            @else
                                @php $bulanPerubahan = $perubahan($month->kondisi_awal, $month->kondisi_akhir); @endphp
                                <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50">
                                    <td class="whitespace-nowrap border-r border-gray-100 px-4 py-3 font-semibold text-gray-900">
                                        {{ $month->bulan_label }}
                                        @unless($month->ada_input)
                                            <span class="block text-sm font-normal text-amber-800">Tidak ada pengamatan, kondisi dibawa</span>
                                        @endunless
                                    </td>
                                    <td class="whitespace-nowrap border-r border-gray-100 px-4 py-3 text-right">{{ $hektare($month->kondisi_awal) }} Ha</td>
                                    @foreach(array_keys($attackFields) as $field)
                                        <td x-show="rincian" x-cloak class="whitespace-nowrap border-r border-gray-100 px-3 py-3 text-right">{{ $hektare($month->values[$field]) }}</td>
                                    @endforeach
                                    <td class="whitespace-nowrap border-r border-gray-100 bg-red-50/30 px-4 py-3 text-right font-semibold text-red-700">{{ $month->ada_input ? $hektare($month->values['tambah_jumlah']).' Ha' : '—' }}</td>
                                    @foreach(array_keys($controlFields) as $field)
                                        <td x-show="rincian" x-cloak class="whitespace-nowrap border-r border-gray-100 px-3 py-3 text-right">{{ $hektare($month->values[$field]) }}</td>
                                    @endforeach
                                    <td class="whitespace-nowrap border-r border-gray-100 bg-blue-50/30 px-4 py-3 text-right font-semibold text-blue-700">{{ $month->ada_input ? $hektare($month->values['kendali_jumlah']).' Ha' : '—' }}</td>
                                    <td class="whitespace-nowrap border-r border-gray-100 bg-emerald-50/30 px-4 py-3 text-right font-bold text-gray-900">{{ $hektare($month->kondisi_akhir) }} Ha</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-0.5 text-sm font-semibold {{ $bulanPerubahan->warna }}"><span aria-hidden="true">{{ $bulanPerubahan->ikon }}</span>{{ $bulanPerubahan->teks }}</span>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 bg-gray-100/50 font-bold text-gray-900">
                        <tr>
                            <td class="border-r border-gray-200 px-4 py-3">Total Periode</td>
                            <td class="whitespace-nowrap border-r border-gray-200 px-4 py-3 text-right">{{ $hektare($chain->total['kondisi_awal']) }} Ha</td>
                            @foreach(array_keys($attackFields) as $field)
                                <td x-show="rincian" x-cloak class="whitespace-nowrap border-r border-gray-200 px-3 py-3 text-right">{{ $hektare($chain->total[$field]) }}</td>
                            @endforeach
                            <td class="whitespace-nowrap border-r border-gray-200 px-4 py-3 text-right text-red-700">{{ $hektare($chain->total['tambah_jumlah']) }} Ha</td>
                            @foreach(array_keys($controlFields) as $field)
                                <td x-show="rincian" x-cloak class="whitespace-nowrap border-r border-gray-200 px-3 py-3 text-right">{{ $hektare($chain->total[$field]) }}</td>
                            @endforeach
                            <td class="whitespace-nowrap border-r border-gray-200 px-4 py-3 text-right text-blue-700">{{ $hektare($chain->total['kendali_jumlah']) }} Ha</td>
                            <td class="whitespace-nowrap border-r border-gray-200 px-4 py-3 text-right">{{ $hektare($chain->total['kondisi_akhir']) }} Ha</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-0.5 text-sm font-semibold {{ $totalPerubahan->warna }}"><span aria-hidden="true">{{ $totalPerubahan->ikon }}</span>{{ $totalPerubahan->teks }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="mb-6 rounded-xl border border-gray-200 bg-white px-6 py-12 text-center text-base text-gray-600 shadow-sm">
            {{ $hasFilters ? 'Belum ada data pengamatan untuk pilihan ini pada periode tersebut.' : 'Isi semua filter lalu klik Tampilkan untuk membaca riwayat pengamatan.' }}
        </div>
    @endif
</x-app-layout>
