@extends('layouts.admin')

@section('title', 'Editar Producto')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Producto: {{ $product->name }}</h5>
            </div>
            <div class="card-body">
                @php
                    $availableColors = \App\Models\AvailableColor::where('active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->get();
                    $productColors = old('colors', $product->colors ?? []);
                @endphp

                <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Información básica -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                       id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Categorización -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">Seleccionar categoría</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subcategory_id" class="form-label">Subcategoría <span class="text-danger">*</span></label>
                                <select class="form-select @error('subcategory_id') is-invalid @enderror" 
                                        id="subcategory_id" name="subcategory_id" required>
                                    <option value="">Seleccionar subcategoría</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}" 
                                                data-category="{{ $subcategory->category_id }}"
                                                {{ old('subcategory_id', $product->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subcategory_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Colores disponibles -->
                    <div class="mb-3">
                        <label class="form-label">Colores Disponibles <span class="text-danger">*</span></label>
                        
                        <!-- Selector de colores dinámico -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Agregar Nuevo Color</h6>
                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <label for="new_color_name" class="form-label">Nombre del Color</label>
                                        <input type="text" class="form-control" id="new_color_name" 
                                               placeholder="Ej: Verde Lima">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="new_color_hex" class="form-label">Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" 
                                                   id="new_color_hex" value="#000000">
                                            <input type="text" class="form-control" id="new_color_hex_text" 
                                                   value="#000000" placeholder="#000000" maxlength="7">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-primary" id="add_available_color">
                                            <i class="bi bi-plus-circle me-1"></i>Agregar Color
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de colores disponibles -->
                        <div id="available-colors-container">
                            <div class="row" id="colors-list">
                                @foreach($availableColors as $color)
                                    <div class="col-md-3 mb-3 color-item" data-color-id="{{ $color->id }}">
                                        <div class="form-check">
                                            <input class="form-check-input color-checkbox" type="checkbox" 
                                                   name="colors[]" value="{{ $color->name }}" 
                                                   id="color_{{ $color->id }}"
                                                   data-hex="{{ $color->hex_code }}"
                                                   {{ in_array($color->name, $productColors) ? 'checked' : '' }}>
                                            <label class="form-check-label d-flex align-items-center" 
                                                   for="color_{{ $color->id }}">
                                                <span class="color-preview me-2" 
                                                      style="display: inline-block; width: 20px; height: 20px; 
                                                             background-color: {{ $color->hex_code }}; 
                                                             border: 1px solid #ddd; border-radius: 3px;">
                                                </span>
                                                {{ $color->name }}
                                                <button type="button" class="btn btn-link btn-sm text-danger ms-auto remove-color" 
                                                        data-color-id="{{ $color->id }}" title="Eliminar color">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Colores seleccionados (resumen visual) -->
                        <div class="mt-3">
                            <label class="form-label">Colores Seleccionados:</label>
                            <div id="selected-colors-preview" class="d-flex flex-wrap gap-2">
                                <!-- Se llenará dinámicamente con JavaScript -->
                            </div>
                        </div>

                        @error('colors')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Material -->
                    <div class="mb-3">
                        <label for="material" class="form-label">Material <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('material') is-invalid @enderror" 
                               id="material" name="material" value="{{ old('material', $product->material) }}" required>
                        @error('material')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tamaños -->
                    <div class="mb-3">
                        <label class="form-label">Tamaños Disponibles <span class="text-danger">*</span></label>
                        <div id="sizes-container">
                            @if(old('sizes', $product->sizes))
                                @foreach(old('sizes', $product->sizes) as $index => $size)
                                    <div class="input-group mb-2 size-row">
                                        <input type="text" class="form-control" name="sizes[]" value="{{ $size }}" 
                                               placeholder="Ej: S, M, L, XL">
                                        <button type="button" class="btn btn-outline-danger remove-size">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <div class="input-group mb-2 size-row">
                                    <input type="text" class="form-control" name="sizes[]" placeholder="Ej: S, M, L, XL">
                                    <button type="button" class="btn btn-outline-danger remove-size">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-size">
                            <i class="bi bi-plus-circle me-1"></i>Agregar Tamaño
                        </button>
                        @error('sizes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Resto del formulario igual que create.blade.php -->
                    <!-- ... continúa con los demás campos ... -->

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Actualizar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CSS para los colores -->
<style>
    .form-control-color {
        width: 50px;
        height: 38px;
        padding: 0.375rem;
        cursor: pointer;
    }
    
    .color-preview {
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        cursor: pointer;
    }
    
    .form-check-label:hover .color-preview {
        transform: scale(1.1);
        transition: transform 0.2s;
    }
    
    .color-item .form-check-label {
        width: 100%;
        cursor: pointer;
        padding: 5px;
        border-radius: 4px;
    }
    
    .color-item .form-check-label:hover {
        background-color: #f8f9fa;
    }
    
    .selected-color-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 20px;
        font-size: 0.875rem;
    }
    
    .selected-color-badge .color-dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 5px;
        border: 1px solid #ddd;
    }
    
    .remove-color {
        padding: 0;
        margin: 0;
        line-height: 1;
    }
    
    .remove-color:hover {
        color: #dc3545 !important;
    }
</style>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    let sizeIndex = {{ $product->sizes ? count($product->sizes) : 1 }};
    let printColorIndex = {{ $product->print_colors ? count($product->print_colors) : 1 }};
    let pricingIndex = {{ $product->pricing->count() ?: 1 }};

    // Manejo de colores personalizados
    const customCheckbox = document.getElementById('color_personalizado');
    const customColorsContainer = document.getElementById('custom-colors-container');
    
    // Mostrar/ocultar colores personalizados
    function toggleCustomColors() {
        if (customCheckbox && customCheckbox.checked) {
            customColorsContainer.style.display = 'block';
            // Si no hay campos personalizados, agregar uno
            if (document.querySelectorAll('.custom-color-row').length === 0) {
                document.getElementById('add-custom-color').click();
            }
        } else {
            customColorsContainer.style.display = 'none';
        }
    }
    
    if (customCheckbox) {
        customCheckbox.addEventListener('change', toggleCustomColors);
    }
    
    // Agregar color personalizado
    document.getElementById('add-custom-color').addEventListener('click', function() {
        const customColorsList = document.getElementById('custom-colors-list');
        const newRow = document.createElement('div');
        newRow.className = 'input-group mb-2 custom-color-row';
        newRow.innerHTML = `
            <input type="text" class="form-control" name="custom_colors[]" 
                   placeholder="Ej: Verde Lima">
            <button type="button" class="btn btn-outline-danger remove-custom-color">
                <i class="bi bi-trash"></i>
            </button>
        `;
        customColorsList.appendChild(newRow);
    });

    // Gestión de tamaños
    document.getElementById('add-size').addEventListener('click', function() {
        const container = document.getElementById('sizes-container');
        const newRow = document.createElement('div');
        newRow.className = 'input-group mb-2 size-row';
        newRow.innerHTML = `
            <input type="text" class="form-control" name="sizes[]" placeholder="Ej: S, M, L, XL">
            <button type="button" class="btn btn-outline-danger remove-size">
                <i class="bi bi-trash"></i>
            </button>
        `;
        container.appendChild(newRow);
        sizeIndex++;
    });

    // Gestión de colores de impresión
    document.getElementById('add-print-color').addEventListener('click', function() {
        const container = document.getElementById('print-colors-container');
        const newRow = document.createElement('div');
        newRow.className = 'input-group mb-2 print-color-row';
        newRow.innerHTML = `
            <input type="text" class="form-control" name="print_colors[]" placeholder="Ej: Negro, Azul, Rojo">
            <button type="button" class="btn btn-outline-danger remove-print-color">
                <i class="bi bi-trash"></i>
            </button>
        `;
        container.appendChild(newRow);
        printColorIndex++;
    });

    // Gestión de precios
    document.getElementById('add-pricing').addEventListener('click', function() {
        const container = document.getElementById('pricing-container');
        const newRow = document.createElement('div');
        newRow.className = 'row mb-2 pricing-row';
        newRow.innerHTML = `
            <div class="col-md-2">
                <input type="number" class="form-control" name="pricing[${pricingIndex}][quantity_from]" placeholder="Desde" min="1">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="pricing[${pricingIndex}][quantity_to]" placeholder="Hasta" min="1">
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="pricing[${pricingIndex}][price]" placeholder="Precio Total" step="0.01" min="0">
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="pricing[${pricingIndex}][unit_price]" placeholder="Precio Unitario" step="0.01" min="0">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger remove-pricing">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        pricingIndex++;
    });

    // Event delegation para remover elementos
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-size')) {
            e.target.closest('.size-row').remove();
        }
        if (e.target.closest('.remove-print-color')) {
            e.target.closest('.print-color-row').remove();
        }
        if (e.target.closest('.remove-pricing')) {
            e.target.closest('.pricing-row').remove();
        }
        if (e.target.closest('.remove-custom-color')) {
            e.target.closest('.custom-color-row').remove();
        }
    });

    // Filtrar subcategorías por categoría
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        const subcategorySelect = document.getElementById('subcategory_id');
        const options = subcategorySelect.querySelectorAll('option[data-category]');
        
        // Mostrar todas las subcategorías de la categoría seleccionada
        options.forEach(option => {
            if (option.dataset.category === categoryId || categoryId === '') {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Resetear selección si no es válida
        if (categoryId !== '' && subcategorySelect.value !== '') {
            const selectedOption = subcategorySelect.querySelector(`option[value="${subcategorySelect.value}"]`);
            if (selectedOption && selectedOption.dataset.category !== categoryId) {
                subcategorySelect.value = '';
            }
        }
    });

    // Sincronizar número de colores con campos
    document.getElementById('print_colors_count').addEventListener('change', function() {
        const count = parseInt(this.value);
        const container = document.getElementById('print-colors-container');
        const currentRows = container.querySelectorAll('.print-color-row');
        
        if (count > currentRows.length) {
            // Agregar filas
            for (let i = currentRows.length; i < count; i++) {
                document.getElementById('add-print-color').click();
            }
        } else if (count < currentRows.length) {
            // Remover filas extras
            for (let i = currentRows.length - 1; i >= count; i--) {
                currentRows[i].remove();
            }
        }
    });

    // Validar que al menos un color esté seleccionado
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const checkedColors = document.querySelectorAll('.color-checkbox:checked');
        const customColors = document.querySelectorAll('input[name="custom_colors[]"]');
        let hasValidCustomColor = false;
        
        customColors.forEach(input => {
            if (input.value.trim() !== '') {
                hasValidCustomColor = true;
            }
        });
        
        if (checkedColors.length === 0 || 
            (customCheckbox && customCheckbox.checked && !hasValidCustomColor)) {
            e.preventDefault();
            alert('Por favor selecciona al menos un color');
        }
    });

    // Ejecutar filtro de subcategorías al cargar la página
    const categorySelect = document.getElementById('category_id');
    if (categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush