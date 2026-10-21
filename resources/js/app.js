import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('pengamatanForm', (historyUrl, initialSummary, initialArea, editId = null, showInitialAttack = false, initialRingan = 0, initialBerat = 0, initialControls = {}) => ({
    historyUrl,
    history: initialSummary,
    luasKomoditi: initialArea,
    areaTouched: Boolean(editId || initialArea !== ''),
    manualAreaTouched: Boolean(!editId && initialArea !== ''),
    editId,
    showAttack: showInitialAttack,
    ringan: initialRingan,
    berat: initialBerat,
    kendaliKab: initialControls.kab || 0,
    kendaliProv: initialControls.prov || 0,
    kendaliMasyarakat: initialControls.masyarakat || 0,
    kendaliApbn: initialControls.apbn || 0,
    loadingHistory: false,
    historyReady: false,
    historyError: '',
    historyRequest: 0,
    currentKey: '',

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

    periodeOf(date) {
        const [year, month] = date.split('-').map(Number);

        return month >= 2 ? year : year - 1;
    },

    toNumber(value) {
        return Number.parseFloat(value) || 0;
    },

    async loadHistory() {
        const requestId = ++this.historyRequest;
        const date = this.$refs.tanggal.value;
        const komoditasId = this.$refs.komoditas.value;
        const optId = this.$refs.opt.value;
        const jenisOptId = this.$refs.jenisOpt.value;
        const kecamatanId = this.$refs.kecamatan?.value || '';

        if (!date || !komoditasId || !optId || (this.$refs.jenisOpt.required && !jenisOptId)) {
            this.historyReady = false;
            this.historyError = '';
            this.loadingHistory = false;
            return;
        }

        const key = [this.periodeOf(date), kecamatanId, komoditasId, optId, jenisOptId].join(':');
        if (!this.editId && this.currentKey && this.currentKey !== key) {
            this.ringan = 0;
            this.berat = 0;
            this.kendaliKab = 0;
            this.kendaliProv = 0;
            this.kendaliMasyarakat = 0;
            this.kendaliApbn = 0;
            this.showAttack = false;
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
