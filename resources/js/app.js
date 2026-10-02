import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

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
