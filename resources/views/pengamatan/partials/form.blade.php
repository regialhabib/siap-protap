@php
    $blankZero = fn ($value) => (float) $value > 0 ? $value : '';
    $formConfig = [
        'historyUrl' => route('pengamatan.history'),
        'editId' => $pengamatan?->id,
        'summary' => $historySummary ?? ['luas_komoditi_ha' => 0, 'serangan_ringan' => 0, 'serangan_berat' => 0, 'serangan_jumlah' => 0, 'kondisi_serangan' => 0],
        'tanggal' => old('tanggal_pengamatan', $pengamatan?->tanggal_pengamatan->format('Y-m-d') ?? now()->format('Y-m-d')),
        'kecamatanId' => (string) old('kecamatan_id', $pengamatan?->kecamatan_id ?? request('kecamatan_id')),
        'komoditasId' => (string) old('komoditas_id', $pengamatan?->komoditas_id ?? request('komoditas_id')),
        'optId' => (string) old('opt_id', $pengamatan?->opt_id ?? request('opt_id')),
        'jenisOptId' => (string) old('jenis_opt_id', $pengamatan?->jenis_opt_id ?? request('jenis_opt_id')),
        'luas' => old('luas_komoditi_ha', $pengamatan?->luas_komoditi_ha ?? ''),
        'ringan' => $blankZero(old('serangan_ringan', $pengamatan?->serangan_ringan)),
        'berat' => $blankZero(old('serangan_berat', $pengamatan?->serangan_berat)),
        'controls' => [
            'prov' => $blankZero(old('kendali_apbd_prov', $pengamatan?->kendali_apbd_prov)),
            'kab' => $blankZero(old('kendali_apbd_kab', $pengamatan?->kendali_apbd_kab)),
            'masyarakat' => $blankZero(old('kendali_masyarakat', $pengamatan?->kendali_masyarakat)),
            'apbn' => $blankZero(old('kendali_apbn', $pengamatan?->kendali_apbn)),
        ],
        'hasKecamatan' => $kecamatans->isNotEmpty(),
        'opts' => $opts->map(fn ($opt) => [
            'id' => (string) $opt->id,
            'jenis' => $opt->jenisOpts->map(fn ($jenis) => ['id' => (string) $jenis->id, 'nama' => $jenis->nama_jenis_opt])->values(),
        ])->values(),
    ];
    $controlFields = [
        'kendali_apbd_prov' => ['label' => 'APBD I (Provinsi)', 'model' => 'kendaliProv'],
        'kendali_apbd_kab' => ['label' => 'APBD II (Kab/Kota)', 'model' => 'kendaliKab'],
        'kendali_masyarakat' => ['label' => 'Swadaya Masyarakat', 'model' => 'kendaliMasyarakat'],
        'kendali_apbn' => ['label' => 'APBN Pusat', 'model' => 'kendaliApbn'],
    ];
@endphp

@if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms class="mb-6 flex items-center justify-between bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg shadow-sm font-medium" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
        <button @click="show = false" type="button" class="text-red-600 hover:text-red-800 focus:outline-none ml-4 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
