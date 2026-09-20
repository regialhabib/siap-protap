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
                    <p class="text-emerald-100 mt-1.5 text-sm md:text-base">Laporkan data serangan Hama & Penyakit Tanaman dengan akurat.</p>
                </div>
                <div class="relative z-10 inline-flex items-center bg-white/20 px-4 py-2.5 rounded-lg border border-white/30 backdrop-blur-sm">
                    <svg class="w-5 h-5 mr-2.5 text-emerald-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-sm font-semibold text-white tracking-wide">Wilayah: {{ Auth::user()->uppt->nama_uppt ?? 'Belum Ditentukan' }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('pengamatan.update', $pengamatan->id) }}" class="p-6 md:p-10" x-data="{
                ringan: {{ old('serangan_ringan', $pengamatan->serangan_ringan) }},
                sedang: {{ old('serangan_sedang', $pengamatan->serangan_sedang) }},
                berat: {{ old('serangan_berat', $pengamatan->serangan_berat) }},
                get totalSerangan() {
                    let r = parseFloat(this.ringan) || 0;
                    let s = parseFloat(this.sedang) || 0;
                    let b = parseFloat(this.berat) || 0;
                    return (r + s + b).toFixed(2);
                }
            }">
                @csrf
                @method('PUT')

                <!-- SEKSI 1: Informasi Dasar -->
                <div class="mb-12">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-emerald-100 text-emerald-700 w-9 h-9 rounded-lg flex items-center justify-center mr-3.5"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                        1. Informasi Dasar
                    </h3>
                    <div class="flex flex-col gap-6 bg-white p-6 md:p-8 rounded-xl border border-gray-200 shadow-sm">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Pengamatan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_pengamatan" value="{{ old('tanggal_pengamatan', \Carbon\Carbon::parse($pengamatan->tanggal_pengamatan)->format('Y-m-d')) }}" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors" required>
                            <x-input-error :messages="$errors->get('tanggal_pengamatan')" class="mt-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Komoditas <span class="text-red-500">*</span></label>
                            <select name="komoditas_id" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors cursor-pointer" required>
                                <option value="">-- Pilih Komoditas --</option>
                                @foreach($komoditas as $k)
                                    <option value="{{ $k->id }}" {{ old('komoditas_id', $pengamatan->komoditas_id) == $k->id ? 'selected' : '' }}>{{ $k->nama_komoditas }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('komoditas_id')" class="mt-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Jenis OPT <span class="text-red-500">*</span></label>
                            <select name="opt_id" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors cursor-pointer" required>
                                <option value="">-- Pilih OPT --</option>
                                @foreach($opts as $o)
                                    <option value="{{ $o->id }}" {{ old('opt_id', $pengamatan->opt_id) == $o->id ? 'selected' : '' }}>{{ $o->nama_opt }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('opt_id')" class="mt-2" />
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
                            <input type="number" step="0.01" min="0" name="luas_komoditi_ha" value="{{ old('luas_komoditi_ha', $pengamatan->luas_komoditi_ha) }}" class="block w-full pl-5 pr-14 py-3.5 rounded-xl border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-lg font-bold text-gray-900 transition-colors" placeholder="0.00" required>
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                                <span class="text-gray-400 font-bold">Ha</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3 font-medium">Masukkan total luas area tanam komoditas yang dipantau (dalam Hektar).</p>
                        <x-input-error :messages="$errors->get('luas_komoditi_ha')" class="mt-2" />
                    </div>

                    <div class="bg-red-50/30 p-6 md:p-8 rounded-xl border border-red-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-red-100 rounded-bl-full -mr-8 -mt-8 opacity-50 pointer-events-none"></div>
                        <p class="text-sm font-bold text-red-800 mb-6 relative z-10">Luas Serangan (Hektar)</p>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative z-10">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Ringan</label>
                                <input type="number" step="0.01" min="0" name="serangan_ringan" x-model="ringan" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Sedang</label>
                                <input type="number" step="0.01" min="0" name="serangan_sedang" x-model="sedang" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Berat</label>
                                <input type="number" step="0.01" min="0" name="serangan_berat" x-model="berat" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-red-800 mb-2 uppercase tracking-wider">Total / Jumlah</label>
                                <div class="block w-full px-4 py-2.5 rounded-lg border border-red-200 bg-red-100 text-sm font-bold text-red-700 flex justify-between items-center shadow-inner">
                                    <span x-text="totalSerangan" class="text-lg"></span>
                                    <span class="opacity-70">Ha</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEKSI 3: Upaya Pengendalian & Kondisi -->
                <div class="mb-10">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-blue-100 text-blue-700 w-9 h-9 rounded-lg flex items-center justify-center mr-3.5"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg></span>
                        3. Tindakan Pengendalian & Kondisi Serangan
                    </h3>
                    
                    <div class="bg-blue-50/30 p-6 md:p-8 rounded-xl border border-blue-100 mb-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-100 rounded-bl-full -mr-8 -mt-8 opacity-50 pointer-events-none"></div>
                        <p class="text-sm font-bold text-blue-800 mb-6 relative z-10">Luas Pengendalian Berdasarkan Sumber Dana (Hektar)</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative z-10">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">APBD Kab/Kota</label>
                                <input type="number" step="0.01" min="0" name="kendali_apbd_kab" value="{{ old('kendali_apbd_kab', $pengamatan->kendali_apbd_kab) }}" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">APBD Provinsi</label>
                                <input type="number" step="0.01" min="0" name="kendali_apbd_prov" value="{{ old('kendali_apbd_prov', $pengamatan->kendali_apbd_prov) }}" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Swadaya/Masyarakat</label>
                                <input type="number" step="0.01" min="0" name="kendali_masyarakat" value="{{ old('kendali_masyarakat', $pengamatan->kendali_masyarakat) }}" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">APBN Pusat</label>
                                <input type="number" step="0.01" min="0" name="kendali_apbn" value="{{ old('kendali_apbn', $pengamatan->kendali_apbn) }}" class="block w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-colors" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50/50 p-6 md:p-8 rounded-xl border border-gray-100">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Kondisi Serangan</label>
                        <input type="text" name="kondisi_serangan" value="{{ old('kondisi_serangan', $pengamatan->kondisi_serangan) }}" class="block w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-colors" placeholder="Contoh: Terkendali, Aman, Waspada, Eksplosif...">
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-6 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-4 mt-10">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-6 py-3.5 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        Batal & Kembali
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Perbarui Data Pengamatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
