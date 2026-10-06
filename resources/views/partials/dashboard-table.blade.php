<div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[11px] font-bold text-gray-400 uppercase bg-gray-50/50 border-b border-gray-100 tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">TANGGAL</th>
                            <th scope="col" class="px-6 py-4">WILAYAH (UPPT)</th>
                            <th scope="col" class="px-6 py-4">KOMODITAS / OPT</th>
                            <th scope="col" class="px-6 py-4 text-center">LUAS KOMODITI</th>
                            <th scope="col" class="px-6 py-4 text-center">LUAS SERANGAN</th>
                            <th scope="col" class="px-6 py-4 text-center">KONDISI</th>
                            <th scope="col" class="px-6 py-4 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse($pengamatans as $item)
                            <tr class="hover:bg-gray-50/50 transition-colors bg-white">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($item->tanggal_pengamatan)->isoFormat('D MMM Y') }}
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    {{ $item->uppt->nama_uppt ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded text-xs font-medium">{{ $item->komoditas->nama_komoditas ?? '-' }}</span>
                                        <span class="bg-red-50 text-red-500 px-2 py-1 rounded text-xs font-medium">{{ $item->opt->nama_opt ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-medium">
                                    {{ number_format($item->luas_komoditi_ha, 2) }} Ha
                                </td>
                                <td class="px-6 py-4 text-center font-medium text-gray-500">
                                    {{ number_format($item->serangan_jumlah, 2) }} Ha
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium lowercase">
                                        {{ $item->kondisi_serangan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button type="button" 
                                            data-item="{{ json_encode([
                                                'id' => $item->id,
                                                'tanggal_raw' => \Carbon\Carbon::parse($item->tanggal_pengamatan)->format('Y-m-d'),
                                                'tanggal' => \Carbon\Carbon::parse($item->tanggal_pengamatan)->isoFormat('D MMMM Y'),
                                                'uppt' => $item->uppt->nama_uppt ?? '-',
                                                'kecamatan' => $item->kecamatan->nama_kecamatan ?? '-',
                                                'komoditas' => $item->komoditas->nama_komoditas ?? '-',
                                                'opt' => $item->opt->nama_opt ?? '-',
                                                'luas_komoditi_num' => number_format($item->luas_komoditi_ha, 2),
                                                'kondisi' => $item->kondisi_serangan,
                                                'serangan_ringan_num' => number_format($item->serangan_ringan, 2),
                                                'serangan_sedang_num' => number_format($item->serangan_sedang, 2),
                                                'serangan_berat_num' => number_format($item->serangan_berat, 2),
                                                'serangan_total_num' => number_format($item->serangan_jumlah, 2),
                                                'kendali_apbd_kab' => number_format($item->kendali_apbd_kab, 2),
                                                'kendali_apbd_prov' => number_format($item->kendali_apbd_prov, 2),
                                                'kendali_apbn' => number_format($item->kendali_apbn, 2),
                                                'kendali_masyarakat' => number_format($item->kendali_masyarakat, 2)
                                            ]) }}"
                                            @click.prevent="modalData = JSON.parse($event.currentTarget.dataset.item); showModal = true" 
                                            title="Lihat" class="bg-blue-500 hover:bg-blue-600 text-white p-1.5 rounded-md transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        
                                        @can('update', $item)
                                        <a href="{{ route('pengamatan.edit', $item->id) }}" title="Edit" class="bg-amber-500 hover:bg-amber-600 text-white p-1.5 rounded-md transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                        
                                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-deletion-{{ $item->id }}')" type="button" title="Hapus" class="bg-red-500 hover:bg-red-600 text-white p-1.5 rounded-md transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>

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
                            
                                                    <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-red-700 transition-colors transition ease-in-out duration-150">
                                                        Ya, Hapus Data
                                                    </button>
                                                </div>
                                            </form>
                                        </x-modal>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 bg-gray-50/50">
                                    <svg class="w-10 h-10 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-base font-medium text-gray-600 mb-1">Belum ada data pengamatan</p>
                                    <p class="text-sm">Anda belum melaporkan data pengamatan bulan ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pengamatans->hasPages())
            <div class="p-4 border-t border-gray-100 bg-white">
                {{ $pengamatans->links() }}
            </div>
            @endif
        </div>
