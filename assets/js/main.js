// Main JavaScript File

console.log('MAIN.JS KELOAD'); 
document.addEventListener('DOMContentLoaded', function() {

    function showSection(sectionId) {
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
        });

        const target = document.getElementById(sectionId);
        if (target) target.classList.add('active');

        const menu = document.querySelector(`.menu-item[data-section="${sectionId}"]`);
        if (menu) menu.classList.add('active');
    }

    document.querySelectorAll('.menu-item[data-section]').forEach(item => {
        item.addEventListener('click', function () {
            const sectionId = this.dataset.section;
            showSection(sectionId);
        });
    });
    // Auto-calculate totals for population forms
    function setupAutoCalculate() {
        // For population forms
        document.querySelectorAll('.population-input').forEach(input => {
            input.addEventListener('input', function() {
                calculateTotals(this);
            });
        });
    }
    
    function calculateTotals(input) {
        const row = input.closest('tr') || input.closest('.form-row');
        if (!row) return;
        
        const jantan = parseFloat(row.querySelector('input[name*="jantan"]')?.value) || 0;
        const betina = parseFloat(row.querySelector('input[name*="betina"]')?.value) || 0;
        const induk = parseFloat(row.querySelector('input[name*="induk"]')?.value) || 0;
        
        const totalInput = row.querySelector('input[name*="total"]');
        if (totalInput) {
            const total = jantan + betina + induk;
            totalInput.value = total;
            
            // Update display if exists
            const totalDisplay = row.querySelector('.total-display');
            if (totalDisplay) {
                totalDisplay.textContent = total;
            }
        }
    }
    
    // Form validation
    function validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
            
            // Validate numeric fields
            if (field.type === 'number' && field.value < 0) {
                field.classList.add('error');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // Attach form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang diperlukan dengan data yang valid.');
            }
        });
    });
    
    // Initialize date fields with today's date
    document.querySelectorAll('input[type="date"]:not([value])').forEach(dateInput => {
        dateInput.valueAsDate = new Date();
    });
    
    // Initialize tooltips
    function initTooltips() {
        const tooltips = document.querySelectorAll('[title]');
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', showTooltip);
            element.addEventListener('mouseleave', hideTooltip);
        });
    }
    
    function showTooltip(e) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = this.getAttribute('title');
        document.body.appendChild(tooltip);
        
        const rect = this.getBoundingClientRect();
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
        tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';
    }
    
    function hideTooltip() {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }
    
    // Initialize
    setupAutoCalculate();
    initTooltips();
    
    // Export data function
window.exportToExcel = function (tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;

    // Pastikan ekstensi .xlsx
    if (!filename) filename = 'export.xlsx';
    if (!filename.toLowerCase().endsWith('.xlsx')) {
        filename += '.xlsx';
    }

    const worksheet = XLSX.utils.table_to_sheet(table, { raw: true });

    // Auto width kolom
    const colWidths = [];
    const rows = table.querySelectorAll('tr');

    rows[0].querySelectorAll('th, td').forEach((cell, i) => {
        let maxLength = cell.innerText.length;
        rows.forEach(row => {
            const c = row.children[i];
            if (c && c.innerText.length > maxLength) {
                maxLength = c.innerText.length;
            }
        });
        colWidths.push({ wch: maxLength + 3 });
    });

    worksheet['!cols'] = colWidths;

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Sheet1');

    XLSX.writeFile(workbook, filename);
};


    // Print function
    window.printTable = function(tableId) {
        const printContent = document.getElementById(tableId).outerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    };
});