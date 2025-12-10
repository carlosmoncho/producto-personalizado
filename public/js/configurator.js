/**
 * Product Configurator JavaScript
 * Handles dynamic product configuration with dependency management
 */
class ProductConfigurator {
    constructor() {
        this.config = window.configuratorData;
        this.currentStep = this.config.currentStep;
        this.currentSelection = { ...this.config.currentSelection };
        this.currentPersonalization = { ...this.config.currentPersonalization };
        this.selectedInks = [...this.config.selectedInks];
        this.requiredInkCount = this.config.requiredInkCount;
        this.isLoading = false;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateProgress();
        this.updatePriceDisplay();
    }

    bindEvents() {
        // Selección de atributos de color
        document.querySelectorAll('#step-color .attribute-card').forEach(card => {
            card.addEventListener('click', (e) => this.handleAttributeSelection(e, 'color'));
        });

        // Eventos dinámicos para otros pasos (se agregan cuando se cargan)
        document.addEventListener('click', (e) => {
            if (e.target.closest('.attribute-card')) {
                const step = e.target.closest('.configurator-step').dataset.step;
                if (['material', 'size', 'quantity'].includes(step)) {
                    this.handleAttributeSelection(e, step);
                }
            }
        });

        // Selector de número de colores
        document.querySelectorAll('input[name="colorCount"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.handleColorCountChange(e));
        });

        // Eventos de tintas (dinámicos)
        document.addEventListener('click', (e) => {
            if (e.target.closest('.ink-card')) {
                this.handleInkSelection(e);
            }
        });

        // Botón finalizar
        document.getElementById('finalizeOrderBtn').addEventListener('click', () => {
            this.finalizeConfiguration();
        });

