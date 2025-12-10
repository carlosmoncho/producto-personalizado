@extends('layouts.admin')

@section('title', 'Nueva Dependencia de Atributos')

@section('content')
<!-- Header con Breadcrumb -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.attribute-dependencies.index') }}">Dependencias de Atributos</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Nueva Dependencia</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-plus-circle"></i>
            </div>
            <div>
                <h2 class="mb-0">Nueva Dependencia de Atributos</h2>
                <small class="text-muted">Crear una nueva relación entre atributos de productos</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.attribute-dependencies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Cancelar
        </a>
    </div>
</div>

<!-- Formulario -->
<form method="POST" action="{{ route('admin.attribute-dependencies.store') }}" id="dependencyForm">
    @csrf

    <div class="row">
        <!-- Columna principal -->
        <div class="col-lg-8">
            <!-- Selección de producto -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-box"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Producto</h5>
                            <small>Selecciona el producto para esta dependencia (opcional - si no se selecciona, será global)</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Producto</label>
                        <select class="form-select @error('product_id') is-invalid @enderror"
                                id="product_id" name="product_id">
                            <option value="">Todas los productos (dependencia global)</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                    @if($product->sku)
                                        - SKU: {{ $product->sku }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Si seleccionas un producto específico, esta dependencia solo se aplicará a ese producto.
                            Si no seleccionas ninguno, se aplicará a todos los productos.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Selección de atributos -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Selección de Atributos</h5>
                            <small>Define qué atributo afecta a cuál - puedes seleccionar varios</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parent_type" class="form-label">Tipo de Atributo Padre</label>
                                <select class="form-select @error('parent_attribute_ids') is-invalid @enderror"
                                        id="parent_type" name="parent_type" required>
                                    <option value="">Seleccionar tipo</option>
                                    @foreach($attributesByType as $type => $attributes)
                                        <option value="{{ $type }}">{{ $typeLabels[$type] ?? $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="parent_attribute_ids" class="form-label">Atributos Padre <span class="badge bg-info">múltiple</span></label>
                                <select class="form-select @error('parent_attribute_ids') is-invalid @enderror"
                                        id="parent_attribute_ids" name="parent_attribute_ids[]" multiple size="6" disabled>
                                </select>
                                @error('parent_attribute_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Ctrl+Click para seleccionar varios atributos</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dependent_type" class="form-label">Tipo de Atributo Dependiente (opcional)</label>
                                <select class="form-select @error('dependent_attribute_ids') is-invalid @enderror"
                                        id="dependent_type" name="dependent_type">
                                    <option value="">Sin tipo (para modificadores individuales)</option>
                                    @foreach($attributesByType as $type => $attributes)
                                        <option value="{{ $type }}">{{ $typeLabels[$type] ?? $type }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Opcional: Solo necesario si vas a seleccionar atributos dependientes</small>
                            </div>

                            <div class="mb-3">
                                <label for="dependent_attribute_ids" class="form-label">Atributos Dependientes <span class="badge bg-info">múltiple</span></label>
                                <select class="form-select @error('dependent_attribute_ids') is-invalid @enderror"
                                        id="dependent_attribute_ids" name="dependent_attribute_ids[]" multiple size="6" disabled>
                                </select>
                                @error('dependent_attribute_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <strong>Opcional:</strong> Deja vacío para crear modificadores individuales.
                                    Ctrl+Click para seleccionar varios.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Contador de combinaciones -->
                    <div class="alert alert-info mt-3" id="combinations-info">
                        <i class="bi bi-calculator me-2"></i>
                        <span id="combinations-count">Selecciona atributos para ver cuántas dependencias se crearán</span>
                    </div>
                </div>
            </div>

            <!-- Tipo de relación -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-gear"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Tipo de Relación</h5>
                            <small>Define cómo un atributo afecta al otro</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="condition_type" class="form-label">Condición (opcional para modificadores individuales)</label>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Solo necesario para dependencias:</strong> Si dejas el atributo dependiente vacío, se asignará automáticamente.
                        </div>
                        <div class="row g-2">
                            @foreach($conditionTypes as $type => $label)
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3 {{ old('condition_type') == $type ? 'border-primary bg-light' : '' }}">
                                        <input class="form-check-input" type="radio"
                                               name="condition_type" id="condition_{{ $type }}"
                                               value="{{ $type }}" {{ old('condition_type') == $type ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="condition_{{ $type }}">
                                            <div class="fw-medium">{{ $label }}</div>
                                            <small class="text-muted">
                                                @switch($type)
                                                    @case('allows')
                                                        Cuando se selecciona el atributo padre, se habilita el dependiente
                                                        @break
                                                    @case('blocks')
                                                        Cuando se selecciona el atributo padre, se deshabilita el dependiente
                                                        @break
                                                    @case('requires')
                                                        El atributo dependiente es obligatorio cuando se selecciona el padre
                                                        @break
                                                    @case('sets_price')
                                                        Modifica el precio cuando ambos atributos están seleccionados
                                                        @break
                                                @endswitch
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('condition_type')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Modificador de precio -->
                    <div class="mt-3">
                        <label for="price_modifier" class="form-label">Modificador de Precio (opcional)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('price_modifier') is-invalid @enderror"
                                   id="price_modifier" name="price_modifier" step="0.0001"
                                   value="{{ old('price_modifier') }}" placeholder="0.0000">
                            <span class="input-group-text">€</span>
                            @error('price_modifier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle text-info me-1"></i>
                            Usa valores positivos para aumentar o negativos para descuento
                        </small>
                    </div>

                    <!-- Aplicar precio a -->
                    <div class="mt-3">
                        <label for="price_applies_to" class="form-label">Aplicar Precio a</label>
                        <select class="form-select @error('price_applies_to') is-invalid @enderror"
                                id="price_applies_to" name="price_applies_to">
                            <option value="unit" {{ old('price_applies_to', 'unit') == 'unit' ? 'selected' : '' }}>Precio Unitario</option>
                            <option value="total" {{ old('price_applies_to') == 'total' ? 'selected' : '' }}>Precio Total (ej: cliché)</option>
                        </select>
                        @error('price_applies_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            <i class="bi bi-info-circle text-info me-1"></i>
                            <strong>Unitario:</strong> Se multiplica por cantidad. <strong>Total:</strong> Se suma una sola vez al total (ej: cliché, costes fijos).
                        </small>
                    </div>
                </div>
            </div>

            <!-- Condiciones adicionales -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-funnel"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Condiciones Adicionales</h5>
                            <small>Configuración avanzada (opcional)</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="conditions" class="form-label">Condiciones Personalizadas (JSON)</label>
                        <textarea class="form-control @error('conditions') is-invalid @enderror" 
                                  id="conditions" name="conditions" rows="4" 
                                  placeholder='{"custom_condition": "value"}'>{{ old('conditions') }}</textarea>
                        @error('conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Condiciones personalizadas en formato JSON para lógica avanzada</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
        <div class="col-lg-4">
            <!-- Configuración de comportamiento -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-toggles"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Comportamiento</h5>
                            <small>Opciones de funcionamiento</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="auto_select" 
                                   name="auto_select" value="1" {{ old('auto_select') ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_select">
                                <div class="fw-medium">Auto-selección</div>
                                <small class="text-muted">Seleccionar automáticamente el atributo dependiente cuando se active la condición</small>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="reset_dependents" 
                                   name="reset_dependents" value="1" {{ old('reset_dependents') ? 'checked' : '' }}>
                            <label class="form-check-label" for="reset_dependents">
                                <div class="fw-medium">Resetear dependientes</div>
                                <small class="text-muted">Limpiar la selección de atributos dependientes cuando cambie el padre</small>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Prioridad</label>
                        <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                               id="priority" name="priority" value="{{ old('priority', 0) }}" 
                               min="0" max="999">
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">0 = Baja prioridad, 999 = Máxima prioridad</small>
                    </div>
                </div>
            </div>

            <!-- Vista previa -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Vista Previa</h5>
                            <small>Resumen de la dependencia</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="preview-content" class="text-center text-muted">
                        <i class="bi bi-diagram-3 display-4"></i>
                        <div class="mt-2">Selecciona los atributos para ver la vista previa</div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="bi bi-check-circle me-2"></i>Crear Dependencia
                    </button>
                    <a href="{{ route('admin.attribute-dependencies.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos de atributos agrupados por tipo
    const attributesByType = @json($attributesByType);
    const typeLabels = @json($typeLabels);
    const conditionLabels = @json($conditionTypes);

    // Elements
    const parentTypeSelect = document.getElementById('parent_type');
    const parentAttributeSelect = document.getElementById('parent_attribute_ids');
    const dependentTypeSelect = document.getElementById('dependent_type');
    const dependentAttributeSelect = document.getElementById('dependent_attribute_ids');
    const conditionRadios = document.querySelectorAll('input[name="condition_type"]');
    const priceModifierInput = document.getElementById('price_modifier');
    const previewContent = document.getElementById('preview-content');
    const combinationsCount = document.getElementById('combinations-count');

    // Event listeners
    parentTypeSelect.addEventListener('change', function() {
        loadAttributeOptions('parent', this.value);
        updatePreview();
        updateCombinationsCount();
    });

    dependentTypeSelect.addEventListener('change', function() {
        loadAttributeOptions('dependent', this.value);
        updatePreview();
        updateCombinationsCount();
    });

    parentAttributeSelect.addEventListener('change', function() {
        updatePreview();
        updateCombinationsCount();
    });

    dependentAttributeSelect.addEventListener('change', function() {
        updatePreview();
        updateCombinationsCount();
    });

    priceModifierInput.addEventListener('input', updatePreview);

    conditionRadios.forEach(radio => {
        radio.addEventListener('change', updatePreview);
    });

    function loadAttributeOptions(attributeRole, type) {
        const selectElement = attributeRole === 'parent' ? parentAttributeSelect : dependentAttributeSelect;

        if (type && attributesByType[type]) {
            selectElement.disabled = false;
            selectElement.innerHTML = '';

            attributesByType[type].forEach(attr => {
                const option = document.createElement('option');
                option.value = attr.id;
                option.textContent = `${attr.name}${attr.value ? ' (' + attr.value + ')' : ''}`;
                selectElement.appendChild(option);
            });
        } else {
            selectElement.disabled = true;
            selectElement.innerHTML = '';
        }
    }

    function getSelectedValues(selectElement) {
        return Array.from(selectElement.selectedOptions).map(opt => opt.value);
    }

    function updateCombinationsCount() {
        const parentCount = getSelectedValues(parentAttributeSelect).length || 0;
        const dependentCount = getSelectedValues(dependentAttributeSelect).length || 0;

        let total = 0;
        if (parentCount > 0) {
            if (dependentCount > 0) {
                total = parentCount * dependentCount;
            } else {
                total = parentCount; // Modificadores individuales
            }
        }

        if (total === 0) {
            combinationsCount.textContent = 'Selecciona atributos para ver cuántas dependencias se crearán';
        } else if (total === 1) {
            combinationsCount.textContent = 'Se creará 1 dependencia';
        } else {
            const formula = dependentCount > 0 ? `${parentCount} × ${dependentCount}` : `${parentCount} modificadores individuales`;
            combinationsCount.innerHTML = `Se crearán <strong>${total}</strong> dependencias (${formula})`;
        }
    }

    function updatePreview() {
        const parentIds = getSelectedValues(parentAttributeSelect);
        const dependentIds = getSelectedValues(dependentAttributeSelect);
        const conditionType = document.querySelector('input[name="condition_type"]:checked')?.value;
        const priceModifier = parseFloat(priceModifierInput.value) || 0;

        if (parentIds.length === 0) {
            previewContent.innerHTML = `
                <i class="bi bi-diagram-3 display-4 text-muted"></i>
                <div class="mt-2 text-muted">Selecciona atributos para ver la vista previa</div>
            `;
            return;
        }

        // Si hay atributos dependientes pero no condición, pedir condición
        if (dependentIds.length > 0 && !conditionType) {
            previewContent.innerHTML = `
                <i class="bi bi-exclamation-triangle display-4 text-warning"></i>
                <div class="mt-2 text-muted">Para dependencias entre atributos, selecciona un tipo de condición</div>
            `;
            return;
        }

        // Buscar los atributos seleccionados
        let parentAttrs = [];
        let dependentAttrs = [];

        Object.values(attributesByType).forEach(attributes => {
            attributes.forEach(attr => {
                if (parentIds.includes(String(attr.id))) parentAttrs.push(attr);
                if (dependentIds.includes(String(attr.id))) dependentAttrs.push(attr);
            });
        });

        // Generar vista previa
        const conditionColors = {
            'allows': 'success',
            'blocks': 'danger',
            'requires': 'warning',
            'sets_price': 'info'
        };

        const priceText = priceModifier !== 0 ?
            `<div class="mt-3 text-center"><span class="badge bg-info fs-6"><i class="bi bi-currency-euro me-1"></i>${priceModifier > 0 ? '+' : ''}${priceModifier.toFixed(4)}€</span></div>` :
            '';

        const totalCombinations = dependentAttrs.length > 0 ? parentAttrs.length * dependentAttrs.length : parentAttrs.length;

        let parentBadges = parentAttrs.map(a => `<span class="badge bg-primary me-1">${a.name}</span>`).join('');
        let dependentBadges = dependentAttrs.length > 0 ? dependentAttrs.map(a => `<span class="badge bg-success me-1">${a.name}</span>`).join('') : '';

        if (dependentAttrs.length === 0) {
            // Modificadores individuales
            previewContent.innerHTML = `
                <div class="text-center mb-3">
                    <div class="badge bg-info text-white fs-6">Modificadores Individuales</div>
                    <div class="small text-muted mt-1">${totalCombinations} dependencia${totalCombinations > 1 ? 's' : ''}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Atributos:</small>
                    <div>${parentBadges}</div>
                </div>
                ${priceText}
                <div class="mt-3 small text-muted text-center">
                    <i class="bi bi-info-circle me-1"></i>
                    Se aplica al seleccionar cualquiera de estos atributos
                </div>
            `;
        } else {
            // Dependencias por combinación
            previewContent.innerHTML = `
                <div class="text-center mb-3">
                    <span class="badge bg-${conditionColors[conditionType]} fs-6">${conditionLabels[conditionType]}</span>
                    <div class="small text-muted mt-1">${totalCombinations} combinación${totalCombinations > 1 ? 'es' : ''}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Atributo 1:</small>
                    <div>${parentBadges}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Atributo 2:</small>
                    <div>${dependentBadges}</div>
                </div>
                ${priceText}
                <div class="mt-3 small text-muted text-center">
                    <i class="bi bi-info-circle me-1"></i>
                    Cada combinación crea una dependencia separada
                </div>
            `;
        }
    }

    // Inicializar
    updatePreview();
    updateCombinationsCount();

    // Cargar opciones si hay valores previos
    if (parentTypeSelect.value) {
        loadAttributeOptions('parent', parentTypeSelect.value);
    }
    if (dependentTypeSelect.value) {
        loadAttributeOptions('dependent', dependentTypeSelect.value);
    }
});
</script>
@endpush