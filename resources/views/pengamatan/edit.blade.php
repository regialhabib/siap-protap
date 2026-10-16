<x-app-layout>

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
            <div class="p-6 md:p-8 border-b border-gray-100 bg-emerald-700 text-white flex flex-col md:flex-row md:justify-between md:items-center gap-4 relative overflow-hidden">
                <!-- Ornamen background -->
                <div class="absolute top-0 right-0 opacity-10">
                    <svg class="w-48 h-48 -mr-10 -mt-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"></path></svg>
                </div>
                
                <div class="relative z-10">
                    <h1 class="text-2xl font-bold tracking-tight">Formulir Pengamatan Lapangan</h1>
                    <p class="text-emerald-100 mt-1.5 text-sm md:text-base">Isi data lokasi, serangan tambahan, dan pengendalian.</p>
                </div>
                <div class="relative z-10 inline-flex items-center bg-white/20 px-4 py-2.5 rounded-lg border border-white/30 backdrop-blur-sm">
                    <svg class="w-5 h-5 mr-2.5 text-emerald-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-sm font-semibold text-white tracking-wide">Wilayah: {{ Auth::user()->uppt->nama_uppt ?? 'Belum Ditentukan' }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('pengamatan.update', $pengamatan->id) }}" class="p-6 md:p-10"
                x-data="pengamatanForm(@js(route('pengamatan.history')), @js($historySummary), @js(old('luas_komoditi_ha', $pengamatan->luas_komoditi_ha)), @js($pengamatan->id), @js((float) old('serangan_ringan', $pengamatan->serangan_ringan) > 0 || (float) old('serangan_berat', $pengamatan->serangan_berat) > 0), @js((float) old('serangan_ringan', $pengamatan->serangan_ringan)), @js((float) old('serangan_berat', $pengamatan->serangan_berat)), @js(['kab' => old('kendali_apbd_kab', $pengamatan->kendali_apbd_kab), 'prov' => old('kendali_apbd_prov', $pengamatan->kendali_apbd_prov), 'masyarakat' => old('kendali_masyarakat', $pengamatan->kendali_masyarakat), 'apbn' => old('kendali_apbn', $pengamatan->kendali_apbn)]))"
                x-init="$nextTick(() => { filterJenisOpt(); loadHistory(); })">
                @csrf
                @method('PUT')

                <!-- SEKSI 1: Informasi Dasar -->
                <div class="mb-12">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-emerald-100 text-emerald-700 w-9 h-9 rounded-lg flex items-center justify-center mr-3.5"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                        1. Lokasi & Jenis Pengamatan
                    </h3>
                    <p class="-mt-4 mb-5 text-sm text-gray-500">Pilih data yang sama untuk melanjutkan histori.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-6 md:p-8 rounded-xl border border-gray-200 shadow-sm">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Pengamatan <span class="text-red-500">*</span></label>
                            <input type="date" x-ref="tanggal" name="tanggal_pengamatan" value="{{ old('tanggal_pengamatan', \Carbon\Carbon::parse($pengamatan->tanggal_pengamatan)->format('Y-m-d')) }}" @change="loadHistory()" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors" required>
                            <x-input-error :messages="$errors->get('tanggal_pengamatan')" class="mt-2" />
                        </div>
                        @if(isset($kecamatans) && $kecamatans->count() > 0)
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Kecamatan <span class="text-red-500">*</span></label>
                            <select name="kecamatan_id" x-ref="kecamatan" @change="loadHistory()" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors cursor-pointer" required>
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach($kecamatans as $kec)
                                    <option value="{{ $kec->id }}" {{ old('kecamatan_id', $pengamatan->kecamatan_id) == $kec->id ? 'selected' : '' }}>{{ $kec->nama_kecamatan }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('kecamatan_id')" class="mt-2" />
                        </div>
                        @endif
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Komoditas <span class="text-red-500">*</span></label>
                            <select name="komoditas_id" x-ref="komoditas" @change="loadHistory()" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors cursor-pointer" required>
                                <option value="">-- Pilih Komoditas --</option>
                                @foreach($komoditas as $k)
                                    <option value="{{ $k->id }}" {{ old('komoditas_id', $pengamatan->komoditas_id) == $k->id ? 'selected' : '' }}>{{ $k->nama_komoditas }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('komoditas_id')" class="mt-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">OPT (Hama/Penyakit) <span class="text-red-500">*</span></label>
                            <select id="opt_id" name="opt_id" x-ref="opt" @change="filterJenisOpt(); loadHistory()" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors cursor-pointer" required>
                                <option value="">-- Pilih OPT --</option>
                                @foreach($opts as $o)
                                    <option value="{{ $o->id }}" {{ old('opt_id', $pengamatan->opt_id) == $o->id ? 'selected' : '' }}>{{ $o->nama_opt }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('opt_id')" class="mt-2" />
                            <div id="jenis-opt-wrapper" class="mt-4">
                                <label for="jenis_opt_id" class="block text-sm font-bold text-gray-700 mb-2">Jenis turunan <span class="text-red-500">*</span></label>
                                <select id="jenis_opt_id" name="jenis_opt_id" x-ref="jenisOpt" @change="loadHistory()" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" data-selected="{{ old('jenis_opt_id', $pengamatan->jenis_opt_id) }}">
                                    <option value="">-- Pilih jenis turunan --</option>
                                    @foreach($opts as $o)
                                        @foreach($o->jenisOpts as $jenis)
                                            <option value="{{ $jenis->id }}" data-opt="{{ $o->id }}" {{ old('jenis_opt_id', $pengamatan->jenis_opt_id) == $jenis->id ? 'selected' : '' }}>{{ $jenis->nama_jenis_opt }}</option>
                                        @endforeach
                                    @endforeach
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
                    
                    <div class="mb-6 bg-white p-6 md:p-8 rounded-xl border border-gray-200 shadow-sm">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Luas Komoditi (Ha) <span class="text-red-500">*</span></label>
                        <div class="relative w-full">
                            <input type="number" step="0.01" min="0" name="luas_komoditi_ha" x-model="luasKomoditi" @input="areaTouched = true; manualAreaTouched = true" class="block w-full pl-5 pr-14 py-3.5 rounded-xl border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-lg font-bold text-gray-900 transition-colors" placeholder="0.00" required>
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                                <span class="text-gray-400 font-bold">Ha</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3 font-medium">Terisi otomatis dari data terakhir dan dapat diubah.</p>
                        <x-input-error :messages="$errors->get('luas_komoditi_ha')" class="mt-2" />
                    </div>

                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5" x-show="loadingHistory || historyReady" x-cloak>
                        <p class="text-sm font-bold text-emerald-900" x-show="history.luas_komoditi_ha > 0">Data sebelumnya</p>
                        <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-emerald-900 sm:grid-cols-2" x-show="historyReady && history.luas_komoditi_ha > 0">
                            <p>Luas komoditi terakhir: <strong x-text="Number(history.luas_komoditi_ha).toFixed(2)"></strong> Ha</p>
                            <p>Keadaan serangan: <strong x-text="Number(history.kondisi_serangan).toFixed(2)"></strong> Ha</p>
                        </div>
                        <p class="text-sm text-emerald-900" x-show="historyReady && history.luas_komoditi_ha <= 0">Belum ada data tahun ini. Keadaan serangan dimulai dari 0 Ha.</p>
                        <p class="text-sm text-emerald-800" x-show="loadingHistory">Memuat data sebelumnya...</p>
                    </div>
                    <p class="mb-6 text-sm font-medium text-red-700" x-show="historyError" x-text="historyError" x-cloak></p>

                    <div class="bg-red-50/30 p-6 md:p-8 rounded-xl border border-red-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-red-100 rounded-bl-full -mr-8 -mt-8 opacity-50 pointer-events-none"></div>
                        <p class="text-sm font-bold text-red-800 mb-6 relative z-10">Serangan Tambahan (Ha)</p>
                        
                        <div class="mb-5 relative z-10">
                            <button type="button" @click="showAttack = !showAttack" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">
                                <span x-text="showAttack ? 'Tutup input serangan' : 'Tambah Serangan'"></span>
                            </button>
                            <p class="mt-2 text-xs text-gray-600">Isi luas serangan tambahan sejak pengamatan terakhir.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10" x-show="showAttack" x-cloak>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Ringan</label>
                                <input type="number" step="0.01" min="0" name="serangan_ringan" x-model="ringan" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Berat</label>
                                <input type="number" step="0.01" min="0" name="serangan_berat" x-model="berat" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 text-sm font-medium transition-colors" placeholder="0.00">
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
                    
                    <div class="bg-blue-50/30 p-6 md:p-8 rounded-xl border border-blue-100 mb-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-100 rounded-bl-full -mr-8 -mt-8 opacity-50 pointer-events-none"></div>
                        <p class="text-sm font-bold text-blue-800 mb-2 relative z-10">Luas Pengendalian per Sumber (Ha)</p>
                        <p class="mb-6 text-xs text-gray-600 relative z-10">Isi luas pengendalian jika ada.</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative z-10">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">APBD Kab/Kota</label>
                                <input type="number" step="0.01" min="0" name="kendali_apbd_kab" x-model="kendaliKab" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">APBD Provinsi</label>
                                <input type="number" step="0.01" min="0" name="kendali_apbd_prov" x-model="kendaliProv" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Swadaya/Masyarakat</label>
                                <input type="number" step="0.01" min="0" name="kendali_masyarakat" x-model="kendaliMasyarakat" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">APBN Pusat</label>
                                <input type="number" step="0.01" min="0" name="kendali_apbn" x-model="kendaliApbn" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                        </div>
                        <p class="mt-5 text-sm font-bold text-blue-800 relative z-10">Jumlah total luas pengendalian: <span x-text="totalKendaliBaru.toFixed(2)"></span> Ha</p>
                    </div>

                    <div class="mb-6 rounded-xl border border-blue-100 bg-white p-6 shadow-sm md:p-8">
                        <label for="cara_pengendalian" class="mb-2 block text-sm font-bold text-gray-700">Cara Pengendalian</label>
                        <textarea id="cara_pengendalian" name="cara_pengendalian" rows="3" maxlength="2000" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('cara_pengendalian', $pengamatan->cara_pengendalian) }}</textarea>
                        <x-input-error :messages="$errors->get('cara_pengendalian')" class="mt-2" />
                    </div>

                    <div class="mb-6 rounded-xl border border-blue-100 bg-white p-6 shadow-sm md:p-8">
                        <label for="cara_pengendalian" class="mb-2 block text-sm font-bold text-gray-700">Cara Pengendalian <span class="font-normal text-gray-500">(opsional)</span></label>
                        <textarea id="cara_pengendalian" name="cara_pengendalian" rows="3" maxlength="2000" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Jelaskan cara pengendalian yang dilakukan">{{ old('cara_pengendalian', $pengamatan->cara_pengendalian) }}</textarea>
                        <x-input-error :messages="$errors->get('cara_pengendalian')" class="mt-2" />
                    </div>

                    <div class="bg-gray-50/50 p-6 md:p-8 rounded-xl border border-gray-100">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Keadaan Serangan</label>
                        <p class="text-2xl font-bold text-emerald-700"><span x-text="kondisiBerjalan.toFixed(2)"></span> Ha</p>
                        <p class="text-xs text-gray-500 mt-2">Sisa serangan setelah pengendalian.</p>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-6 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-4 mt-10">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-6 py-3.5 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        Batal & Kembali
                    </a>
                    <button type="submit" :disabled="loadingHistory || historyError || !historyReady" class="inline-flex items-center justify-center px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Perbarui Data Pengamatan
                    </button>
                </div>
            </form>
        </div>
    </div>
<script>
    function filterJenisOpt() {
        const parent = document.getElementById('opt_id').value;
        const child = document.getElementById('jenis_opt_id');
        const wrapper = document.getElementById('jenis-opt-wrapper');
        const childOptions = [...child.options].filter(option => option.dataset.opt === parent);
        [...child.options].forEach(option => {
            if (option.dataset.opt) option.hidden = option.dataset.opt !== parent;
        });
        if (child.selectedOptions[0]?.dataset.opt !== parent) child.value = '';
        child.required = childOptions.length > 0;
        wrapper.hidden = childOptions.length === 0;
    }
    document.addEventListener('DOMContentLoaded', filterJenisOpt);
</script>
</x-app-layout>
