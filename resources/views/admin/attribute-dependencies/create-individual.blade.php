@extends('layouts.admin')

@section('title', 'Nuevo Modificador Individual')

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
                <li class="breadcrumb-item active" aria-current="page">Modificador Individual</li>
            </ol>
        </nav>

        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-info text-white rounded me-3">
                <i class="bi bi-currency-euro"></i>
            </div>
            <div>
                <h2 class="mb-0">Nuevo Modificador Individual</h2>
                <small class="text-muted">Crea un modificador de precio que se aplica automáticamente al seleccionar un atributo</small>
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
<form method="POST" action="{{ route('admin.attribute-dependencies.store-individual') }}" id="individualForm">
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
                            <small>Selecciona el producto para este modificador (opcional - aplica a todos si no se selecciona)</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Producto</label>
                        <select class="form-select @error('product_id') is-invalid @enderror"
                                id="product_id" name="product_id">
                            <option value="">Todos los productos (modificador global)</option>
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

            <!-- Selección de atributo -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-tag"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Atributo</h5>
                            <small>Selecciona el atributo que tendrá un modificador de precio individual</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="attribute_type" class="form-label">Tipo de Atributo</label>
                                <select class="form-select" id="attribute_type" name="attribute_type" required>
                                    <option value="">Seleccionar tipo</option>
                                    @foreach($attributesByType as $type => $attributes)
                                        <option value="{{ $type }}">{{ $typeLabels[$type] ?? $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parent_attribute_id" class="form-label">Atributo *</label>
                                <select class="form-select @error('parent_attribute_id') is-invalid @enderror"
                                        id="parent_attribute_id" name="parent_attribute_id" required disabled>
                                    <option value="">Selecciona primero el tipo</option>
                                </select>
                                @error('parent_attribute_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modificador de precio -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Modificador de Precio</h5>
                            <small>Define cuánto afecta este atributo al precio unitario</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price_modifier" class="form-label">Modificador Fijo (€)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('price_modifier') is-invalid @enderror"
                                           id="price_modifier" name="price_modifier" step="0.0001"
                                           value="{{ old('price_modifier') }}"
                                           placeholder="Ej: 0.01">
                                    <span class="input-group-text">€</span>
                                    @error('price_modifier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price_percentage" class="form-label">Modificador Porcentual (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('price_percentage') is-invalid @enderror"
                                           id="price_percentage" name="price_percentage" step="0.01"
                                           value="{{ old('price_percentage') }}"
                                           placeholder="Ej: 10">
                                    <span class="input-group-text">%</span>
                                    @error('price_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-text mb-3">
                        <i class="bi bi-info-circle text-info me-1"></i>
                        Especifica un modificador fijo (€) y/o porcentual (%). Ej: 10% = +10% sobre el precio.
                    </div>

                    <!-- Aplicar precio a -->
                    <div class="mb-3">
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
                            <strong>Unitario:</strong> Se multiplica por cantidad. <strong>Total:</strong> Se suma una sola vez al total.
                        </small>
                    </div>

                    <!-- Ejemplos -->
                    <div class="alert alert-light">
                        <h6 class="alert-heading">Ejemplos:</h6>
                        <ul class="mb-0">
                            <li><strong>+0.01</strong> → Aumenta 1 céntimo por unidad</li>
                            <li><strong>+1.50</strong> → Aumenta 1.50€ por unidad</li>
                            <li><strong>-0.05</strong> → Descuento de 5 céntimos por unidad</li>
                            <li><strong>-2.00</strong> → Descuento de 2€ por unidad</li>
                        </ul>
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
                            <small>Resumen del modificador</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="preview-content" class="text-center text-muted">
                        <i class="bi bi-currency-euro display-4"></i>
                        <div class="mt-2">Completa el formulario para ver la vista previa</div>
                    </div>
                </div>
            </div>

            <!-- Configuración adicional -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-gear"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Configuración</h5>
                            <small>Opciones adicionales</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
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
                    <button type="submit" class="btn btn-info btn-lg w-100 mb-2">
                        <i class="bi bi-check-circle me-2"></i>Crear Modificador Individual
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
    const attributesByType = @json($attributesByType);
    const typeLabels = @json($typeLabels);

    const productSelect = document.getElementById('product_id');
    const typeSelect = document.getElementById('attribute_type');
    const attributeSelect = document.getElementById('parent_attribute_id');
    const priceInput = document.getElementById('price_modifier');
    const previewContent = document.getElementById('preview-content');

    // Cargar atributos cuando se selecciona el tipo
    typeSelect.addEventListener('change', function() {
        const type = this.value;

        if (type && attributesByType[type]) {
            attributeSelect.disabled = false;
            attributeSelect.innerHTML = '<option value="">Selecciona un atributo</option>';

            attributesByType[type].forEach(attr => {
                const option = document.createElement('option');
                option.value = attr.id;
                option.textContent = `${attr.name}${attr.value ? ' (' + attr.value + ')' : ''}`;
                attributeSelect.appendChild(option);
            });
        } else {
            attributeSelect.disabled = true;
            attributeSelect.innerHTML = '<option value="">Selecciona primero el tipo</option>';
        }

        updatePreview();
    });

    // Actualizar vista previa
    function updatePreview() {
        const productId = productSelect.value;
        const attributeId = attributeSelect.value;
        const price = parseFloat(priceInput.value) || 0;

        if (!attributeId) {
            previewContent.innerHTML = `
                <i class="bi bi-currency-euro display-4 text-muted"></i>
                <div class="mt-2 text-muted">Selecciona un atributo para ver la vista previa</div>
            `;
            return;
        }

        const productName = productId ? productSelect.options[productSelect.selectedIndex].text : 'Todos los productos';
        const attributeName = attributeSelect.options[attributeSelect.selectedIndex].text;

        const priceText = price > 0 ?
            `<span class="text-success">+${price.toFixed(4)}€</span>` :
            price < 0 ?
            `<span class="text-danger">${price.toFixed(4)}€</span>` :
            `<span class="text-muted">Sin cambio</span>`;

        previewContent.innerHTML = `
            <div class="text-center">
                <div class="badge bg-info text-white mb-3">Modificador Individual</div>
                <div class="text-start">
                    <p class="mb-2"><strong>Producto:</strong><br><small>${productName}</small></p>
                    <p class="mb-2"><strong>Atributo:</strong><br>${attributeName}</p>
                    <p class="mb-0"><strong>Precio:</strong><br>${priceText} por unidad</p>
                </div>
                <div class="mt-3 text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    <small>Se aplicará automáticamente al seleccionar este atributo</small>
                </div>
            </div>
        `;
    }

    // Event listeners para actualizar preview
    productSelect.addEventListener('change', updatePreview);
    attributeSelect.addEventListener('change', updatePreview);
    priceInput.addEventListener('input', updatePreview);

    // Inicializar
    updatePreview();
});
</script>
@endpush