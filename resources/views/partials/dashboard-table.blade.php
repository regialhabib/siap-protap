@php
    $hektare = fn ($value) => \App\Services\PengamatanHistoryService::hektare((float) $value);
    $detail = fn ($item) => [
        'tanggal' => $item->tanggal_pengamatan->translatedFormat('d F Y'),
        'uppt' => $item->uppt->nama_uppt ?? '-',
        'kecamatan' => $item->kecamatan->nama_kecamatan ?? '-',
        'komoditas' => $item->komoditas->nama_komoditas ?? '-',
        'opt' => ($item->opt->nama_opt ?? '-').($item->jenisOpt ? ' — '.$item->jenisOpt->nama_jenis_opt : ''),
        'luas_komoditi' => $hektare($item->luas_komoditi_ha).' Ha',
        'kondisi' => $hektare($item->kondisi_serangan_ha).' Ha',
        'tambah_ringan' => $hektare($item->serangan_ringan),
        'tambah_berat' => $hektare($item->serangan_berat),
        'tambah_jumlah' => $hektare($item->serangan_ringan + $item->serangan_berat),
        'serangan_ringan_num' => $hektare($item->serangan_ringan_berjalan),
        'serangan_berat_num' => $hektare($item->serangan_berat_berjalan),
        'serangan_total_num' => $hektare($item->serangan_jumlah_berjalan),
        'kendali_apbd_prov' => $hektare($item->kendali_apbd_prov),
        'kendali_apbd_kab' => $hektare($item->kendali_apbd_kab),
        'kendali_masyarakat' => $hektare($item->kendali_masyarakat),
        'kendali_apbn' => $hektare($item->kendali_apbn),
        'kendali_jumlah' => $hektare($item->kendali_jumlah),
    ];
    $neutralButton = 'inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50';
    $dangerButton = 'inline-flex items-center justify-center rounded-md border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 shadow-sm transition-colors hover:bg-red-50';
@endphp

{{-- Mobile: daftar kartu --}}
<ul class="divide-y divide-gray-100 md:hidden">
    @forelse($pengamatans as $item)
        <li class="space-y-3 px-4 py-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900">{{ $item->komoditas->nama_komoditas ?? '-' }}</p>
                    <p class="text-sm text-gray-600">{{ $item->opt->nama_opt ?? '-' }}{{ $item->jenisOpt ? ' — '.$item->jenisOpt->nama_jenis_opt : '' }}</p>
                </div>
                <p class="shrink-0 text-sm text-gray-600">{{ $item->tanggal_pengamatan->isoFormat('D MMM Y') }}</p>
            </div>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                <dt class="text-gray-600">Kecamatan</dt><dd class="text-right font-medium text-gray-900">{{ $item->kecamatan->nama_kecamatan ?? '-' }}</dd>
                <dt class="text-gray-600">Luas komoditi</dt><dd class="text-right text-gray-900">{{ $hektare($item->luas_komoditi_ha) }} Ha</dd>
                <dt class="text-gray-600">Luas serangan</dt><dd class="text-right text-gray-900">{{ $hektare($item->serangan_jumlah_berjalan) }} Ha</dd>
                <dt class="text-gray-600">Kondisi</dt><dd class="text-right font-semibold text-gray-900">{{ $hektare($item->kondisi_serangan_ha) }} Ha</dd>
            </dl>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" data-item="{{ json_encode($detail($item)) }}" @click.prevent="modalData = JSON.parse($event.currentTarget.dataset.item); showModal = true" class="{{ $neutralButton }}">Lihat</button>
                @can('update', $item)
                    <a href="{{ route('pengamatan.edit', $item->id) }}" class="{{ $neutralButton }}">Edit</a>
                    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-deletion-{{ $item->id }}')" class="{{ $dangerButton }}">Hapus</button>
                @endcan
            </div>
        </li>
    @empty
        <li class="px-4 py-8 text-center text-base font-medium text-gray-600">Belum ada data pengamatan</li>
    @endforelse
</ul>

