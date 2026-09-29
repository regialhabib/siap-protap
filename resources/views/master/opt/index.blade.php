<x-app-layout>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms class="mb-6 flex items-center justify-between bg-emerald-100 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg shadow-sm font-medium" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="show = false" type="button" class="text-emerald-600 hover:text-emerald-800 focus:outline-none ml-4 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

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

    <!-- Main Card Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ 
        search: '{{ request('search') }}',
        loading: false,
        updateTable() {
            this.loading = true;
            let url = new URL(window.location.href);
            if(this.search) {
                url.searchParams.set('search', this.search);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page'); 
            fetch(url, { headers: {'X-Requested-With': 'XMLHttpRequest'} })
                .then(res => res.text())
                .then(html => {
                    let doc = new DOMParser().parseFromString(html, 'text/html');
                    document.getElementById('table-container').innerHTML = doc.getElementById('table-container').innerHTML;
                    this.loading = false;
                    window.history.pushState({}, '', url);
                });
        }
    }">
        
        <!-- Header inside Card -->
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:justify-between md:items-center gap-4 bg-white">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Data OPT</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola jenis hama dan penyakit (Organisme Pengganggu Tumbuhan).</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="relative hidden">
                    <input type="text" x-model.debounce.500ms="search" @input="updateTable()" placeholder="Cari OPT..." class="block w-64 pl-10 pr-10 py-2 border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <!-- Tombol Hapus / Clear -->
                    <button type="button" x-cloak x-show="search.length > 0 && !loading" @click="search = ''; updateTable()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <!-- Spinner Loading -->
                    <div x-show="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center" style="display: none;">
                        <svg class="animate-spin h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>
                <!-- Tombol Tambah yang memicu Modal -->
                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'tambah-opt')" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-lg shadow-sm transition duration-150 whitespace-nowrap">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah OPT
                </button>
            </div>
        </div>
        
        <div id="table-container" @click="if($event.target.closest('.pagination a') || $event.target.closest('nav[role=navigation] a')) { 
            $event.preventDefault(); 
            let link = $event.target.closest('a').href;
            fetch(link, { headers: {'X-Requested-With': 'XMLHttpRequest'} })
                .then(res => res.text())
                .then(html => {
                    let doc = new DOMParser().parseFromString(html, 'text/html');
                    document.getElementById('table-container').innerHTML = doc.getElementById('table-container').innerHTML;
                    window.history.pushState({}, '', link);
                });
        }">
            <div class="overflow-x-auto px-6 pt-2 pb-8">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-y border-gray-200">
                        <tr>
                            <th class="px-6 py-4 font-bold tracking-wider w-16">No</th>
                            <th class="px-6 py-4 font-bold tracking-wider">Nama OPT (Hama/Penyakit)</th>
                            <th class="px-6 py-4 font-bold tracking-wider text-right w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($opts as $index => $k)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-gray-500">
                                {{ $opts->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-900">{{ $k->nama_opt }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <!-- Edit Button (Triggers Modal) -->
                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-opt-{{ $k->id }}')" class="inline-flex items-center text-white font-medium text-xs px-3 py-1.5 bg-blue-500 hover:bg-blue-600 rounded shadow-sm transition mr-2">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    Edit
                                </button>
    
                                <!-- Modal Edit OPT -->
                                <x-modal name="edit-opt-{{ $k->id }}" focusable>
                                    <form method="post" action="{{ route('opt.update', $k->id) }}" class="p-6 text-left whitespace-normal">
                                        @csrf
                                        @method('put')
    
                                        <h2 class="text-lg font-bold text-gray-900">
                                            Edit OPT
                                        </h2>
    
                                        <div class="mt-6">
                                            <label for="nama_opt_{{ $k->id }}" class="block text-sm font-semibold text-gray-700 mb-1">Nama OPT</label>
                                            <input id="nama_opt_{{ $k->id }}" name="nama_opt" type="text" value="{{ $k->nama_opt }}" class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                        </div>
    
                                        <div class="mt-6 flex justify-end">
                                            <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150 mr-3">
                                                Batal
                                            </button>
    
                                            <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-sm transition ease-in-out duration-150">
                                                Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </x-modal>
                                
                                <!-- Delete Button (Triggers Modal) -->
                                <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'hapus-opt-{{ $k->id }}')" class="inline-flex items-center text-white font-medium text-xs px-3 py-1.5 bg-red-500 hover:bg-red-600 rounded shadow-sm transition">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </button>
    
                                <!-- Modal Konfirmasi Hapus -->
                                <x-modal maxWidth="md" name="hapus-opt-{{ $k->id }}" focusable>
                                    <form method="post" action="{{ route('opt.destroy', $k->id) }}" class="p-6 text-left whitespace-normal">
                                        @csrf
                                        @method('delete')
    
                                        <h2 class="text-lg font-bold text-gray-900">
                                            Konfirmasi Penghapusan
                                        </h2>
    
                                        <p class="mt-2 text-sm text-gray-600">
                                            Apakah Anda yakin ingin menghapus OPT <strong>{{ $k->nama_opt }}</strong>? Data yang dihapus tidak dapat dikembalikan.
                                        </p>
    
                                        <div class="mt-6 flex justify-end">
                                            <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150 mr-3">
                                                Batal
                                            </button>
    
                                            <button type="submit" class="px-4 py-2.5 bg-red-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-red-700 transition-colors transition ease-in-out duration-150">
                                                Ya, Hapus Data
                                            </button>
                                        </div>
                                    </form>
                                </x-modal>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900">Belum ada OPT</h3>
                                <p class="text-xs text-gray-500 mt-1">Silakan tambah data OPT baru.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($opts->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $opts->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Tambah OPT -->
    <x-modal name="tambah-opt" focusable>
        <form method="post" action="{{ route('opt.store') }}" class="p-6 text-left whitespace-normal">
            @csrf

            <h2 class="text-lg font-bold text-gray-900">
                Tambah OPT Baru
            </h2>

            <p class="mt-1 text-sm text-gray-600 mb-6">
                Masukkan nama jenis hama atau penyakit tanaman.
            </p>

            <div class="mt-6">
                <label for="nama_opt" class="block text-sm font-semibold text-gray-700 mb-1">Nama OPT</label>
                <input id="nama_opt" name="nama_opt" type="text" class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" placeholder="Contoh: Tikus / Wereng" required>
                <x-input-error :messages="$errors->get('nama_opt')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                    Batal
                </button>

                <button type="submit" class="px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Simpan Data
                </button>
            </div>
        </form>
    </x-modal>

</x-app-layout>
