@extends('layouts.admin')

@section('title', 'Editar Regla de Precios')

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
                    <a href="{{ route('admin.price-rules.index') }}">Reglas de Precios</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $priceRule->name }}</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-warning text-white rounded me-3">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <h2 class="mb-0">Editar Regla de Precios</h2>
                <small class="text-muted">Modificar regla automática para cálculo de precios</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.price-rules.show', $priceRule) }}" class="btn btn-outline-info">
            <i class="bi bi-eye me-2"></i>Ver
        </a>
        <a href="{{ route('admin.price-rules.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Cancelar
        </a>
    </div>
</div>

<!-- Formulario -->
<form method="POST" action="{{ route('admin.price-rules.update', $priceRule) }}" id="priceRuleForm">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Columna principal -->
        <div class="col-lg-8">
            <!-- Información básica -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Información Básica</h5>
                            <small>Datos identificativos de la regla</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre de la Regla</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $priceRule->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description', $priceRule->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Prioridad</label>
                                <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                                       id="priority" name="priority" value="{{ old('priority', $priceRule->priority) }}" 
                                       min="0" max="100">
                                <small class="text-muted">0 = Baja, 100 = Máxima</small>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                       {{ old('active', $priceRule->active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">
                                    Regla activa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tipo de regla y acción -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-gear"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Configuración de la Regla</h5>
                            <small>Tipo y acción que realizará la regla</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rule_type" class="form-label">Tipo de Regla</label>
                                <select class="form-select @error('rule_type') is-invalid @enderror" 
                                        id="rule_type" name="rule_type" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="combination" {{ old('rule_type', $priceRule->rule_type) == 'combination' ? 'selected' : '' }}>
                                        Combinación de Atributos
                                    </option>
                                    <option value="volume" {{ old('rule_type', $priceRule->rule_type) == 'volume' ? 'selected' : '' }}>
                                        Descuento por Volumen
                                    </option>
                                    <option value="attribute_specific" {{ old('rule_type', $priceRule->rule_type) == 'attribute_specific' ? 'selected' : '' }}>
                                        Atributo Específico
                                    </option>
                                    <option value="conditional" {{ old('rule_type', $priceRule->rule_type) == 'conditional' ? 'selected' : '' }}>
                                        Condicional
                                    </option>
                                </select>
                                @error('rule_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="action_type" class="form-label">Acción</label>
                                <select class="form-select @error('action_type') is-invalid @enderror" 
                                        id="action_type" name="action_type" required>
                                    <option value="">Seleccionar acción</option>
                                    <option value="add_fixed" {{ old('action_type', $priceRule->action_type) == 'add_fixed' ? 'selected' : '' }}>
                                        Sumar importe fijo
                                    </option>
                                    <option value="add_percentage" {{ old('action_type', $priceRule->action_type) == 'add_percentage' ? 'selected' : '' }}>
                                        Sumar porcentaje
                                    </option>
                                    <option value="multiply" {{ old('action_type', $priceRule->action_type) == 'multiply' ? 'selected' : '' }}>
                                        Multiplicar
                                    </option>
                                    <option value="set_fixed" {{ old('action_type', $priceRule->action_type) == 'set_fixed' ? 'selected' : '' }}>
                                        Fijar precio
                                    </option>
                                    <option value="set_percentage" {{ old('action_type', $priceRule->action_type) == 'set_percentage' ? 'selected' : '' }}>
                                        Fijar porcentaje
                                    </option>
                                </select>
                                @error('action_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="action_value" class="form-label">Valor</label>
                                <input type="number" class="form-control @error('action_value') is-invalid @enderror" 
                                       id="action_value" name="action_value" value="{{ old('action_value', $priceRule->action_value) }}" 
                                       step="0.01" required>
                                @error('action_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Condiciones -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-funnel"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Condiciones</h5>
                            <small>Criterios para aplicar la regla</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="conditions-container">
                        <!-- Las condiciones se cargarán dinámicamente según el tipo de regla -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
        <div class="col-lg-4">
            <!-- Alcance -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-target"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Alcance</h5>
                            <small>Dónde se aplica la regla</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Producto Específico</label>
                        <select class="form-select @error('product_id') is-invalid @enderror" 
                                id="product_id" name="product_id">
                            <option value="">Todos los productos</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', $priceRule->product_id) == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Categoría</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $priceRule->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="quantity_min" class="form-label">Cantidad mínima</label>
                                <input type="number" class="form-control @error('quantity_min') is-invalid @enderror" 
                                       id="quantity_min" name="quantity_min" value="{{ old('quantity_min', $priceRule->quantity_min) }}" min="1">
                                @error('quantity_min')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="quantity_max" class="form-label">Cantidad máxima</label>
                                <input type="number" class="form-control @error('quantity_max') is-invalid @enderror" 
                                       id="quantity_max" name="quantity_max" value="{{ old('quantity_max', $priceRule->quantity_max) }}" min="1">
                                @error('quantity_max')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vigencia -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-calendar-range"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Vigencia</h5>
                            <small>Período de validez</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="valid_from" class="form-label">Válida desde</label>
                        <input type="date" class="form-control @error('valid_from') is-invalid @enderror" 
                               id="valid_from" name="valid_from" value="{{ old('valid_from', $priceRule->valid_from ? $priceRule->valid_from->format('Y-m-d') : '') }}">
                        @error('valid_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="valid_until" class="form-label">Válida hasta</label>
                        <input type="date" class="form-control @error('valid_until') is-invalid @enderror" 
                               id="valid_until" name="valid_until" value="{{ old('valid_until', $priceRule->valid_until ? $priceRule->valid_until->format('Y-m-d') : '') }}">
                        @error('valid_until')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-warning btn-lg w-100 mb-2">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Regla
                    </button>
                    <a href="{{ route('admin.price-rules.index') }}" class="btn btn-outline-secondary w-100">
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
    const ruleTypeSelect = document.getElementById('rule_type');
    const conditionsContainer = document.getElementById('conditions-container');
    
    // Datos de atributos agrupados por tipo
    const attributesByType = @json($attributes->map(function($attrs, $type) {
        return $attrs->map(function($attr) {
            return ['id' => $attr->id, 'name' => $attr->name, 'value' => $attr->value];
        });
    }));

    // Condiciones existentes de la regla
    const existingConditions = @json($priceRule->conditions ?? []);

    ruleTypeSelect.addEventListener('change', function() {
        loadConditionsForm(this.value);
    });

    function loadConditionsForm(ruleType) {
        let html = '';
        
        switch(ruleType) {
            case 'combination':
                html = `
                    <div class="mb-3">
                        <label class="form-label">Combinaciones de Atributos</label>
                        <p class="text-muted small">Selecciona los atributos que deben estar presentes para aplicar la regla.</p>
                        <div id="combination-attributes">
                `;
                
                // Si hay atributos existentes, cargarlos
                if (existingConditions.attributes && existingConditions.attributes.length > 0) {
                    existingConditions.attributes.forEach((attr, index) => {
                        html += `
                            <div class="row mb-3">
                                <div class="col-4">
                                    <select class="form-select" name="conditions[attributes][${index}][type]" onchange="loadAttributeOptions(this, ${index})">
                                        <option value="">Tipo de atributo</option>
                                        ${Object.keys(attributesByType).map(type => 
                                            `<option value="${type}" ${attr.type === type ? 'selected' : ''}>${type}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select class="form-select" name="conditions[attributes][${index}][id]">
                                        <option value="">Selecciona atributo</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    ${index === 0 ? 
                                        `<button type="button" class="btn btn-outline-success" onclick="addAttributeRow()"><i class="bi bi-plus"></i></button>` :
                                        `<button type="button" class="btn btn-outline-danger" onclick="removeAttributeRow(this)"><i class="bi bi-trash"></i></button>`
                                    }
                                </div>
                            </div>
                        `;
                    });
                } else {
                    // Agregar fila por defecto
                    html += `
                        <div class="row mb-3">
                            <div class="col-4">
                                <select class="form-select" name="conditions[attributes][0][type]" onchange="loadAttributeOptions(this, 0)">
                                    <option value="">Tipo de atributo</option>
                                    ${Object.keys(attributesByType).map(type => `<option value="${type}">${type}</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-6">
                                <select class="form-select" name="conditions[attributes][0][id]" disabled>
                                    <option value="">Selecciona primero el tipo</option>
                                </select>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-outline-success" onclick="addAttributeRow()">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }
                
                html += `
                        </div>
                    </div>
                `;
                break;
                
            case 'volume':
                const volumeMin = existingConditions.volume_min || '';
                html = `
                    <div class="mb-3">
                        <label for="volume_min" class="form-label">Cantidad mínima para descuento</label>
                        <input type="number" class="form-control" name="conditions[volume_min]" 
                               value="${volumeMin}" min="1" required>
                    </div>
                `;
                break;
                
            case 'attribute_specific':
                const attrType = existingConditions.attribute_type || '';
                const attrId = existingConditions.attribute_id || '';
                html = `
                    <div class="mb-3">
                        <label class="form-label">Atributo Específico</label>
                        <div class="row">
                            <div class="col-6">
                                <select class="form-select" name="conditions[attribute_type]" onchange="loadSpecificAttributeOptions(this)">
                                    <option value="">Tipo de atributo</option>
                                    ${Object.keys(attributesByType).map(type => 
                                        `<option value="${type}" ${attrType === type ? 'selected' : ''}>${type}</option>`
                                    ).join('')}
                                </select>
                            </div>
                            <div class="col-6">
                                <select class="form-select" name="conditions[attribute_id]" ${!attrType ? 'disabled' : ''} required>
                                    <option value="">Selecciona el atributo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'conditional':
                const customConditions = existingConditions.custom ? JSON.stringify(existingConditions.custom, null, 2) : '';
                html = `
                    <div class="mb-3">
                        <label class="form-label">Condiciones</label>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Las reglas condicionales requieren configuración avanzada. 
                            Define las condiciones específicas en formato JSON.
                        </div>
                        <textarea class="form-control" name="conditions[custom]" rows="5" 
                                  placeholder='{"condition": "value", "operator": "equals"}'>${customConditions}</textarea>
                    </div>
                `;
                break;
                
            default:
                html = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Selecciona un tipo de regla para configurar las condiciones.
                    </div>
                `;
        }
        
        conditionsContainer.innerHTML = html;
        
        // Cargar opciones de atributos específicos si es necesario
        if (ruleType === 'attribute_specific' && attrType) {
            setTimeout(() => loadSpecificAttributeOptionsWithValue(attrType, attrId), 100);
        }
        
        // Cargar opciones de atributos para combinaciones existentes
        if (ruleType === 'combination' && existingConditions.attributes) {
            existingConditions.attributes.forEach((attr, index) => {
                if (attr.type) {
                    setTimeout(() => loadAttributeOptionsWithValue(attr.type, index, attr.id), 100);
                }
            });
        }
    }

    function loadSpecificAttributeOptionsWithValue(type, selectedId) {
        const attributeSelect = document.querySelector(`select[name="conditions[attribute_id]"]`);
        if (type && attributesByType[type]) {
            attributeSelect.disabled = false;
            attributeSelect.innerHTML = '<option value="">Selecciona atributo</option>';
            attributesByType[type].forEach(attr => {
                const selected = attr.id == selectedId ? 'selected' : '';
                attributeSelect.innerHTML += `<option value="${attr.id}" ${selected}>${attr.name} (${attr.value})</option>`;
            });
        }
    }

    function loadAttributeOptionsWithValue(type, index, selectedId) {
        const attributeSelect = document.querySelector(`select[name="conditions[attributes][${index}][id]"]`);
        if (type && attributesByType[type]) {
            attributeSelect.disabled = false;
            attributeSelect.innerHTML = '<option value="">Selecciona atributo</option>';
            attributesByType[type].forEach(attr => {
                const selected = attr.id == selectedId ? 'selected' : '';
                attributeSelect.innerHTML += `<option value="${attr.id}" ${selected}>${attr.name} (${attr.value})</option>`;
            });
        }
    }

    // Funciones globales (mismas que en create)
    window.loadAttributeOptions = function(select, index) {
        const type = select.value;
        const attributeSelect = document.querySelector(`select[name="conditions[attributes][${index}][id]"]`);
        
        if (type && attributesByType[type]) {
            attributeSelect.disabled = false;
            attributeSelect.innerHTML = '<option value="">Selecciona atributo</option>';
            attributesByType[type].forEach(attr => {
                attributeSelect.innerHTML += `<option value="${attr.id}">${attr.name} (${attr.value})</option>`;
            });
        } else {
            attributeSelect.disabled = true;
            attributeSelect.innerHTML = '<option value="">Selecciona primero el tipo</option>';
        }
    };

    window.loadSpecificAttributeOptions = function(select) {
        const type = select.value;
        const attributeSelect = document.querySelector(`select[name="conditions[attribute_id]"]`);
        
        if (type && attributesByType[type]) {
            attributeSelect.disabled = false;
            attributeSelect.innerHTML = '<option value="">Selecciona atributo</option>';
            attributesByType[type].forEach(attr => {
                attributeSelect.innerHTML += `<option value="${attr.id}">${attr.name} (${attr.value})</option>`;
            });
        } else {
            attributeSelect.disabled = true;
            attributeSelect.innerHTML = '<option value="">Selecciona primero el tipo</option>';
        }
    };

    window.addAttributeRow = function() {
        const container = document.getElementById('combination-attributes');
        const currentRows = container.querySelectorAll('.row').length;
        const newIndex = currentRows;
        
        const newRow = document.createElement('div');
        newRow.className = 'row mb-3';
        newRow.innerHTML = `
            <div class="col-4">
                <select class="form-select" name="conditions[attributes][${newIndex}][type]" onchange="loadAttributeOptions(this, ${newIndex})">
                    <option value="">Tipo de atributo</option>
                    ${Object.keys(attributesByType).map(type => `<option value="${type}">${type}</option>`).join('')}
                </select>
            </div>
            <div class="col-6">
                <select class="form-select" name="conditions[attributes][${newIndex}][id]" disabled>
                    <option value="">Selecciona primero el tipo</option>
                </select>
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger" onclick="removeAttributeRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        
        container.appendChild(newRow);
    };

    window.removeAttributeRow = function(button) {
        button.closest('.row').remove();
    };

    // Cargar condiciones existentes al cargar la página
    if (ruleTypeSelect.value) {
        loadConditionsForm(ruleTypeSelect.value);
    }
});
</script>
@endpush