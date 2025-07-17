@extends('layouts.admin')

@section('title', 'Crear Producto')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Crear Nuevo Producto</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Información básica -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                       id="sku" name="sku" value="{{ old('sku') }}" required>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
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
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                                {{ old('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
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

                    <!-- Especificaciones del producto -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('color') is-invalid @enderror" 
                                       id="color" name="color" value="{{ old('color') }}" required>
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="material" class="form-label">Material <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('material') is-invalid @enderror" 
                                       id="material" name="material" value="{{ old('material') }}" required>
                                @error('material')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="printing_system" class="form-label">Sistema de Impresión <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('printing_system') is-invalid @enderror" 
                                       id="printing_system" name="printing_system" value="{{ old('printing_system') }}" required>
                                @error('printing_system')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tamaños -->
                    <div class="mb-3">
                        <label class="form-label">Tamaños Disponibles <span class="text-danger">*</span></label>
                        <div id="sizes-container">
                            @if(old('sizes'))
                                @foreach(old('sizes') as $index => $size)
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

                    <!-- Impresión -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="face_count" class="form-label">Número de Caras <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('face_count') is-invalid @enderror" 
                                       id="face_count" name="face_count" value="{{ old('face_count', 1) }}" min="1" required>
                                @error('face_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="print_colors_count" class="form-label">Número de Colores de Impresión <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('print_colors_count') is-invalid @enderror" 
                                       id="print_colors_count" name="print_colors_count" value="{{ old('print_colors_count', 1) }}" min="1" required>
                                @error('print_colors_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Colores de impresión -->
                    <div class="mb-3">
                        <label class="form-label">Colores de Impresión <span class="text-danger">*</span></label>
                        <div id="print-colors-container">
                            @if(old('print_colors'))
                                @foreach(old('print_colors') as $index => $color)
                                    <div class="input-group mb-2 print-color-row">
                                        <input type="text" class="form-control" name="print_colors[]" value="{{ $color }}" 
                                               placeholder="Ej: Negro, Azul, Rojo">
                                        <button type="button" class="btn btn-outline-danger remove-print-color">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <div class="input-group mb-2 print-color-row">
                                    <input type="text" class="form-control" name="print_colors[]" placeholder="Ej: Negro, Azul, Rojo">
                                    <button type="button" class="btn btn-outline-danger remove-print-color">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-print-color">
                            <i class="bi bi-plus-circle me-1"></i>Agregar Color
                        </button>
                        @error('print_colors')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Archivos -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="images" class="form-label">Imágenes del Producto</label>
                                <input type="file" class="form-control @error('images') is-invalid @enderror" 
                                       id="images" name="images[]" accept="image/*" multiple>
                                <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB por imagen.</div>
                                @error('images')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="model_3d" class="form-label">Modelo 3D</label>
                                <input type="file" class="form-control @error('model_3d') is-invalid @enderror" 
                                       id="model_3d" name="model_3d" accept=".glb,.gltf">
                                <div class="form-text">Formatos permitidos: GLB, GLTF. Tamaño máximo: 10MB.</div>
                                @error('model_3d')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Precios -->
                    <div class="mb-3">
                        <label class="form-label">Tabla de Precios <span class="text-danger">*</span></label>
                        <div id="pricing-container">
                            @if(old('pricing'))
                                @foreach(old('pricing') as $index => $pricing)
                                    <div class="row mb-2 pricing-row">
                                        <div class="col-md-2">
                                            <input type="number" class="form-control" name="pricing[{{ $index }}][quantity_from]" 
                                                   value="{{ $pricing['quantity_from'] ?? '' }}" placeholder="Desde" min="1">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control" name="pricing[{{ $index }}][quantity_to]" 
                                                   value="{{ $pricing['quantity_to'] ?? '' }}" placeholder="Hasta" min="1">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="pricing[{{ $index }}][price]" 
                                                   value="{{ $pricing['price'] ?? '' }}" placeholder="Precio Total" step="0.01" min="0">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="pricing[{{ $index }}][unit_price]" 
                                                   value="{{ $pricing['unit_price'] ?? '' }}" placeholder="Precio Unitario" step="0.01" min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger remove-pricing">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row mb-2 pricing-row">
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" name="pricing[0][quantity_from]" placeholder="Desde" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" name="pricing[0][quantity_to]" placeholder="Hasta" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" name="pricing[0][price]" placeholder="Precio Total" step="0.01" min="0">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" name="pricing[0][unit_price]" placeholder="Precio Unitario" step="0.01" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-pricing">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-pricing">
                            <i class="bi bi-plus-circle me-1"></i>Agregar Rango de Precio
                        </button>
                        @error('pricing')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                   {{ old('active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Producto activo
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Crear Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let sizeIndex = {{ old('sizes') ? count(old('sizes')) : 1 }};
    let printColorIndex = {{ old('print_colors') ? count(old('print_colors')) : 1 }};
    let pricingIndex = {{ old('pricing') ? count(old('pricing')) : 1 }};

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
    });

    // Filtrar subcategorías por categoría
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        const subcategorySelect = document.getElementById('subcategory_id');
        const options = subcategorySelect.querySelectorAll('option[data-category]');
        
        // Ocultar todas las subcategorías
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
});
</script>
@endpush
