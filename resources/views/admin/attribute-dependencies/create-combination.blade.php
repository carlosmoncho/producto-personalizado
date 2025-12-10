@extends('layouts.admin')

@section('title', 'Nueva Dependencia por Combinación')

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
                    <a href="{{ route('admin.attribute-dependencies.index') }}">Dependencias & Precios</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Dependencia por Combinación</li>
            </ol>
        </nav>

        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-success text-white rounded me-3">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <div>
                <h2 class="mb-0">Nueva Dependencia por Combinación</h2>
                <small class="text-muted">Crea una relación entre dos atributos que se aplica solo cuando ambos están seleccionados</small>
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
<form method="POST" action="{{ route('admin.attribute-dependencies.store-combination') }}" id="combinationForm">
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
                            <small>Selecciona el producto para esta dependencia (opcional - aplica a todos si no se selecciona)</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Producto</label>
                        <select class="form-select @error('product_id') is-invalid @enderror"
                                id="product_id" name="product_id">
                            <option value="">Todos los productos (dependencia global)</option>
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
                            <h5 class="mb-0">Selección de Atributos (2-3)</h5>
                            <small>Define la combinación de atributos que activa esta regla</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Atributo 1 (Padre) -->
                        <div class="col-md-4">
                            <div class="card bg-light border-0 p-3 mb-3">
                                <h6 class="text-primary mb-3"><i class="bi bi-1-circle me-2"></i>Atributo 1 (Padre) *</h6>
                                <div class="mb-3">
                                    <label for="parent_type" class="form-label">Tipo</label>
                                    <select class="form-select" id="parent_type" name="parent_type" required>
                                        <option value="">Seleccionar tipo</option>
                                        @foreach($attributesByType as $type => $attributes)
                                            <option value="{{ $type }}">{{ $typeLabels[$type] ?? $type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-0">
                                    <label for="parent_attribute_ids" class="form-label">Atributos <span class="badge bg-info">múltiple</span></label>
                                    <select class="form-select @error('parent_attribute_ids') is-invalid @enderror"
                                            id="parent_attribute_ids" name="parent_attribute_ids[]" multiple size="5" disabled>
                                    </select>
                                    <small class="text-muted">Ctrl+Click para seleccionar varios</small>
                                    @error('parent_attribute_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Atributo 2 (Dependiente) -->
                        <div class="col-md-4">
                            <div class="card bg-light border-0 p-3 mb-3">
                                <h6 class="text-success mb-3"><i class="bi bi-2-circle me-2"></i>Atributo 2 *</h6>
                                <div class="mb-3">
                                    <label for="dependent_type" class="form-label">Tipo</label>
                                    <select class="form-select" id="dependent_type" name="dependent_type" required>
                                        <option value="">Seleccionar tipo</option>
                                        @foreach($attributesByType as $type => $attributes)
                                            <option value="{{ $type }}">{{ $typeLabels[$type] ?? $type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-0">
                                    <label for="dependent_attribute_ids" class="form-label">Atributos <span class="badge bg-info">múltiple</span></label>
                                    <select class="form-select @error('dependent_attribute_ids') is-invalid @enderror"
                                            id="dependent_attribute_ids" name="dependent_attribute_ids[]" multiple size="5" disabled>
                                    </select>
                                    <small class="text-muted">Ctrl+Click para seleccionar varios</small>
                                    @error('dependent_attribute_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Atributo 3 (Opcional) -->
                        <div class="col-md-4">
                            <div class="card bg-light border-0 p-3 mb-3">
                                <h6 class="text-warning mb-3"><i class="bi bi-3-circle me-2"></i>Atributo 3 (Opcional)</h6>
                                <div class="mb-3">
                                    <label for="third_type" class="form-label">Tipo</label>
                                    <select class="form-select" id="third_type" name="third_type">
                                        <option value="">Sin tercer atributo</option>
                                        @foreach($attributesByType as $type => $attributes)
                                            <option value="{{ $type }}">{{ $typeLabels[$type] ?? $type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-0">
                                    <label for="third_attribute_ids" class="form-label">Atributos <span class="badge bg-info">múltiple</span></label>
                                    <select class="form-select @error('third_attribute_ids') is-invalid @enderror"
                                            id="third_attribute_ids" name="third_attribute_ids[]" multiple size="5" disabled>
                                    </select>
                                    <small class="text-muted">Ctrl+Click para seleccionar varios</small>
                                    @error('third_attribute_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contador de combinaciones -->
                    <div class="alert alert-info mt-3" id="combinations-info">
                        <i class="bi bi-calculator me-2"></i>
                        <span id="combinations-count">Selecciona atributos para ver cuántas dependencias se crearán</span>
                    </div>

                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Puedes seleccionar <strong>múltiples atributos</strong> en cada columna. Se creará una dependencia para cada combinación posible.
                    </small>
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
                        <label for="condition_type" class="form-label">Condición *</label>
                        <div class="row g-2">
                            @foreach($conditionTypes as $type => $label)
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3 {{ old('condition_type') == $type ? 'border-primary bg-light' : '' }}">
                                        <input class="form-check-input" type="radio"
                                               name="condition_type" id="condition_{{ $type }}"
                                               value="{{ $type }}" {{ old('condition_type') == $type ? 'checked' : '' }} required>
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
                        <label for="price_modifier" class="form-label">Modificador de Precio por Combinación (opcional)</label>
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
                            Se aplica solo cuando ambos atributos están seleccionados. Puede ser negativo para descuentos.
                        </small>
                    </div>

                    <!-- Modificador porcentual -->
                    <div class="mt-3">
                        <label for="price_percentage" class="form-label">Modificador Porcentual (opcional)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('price_percentage') is-invalid @enderror"
                                   id="price_percentage" name="price_percentage" step="0.01"
                                   value="{{ old('price_percentage') }}" placeholder="0.00">
                            <span class="input-group-text">%</span>
                            @error('price_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle text-info me-1"></i>
                            Ej: 10 = +10%, -5 = -5%. Se aplica sobre el precio después de modificadores fijos.
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
        </div>

        <!-- Columna lateral -->
        <div class="col-lg-4">
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

            <!-- Botones de acción -->
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-success btn-lg w-100 mb-2">
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
    const thirdTypeSelect = document.getElementById('third_type');
    const thirdAttributeSelect = document.getElementById('third_attribute_ids');
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

    thirdTypeSelect.addEventListener('change', function() {
        loadAttributeOptions('third', this.value);
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

    thirdAttributeSelect.addEventListener('change', function() {
        updatePreview();
        updateCombinationsCount();
    });

    priceModifierInput.addEventListener('input', updatePreview);

    conditionRadios.forEach(radio => {
        radio.addEventListener('change', updatePreview);
    });

    function loadAttributeOptions(attributeRole, type) {
        let selectElement;
        if (attributeRole === 'parent') selectElement = parentAttributeSelect;
        else if (attributeRole === 'dependent') selectElement = dependentAttributeSelect;
        else selectElement = thirdAttributeSelect;

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
        const thirdCount = getSelectedValues(thirdAttributeSelect).length || 0;

        let total = 0;
        if (parentCount > 0 && dependentCount > 0) {
            if (thirdCount > 0) {
                total = parentCount * dependentCount * thirdCount;
            } else {
                total = parentCount * dependentCount;
            }
        }

        if (total === 0) {
            combinationsCount.textContent = 'Selecciona atributos para ver cuántas dependencias se crearán';
        } else if (total === 1) {
            combinationsCount.textContent = 'Se creará 1 dependencia';
        } else {
            combinationsCount.innerHTML = `Se crearán <strong>${total}</strong> dependencias (${parentCount} × ${dependentCount}${thirdCount > 0 ? ' × ' + thirdCount : ''})`;
        }
    }

    function updatePreview() {
        const parentIds = getSelectedValues(parentAttributeSelect);
        const dependentIds = getSelectedValues(dependentAttributeSelect);
        const thirdIds = getSelectedValues(thirdAttributeSelect);
        const conditionType = document.querySelector('input[name="condition_type"]:checked')?.value;
        const priceModifier = parseFloat(priceModifierInput.value) || 0;

        if (parentIds.length === 0 || dependentIds.length === 0 || !conditionType) {
            previewContent.innerHTML = `
                <i class="bi bi-diagram-3 display-4 text-muted"></i>
                <div class="mt-2 text-muted">Completa los campos obligatorios para ver la vista previa</div>
            `;
            return;
        }

        // Buscar los atributos seleccionados
        let parentAttrs = [];
        let dependentAttrs = [];
        let thirdAttrs = [];

        Object.values(attributesByType).forEach(attributes => {
            attributes.forEach(attr => {
                if (parentIds.includes(String(attr.id))) parentAttrs.push(attr);
                if (dependentIds.includes(String(attr.id))) dependentAttrs.push(attr);
                if (thirdIds.includes(String(attr.id))) thirdAttrs.push(attr);
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

        const totalCombinations = parentAttrs.length * dependentAttrs.length * (thirdAttrs.length || 1);

        let parentBadges = parentAttrs.map(a => `<span class="badge bg-primary me-1">${a.name}</span>`).join('');
        let dependentBadges = dependentAttrs.map(a => `<span class="badge bg-success me-1">${a.name}</span>`).join('');
        let thirdBadges = thirdAttrs.length > 0 ? thirdAttrs.map(a => `<span class="badge bg-warning me-1">${a.name}</span>`).join('') : '';

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
            ${thirdBadges ? `
            <div class="mb-2">
                <small class="text-muted">Atributo 3:</small>
                <div>${thirdBadges}</div>
            </div>
            ` : ''}
            ${priceText}
            <div class="mt-3 small text-muted text-center">
                <i class="bi bi-info-circle me-1"></i>
                Cada combinación crea una dependencia separada
            </div>
        `;
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
    if (thirdTypeSelect.value) {
        loadAttributeOptions('third', thirdTypeSelect.value);
    }
});
</script>
@endpush