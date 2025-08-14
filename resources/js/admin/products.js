/**
 * Product Management JavaScript
 * Handles product creation, editing, and dynamic interactions
 */

class ProductManager {
    constructor() {
        this.pricingIndex = document.querySelectorAll('#pricing-tbody tr, #pricing-rows tr').length || 1;
        this.imagesToRemove = [];
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.init();
    }

    init() {
        this.bindEvents();
        this.initColorPickers();
        this.initSubcategoryFilter();
        this.updateSystemsInfo();
    }

    bindEvents() {
        // Pricing table events
        document.addEventListener('click', (e) => {
            if (e.target.closest('#add-price-row')) {
                this.addPricingRow();
            }
            if (e.target.closest('.remove-price-row')) {
                this.removePricingRow(e.target.closest('tr'));
            }
        });

        // Modal events
        const saveButtons = {
            '#saveColorBtn': () => this.saveColor(),
            '#savePrintColorBtn': () => this.savePrintColor(),
            '#saveSizeBtn': () => this.saveSize(),
        };

        Object.entries(saveButtons).forEach(([selector, handler]) => {
            const element = document.querySelector(selector);
            if (element) element.addEventListener('click', handler);
        });

        // Printing system checkboxes
        const printingCheckboxes = document.querySelectorAll('.printing-system-checkbox');
        printingCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateSystemsInfo());
        });

        // Global functions for onclick handlers
        window.addMaterial = () => this.addMaterial();
        window.addColor = () => this.addColor();
        window.addPrintColor = () => this.addPrintColor();
        window.addSize = () => this.addSize();
        window.addPrintingSystem = () => this.addPrintingSystem();
        window.deletePrintingSystem = (id, name) => this.deletePrintingSystem(id, name);
        window.deleteMaterial = (id, name) => this.deleteMaterial(id, name);
        window.deleteColor = (id, name) => this.deleteColor(id, name);
        window.deletePrintColor = (id, name) => this.deletePrintColor(id, name);
        window.deleteSize = (id, name) => this.deleteSize(id, name);
        window.removeImage = (index) => this.removeImage(index);
        window.addPricingRow = () => this.addPricingRow();
        window.removePricingRow = (button) => this.removePricingRow(button.closest('tr'));
    }

    initColorPickers() {
        const colorPairs = [
            ['#color_picker', '#color_hex'],
            ['#print_color_picker', '#print_color_hex'],
            ['#colorPicker', '#colorHex'],
            ['#printColorPicker', '#printColorHex']
        ];

        colorPairs.forEach(([pickerSelector, inputSelector]) => {
            const picker = document.querySelector(pickerSelector);
            const input = document.querySelector(inputSelector);
            
            if (picker && input) {
                picker.addEventListener('input', () => {
                    input.value = picker.value.toUpperCase();
                });
                
                input.addEventListener('input', () => {
                    if (input.value.match(/^#[0-9A-F]{6}$/i)) {
                        picker.value = input.value;
                    }
                });
            }
        });
    }

    initSubcategoryFilter() {
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');
        
        if (!categorySelect || !subcategorySelect) return;

        // Store original options
        const originalOptions = Array.from(subcategorySelect.options).slice(1); // Skip first "Seleccione" option

        const filterSubcategories = () => {
            const categoryId = categorySelect.value;
            const currentSubcategoryId = subcategorySelect.value;
            
            // Clear subcategories but keep the default option
            const defaultText = subcategorySelect.options[0].text;
            subcategorySelect.innerHTML = `<option value="">${defaultText}</option>`;
            
            if (categoryId) {
                // Method 1: Use window.subcategoriesData if available (for index page)
                if (window.subcategoriesData) {
                    window.subcategoriesData.forEach(subcategory => {
                        if (subcategory.category_id == categoryId) {
                            const option = new Option(subcategory.name, subcategory.id);
                            if (subcategory.id == currentSubcategoryId) {
                                option.selected = true;
                            }
                            subcategorySelect.add(option);
                        }
                    });
                }
                // Method 2: Use data-category attributes (for create/edit pages)
                else if (originalOptions.length > 0) {
                    originalOptions.forEach(option => {
                        if (option.dataset.category == categoryId) {
                            const newOption = new Option(option.text, option.value);
                            if (option.value == currentSubcategoryId) {
                                newOption.selected = true;
                            }
                            subcategorySelect.add(newOption);
                        }
                    });
                }
            } else {
                // Show all subcategories
                if (window.subcategoriesData) {
                    window.subcategoriesData.forEach(subcategory => {
                        const option = new Option(subcategory.name, subcategory.id);
                        if (subcategory.id == currentSubcategoryId) {
                            option.selected = true;
                        }
                        subcategorySelect.add(option);
                    });
                } else {
                    originalOptions.forEach(option => {
                        const newOption = new Option(option.text, option.value);
                        if (option.value == currentSubcategoryId) {
                            newOption.selected = true;
                        }
                        subcategorySelect.add(newOption);
                    });
                }
            }
        };

        categorySelect.addEventListener('change', filterSubcategories);
        
        // Execute on page load to set initial state
        filterSubcategories();
    }

    updateSystemsInfo() {
        const checkboxes = document.querySelectorAll('.printing-system-checkbox');
        const infoDiv = document.getElementById('printing-systems-info');
        const infoText = document.getElementById('systems-info-text');
        
        if (!infoDiv || !infoText) return;

        const selectedSystems = [];
        let maxColors = 0;
        let minUnits = Infinity;
        let avgPrice = 0;
        let selectedCount = 0;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const label = checkbox.nextElementSibling;
                const systemName = label.querySelector('strong')?.textContent || '';
                const colors = parseInt(checkbox.getAttribute('data-colors') || 0);
                const units = parseInt(checkbox.getAttribute('data-min-units') || 0);
                const price = parseFloat(checkbox.getAttribute('data-price') || 0);

                if (systemName) {
                    selectedSystems.push({ name: systemName, colors, units, price });
                    maxColors = Math.max(maxColors, colors);
                    minUnits = Math.min(minUnits, units);
                    avgPrice += price;
                    selectedCount++;
                }
            }
        });

        if (selectedCount > 0) {
            avgPrice = avgPrice / selectedCount;
            
            let html = '<ul class="mb-0">';
            selectedSystems.forEach(system => {
                html += `<li>${system.name} (${system.colors} colores, €${system.price.toFixed(2)}/ud)</li>`;
            });
            html += '</ul><hr class="my-2"><small>';
            html += `• Máximo de colores disponible: ${maxColors}<br>`;
            html += `• Cantidad mínima requerida: ${minUnits} unidades<br>`;
            html += `• Precio promedio: €${avgPrice.toFixed(2)}/unidad</small>`;
            
            infoText.innerHTML = html;
            infoDiv.style.display = 'block';
            
            // Update print colors count
            const printColorsCount = document.getElementById('print_colors_count');
            if (printColorsCount) {
                printColorsCount.value = maxColors;
            }
        } else {
            infoDiv.style.display = 'none';
            const printColorsCount = document.getElementById('print_colors_count');
            if (printColorsCount) {
                printColorsCount.value = 1;
            }
        }
    }

    addPricingRow() {
        const tbody = document.getElementById('pricing-tbody') || document.getElementById('pricing-rows');
        if (!tbody) return;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="number" class="form-control form-control-sm" name="pricing[${this.pricingIndex}][quantity_from]" min="1" required></td>
            <td><input type="number" class="form-control form-control-sm" name="pricing[${this.pricingIndex}][quantity_to]" min="1" required></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="pricing[${this.pricingIndex}][price]" min="0" required></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="pricing[${this.pricingIndex}][unit_price]" min="0" required></td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-price-row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        this.pricingIndex++;
    }

    removePricingRow(row) {
        const tbody = row.parentElement;
        if (tbody.children.length > 1) {
            row.remove();
        } else {
            this.showAlert('Debe mantener al menos un rango de precio');
        }
    }

    async saveColor() {
        const name = document.getElementById('colorName')?.value || document.getElementById('color_name')?.value;
        const hexCode = document.getElementById('colorHex')?.value || document.getElementById('color_hex')?.value;

        if (!name || !hexCode) {
            this.showAlert('Por favor complete todos los campos');
            return;
        }

        try {
            const response = await this.makeRequest('/admin/available-colors', {
                name: name,
                hex_code: hexCode
            });

            if (response.success) {
                this.addColorToContainer('colorsContainer', response.color, 'colors[]');
                this.closeModal('addColorModal');
                this.resetColorForm();
                this.showSuccess(response.message);
            } else {
                this.showAlert(response.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al agregar el color');
        }
    }

    async savePrintColor() {
        const name = document.getElementById('printColorName')?.value || document.getElementById('print_color_name')?.value;
        const hexCode = document.getElementById('printColorHex')?.value || document.getElementById('print_color_hex')?.value;

        if (!name || !hexCode) {
            this.showAlert('Por favor complete todos los campos');
            return;
        }

        try {
            const response = await this.makeRequest('/admin/available-print-colors', {
                name: name,
                hex_code: hexCode
            });

            if (response.success) {
                this.addColorToContainer('printColorsContainer', response.color, 'print_colors[]');
                this.closeModal('addPrintColorModal');
                this.resetPrintColorForm();
                this.showSuccess(response.message);
            } else {
                this.showAlert(response.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al agregar el color de impresión');
        }
    }

    async saveSize() {
        const name = document.getElementById('sizeName')?.value || document.getElementById('size_name')?.value;
        const code = document.getElementById('sizeCode')?.value || document.getElementById('size_code')?.value;
        const description = document.getElementById('sizeDescription')?.value || document.getElementById('size_description')?.value;

        if (!name) {
            this.showAlert('Por favor ingrese el nombre del tamaño');
            return;
        }

        try {
            const response = await this.makeRequest('/admin/available-sizes', {
                name: name,
                code: code,
                description: description
            });

            if (response.success) {
                this.addSizeToContainer(response.size);
                this.closeModal('addSizeModal');
                this.resetSizeForm();
                this.showSuccess(response.message);
            } else {
                this.showAlert(response.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al agregar el tamaño');
        }
    }

    async makeRequest(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });

        return response.json();
    }

    addColorToContainer(containerId, color, inputName) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const deleteFunction = inputName === 'print_colors[]' ? 'deletePrintColor' : 'deleteColor';
        const itemId = inputName === 'print_colors[]' ? `print-color-item-${color.id}` : `color-item-${color.id}`;

        const colorHtml = `
            <div class="col-md-3 mb-2" id="${itemId}">
                <div class="d-flex align-items-center">
                    <div class="form-check flex-grow-1">
                        <input class="form-check-input" type="checkbox" name="${inputName}" 
                               value="${color.name}" id="${inputName.replace('[]', '')}_${color.id}" checked>
                        <label class="form-check-label d-flex align-items-center" for="${inputName.replace('[]', '')}_${color.id}">
                            <span class="badge me-2" style="background-color: ${color.hex_code}; color: ${color.hex_code === '#FFFFFF' ? '#000' : '#FFF'}; width: 20px; height: 20px; display: inline-block;"></span>
                            ${color.name}
                        </label>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 tooltip-trigger no-hover-effect" 
                            onclick="${deleteFunction}(${color.id}, '${color.name}')"
                            data-tooltip="Eliminar color">
                        <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', colorHtml);
    }

    addSizeToContainer(size) {
        const container = document.querySelector('#sizesContainer .row');
        if (!container) return;

        const sizeHtml = `
            <div class="col-md-2 mb-2" id="size-item-${size.id}">
                <div class="d-flex align-items-center">
                    <div class="form-check flex-grow-1">
                        <input class="form-check-input" type="checkbox" name="sizes[]" 
                               value="${size.name}" id="size_${size.id}" checked>
                        <label class="form-check-label" for="size_${size.id}">
                            ${size.name}
                            ${size.code ? `<small class="text-muted">(${size.code})</small>` : ''}
                        </label>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 tooltip-trigger no-hover-effect" 
                            onclick="deleteSize(${size.id}, '${size.name}')"
                            data-tooltip="Eliminar tamaño">
                        <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', sizeHtml);
    }

    closeModal(modalId) {
        const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
        if (modal) modal.hide();
    }

    resetColorForm() {
        const forms = ['addColorForm'];
        const pickers = ['#color_picker', '#colorPicker'];
        const inputs = ['#color_hex', '#colorHex'];
        
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) form.reset();
        });
        
        pickers.forEach(selector => {
            const picker = document.querySelector(selector);
            if (picker) picker.value = '#000000';
        });
        
        inputs.forEach(selector => {
            const input = document.querySelector(selector);
            if (input) input.value = '#000000';
        });
    }

    resetPrintColorForm() {
        const forms = ['addPrintColorForm'];
        const pickers = ['#print_color_picker', '#printColorPicker'];
        const inputs = ['#print_color_hex', '#printColorHex'];
        
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) form.reset();
        });
        
        pickers.forEach(selector => {
            const picker = document.querySelector(selector);
            if (picker) picker.value = '#000000';
        });
        
        inputs.forEach(selector => {
            const input = document.querySelector(selector);
            if (input) input.value = '#000000';
        });
    }

    resetSizeForm() {
        const form = document.getElementById('addSizeForm');
        if (form) form.reset();
    }

    removeImage(index) {
        this.imagesToRemove.push(index);
        const removeImagesInput = document.getElementById('remove_images');
        if (removeImagesInput) {
            removeImagesInput.value = this.imagesToRemove.join(',');
        }
        // Hide image in UI
        const imageElement = event.target.closest('.col-md-3');
        if (imageElement) {
            imageElement.style.display = 'none';
        }
    }

    showAlert(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }

    showSuccess(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            alert(message);
        }
    }

    addMaterialToContainer(material) {
        const container = document.getElementById('materialsContainer')?.querySelector('.row');
        if (!container) return;

        const materialHtml = `
            <div class="col-md-4 mb-2" id="material-item-${material.id}">
                <div class="d-flex align-items-center">
                    <div class="form-check flex-grow-1">
                        <input class="form-check-input" type="checkbox" name="materials[]" 
                               value="${material.name}" id="material_${material.id}" checked>
                        <label class="form-check-label" for="material_${material.id}">
                            ${material.name}
                        </label>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 tooltip-trigger no-hover-effect" 
                            onclick="deleteMaterial(${material.id}, '${material.name}')"
                            data-tooltip="Eliminar material">
                        <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', materialHtml);
    }

    addPrintingSystemToContainer(system) {
        const container = document.getElementById('printingSystemsContainer');
        if (!container) return;

        const systemHtml = `
            <div class="col-12 mb-2" id="printing-system-item-${system.id}">
                <div class="d-flex align-items-start">
                    <div class="form-check flex-grow-1">
                        <input class="form-check-input printing-system-checkbox" 
                               type="checkbox" 
                               name="printing_systems[]" 
                               value="${system.id}" 
                               id="printing_system_${system.id}"
                               data-colors="${system.total_colors}"
                               data-min-units="${system.min_units}"
                               data-price="${system.price_per_unit}"
                               checked>
                        <label class="form-check-label" for="printing_system_${system.id}">
                            <strong>${system.name}</strong>
                            <small class="text-muted d-block">
                                ${system.total_colors} colores, mín. ${system.min_units} uds, €${parseFloat(system.price_per_unit).toFixed(2)}/ud
                            </small>
                        </label>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 tooltip-trigger no-hover-effect" 
                            onclick="deletePrintingSystem(${system.id}, '${system.name}')"
                            data-tooltip="Eliminar sistema">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', systemHtml);

        // Add event listener to the new checkbox
        const newCheckbox = container.querySelector(`#printing_system_${system.id}`);
        if (newCheckbox) {
            newCheckbox.addEventListener('change', () => this.updateSystemsInfo());
        }
    }

    resetMaterialForm() {
        const form = document.getElementById('addMaterialForm');
        if (form) form.reset();
    }

    resetPrintingSystemForm() {
        const form = document.getElementById('addPrintingSystemForm');
        if (form) form.reset();
        
        // Reset to default values
        const totalColors = document.getElementById('system_total_colors');
        const minUnits = document.getElementById('system_min_units');
        const pricePerUnit = document.getElementById('system_price_per_unit');
        
        if (totalColors) totalColors.value = '1';
        if (minUnits) minUnits.value = '1';
        if (pricePerUnit) pricePerUnit.value = '0';
    }

    async addMaterial() {
        const name = document.getElementById('material_name')?.value;
        const description = document.getElementById('material_description')?.value;

        if (!name) {
            this.showAlert('Por favor ingrese el nombre del material');
            return;
        }

        try {
            const response = await this.makeRequest('/admin/available-materials', {
                name: name,
                description: description
            });

            if (response.success) {
                this.addMaterialToContainer(response.material);
                this.closeModal('addMaterialModal');
                this.resetMaterialForm();
                this.showSuccess(response.message);
            } else {
                this.showAlert(response.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al agregar el material');
        }
    }

    async addPrintingSystem() {
        const name = document.getElementById('system_name')?.value;
        const totalColors = document.getElementById('system_total_colors')?.value;
        const minUnits = document.getElementById('system_min_units')?.value;
        const pricePerUnit = document.getElementById('system_price_per_unit')?.value;
        const description = document.getElementById('system_description')?.value;

        if (!name || !totalColors || !minUnits || !pricePerUnit) {
            this.showAlert('Por favor complete todos los campos obligatorios');
            return;
        }

        try {
            const response = await this.makeRequest('/admin/printing-systems', {
                name: name,
                total_colors: totalColors,
                min_units: minUnits,
                price_per_unit: pricePerUnit,
                description: description,
                active: true
            });

            if (response.success) {
                this.addPrintingSystemToContainer(response.printingSystem);
                this.closeModal('addPrintingSystemModal');
                this.resetPrintingSystemForm();
                this.showSuccess(response.message);
                this.updateSystemsInfo();
            } else {
                this.showAlert(response.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al agregar el sistema de impresión');
        }
    }

    async deletePrintingSystem(id, name) {
        const result = await Swal.fire({
            title: '¿Eliminar Sistema de Impresión?',
            html: `¿Está seguro de eliminar el sistema <strong>"${name}"</strong>?<br><br><small class="text-muted">Esta acción no se puede deshacer.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Eliminar',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
            reverseButtons: true,
            focusCancel: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                container: 'sweet-alert-container'
            }
        });

        if (!result.isConfirmed) {
            return;
        }

        try {
            const response = await fetch(`/admin/printing-systems/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                const element = document.getElementById(`printing-system-item-${id}`);
                if (element) {
                    element.remove();
                }
                this.updateSystemsInfo();
                
                this.showSuccess(data.message || 'Sistema eliminado exitosamente');
            } else {
                this.showAlert(data.message || 'Error al eliminar el sistema');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al eliminar el sistema de impresión');
        }
    }

    async deleteMaterial(id, name) {
        const result = await Swal.fire({
            title: '¿Eliminar Material?',
            html: `¿Está seguro de eliminar el material <strong>"${name}"</strong>?<br><br><small class="text-muted">Esta acción no se puede deshacer y podría afectar productos existentes.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Eliminar',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
            reverseButtons: true,
            focusCancel: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                container: 'sweet-alert-container'
            }
        });

        if (!result.isConfirmed) {
            return;
        }

        try {
            const response = await fetch(`/admin/available-materials/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                const element = document.getElementById(`material-item-${id}`);
                if (element) {
                    element.remove();
                }
                
                this.showSuccess(data.message || 'Material eliminado exitosamente');
            } else {
                this.showAlert(data.message || 'Error al eliminar el material');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al eliminar el material');
        }
    }

    async deleteColor(id, name) {
        const result = await Swal.fire({
            title: '¿Eliminar Color?',
            html: `¿Está seguro de eliminar el color <strong>"${name}"</strong>?<br><br><small class="text-muted">Esta acción no se puede deshacer y podría afectar productos existentes.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Eliminar',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
            reverseButtons: true,
            focusCancel: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                container: 'sweet-alert-container'
            }
        });

        if (!result.isConfirmed) {
            return;
        }

        try {
            const response = await fetch(`/admin/available-colors/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                const element = document.getElementById(`color-item-${id}`);
                if (element) {
                    element.remove();
                }
                
                this.showSuccess(data.message || 'Color eliminado exitosamente');
            } else {
                this.showAlert(data.message || 'Error al eliminar el color');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al eliminar el color');
        }
    }

    async deletePrintColor(id, name) {
        const result = await Swal.fire({
            title: '¿Eliminar Color de Impresión?',
            html: `¿Está seguro de eliminar el color de impresión <strong>"${name}"</strong>?<br><br><small class="text-muted">Esta acción no se puede deshacer y podría afectar productos existentes.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Eliminar',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
            reverseButtons: true,
            focusCancel: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                container: 'sweet-alert-container'
            }
        });

        if (!result.isConfirmed) {
            return;
        }

        try {
            const response = await fetch(`/admin/available-print-colors/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                const element = document.getElementById(`print-color-item-${id}`);
                if (element) {
                    element.remove();
                }
                
                this.showSuccess(data.message || 'Color de impresión eliminado exitosamente');
            } else {
                this.showAlert(data.message || 'Error al eliminar el color de impresión');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al eliminar el color de impresión');
        }
    }

    async deleteSize(id, name) {
        const result = await Swal.fire({
            title: '¿Eliminar Tamaño?',
            html: `¿Está seguro de eliminar el tamaño <strong>"${name}"</strong>?<br><br><small class="text-muted">Esta acción no se puede deshacer y podría afectar productos existentes.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Eliminar',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
            reverseButtons: true,
            focusCancel: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                container: 'sweet-alert-container'
            }
        });

        if (!result.isConfirmed) {
            return;
        }

        try {
            const response = await fetch(`/admin/available-sizes/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                const element = document.getElementById(`size-item-${id}`);
                if (element) {
                    element.remove();
                }
                
                this.showSuccess(data.message || 'Tamaño eliminado exitosamente');
            } else {
                this.showAlert(data.message || 'Error al eliminar el tamaño');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al eliminar el tamaño');
        }
    }

    // Legacy methods for backward compatibility
    addColor() { this.saveColor(); }
    addPrintColor() { this.savePrintColor(); }
    addSize() { this.saveSize(); }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new ProductManager();
});

// Export for testing
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProductManager;
}