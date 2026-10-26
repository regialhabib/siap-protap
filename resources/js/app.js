import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('pengamatanForm', (config) => ({
    historyUrl: config.historyUrl,
    editId: config.editId ?? null,
    opts: config.opts,
    hasKecamatan: config.hasKecamatan,
    tanggal: config.tanggal,
    kecamatanId: config.kecamatanId,
    komoditasId: config.komoditasId,
    optId: config.optId,
    jenisOptId: config.jenisOptId,
    history: config.summary,
    terakhir: null,
    bulanSama: [],
    luasKomoditi: config.luas,
    areaTouched: Boolean(config.editId || config.luas !== ''),
    manualAreaTouched: Boolean(!config.editId && config.luas !== ''),
    ringan: config.ringan,
    berat: config.berat,
    kendaliKab: config.controls.kab,
    kendaliProv: config.controls.prov,
    kendaliMasyarakat: config.controls.masyarakat,
    kendaliApbn: config.controls.apbn,
    loadingHistory: false,
    historyReady: false,
    historyError: '',
    historyRequest: 0,
    currentKey: '',

    init() {
        this.$nextTick(() => this.loadHistory());
    },

    get jenisOptions() {
        return this.opts.find((opt) => opt.id === this.optId)?.jenis ?? [];
    },

    get lokasiLengkap() {
        return Boolean(this.tanggal && this.komoditasId && this.optId
            && (!this.hasKecamatan || this.kecamatanId)
            && (this.jenisOptions.length === 0 || this.jenisOptId));
    },

    get submitBlocker() {
        if (!this.lokasiLengkap) {
            return 'Lengkapi lokasi & jenis pengamatan terlebih dahulu.';
        }
        if (this.historyError) {
            return 'Data sebelumnya belum berhasil dimuat.';
        }
        if (this.loadingHistory || !this.historyReady) {
            return 'Memuat data sebelumnya...';
        }
        if (this.pengendalianMelebihiBatas) {
            return 'Pengendalian melebihi batas maksimal.';
        }
        if (this.kondisiMelebihiLuas) {
            return 'Sisa serangan melebihi luas komoditi.';
        }

        return '';
    },

    onOptChanged() {
        this.jenisOptId = '';
        this.loadHistory();
    },

    formatHa(value) {
        return `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(this.toNumber(value))} Ha`;
    },

    get totalSeranganBaru() {
        return this.toNumber(this.ringan) + this.toNumber(this.berat);
    },

    get totalSeranganBerjalan() {
        return this.toNumber(this.history.serangan_jumlah) + this.totalSeranganBaru;
    },

    get totalKendaliBaru() {
        return this.toNumber(this.kendaliKab) + this.toNumber(this.kendaliProv)
            + this.toNumber(this.kendaliMasyarakat) + this.toNumber(this.kendaliApbn);
    },

    get batasPengendalian() {
        return this.toNumber(this.history.kondisi_serangan) + this.totalSeranganBaru;
    },

    get pengendalianMelebihiBatas() {
        return this.totalKendaliBaru - this.batasPengendalian > 0.001;
    },

    get kondisiBerjalan() {
        return this.batasPengendalian - this.totalKendaliBaru;
    },

    get kondisiMelebihiLuas() {
        return this.historyReady && this.luasKomoditi !== ''
            && this.kondisiBerjalan - this.toNumber(this.luasKomoditi) > 0.001;
    },

    get statusKondisi() {
        const selisih = Math.round((this.totalSeranganBaru - this.totalKendaliBaru) * 100) / 100;
        if (selisih > 0) {
            return { label: `Bertambah ${this.formatHa(selisih)}`, classes: 'bg-red-50 text-red-700' };
        }
        if (selisih < 0) {
            return { label: `Berkurang ${this.formatHa(-selisih)}`, classes: 'bg-emerald-50 text-emerald-700' };
        }

        return { label: 'Tetap', classes: 'bg-gray-100 text-gray-600' };
    },

    periodeOf(date) {
        const [year, month] = date.split('-').map(Number);

        return month >= 2 ? year : year - 1;
    },

    toNumber(value) {
        return Number.parseFloat(value) || 0;
    },

    async loadHistory() {
        const requestId = ++this.historyRequest;
        const date = this.tanggal;
        const komoditasId = this.komoditasId;
        const optId = this.optId;
        const jenisOptId = this.jenisOptId;
        const kecamatanId = this.hasKecamatan ? this.kecamatanId : '';

        if (!this.lokasiLengkap) {
            this.historyReady = false;
            this.historyError = '';
            this.loadingHistory = false;
            this.terakhir = null;
            this.bulanSama = [];
            return;
        }

        const key = [this.periodeOf(date), kecamatanId, komoditasId, optId, jenisOptId].join(':');
        if (!this.editId && this.currentKey && this.currentKey !== key) {
            this.ringan = '';
            this.berat = '';
            this.kendaliKab = '';
            this.kendaliProv = '';
            this.kendaliMasyarakat = '';
            this.kendaliApbn = '';
            if (!this.manualAreaTouched) {
                this.areaTouched = false;
            }
        }
        this.currentKey = key;

        const params = new URLSearchParams({
            tanggal_pengamatan: date,
            komoditas_id: komoditasId,
            opt_id: optId,
            jenis_opt_id: jenisOptId,
            kecamatan_id: kecamatanId,
        });
        if (this.editId) {
            params.set('pengamatan_id', this.editId);
        }

        this.loadingHistory = true;
        this.historyReady = false;
        this.historyError = '';
        try {
            const response = await fetch(`${this.historyUrl}?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (requestId !== this.historyRequest) {
                return;
            }
            if (!response.ok) {
                throw new Error('History request failed.');
            }

            const payload = await response.json();
            if (requestId !== this.historyRequest) {
                return;
            }
            this.history = payload.summary;
            this.terakhir = payload.terakhir;
            this.bulanSama = payload.bulan_sama;
            this.historyReady = true;
            if (!this.areaTouched) {
                this.luasKomoditi = Number(this.history.luas_komoditi_ha) > 0
                    ? this.history.luas_komoditi_ha
                    : '';
            }
        } catch {
            if (requestId === this.historyRequest) {
                this.historyError = 'Histori belum berhasil dimuat. Periksa koneksi lalu pilih ulang data pengamatan.';
            }
        } finally {
            if (requestId === this.historyRequest) {
                this.loadingHistory = false;
            }
        }
    },
}));

Alpine.data('tablePagination', () => ({
    search: '',
    currentPage: 1,
    perPage: 10,
    rows: [],
    
    init() {
        this.rows = Array.from(this.$el.querySelectorAll('tbody tr.data-row'));
        this.updateVisibility();
        
        this.$watch('search', () => {
            this.currentPage = 1;
            this.updateVisibility();
        });
        
        this.$watch('currentPage', () => {
            this.updateVisibility();
        });
    },
    
    get filteredRows() {
        let searchLower = this.search.toLowerCase();
        return this.rows.filter(row => {
            let cols = row.querySelectorAll('.searchable-col');
            let text = cols.length > 0 
                ? Array.from(cols).map(c => c.innerText).join(' ') 
                : row.innerText;
            return text.toLowerCase().includes(searchLower);
        });
    },

    get totalPages() {
        return Math.ceil(this.filteredRows.length / this.perPage) || 1;
    },
    
    updateVisibility() {
        let filtered = this.filteredRows;
        let start = (this.currentPage - 1) * this.perPage;
        let end = start + this.perPage;
        
        this.rows.forEach(row => row.style.display = 'none');
        filtered.slice(start, end).forEach(row => row.style.display = '');
    },
    
    nextPage() {
        if (this.currentPage < this.totalPages) this.currentPage++;
    },
    
    prevPage() {
        if (this.currentPage > 1) this.currentPage--;
    },

    get showingStart() {
        return this.filteredRows.length === 0 ? 0 : ((this.currentPage - 1) * this.perPage) + 1;
    },

    get showingEnd() {
        return Math.min(this.currentPage * this.perPage, this.filteredRows.length);
    },

    get totalItems() {
        return this.filteredRows.length;
    }
}));

Alpine.start();