        // Botón agregar al carrito
        document.getElementById('addToCartBtn').addEventListener('click', () => {
            this.addToCart();
        });
    }

    async handleAttributeSelection(event, attributeType) {
        const card = event.target.closest('.attribute-card');
        if (card.classList.contains('disabled')) return;

        const attributeId = parseInt(card.dataset.attributeId);
        
        // Marcar como seleccionado
        this.selectAttributeCard(card, attributeType);
        
        // Actualizar selección
        this.currentSelection[attributeType] = attributeId;
        
        // Actualizar configuración en backend
        await this.updateConfiguration(attributeType, attributeId, 'attributes_base');
        
        // Avanzar al siguiente paso
        await this.proceedToNextStep(attributeType);
    }

    selectAttributeCard(card, attributeType) {
        // Deseleccionar otros en el mismo paso
        const step = card.closest('.configurator-step');
        step.querySelectorAll('.attribute-card').forEach(c => c.classList.remove('selected'));
        
        // Seleccionar el actual
        card.classList.add('selected');
        
        // Marcar paso como completado
        step.classList.add('completed');
    }

    async proceedToNextStep(currentStepType) {
        const stepOrder = ['color', 'material', 'size', 'quantity', 'personalization', 'summary'];
        const currentIndex = stepOrder.indexOf(currentStepType);
        const nextStepType = stepOrder[currentIndex + 1];
        
        if (!nextStepType) return;
        
        // Habilitar siguiente paso
        const nextStep = document.getElementById(`step-${nextStepType}`);
        nextStep.classList.remove('disabled');
        
        // Cargar contenido del siguiente paso
        await this.loadStepContent(nextStepType);
        
        // Activar siguiente paso
        this.activateStep(nextStepType);
        
        // Actualizar progreso
        this.updateProgress();
        
        // Mostrar precio si hay selección suficiente
        if (['material', 'size', 'quantity'].includes(currentStepType)) {
            this.updatePriceDisplay();
        }
    }

    async loadStepContent(stepType) {
        if (stepType === 'summary') {
            await this.loadSummary();
            return;
        }

        const endpoint = this.config.apiEndpoints.attributes;
        
        try {
            this.showLoading();
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify({
                    type: stepType,
                    selection: this.currentSelection
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.renderStepContent(stepType, data.attributes);
                
                // Manejar auto-selecciones
                if (data.auto_select && data.auto_select.length > 0) {
                    await this.handleAutoSelections(data.auto_select);
                }
            }
        } catch (error) {
            console.error('Error loading step content:', error);
            this.showMessage('Error al cargar opciones', 'error');
        } finally {
            this.hideLoading();
        }
    }

    renderStepContent(stepType, attributes) {
        const container = document.getElementById(`${stepType}sGrid`);
        if (!container) return;

        if (stepType === 'quantity') {
            this.renderQuantityOptions(container, attributes);
        } else {
            this.renderAttributeGrid(container, attributes, stepType);
        }
    }

    renderAttributeGrid(container, attributes, stepType) {
        container.innerHTML = '';
        
        attributes.forEach(attribute => {
            const card = document.createElement('div');
            card.className = 'attribute-card';
            if (!attribute.is_compatible) {
                card.classList.add('disabled');
            }
            if (attribute.is_recommended) {
                card.classList.add('recommended');
            }
            
            card.dataset.attributeId = attribute.id;
            card.dataset.attributeType = stepType;
            
            let content = `<h6 class="mb-1">${attribute.name}</h6>`;
            content += `<small class="text-muted">${attribute.value}</small>`;
            
            if (attribute.price_modifier !== 0 && attribute.price_modifier != null) {
                const priceValue = parseFloat(attribute.price_modifier) || 0;
                const sign = priceValue > 0 ? '+' : '';
                content += `<div class="badge bg-info mt-2">${sign}€${priceValue.toFixed(2)}</div>`;
            }
            
            if (attribute.certifications && attribute.certifications.length > 0) {
                content += '<div class="certification-badges mt-2">';
                attribute.certifications.forEach(cert => {
                    content += `<span class="certification-badge">${cert}</span>`;
                });
                content += '</div>';
            }
            
            card.innerHTML = content;
            container.appendChild(card);
        });
    }

    renderQuantityOptions(container, quantities) {
        container.innerHTML = '';
        
        quantities.forEach(quantity => {
            const card = document.createElement('div');
            card.className = 'quantity-card';
            if (quantity.is_recommended) {
                card.classList.add('recommended');
            }
            if (quantity.metadata && quantity.metadata.best_value) {
                card.classList.add('best-value');
            }
            
            card.dataset.attributeId = quantity.id;
            card.dataset.attributeType = 'quantity';
            
            let content = `<h5 class="mb-2">${quantity.name}</h5>`;
            
            if (quantity.metadata) {
                const meta = quantity.metadata;
                if (meta.packaging) {
                    content += `<p class="text-muted mb-2">${meta.packaging}</p>`;
                }
                if (meta.unit_price) {
                    content += `<p class="mb-1"><strong>€${meta.unit_price}/unidad</strong></p>`;
                }
                if (meta.discount_percentage) {
                    content += `<div class="badge bg-success mb-2">${meta.discount_percentage}% descuento</div>`;
                }
                if (meta.best_value) {
                    content += '<div class="badge">Mejor Valor</div>';
                }
            }
            
            card.innerHTML = content;
            container.appendChild(card);
        });
    }

    activateStep(stepType) {
        // Desactivar todos los pasos
        document.querySelectorAll('.configurator-step').forEach(step => {
            step.classList.remove('active');
        });
        
        // Activar el paso actual
        const currentStep = document.getElementById(`step-${stepType}`);
        currentStep.classList.add('active');
        
        this.currentStep = stepType;
    }

    async handleColorCountChange(event) {
        const colorCount = parseInt(event.target.value);
        this.requiredInkCount = colorCount;
        this.selectedInks = [];
        
        // Mostrar selector de tintas
        document.getElementById('inkSelectorContainer').style.display = 'block';
        
        // Cargar tintas disponibles
        await this.loadAvailableInks();
    }

    async loadAvailableInks() {
        const colorHex = this.getSelectedColorHex();
        const materialType = this.getSelectedMaterialType();
        
        if (!colorHex) return;
        
        try {
            const response = await fetch(this.config.apiEndpoints.recommendedInks, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify({
                    color_hex: colorHex,
                    material_type: materialType
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.renderInkOptions(data.recommended_inks);
            }
        } catch (error) {
            console.error('Error loading inks:', error);
        }
    }

    renderInkOptions(inks) {
        const container = document.getElementById('inksGrid');
        container.innerHTML = '';
        
        inks.forEach(ink => {
            const card = document.createElement('div');
            card.className = 'ink-card';
            if (ink.is_recommended) {
                card.classList.add('recommended');
            }
            
            card.dataset.inkId = ink.id;
            card.dataset.inkHex = ink.hex_code;
            
            let content = `<div class="ink-preview" style="background-color: ${ink.hex_code};"></div>`;
            content += `<h6 class="mb-1">${ink.name}</h6>`;
            content += `<small class="text-muted">${ink.value}</small>`;
            
            if (ink.price_modifier !== 0) {
                const sign = ink.price_modifier > 0 ? '+' : '';
                content += `<div class="badge bg-info mt-2">${sign}€${ink.price_modifier.toFixed(3)}</div>`;
            }
            
            card.innerHTML = content;
            container.appendChild(card);
        });
    }

    handleInkSelection(event) {
        const card = event.target.closest('.ink-card');
        const inkId = parseInt(card.dataset.inkId);
        
        if (card.classList.contains('selected')) {
            // Deseleccionar
            card.classList.remove('selected');
            this.selectedInks = this.selectedInks.filter(id => id !== inkId);
        } else {
            // Seleccionar si no se ha alcanzado el límite
            if (this.selectedInks.length < this.requiredInkCount) {
                card.classList.add('selected');
                this.selectedInks.push(inkId);
            } else {
                this.showMessage(`Solo puede seleccionar ${this.requiredInkCount} tinta(s)`, 'warning');
                return;
            }
        }
        
        // Actualizar personalización
        this.currentPersonalization.number_of_colors = this.requiredInkCount;
        this.currentPersonalization.selected_inks = this.selectedInks;
        
        // Verificar si puede avanzar al resumen
        if (this.selectedInks.length === this.requiredInkCount) {
            this.proceedToNextStep('personalization');
        }
    }

    async updateConfiguration(attributeType, attributeId, section = 'attributes_base') {
        try {
            const response = await fetch(this.config.apiEndpoints.updateConfiguration, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify({
                    configuration_id: this.config.configuration.id,
                    attribute_type: attributeType,
                    attribute_id: attributeId,
                    section: section
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Manejar reseteos de atributos
                if (data.reset_attributes && data.reset_attributes.length > 0) {
                    this.handleAttributeResets(data.reset_attributes);
                }
                
                // Mostrar errores de validación si existen
                if (data.validation_errors && data.validation_errors.length > 0) {
                    this.showValidationErrors(data.validation_errors);
                }
            }
        } catch (error) {
            console.error('Error updating configuration:', error);
        }
    }

    async updatePriceDisplay() {
        if (Object.keys(this.currentSelection).length < 2) return;
        
        try {
            const response = await fetch(this.config.apiEndpoints.calculatePrice, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify({
                    product_id: this.config.product.id,
                    selection: this.currentSelection,
                    quantity: this.getSelectedQuantity() || 1
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.renderPriceDisplay(data);
                document.getElementById('priceDisplay').style.display = 'block';
            } else {
                console.error('Price calculation failed:', data.error);
                this.showMessage('Error calculando precio: ' + (data.error || 'Error desconocido'), 'error');
            }
        } catch (error) {
            console.error('Error calculating price:', error);
            this.showMessage('Error de conexión al calcular precio', 'error');
        }
    }

    renderPriceDisplay(priceData) {
        const pricing = priceData.pricing;
        
        document.getElementById('unitPrice').textContent = `€${pricing.unit_price}`;
        document.getElementById('quantityDisplay').textContent = pricing.quantity.toLocaleString();
        document.getElementById('totalPrice').textContent = `€${pricing.total_price.toLocaleString()}`;
        
        // Breakdown
        const breakdown = document.getElementById('priceBreakdown');
        let breakdownHtml = '';
        if (pricing.volume_discount > 0) {
            breakdownHtml += `<div>Descuento por volumen: -${pricing.volume_discount}%</div>`;
        }
        breakdownHtml += `<div>Precio base: €${pricing.base_price}</div>`;
        breakdown.innerHTML = breakdownHtml;
        
        // Certificaciones
        if (priceData.certifications && priceData.certifications.length > 0) {
            const badges = document.getElementById('certificationBadges');
            badges.innerHTML = priceData.certifications.map(cert => 
                `<span class="certification-badge">${cert}</span>`
            ).join('');
        }
        
        // Mostrar botón agregar al carrito si la configuración está completa
        if (this.isConfigurationComplete()) {
            document.getElementById('addToCartBtn').style.display = 'block';
        }
    }

    async loadSummary() {
        const container = document.getElementById('configurationSummary');
        const orderDetails = document.getElementById('orderDetails');
        
        let summaryHtml = '<div class="row">';
        
        // Mostrar selecciones realizadas
        const selections = this.getAllSelections();
        for (const [type, attributeId] of Object.entries(selections)) {
            const attribute = await this.getAttributeData(attributeId);
            if (attribute) {
                summaryHtml += this.renderSummaryItem(type, attribute);
            }
        }
        
        summaryHtml += '</div>';
        container.innerHTML = summaryHtml;
        
        // Detalles del pedido
        const pricing = await this.getCurrentPricing();
        if (pricing) {
            orderDetails.innerHTML = this.renderOrderDetails(pricing);
        }
        
        // Habilitar botón finalizar
        document.getElementById('finalizeOrderBtn').disabled = false;
    }

    renderSummaryItem(type, attribute) {
        let icon = this.getTypeIcon(type);
        let extra = '';
        
        if (attribute.hex_code) {
            extra = `<div class="color-preview" style="background-color: ${attribute.hex_code}; width: 30px; height: 30px; display: inline-block; margin-right: 10px;"></div>`;
        }
        
        return `
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="${icon} me-3 text-primary"></i>
                            ${extra}
                            <div>
                                <h6 class="mb-1">${this.getTypeLabel(type)}</h6>
                                <p class="mb-0 text-muted">${attribute.name}</p>
                                <small class="text-success">${attribute.value}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderOrderDetails(pricing) {
        return `
            <p><strong>Producto:</strong> ${this.config.product.name}</p>
            <p><strong>Cantidad:</strong> ${pricing.quantity.toLocaleString()} unidades</p>
            <p><strong>Precio unitario:</strong> €${pricing.unit_price}</p>
            <p><strong>Total:</strong> <span class="h5 text-primary">€${pricing.total_price.toLocaleString()}</span></p>
            <hr>
            <p><strong>Tiempo estimado:</strong> ${pricing.production_time || 15} días laborales</p>
        `;
    }

    // Utility methods
    updateProgress() {
        const steps = ['color', 'material', 'size', 'quantity', 'personalization', 'summary'];
        const completedSteps = steps.filter(step => {
            return document.getElementById(`step-${step}`).classList.contains('completed');
        });
        
        const progress = (completedSteps.length / steps.length) * 100;
        document.getElementById('progressBar').style.width = `${progress}%`;
    }

    getSelectedColorHex() {
        const colorId = this.currentSelection.color;
        if (colorId) {
            const colorCard = document.querySelector(`[data-attribute-id="${colorId}"][data-attribute-type="color"]`);
            return colorCard ? colorCard.dataset.hex : null;
        }
        return null;
    }

    getSelectedMaterialType() {
        const materialId = this.currentSelection.material;
        // Lógica para obtener el tipo de material basado en ID
        return null; // Implementar si es necesario
    }

    getSelectedQuantity() {
        const quantityId = this.currentSelection.quantity;
        if (quantityId) {
            const quantityCard = document.querySelector(`[data-attribute-id="${quantityId}"][data-attribute-type="quantity"]`);
            return quantityCard ? parseInt(quantityCard.querySelector('h5').textContent.match(/[\d,]+/)[0].replace(/,/g, '')) : null;
        }
        return null;
    }

    getAllSelections() {
        return { ...this.currentSelection, ...this.currentPersonalization };
    }

    async getAttributeData(attributeId) {
        // Esta función debería hacer una llamada a la API o usar datos cacheados
        // Por ahora retornamos un objeto mock
        return {
            id: attributeId,
            name: 'Attribute Name',
            value: 'Attribute Value'
        };
    }

    async getCurrentPricing() {
        // Retorna los datos de pricing actuales
        return {
            quantity: this.getSelectedQuantity() || 16200,
            unit_price: '0.133',
            total_price: '2154.60',
            production_time: 15
        };
    }

    getTypeIcon(type) {
        const icons = {
            color: 'bi bi-palette',
            material: 'bi bi-layers',
            size: 'bi bi-rulers',
            quantity: 'bi bi-boxes',
            ink: 'bi bi-droplet'
        };
        return icons[type] || 'bi bi-gear';
    }

    getTypeLabel(type) {
        const labels = {
            color: 'Color',
            material: 'Material',
            size: 'Tamaño',
            quantity: 'Cantidad',
            ink: 'Tinta'
        };
        return labels[type] || type;
    }

    isConfigurationComplete() {
        const required = ['color', 'material', 'size', 'quantity'];
        return required.every(attr => this.currentSelection[attr]);
    }

    handleAttributeResets(resetAttributeIds) {
        resetAttributeIds.forEach(attributeId => {
            const card = document.querySelector(`[data-attribute-id="${attributeId}"]`);
            if (card) {
                card.classList.remove('selected');
            }
        });
    }

    async handleAutoSelections(autoSelectIds) {
        for (const attributeId of autoSelectIds) {
            const card = document.querySelector(`[data-attribute-id="${attributeId}"]`);
            if (card) {
                card.click(); // Simular click para auto-seleccionar
            }
        }
    }

    showValidationErrors(errors) {
        const container = document.getElementById('messageContainer');
        container.innerHTML = `
            <div class="validation-message">
                <strong>Errores de validación:</strong>
                <ul class="mb-0 mt-2">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    showMessage(message, type = 'info') {
        const container = document.getElementById('messageContainer');
        const className = type === 'error' ? 'validation-message' : 'success-message';
        container.innerHTML = `<div class="${className}">${message}</div>`;
        
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }

    showLoading() {
        this.isLoading = true;
        const modal = document.getElementById('loadingModal');
        if (modal) {
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
        }
    }

    hideLoading() {
        this.isLoading = false;
        const modal = document.getElementById('loadingModal');
        if (modal) {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        }
    }

    async finalizeConfiguration() {
        try {
            this.showLoading();
            
            const response = await fetch(this.config.apiEndpoints.validateConfiguration, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken
                },
                body: JSON.stringify({
                    configuration_id: this.config.configuration.id
                })
            });
            
            const data = await response.json();
            
            if (data.success && data.is_valid) {
                this.showMessage('Configuración finalizada correctamente', 'success');
                // Redirigir o manejar siguiente paso
            } else {
                this.showValidationErrors(data.errors || ['Error desconocido']);
            }
        } catch (error) {
            console.error('Error finalizing configuration:', error);
            this.showMessage('Error al finalizar la configuración', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async addToCart() {
        // Lógica para agregar al carrito
        this.showMessage('Producto agregado al carrito', 'success');
    }
}

// Inicializar configurador cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (window.configuratorData) {
        window.configurator = new ProductConfigurator();
    }
});