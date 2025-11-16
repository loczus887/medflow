document.addEventListener('DOMContentLoaded', function() {

    const patientSearchInput = document.getElementById('patient-search');
    
    if (patientSearchInput) {
        let searchTimeout;
        
        patientSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            const query = this.value.trim();
            
            if (query.length < 2) {
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 500);
        });
        
        async function performSearch(query) {
            try {
                window.location.href = `/patients-list?search=${encodeURIComponent(query)}`;
            } catch (error) {
                console.error('Search failed:', error);
            }
        }
    }
    
    const doctorFilterSelect = document.getElementById('doctor-filter');
    
    if (doctorFilterSelect) {
        doctorFilterSelect.addEventListener('change', function() {
            const doctorId = this.value;
            const currentUrl = new URL(window.location.href);
            
            if (doctorId) {
                currentUrl.searchParams.set('doctor_id', doctorId);
            } else {
                currentUrl.searchParams.delete('doctor_id');
            }
            
            window.location.href = currentUrl.toString();
        });
    }

    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const filterButton = document.getElementById('filter-button');
    
    if (startDateInput && endDateInput && filterButton) {
        filterButton.addEventListener('click', function() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            
            if (!startDate || !endDate) {
                UI.showError('Wybierz zakres dat');
                return;
            }
            
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('start', startDate);
            currentUrl.searchParams.set('end', endDate);
            
            window.location.href = currentUrl.toString();
        });
    }
    
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const sortBy = this.dataset.sort;
            const currentUrl = new URL(window.location.href);
            const currentSort = currentUrl.searchParams.get('sort');
            const currentOrder = currentUrl.searchParams.get('order');
            
            let newOrder = 'asc';
            if (currentSort === sortBy && currentOrder === 'asc') {
                newOrder = 'desc';
            }
            
            currentUrl.searchParams.set('sort', sortBy);
            currentUrl.searchParams.set('order', newOrder);
            
            window.location.href = currentUrl.toString();
        });
    });
});