@extends('layouts.admin')

@section('title', 'Editar Dependencia de Atributos')

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
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $attributeDependency->parentAttribute?->name ?? 'Sin padre' }} {{ $attributeDependency->dependentAttribute ? '→ ' . $attributeDependency->dependentAttribute->name : '(Modificador individual)' }}
                </li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-warning text-white rounded me-3">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <h2 class="mb-0">Editar Dependencia de Atributos</h2>
                <small class="text-muted">Modificar relación entre atributos de productos</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.attribute-dependencies.show', $attributeDependency) }}" class="btn btn-outline-info">
            <i class="bi bi-eye me-2"></i>Ver
        </a>
        <a href="{{ route('admin.attribute-dependencies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Cancelar
        </a>
    </div>
</div>

<!-- Formulario -->
<form method="POST" action="{{ route('admin.attribute-dependencies.update', $attributeDependency) }}" id="dependencyForm">
    @csrf
    @method('PUT')

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
                            <option value="" {{ old('product_id', $attributeDependency->product_id) == '' ? 'selected' : '' }}>
                                Todas los productos (dependencia global)
                            </option>
                            @php
                                $products = \App\Models\Product::where('has_configurator', true)
                                    ->where('active', true)
                                    ->orderBy('name')
                                    ->get();
                            @endphp
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', $attributeDependency->product_id) == $product->id ? 'selected' : '' }}>
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
                                    <select class="form-select @error('parent_attribute_id') is-invalid @enderror"
                                            id="parent_type" name="parent_type" required>
                                        <option value="">Seleccionar tipo</option>
                                        @foreach($attributesByType as $type => $attributes)
                                            <option value="{{ $type }}" {{ old('parent_type', $attributeDependency->parentAttribute?->type) == $type ? 'selected' : '' }}>
                                                {{ $typeLabels[$type] ?? $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-0">
                                    <label for="parent_attribute_id" class="form-label">Atributo</label>
                                    <select class="form-select @error('parent_attribute_id') is-invalid @enderror"
                                            id="parent_attribute_id" name="parent_attribute_id" required>
                                        <option value="">Selecciona un atributo</option>
                                    </select>
                                    @error('parent_attribute_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Atributo 2 (Dependiente) -->
                        <div class="col-md-4">
                            <div class="card bg-light border-0 p-3 mb-3">
                                <h6 class="text-success mb-3"><i class="bi bi-2-circle me-2"></i>Atributo 2 (Opcional)</h6>
                                <div class="mb-3">
                                    <label for="dependent_type" class="form-label">Tipo</label>
                                    <select class="form-select @error('dependent_attribute_id') is-invalid @enderror"
                                            id="dependent_type" name="dependent_type">
                                        <option value="">Sin segundo atributo</option>
                                        @foreach($attributesByType as $type => $attributes)
                                            <option value="{{ $type }}" {{ old('dependent_type', $attributeDependency->dependentAttribute?->type) == $type ? 'selected' : '' }}>
                                                {{ $typeLabels[$type] ?? $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-0">
                                    <label for="dependent_attribute_id" class="form-label">Atributo</label>
                                    <select class="form-select @error('dependent_attribute_id') is-invalid @enderror"
                                            id="dependent_attribute_id" name="dependent_attribute_id">
                                        <option value="">Sin segundo atributo</option>
                                    </select>
                                    @error('dependent_attribute_id')
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
                                    <select class="form-select @error('third_attribute_id') is-invalid @enderror"
                                            id="third_type" name="third_type">
                                        <option value="">Sin tercer atributo</option>
                                        @foreach($attributesByType as $type => $attributes)
                                            <option value="{{ $type }}" {{ old('third_type', $attributeDependency->thirdAttribute?->type) == $type ? 'selected' : '' }}>
                                                {{ $typeLabels[$type] ?? $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-0">
                                    <label for="third_attribute_id" class="form-label">Atributo</label>
                                    <select class="form-select @error('third_attribute_id') is-invalid @enderror"
                                            id="third_attribute_id" name="third_attribute_id" disabled>
                                        <option value="">Sin tercer atributo</option>
                                    </select>
                                    @error('third_attribute_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        La regla se aplicará cuando <strong>todos</strong> los atributos seleccionados estén activos. Los atributos 2 y 3 son opcionales.
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
                        <label for="condition_type" class="form-label">Condición</label>
                        <div class="row g-2">
                            @foreach($conditionTypes as $type => $label)
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3 {{ old('condition_type', $attributeDependency->condition_type) == $type ? 'border-primary bg-light' : '' }}">
                                        <input class="form-check-input" type="radio" 
                                               name="condition_type" id="condition_{{ $type }}" 
                                               value="{{ $type }}" {{ old('condition_type', $attributeDependency->condition_type) == $type ? 'checked' : '' }} required>
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
                                   value="{{ old('price_modifier', $attributeDependency->price_modifier) }}" placeholder="0.0000">
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
                            <option value="unit" {{ old('price_applies_to', $attributeDependency->price_applies_to ?? 'unit') == 'unit' ? 'selected' : '' }}>Precio Unitario</option>
                            <option value="total" {{ old('price_applies_to', $attributeDependency->price_applies_to ?? 'unit') == 'total' ? 'selected' : '' }}>Precio Total (ej: cliché)</option>
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
                                  placeholder='{"custom_condition": "value"}'>{{ old('conditions', $attributeDependency->conditions ? json_encode($attributeDependency->conditions, JSON_PRETTY_PRINT) : '') }}</textarea>
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
                                   name="auto_select" value="1" {{ old('auto_select', $attributeDependency->auto_select) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_select">
                                <div class="fw-medium">Auto-selección</div>
                                <small class="text-muted">Seleccionar automáticamente el atributo dependiente cuando se active la condición</small>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="reset_dependents" 
                                   name="reset_dependents" value="1" {{ old('reset_dependents', $attributeDependency->reset_dependents) ? 'checked' : '' }}>
                            <label class="form-check-label" for="reset_dependents">
                                <div class="fw-medium">Resetear dependientes</div>
                                <small class="text-muted">Limpiar la selección de atributos dependientes cuando cambie el padre</small>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Prioridad</label>
                        <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                               id="priority" name="priority" value="{{ old('priority', $attributeDependency->priority) }}" 
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
                        <div class="mt-2">Cargando vista previa...</div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-warning btn-lg w-100 mb-2">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Dependencia
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
    @php
        $attributesData = [];
        if ($attributesByType) {
            foreach ($attributesByType as $type => $attrs) {
                $attributesData[$type] = $attrs->map(function($attr) {
                    return [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'value' => $attr->value,
                        'type' => $attr->type
                    ];
                })->toArray();
            }
        }
    @endphp

    const attributesByType = @json($attributesData);
    const typeLabels = @json($typeLabels);
    const conditionLabels = @json($conditionTypes);

    // Datos existentes
    const existingParentId = {{ $attributeDependency->parent_attribute_id ?? 'null' }};
    const existingDependentId = {{ $attributeDependency->dependent_attribute_id ?? 'null' }};
    const existingThirdId = {{ $attributeDependency->third_attribute_id ?? 'null' }};

    // Elements
    const parentTypeSelect = document.getElementById('parent_type');
    const parentAttributeSelect = document.getElementById('parent_attribute_id');
    const dependentTypeSelect = document.getElementById('dependent_type');
    const dependentAttributeSelect = document.getElementById('dependent_attribute_id');
    const thirdTypeSelect = document.getElementById('third_type');
    const thirdAttributeSelect = document.getElementById('third_attribute_id');
    const conditionRadios = document.querySelectorAll('input[name="condition_type"]');
    const priceModifierInput = document.getElementById('price_modifier');
    const previewContent = document.getElementById('preview-content');

    // Event listeners
    parentTypeSelect.addEventListener('change', function() {
        loadAttributeOptions('parent', this.value);
        updatePreview();
    });

    dependentTypeSelect.addEventListener('change', function() {
        loadAttributeOptions('dependent', this.value);
        updatePreview();
    });

    thirdTypeSelect.addEventListener('change', function() {
        loadAttributeOptions('third', this.value);
        updatePreview();
    });

    parentAttributeSelect.addEventListener('change', updatePreview);
    dependentAttributeSelect.addEventListener('change', updatePreview);
    thirdAttributeSelect.addEventListener('change', updatePreview);
    priceModifierInput.addEventListener('input', updatePreview);

    conditionRadios.forEach(radio => {
        radio.addEventListener('change', updatePreview);
    });

    function loadAttributeOptions(attributeRole, type) {
        let selectElement, existingValue;
        const excludeIds = [];

        if (attributeRole === 'parent') {
            selectElement = parentAttributeSelect;
            existingValue = existingParentId;
            if (dependentAttributeSelect.value) excludeIds.push(dependentAttributeSelect.value);
            if (thirdAttributeSelect.value) excludeIds.push(thirdAttributeSelect.value);
        } else if (attributeRole === 'dependent') {
            selectElement = dependentAttributeSelect;
            existingValue = existingDependentId;
            if (parentAttributeSelect.value) excludeIds.push(parentAttributeSelect.value);
            if (thirdAttributeSelect.value) excludeIds.push(thirdAttributeSelect.value);
        } else {
            selectElement = thirdAttributeSelect;
            existingValue = existingThirdId;
            if (parentAttributeSelect.value) excludeIds.push(parentAttributeSelect.value);
            if (dependentAttributeSelect.value) excludeIds.push(dependentAttributeSelect.value);
        }

        if (type && attributesByType[type]) {
            selectElement.disabled = false;
            const emptyOptionText = attributeRole === 'parent' ? 'Selecciona un atributo' : `Sin ${attributeRole === 'dependent' ? 'segundo' : 'tercer'} atributo`;
            selectElement.innerHTML = `<option value="">${emptyOptionText}</option>`;

            attributesByType[type].forEach(attr => {
                if (!excludeIds.includes(String(attr.id))) {
                    const option = document.createElement('option');
                    option.value = attr.id;
                    option.textContent = `${attr.name}${attr.value ? ' (' + attr.value + ')' : ''}`;
                    if (attr.id == existingValue) {
                        option.selected = true;
                    }
                    selectElement.appendChild(option);
                }
            });
        } else {
            selectElement.disabled = true;
            const emptyOptionText = attributeRole === 'parent' ? 'Selecciona primero el tipo' : `Sin ${attributeRole === 'dependent' ? 'segundo' : 'tercer'} atributo`;
            selectElement.innerHTML = `<option value="">${emptyOptionText}</option>`;
        }
    }

    function updatePreview() {
        const parentAttrId = parentAttributeSelect.value;
        const dependentAttrId = dependentAttributeSelect.value;
        const thirdAttrId = thirdAttributeSelect.value;
        const conditionType = document.querySelector('input[name="condition_type"]:checked')?.value;
        const priceModifier = parseFloat(priceModifierInput.value) || 0;

        if (!parentAttrId || !conditionType) {
            previewContent.innerHTML = `
                <i class="bi bi-diagram-3 display-4 text-muted"></i>
                <div class="mt-2 text-muted">Selecciona al menos el atributo padre y tipo de condición para ver la vista previa</div>
            `;
            return;
        }

        // Buscar los atributos seleccionados
        let parentAttr = null;
        let dependentAttr = null;
        let thirdAttr = null;

        Object.values(attributesByType).forEach(attributes => {
            attributes.forEach(attr => {
                if (attr.id == parentAttrId) parentAttr = attr;
                if (dependentAttrId && attr.id == dependentAttrId) dependentAttr = attr;
                if (thirdAttrId && attr.id == thirdAttrId) thirdAttr = attr;
            });
        });

        if (!parentAttr) {
            previewContent.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error al cargar el atributo padre seleccionado
                </div>
            `;
            return;
        }

        // Generar vista previa
        const conditionIcons = {
            'allows': 'bi-arrow-right text-success',
            'blocks': 'bi-x text-danger',
            'requires': 'bi-exclamation-triangle text-warning',
            'sets_price': 'bi-currency-euro text-info'
        };

        const conditionColors = {
            'allows': 'success',
            'blocks': 'danger',
            'requires': 'warning',
            'sets_price': 'info'
        };

        const priceText = priceModifier !== 0 ?
            `<div class="mt-2 text-center"><strong>Precio:</strong> ${priceModifier > 0 ? '+' : ''}${priceModifier.toFixed(4)}€ por unidad</div>` :
            '';

        if (!dependentAttr) {
            // Modificador individual
            previewContent.innerHTML = `
                <div class="text-center">
                    <div class="badge bg-info text-white mb-3">Modificador Individual</div>
                    <div class="text-center">
                        <div class="badge bg-light text-dark mb-2">${typeLabels[parentAttr.type] || parentAttr.type}</div>
                        <div class="fw-medium">${parentAttr.name}</div>
                        ${parentAttr.value ? `<small class="text-muted">${parentAttr.value}</small>` : ''}
                    </div>
                    <div class="mt-2 text-muted">
                        <i class="bi bi-info-circle me-1"></i>Se aplicará automáticamente al seleccionar este atributo
                    </div>
                    ${priceText}
                </div>
            `;
        } else if (!thirdAttr) {
            // Dependencia con 2 atributos
            previewContent.innerHTML = `
                <div class="text-center mb-2">
                    <div class="badge bg-success text-white">Dependencia por Combinación (x2)</div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-center">
                        <div class="badge bg-light text-dark mb-2">${typeLabels[parentAttr.type] || parentAttr.type}</div>
                        <div class="fw-medium">${parentAttr.name}</div>
                        ${parentAttr.value ? `<small class="text-muted">${parentAttr.value}</small>` : ''}
                    </div>
                    <div class="text-center mx-3">
                        <i class="bi ${conditionIcons[conditionType]} display-6"></i>
                        <div class="badge bg-${conditionColors[conditionType]} mt-2">${conditionLabels[conditionType]}</div>
                    </div>
                    <div class="text-center">
                        <div class="badge bg-light text-dark mb-2">${typeLabels[dependentAttr.type] || dependentAttr.type}</div>
                        <div class="fw-medium">${dependentAttr.name}</div>
                        ${dependentAttr.value ? `<small class="text-muted">${dependentAttr.value}</small>` : ''}
                    </div>
                </div>
                ${priceText}
            `;
        } else {
            // Dependencia con 3 atributos
            previewContent.innerHTML = `
                <div class="text-center mb-2">
                    <div class="badge bg-warning text-dark">Dependencia por Combinación (x3)</div>
                </div>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-center flex-fill">
                            <div class="badge bg-primary text-white mb-1">1</div>
                            <div class="badge bg-light text-dark mb-1">${typeLabels[parentAttr.type] || parentAttr.type}</div>
                            <div class="fw-medium small">${parentAttr.name}</div>
                        </div>
                        <div class="text-muted mx-2">+</div>
                        <div class="text-center flex-fill">
                            <div class="badge bg-success text-white mb-1">2</div>
                            <div class="badge bg-light text-dark mb-1">${typeLabels[dependentAttr.type] || dependentAttr.type}</div>
                            <div class="fw-medium small">${dependentAttr.name}</div>
                        </div>
                        <div class="text-muted mx-2">+</div>
                        <div class="text-center flex-fill">
                            <div class="badge bg-warning text-dark mb-1">3</div>
                            <div class="badge bg-light text-dark mb-1">${typeLabels[thirdAttr.type] || thirdAttr.type}</div>
                            <div class="fw-medium small">${thirdAttr.name}</div>
                        </div>
                    </div>
                    <div class="text-center">
                        <i class="bi ${conditionIcons[conditionType]}"></i>
                        <span class="badge bg-${conditionColors[conditionType]}">${conditionLabels[conditionType]}</span>
                    </div>
                </div>
                ${priceText}
            `;
        }
    }

    // Inicializar
    // Cargar opciones con valores existentes
    if (parentTypeSelect.value) {
        loadAttributeOptions('parent', parentTypeSelect.value);
    }
    if (dependentTypeSelect.value) {
        loadAttributeOptions('dependent', dependentTypeSelect.value);
    }
    if (thirdTypeSelect.value) {
        loadAttributeOptions('third', thirdTypeSelect.value);
    }

    // Actualizar vista previa después de cargar todo
    setTimeout(() => {
        updatePreview();
    }, 100);
});
</script>
@endpush