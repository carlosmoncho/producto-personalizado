/**
 * Common Admin JavaScript Functions
 * Shared utilities across all admin pages
 */

class AdminCommon {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.init();
    }

    init() {
        this.setupConfirmations();
        this.setupTooltips();
        this.setupFormValidation();
        this.setupImagePreviews();
        this.setupAutoSave();
    }

    // Confirmation dialogs
    setupConfirmations() {
        document.addEventListener('click', (e) => {
            const element = e.target.closest('[data-confirm]');
            if (element) {
                const message = element.getAttribute('data-confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        });
    }

    // Bootstrap tooltips
    setupTooltips() {
        // Solo inicializar tooltips de Bootstrap, no elementos con title o data-tooltip
        const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltipElements.length && typeof bootstrap !== 'undefined') {
            tooltipElements.forEach(el => {
                // Evitar conflictos con otros sistemas de tooltips
                if (!el.hasAttribute('title') && !el.hasAttribute('data-tooltip')) {
                    new bootstrap.Tooltip(el);
                }
            });
        }

        // Deshabilitar tooltips nativos del browser en elementos problemáticos
        document.querySelectorAll('[data-tooltip]').forEach(el => {
            el.removeAttribute('title');
        });
    }

    // Form validation enhancement
    setupFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    // Image preview functionality
    setupImagePreviews() {
        const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
        imageInputs.forEach(input => {
            input.addEventListener('change', (e) => this.handleImagePreview(e));
        });
    }

    handleImagePreview(event) {
        const input = event.target;

        // Skip inputs that are inside modals or have data-skip-preview attribute
        if (input.closest('.modal') || input.hasAttribute('data-skip-preview')) {
            return;
        }

        const parentContainer = input.closest('.mb-3');
        if (!parentContainer) {
            return; // No container found, skip preview
        }

        let container = parentContainer.querySelector('.image-preview');

        if (!container) {
            // Create preview container if doesn't exist
            container = document.createElement('div');
            container.className = 'image-preview mt-2';
            input.parentNode.appendChild(container);
        }

        const files = input.files;
        container.innerHTML = '';

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'd-inline-block me-2 mb-2';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail" style="max-height: 100px; max-width: 100px;">
                        <div class="text-center mt-1">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Auto-save functionality
    setupAutoSave() {
        const autoSaveForms = document.querySelectorAll('[data-auto-save]');
        autoSaveForms.forEach(form => {
            let timeout;
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => this.autoSave(form), 2000);
                });
            });
        });
    }

    autoSave(form) {
        const formData = new FormData(form);
        const statusIndicator = form.querySelector('.auto-save-status');
        
        if (statusIndicator) {
            statusIndicator.textContent = 'Guardando...';
            statusIndicator.className = 'auto-save-status text-warning';
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (statusIndicator) {
                if (data.success) {
                    statusIndicator.textContent = 'Guardado automáticamente';
                    statusIndicator.className = 'auto-save-status text-success';
                } else {
                    statusIndicator.textContent = 'Error al guardar';
                    statusIndicator.className = 'auto-save-status text-danger';
                }
                
                setTimeout(() => {
                    statusIndicator.textContent = '';
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Auto-save error:', error);
            if (statusIndicator) {
                statusIndicator.textContent = 'Error al guardar';
                statusIndicator.className = 'auto-save-status text-danger';
            }
        });
    }

    // Utility methods
    showLoading(element, text = 'Cargando...') {
        const originalContent = element.innerHTML;
        element.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            ${text}
        `;
        element.disabled = true;
        return originalContent;
    }

    hideLoading(element, originalContent) {
        element.innerHTML = originalContent;
        element.disabled = false;
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // AJAX helper
    async makeRequest(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const config = { ...defaultOptions, ...options };
        if (config.headers['Content-Type'] === 'application/json' && config.body && typeof config.body === 'object') {
            config.body = JSON.stringify(config.body);
        }

        try {
            const response = await fetch(url, config);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('Request failed:', error);
            throw error;
        }
    }

    // Notification system
    showNotification(message, type = 'info', duration = 3000) {
        // Try to use toastr if available
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
            return;
        }

        // Fallback to custom notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
        `;
        notification.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span>${message}</span>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }
}

// Table utilities
class TableUtilities {
    static setupSortable(tableSelector) {
        const table = document.querySelector(tableSelector);
        if (!table) return;

        const headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => this.sortTable(table, header));
        });
    }

    static sortTable(table, header) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const cellIndex = Array.from(header.parentElement.children).indexOf(header);
        const isNumeric = header.dataset.type === 'number';
        const currentOrder = header.dataset.order || 'asc';
        const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

        rows.sort((a, b) => {
            const aVal = a.cells[cellIndex].textContent.trim();
            const bVal = b.cells[cellIndex].textContent.trim();

            let comparison = 0;
            if (isNumeric) {
                comparison = parseFloat(aVal) - parseFloat(bVal);
            } else {
                comparison = aVal.localeCompare(bVal);
            }

            return newOrder === 'desc' ? -comparison : comparison;
        });

        // Update DOM
        rows.forEach(row => tbody.appendChild(row));
        header.dataset.order = newOrder;

        // Update visual indicators
        headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
        header.classList.add(`sort-${newOrder}`);
    }

    static setupSearch(searchInputSelector, tableSelector) {
        const searchInput = document.querySelector(searchInputSelector);
        const table = document.querySelector(tableSelector);
        
        if (!searchInput || !table) return;

        const adminCommon = new AdminCommon();
        const debouncedSearch = adminCommon.debounce((query) => {
            this.filterTable(table, query);
        }, 300);

        searchInput.addEventListener('input', (e) => {
            debouncedSearch(e.target.value);
        });
    }

    static filterTable(table, query) {
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(query.toLowerCase());
            row.style.display = matches ? '' : 'none';
        });
    }
}

// Initialize common functionality
document.addEventListener('DOMContentLoaded', function() {
    new AdminCommon();
});

// Global utilities
window.AdminCommon = AdminCommon;
window.TableUtilities = TableUtilities;

// Export for testing
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { AdminCommon, TableUtilities };
}