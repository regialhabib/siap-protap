<x-app-layout>
    @php
        $periode = \App\Services\PeriodePengamatan::forDate(\Carbon\Carbon::create($tahun, $bulan, 1));
        $hektare = fn ($value) => \App\Services\PengamatanHistoryService::hektare((float) $value);
    @endphp

    <!-- Filter Form -->
    <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm mt-6 overflow-visible">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 rounded-t-xl">
            <h2 class="font-bold text-xl text-gray-800 leading-tight tracking-tight">
                Filter Laporan Bulanan
            </h2>
            <p class="mt-1 text-sm text-gray-500">Luas serangan dan pengendalian bulan ini saja; kondisi serangan adalah posisi akhir bulan pada periode {{ $periode }} (Februari–Januari).</p>
        </div>
        <div class="p-6">
            <form id="filterForm" method="GET" action="{{ route('laporan.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4" autocomplete="off"
                  x-data="{
                      uppt_id: @js((string) $uppt_id),
                      openUpptOptions: false,
                      upptSearch: '',
                      upptOptions: @js($uppts->map(fn ($uppt) => ['id' => (string) $uppt->id, 'name' => $uppt->nama_uppt])->values()),
                      kecamatans: {{ Js::from($kecamatans) }},
                      kecamatan_id: @js((string) $kecamatan_id),
                      get filteredUpptOptions() {
                          return this.upptOptions.filter(option => option.name.toLowerCase().includes(this.upptSearch.toLowerCase()));
                      },
                      selectUppt(option) {
                          this.uppt_id = option.id;
                          this.upptSearch = option.name;
                          this.openUpptOptions = false;
                          this.kecamatan_id = '';
                          this.fetchKecamatans();
                      },
                      fetchKecamatans() {
                          if (!this.uppt_id) {
                              this.kecamatans = [];
                              this.kecamatan_id = '';
                              return;
                          }
                          fetch(`/api/uppt/${this.uppt_id}/kecamatans`)
                              .then(res => res.json())
                              .then(data => {
                                  this.kecamatans = data;
                                  if (!data.find(k => k.id == this.kecamatan_id)) {
                                      this.kecamatan_id = '';
                                  }
                              });
                      }
                  }"
                  x-init="upptSearch = upptOptions.find(option => option.id === uppt_id)?.name ?? ''"
                  @submit="if (!uppt_id) { $event.preventDefault(); $refs.upptSearch.setCustomValidity('Pilih wilayah UPPT dari daftar.'); $refs.upptSearch.reportValidity(); }">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Bulan</label>
                <select name="bulan" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tahun</label>
                <select name="tahun" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    @foreach(range(date('Y') + 1, date('Y') - 5) as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter UPPT -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Wilayah / UPPT</label>
                <div class="relative" @click.outside="openUpptOptions = false">
                    <input id="report-uppt-search" x-ref="upptSearch" type="text" x-model="upptSearch"
                        @focus="openUpptOptions = true"
                        @input="uppt_id = ''; kecamatans = []; kecamatan_id = ''; $refs.upptSearch.setCustomValidity('')"
                        @keydown.escape="openUpptOptions = false"
                        role="combobox" aria-autocomplete="list" aria-controls="report-uppt-options"
                        :aria-expanded="openUpptOptions.toString()" required autocomplete="off"
                        placeholder="Cari atau pilih wilayah UPPT"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <input type="hidden" name="uppt_id" :value="uppt_id">

                    <div id="report-uppt-options" x-cloak x-show="openUpptOptions" role="listbox"
                        class="absolute z-30 mt-1 max-h-56 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                        <template x-for="option in filteredUpptOptions" :key="option.id">
                            <button type="button" role="option" :aria-selected="uppt_id === option.id"
                                @click="selectUppt(option)"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 focus:bg-emerald-50 focus:outline-none"
                                x-text="option.name"></button>
                        </template>
                        <p x-show="filteredUpptOptions.length === 0" class="px-4 py-2 text-sm text-gray-500">
                            Wilayah UPPT tidak ditemukan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filter Kecamatan (Dynamic) -->
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kecamatan</label>
                <select name="kecamatan_id" x-model="kecamatan_id" :disabled="kecamatans.length === 0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500">
                    <option value="" x-text="kecamatans.length > 0 ? '-- Semua Kecamatan --' : 'Pilih UPPT terlebih dahulu'"></option>
                    <template x-for="kec in kecamatans" :key="kec.id">
                        <option :value="kec.id" x-text="kec.nama_kecamatan"></option>
                    </template>
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-gray-900">
                    Laporan Bulanan {{ \Carbon\Carbon::create()->month((int) $bulan)->translatedFormat('F') }} {{ $tahun }}
                </h3>
                <p class="text-sm text-gray-500 mt-1">Ditemukan {{ $data->total() }} data pengamatan.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                @if($uppt_id)
                    <a href="{{ route('laporan.export.pdf', request()->query()) }}" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-bold rounded-lg shadow-sm transition-colors">
                        Cetak PDF
                    </a>
                    <a href="{{ route('laporan.export.excel', request()->query()) }}" class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-bold rounded-lg shadow-sm transition-colors shadow-emerald-500/20">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Unduh Excel
                    </a>
                @else
                    <button type="button" disabled title="Pilih UPPT dan tampilkan laporan terlebih dahulu" class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 text-gray-400 text-sm font-bold rounded-lg cursor-not-allowed">Cetak PDF</button>
                    <button type="button" disabled title="Pilih UPPT dan tampilkan laporan terlebih dahulu" class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 text-gray-400 text-sm font-bold rounded-lg cursor-not-allowed">Unduh Excel</button>
                @endif
            </div>
        </div>

        <x-keterangan-singkatan />
        <div id="table-container" class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100/50 border-b border-gray-200">
                    <tr>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">No</th>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">Wilayah Pengamatan / Kecamatan</th>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">Komoditas</th>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">OPT</th>
                        <th rowspan="2" class="px-4 py-3 border-r border-gray-200">Luas (Ha)</th>
                        <th colspan="3" class="px-4 py-2 border-r border-gray-200 text-center border-b">Luas Serangan Bulan Ini (Ha)</th>
                        <th colspan="5" class="px-4 py-2 border-r border-gray-200 text-center border-b">Luas Pengendalian Bulan Ini (Ha)</th>
                        <th rowspan="2" class="px-4 py-3">Kondisi Serangan</th>
                    </tr>
                    <tr>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-yellow-50/50">R</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-red-50/50">B</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center font-bold">JML</th>

                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-blue-50/50">APBD I</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-blue-50/50">APBD II</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-blue-50/50">Masy</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center bg-blue-50/50">APBN</th>
                        <th class="px-3 py-2 border-r border-gray-200 text-center font-bold">JML</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $d)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 border-r border-gray-100 text-center">{{ $data->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 border-r border-gray-100 font-medium text-gray-900">
                                {{ $d->uppt->nama_uppt ?? '-' }}{{ $d->kecamatan ? ' / ' . $d->kecamatan->nama_kecamatan : '' }}
                                @unless($d->ada_input)
                                    <span class="mt-1 block text-xs font-normal text-amber-700">Tidak ada input bulan ini</span>
                                @endunless
                            </td>
                            <td class="px-4 py-3 border-r border-gray-100">{{ $d->komoditas->nama_komoditas ?? '-' }}</td>
                            <td class="px-4 py-3 border-r border-gray-100">{{ $d->opt->nama_opt ?? '-' }}{{ $d->jenisOpt ? ' — '.$d->jenisOpt->nama_jenis_opt : '' }}</td>
                            <td class="px-4 py-3 border-r border-gray-100 font-bold text-center">{{ $hektare($d->luas_komoditi_ha) }}</td>

                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ $hektare($d->serangan_ringan) }}</td>
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ $hektare($d->serangan_berat) }}</td>
                            <td class="px-3 py-3 border-r border-gray-200 font-bold text-center bg-gray-50">{{ $hektare($d->serangan_jumlah) }}</td>

                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ $hektare($d->kendali_apbd_prov) }}</td>
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ $hektare($d->kendali_apbd_kab) }}</td>
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ $hektare($d->kendali_masyarakat) }}</td>
                            <td class="px-3 py-3 border-r border-gray-100 text-center">{{ $hektare($d->kendali_apbn) }}</td>
                            <td class="px-3 py-3 border-r border-gray-200 font-bold text-center bg-blue-50/50">{{ $hektare($d->kendali_jumlah) }}</td>

                            <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $d->kondisi_label }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data pengamatan yang ditemukan pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">{{ $data->links() }}</div>
        </div>
    </div>
</x-app-layout>