{{-- Desktop: tabel --}}
<div class="hidden overflow-x-auto md:block">
    <table class="w-full text-sm text-left">
        <thead class="text-xs font-bold text-gray-600 uppercase bg-gray-50 border-b border-gray-100 tracking-wider">
            <tr>
                <th scope="col" class="whitespace-nowrap px-4 py-3 lg:px-6">Tanggal</th>
                <th scope="col" class="whitespace-nowrap px-4 py-3">Kecamatan</th>
                <th scope="col" class="whitespace-nowrap px-4 py-3">Komoditas / OPT</th>
                <th scope="col" class="whitespace-nowrap px-4 py-3 text-right">Luas Komoditi</th>
                <th scope="col" class="whitespace-nowrap px-4 py-3 text-right">Luas Serangan</th>
                <th scope="col" class="whitespace-nowrap px-4 py-3 text-right">Kondisi</th>
                <th scope="col" class="whitespace-nowrap px-4 py-3 text-center lg:px-6">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-gray-700">
            @forelse($pengamatans as $item)
                <tr class="hover:bg-gray-50/50 transition-colors bg-white">
                    <td class="whitespace-nowrap px-4 py-4 lg:px-6">{{ $item->tanggal_pengamatan->isoFormat('D MMM Y') }}</td>
                    <td class="whitespace-nowrap px-4 py-4 font-medium text-gray-900">{{ $item->kecamatan->nama_kecamatan ?? '-' }}</td>
                    <td class="min-w-[12rem] px-4 py-4">
                        <span class="font-medium text-gray-900">{{ $item->komoditas->nama_komoditas ?? '-' }}</span>
                        <span class="block text-sm text-gray-600">{{ $item->opt->nama_opt ?? '-' }}{{ $item->jenisOpt ? ' — '.$item->jenisOpt->nama_jenis_opt : '' }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-4 text-right">{{ $hektare($item->luas_komoditi_ha) }} Ha</td>
                    <td class="whitespace-nowrap px-4 py-4 text-right">{{ $hektare($item->serangan_jumlah_berjalan) }} Ha</td>
                    <td class="whitespace-nowrap px-4 py-4 text-right font-semibold text-gray-900">{{ $hektare($item->kondisi_serangan_ha) }} Ha</td>
                    <td class="whitespace-nowrap px-4 py-4 lg:px-6">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" data-item="{{ json_encode($detail($item)) }}" @click.prevent="modalData = JSON.parse($event.currentTarget.dataset.item); showModal = true" class="{{ $neutralButton }}">Lihat</button>
                            @can('update', $item)
                                <a href="{{ route('pengamatan.edit', $item->id) }}" class="{{ $neutralButton }}">Edit</a>
                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-deletion-{{ $item->id }}')" class="{{ $dangerButton }}">Hapus</button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 bg-gray-50/50">
                        <svg class="w-10 h-10 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p class="text-base font-medium text-gray-600 mb-1">Belum ada data pengamatan</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@foreach($pengamatans as $item)
    @can('update', $item)
        <x-modal name="confirm-deletion-{{ $item->id }}" maxWidth="md" focusable>
            <form method="post" action="{{ route('pengamatan.destroy', $item->id) }}" class="p-6 text-left whitespace-normal">
                @csrf
                @method('delete')

                <h2 class="text-lg font-bold text-gray-900">
                    Konfirmasi Penghapusan
                </h2>

                <p class="mt-2 text-sm text-gray-600">
                    Apakah Anda yakin ingin menghapus data pengamatan ini? Data yang dihapus tidak dapat dikembalikan.
                </p>

                <div class="mt-6 flex justify-end">
                    <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150 mr-3">
                        Batal
                    </button>

                    <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-red-700 transition ease-in-out duration-150">
                        Ya, Hapus Data
                    </button>
                </div>
            </form>
        </x-modal>
    @endcan
@endforeach

@if($pengamatans->hasPages())
    <div class="p-4 border-t border-gray-100 bg-white">
        {{ $pengamatans->links() }}
    </div>
@endif