@endif

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-12">

        <!-- Header Banner -->
        <div class="p-6 md:p-8 border-b border-gray-100 bg-emerald-700 text-white flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Formulir Pengamatan Lapangan</h1>
                <p class="text-emerald-100 mt-1.5 text-sm md:text-base">Isi data lokasi, serangan tambahan, dan pengendalian.</p>
            </div>
            <div class="inline-flex items-center self-start md:self-auto bg-white/20 px-4 py-2.5 rounded-lg border border-white/30 backdrop-blur-sm">
                <svg class="w-5 h-5 mr-2.5 text-emerald-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-sm font-semibold text-white tracking-wide">Wilayah: {{ $pengamatan?->uppt?->nama_uppt ?? Auth::user()->uppt?->nama_uppt ?? 'Belum Ditentukan' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ $pengamatan ? route('pengamatan.update', $pengamatan) : route('pengamatan.store') }}" class="p-6 md:p-10" x-data="pengamatanForm(@js($formConfig))">
            @csrf
            @if($pengamatan)
                @method('PUT')
            @endif

            <!-- SEKSI 1: Informasi Dasar -->
            <div class="mb-12">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                    <span class="bg-emerald-100 text-emerald-700 w-9 h-9 rounded-lg flex items-center justify-center mr-3.5"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                    1. Lokasi & Jenis Pengamatan
                </h3>
                <p class="-mt-4 mb-5 text-sm text-gray-500">Pilih data yang sama untuk melanjutkan histori.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-6 md:p-8 rounded-xl border border-gray-200 shadow-sm">
                    <div>
                        <label for="tanggal_pengamatan" class="block text-sm font-bold text-gray-700 mb-2">Tanggal Pengamatan <span class="text-red-500">*</span></label>
                        <input id="tanggal_pengamatan" type="date" name="tanggal_pengamatan" x-model="tanggal" @change="loadHistory()" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors" required>
                        <x-input-error :messages="$errors->get('tanggal_pengamatan')" class="mt-2" />
                    </div>
                    @if($kecamatans->isNotEmpty())
                    <div>
                        <label for="kecamatan_id" class="block text-sm font-bold text-gray-700 mb-2">Kecamatan <span class="text-red-500">*</span></label>
                        <select id="kecamatan_id" name="kecamatan_id" x-model="kecamatanId" @change="loadHistory()" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors cursor-pointer" required>
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach($kecamatans as $kec)
                                <option value="{{ $kec->id }}" @selected($formConfig['kecamatanId'] === (string) $kec->id)>{{ $kec->nama_kecamatan }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('kecamatan_id')" class="mt-2" />
                    </div>
                    @endif
                    <div>
                        <label for="komoditas_id" class="block text-sm font-bold text-gray-700 mb-2">Komoditas <span class="text-red-500">*</span></label>
                        <select id="komoditas_id" name="komoditas_id" x-model="komoditasId" @change="loadHistory()" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors cursor-pointer" required>
                            <option value="">-- Pilih Komoditas --</option>
                            @foreach($komoditas as $k)
                                <option value="{{ $k->id }}" @selected($formConfig['komoditasId'] === (string) $k->id)>{{ $k->nama_komoditas }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('komoditas_id')" class="mt-2" />
                    </div>
                    <div>
                        <label for="opt_id" class="block text-sm font-bold text-gray-700 mb-2">OPT (Hama/Penyakit) <span class="text-red-500">*</span></label>
                        <select id="opt_id" name="opt_id" x-model="optId" @change="onOptChanged()" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors cursor-pointer" required>
                            <option value="">-- Pilih OPT --</option>
                            @foreach($opts as $o)
                                <option value="{{ $o->id }}" @selected($formConfig['optId'] === (string) $o->id)>{{ $o->nama_opt }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opt_id')" class="mt-2" />
                        <div class="mt-4" x-show="jenisOptions.length > 0" x-cloak>
                            <label for="jenis_opt_id" class="block text-sm font-bold text-gray-700 mb-2">Jenis OPT <span class="text-red-500">*</span></label>
                            <select id="jenis_opt_id" name="jenis_opt_id" x-model="jenisOptId" @change="loadHistory()" :required="jenisOptions.length > 0" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                                <option value="">-- Pilih Jenis OPT --</option>
                                <template x-for="jenis in jenisOptions" :key="jenis.id">
                                    <option :value="jenis.id" :selected="jenis.id === jenisOptId" x-text="jenis.nama"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('jenis_opt_id')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEKSI 2: Data Luas & Serangan -->
            <div class="mb-12">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                    <span class="bg-red-100 text-red-700 w-9 h-9 rounded-lg flex items-center justify-center mr-3.5"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></span>
                    2. Luas Tanam & Intensitas Serangan
                </h3>

                <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-5 text-sm text-amber-900" x-show="historyReady && bulanSama.length > 0" x-cloak>
                    <p class="font-bold">Sudah ada input di bulan yang sama</p>
                    <ul class="mt-2 space-y-1">
                        <template x-for="entri in bulanSama" :key="entri.edit_url">
                            <li><span x-text="entri.tanggal"></span> · <a :href="entri.edit_url" class="font-semibold underline hover:text-amber-700">Edit</a></li>
                        </template>
                    </ul>
                    <p class="mt-2">Data baru akan ditambahkan, bukan menggantikan.</p>
                </div>

                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5" x-show="loadingHistory || historyReady" x-cloak>
                    <template x-if="historyReady && terakhir">
                        <div>
                            <p class="text-sm font-bold text-emerald-900">Data sebelumnya</p>
                            <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-emerald-900 sm:grid-cols-2">
                                <p>Input terakhir: <strong x-text="terakhir"></strong></p>
                                <p>Luas komoditi terakhir: <strong x-text="formatHa(history.luas_komoditi_ha)"></strong></p>
                                <p>Serangan kumulatif: R <strong x-text="formatHa(history.serangan_ringan)"></strong> · B <strong x-text="formatHa(history.serangan_berat)"></strong></p>
                                <p>Kondisi serangan: <strong x-text="formatHa(history.kondisi_serangan)"></strong></p>
                            </div>
                        </div>
                    </template>
                    <p class="text-sm text-emerald-900" x-show="historyReady && !terakhir">Belum ada data pada periode ini.</p>
                    <p class="text-sm text-emerald-800" x-show="loadingHistory">Memuat data sebelumnya...</p>
                </div>
                <p class="mb-6 text-sm font-medium text-red-700" x-show="historyError" x-text="historyError" x-cloak></p>

                <div class="mb-6 bg-white p-6 md:p-8 rounded-xl border border-gray-200 shadow-sm">
                    <label for="luas_komoditi_ha" class="block text-sm font-bold text-gray-700 mb-3">Luas Komoditi (Ha) <span class="text-red-500">*</span></label>
                    <div class="relative w-full">
                        <input id="luas_komoditi_ha" type="number" step="0.01" min="0" name="luas_komoditi_ha" x-model="luasKomoditi" @input="areaTouched = true; manualAreaTouched = true" class="block w-full pl-5 pr-14 py-3.5 rounded-xl border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-lg font-bold text-gray-900 transition-colors" placeholder="0.00" required>
                        <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                            <span class="text-gray-400 font-bold">Ha</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-3">Terisi otomatis dari data terakhir dan dapat diubah.</p>
                    <x-input-error :messages="$errors->get('luas_komoditi_ha')" class="mt-2" />
                </div>

                <div class="bg-red-50/40 p-6 md:p-8 rounded-xl border border-red-100">
                    <p class="text-base font-bold text-red-800">Serangan Tambahan (Ha)</p>
                    <p class="mt-1 mb-6 text-sm text-gray-600">Isi luas serangan baru sejak pengamatan terakhir. Kosongkan jika tidak ada.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        @foreach(['serangan_ringan' => ['label' => 'Ringan', 'model' => 'ringan'], 'serangan_berat' => ['label' => 'Berat', 'model' => 'berat']] as $field => $attack)
                            <div>
                                <label for="{{ $field }}" class="block text-sm font-bold text-gray-700 mb-2">{{ $attack['label'] }}</label>
                                <input id="{{ $field }}" type="number" inputmode="decimal" step="0.01" min="0" name="{{ $field }}" x-model="{{ $attack['model'] }}" class="block w-full px-4 py-3 rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 text-base font-medium transition-colors" placeholder="0">
                                <x-input-error :messages="$errors->get($field)" class="mt-2" />
                            </div>
                        @endforeach
                        <div>
                            <p class="block text-sm font-bold text-gray-700 mb-2">Jumlah</p>
                            <p class="flex h-[50px] items-center rounded-lg bg-white/70 px-4 text-base font-bold text-red-700 ring-1 ring-inset ring-red-100" x-text="formatHa(totalSeranganBaru)"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEKSI 3: Upaya Pengendalian & Kondisi -->
            <div class="mb-10">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                    <span class="bg-blue-100 text-blue-700 w-9 h-9 rounded-lg flex items-center justify-center mr-3.5"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg></span>
                    3. Pengendalian
                </h3>

                <div class="bg-blue-50/40 p-6 md:p-8 rounded-xl border border-blue-100 mb-6">
                    <p class="text-base font-bold text-blue-800">Luas Pengendalian per Sumber Dana (Ha)</p>
                    <p class="mt-1 mb-6 text-sm text-gray-600">Isi luas pengendalian jika ada. Kosongkan sumber dana yang tidak dipakai.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($controlFields as $field => $control)
                            <div>
                                <label for="{{ $field }}" class="block text-sm font-bold text-gray-700 mb-2">{{ $control['label'] }}</label>
                                <input id="{{ $field }}" type="number" inputmode="decimal" step="0.01" min="0" name="{{ $field }}" x-model="{{ $control['model'] }}" class="block w-full px-4 py-3 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base font-medium transition-colors" placeholder="0">
                                <x-input-error :messages="$errors->get($field)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 flex flex-col gap-1 border-t border-blue-100 pt-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                        <p class="font-bold text-blue-800">Jumlah pengendalian: <span x-text="formatHa(totalKendaliBaru)"></span></p>
                        <p class="text-blue-900" x-show="historyReady">Maksimal: <strong x-text="formatHa(batasPengendalian)"></strong></p>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-red-700" x-show="pengendalianMelebihiBatas" x-cloak>Pengendalian melebihi luas serangan yang ada.</p>
                    <x-input-error :messages="$errors->get('pengendalian')" class="mt-2" />
                </div>

                <div class="mb-6 rounded-xl border border-blue-100 bg-white p-6 shadow-sm md:p-8">
                    <label for="cara_pengendalian" class="mb-2 block text-sm font-bold text-gray-700">Cara Pengendalian</label>
                    <textarea id="cara_pengendalian" name="cara_pengendalian" rows="3" maxlength="2000" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('cara_pengendalian', $pengamatan?->cara_pengendalian) }}</textarea>
                    <x-input-error :messages="$errors->get('cara_pengendalian')" class="mt-2" />
                </div>

                <div class="bg-gray-50 p-6 md:p-8 rounded-xl border border-gray-200">
                    <p class="text-base font-bold text-gray-800 mb-4">Kondisi Serangan</p>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:items-center">
                        <dl class="space-y-2 text-base text-gray-700" x-show="historyReady" x-cloak>
                            <div class="flex justify-between gap-4"><dt>Kondisi sebelumnya</dt><dd class="font-semibold" x-text="formatHa(history.kondisi_serangan)"></dd></div>
                            <div class="flex justify-between gap-4"><dt>+ Serangan tambahan</dt><dd class="font-semibold text-red-700" x-text="formatHa(totalSeranganBaru)"></dd></div>
                            <div class="flex justify-between gap-4 border-b border-gray-300 pb-2"><dt>− Pengendalian</dt><dd class="font-semibold text-blue-700" x-text="formatHa(totalKendaliBaru)"></dd></div>
                        </dl>
                        <div class="rounded-xl border border-gray-200 bg-white p-5">
                            <p class="text-sm text-gray-600">Sisa serangan setelah pengendalian</p>
                            <div class="mt-1 flex flex-wrap items-center gap-3">
                                <p class="text-3xl font-bold" :class="kondisiBerjalan < 0 ? 'text-red-700' : 'text-emerald-700'" x-text="formatHa(kondisiBerjalan)"></p>
                                <span class="rounded-full px-3 py-1 text-sm font-semibold" :class="statusKondisi.classes" x-show="historyReady" x-text="statusKondisi.label" x-cloak></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-6 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-4 mt-10">
                <p class="text-sm font-medium text-amber-700 sm:mr-auto" x-show="submitBlocker" x-text="submitBlocker" x-cloak></p>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-6 py-3.5 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                    Batal & Kembali
                </a>
                <button type="submit" :disabled="submitBlocker !== ''" class="inline-flex items-center justify-center px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition-all duration-200 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    {{ $pengamatan ? 'Perbarui Data Pengamatan' : 'Simpan Data Pengamatan' }}
                </button>
            </div>
        </form>
    </div>
</div>
