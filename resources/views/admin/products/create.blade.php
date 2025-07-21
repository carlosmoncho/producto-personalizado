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
                                @php
                                    $availableColors = \App\Models\AvailableColor::where('active', true)
                                        ->orderBy('sort_order')
                                        ->orderBy('name')
                                        ->get();
                                @endphp
                                @foreach($availableColors as $color)
                                    <div class="col-md-3 mb-3 color-item" data-color-id="{{ $color->id }}">
                                        <div class="form-check">
                                            <input class="form-check-input color-checkbox" type="checkbox" 
                                                   name="colors[]" value="{{ $color->name }}" 
                                                   id="color_{{ $color->id }}"
                                                   data-hex="{{ $color->hex_code }}"
                                                   {{ in_array($color->name, old('colors', [])) ? 'checked' : '' }}>
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

                    <!-- Material y sistema de impresión -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="material" class="form-label">Material <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('material') is-invalid @enderror" 
                                       id="material" name="material" value="{{ old('material') }}" required>
                                @error('material')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtener elementos del DOM con verificación
    const colorHexInput = document.getElementById('new_color_hex');
    const colorHexText = document.getElementById('new_color_hex_text');
    const colorNameInput = document.getElementById('new_color_name');
    const addColorBtn = document.getElementById('add_available_color');
    const colorsList = document.getElementById('colors-list');
    const selectedColorsPreview = document.getElementById('selected-colors-preview');

    // Verificar que todos los elementos existan
    if (!colorHexInput || !colorHexText || !colorNameInput || !addColorBtn || !colorsList || !selectedColorsPreview) {
        console.error('No se encontraron todos los elementos necesarios para el selector de colores');
        return;
    }

    // Sincronizar el selector de color con el campo de texto
    colorHexInput.addEventListener('input', function() {
        colorHexText.value = this.value.toUpperCase();
    });

    colorHexText.addEventListener('input', function() {
        const value = this.value.toUpperCase();
        if (/^#[0-9A-F]{6}$/i.test(value)) {
            colorHexInput.value = value;
        }
    });

    // Formatear el texto hex mientras se escribe
    colorHexText.addEventListener('keyup', function() {
        let value = this.value.toUpperCase();
        
        // Asegurar que empiece con #
        if (value.length > 0 && value[0] !== '#') {
            value = '#' + value;
        }
        
        // Remover caracteres no válidos
        value = value.replace(/[^#0-9A-F]/g, '');
        
        // Limitar a 7 caracteres
        if (value.length > 7) {
            value = value.substr(0, 7);
        }
        
        this.value = value;
    });

    // Agregar nuevo color disponible
    addColorBtn.addEventListener('click', function() {
        const colorName = colorNameInput.value.trim();
        const colorHex = colorHexText.value.toUpperCase();

        if (!colorName) {
            alert('Por favor ingresa un nombre para el color');
            colorNameInput.focus();
            return;
        }

        if (!/^#[0-9A-F]{6}$/i.test(colorHex)) {
            alert('Por favor ingresa un código de color válido (ej: #FF0000)');
            colorHexText.focus();
            return;
        }

        // Verificar si el color ya existe
        const existingColor = Array.from(document.querySelectorAll('.color-checkbox')).find(
            cb => cb.value.toLowerCase() === colorName.toLowerCase()
        );

        if (existingColor) {
            alert('Este color ya existe');
            return;
        }

        // Deshabilitar el botón mientras se procesa
        addColorBtn.disabled = true;
        addColorBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

        // Guardar el color en la base de datos via AJAX
        fetch('{{ route("admin.available-colors.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: colorName,
                hex_code: colorHex
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Agregar el color a la lista
                const colorId = data.color.id;
                const colorDiv = document.createElement('div');
                colorDiv.className = 'col-md-3 mb-3 color-item';
                colorDiv.setAttribute('data-color-id', colorId);
                colorDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input color-checkbox" type="checkbox" 
                               name="colors[]" value="${colorName}" 
                               id="color_${colorId}"
                               data-hex="${colorHex}">
                        <label class="form-check-label d-flex align-items-center" 
                               for="color_${colorId}">
                            <span class="color-preview me-2" 
                                  style="display: inline-block; width: 20px; height: 20px; 
                                         background-color: ${colorHex}; 
                                         border: 1px solid #ddd; border-radius: 3px;">
                            </span>
                            ${colorName}
                            <button type="button" class="btn btn-link btn-sm text-danger ms-auto remove-color" 
                                    data-color-id="${colorId}" title="Eliminar color">
                                <i class="bi bi-trash"></i>
                            </button>
                        </label>
                    </div>
                `;
                colorsList.appendChild(colorDiv);

                // Limpiar campos
                colorNameInput.value = '';
                colorHexInput.value = '#000000';
                colorHexText.value = '#000000';

                // Marcar el nuevo color como seleccionado
                setTimeout(() => {
                    const newCheckbox = document.getElementById(`color_${colorId}`);
                    if (newCheckbox) {
                        newCheckbox.checked = true;
                        updateSelectedColorsPreview();
                    }
                }, 100);

                // Mostrar mensaje de éxito
                showToast('Color agregado exitosamente', 'success');
            } else {
                alert('Error al guardar el color: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar el color. Por favor intenta de nuevo.');
        })
        .finally(() => {
            // Rehabilitar el botón
            addColorBtn.disabled = false;
            addColorBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i>Agregar Color';
        });
    });

    // Eliminar color disponible
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-color')) {
            e.preventDefault();
            const button = e.target.closest('.remove-color');
            const colorId = button.getAttribute('data-color-id');
            const colorItem = button.closest('.color-item');
            const checkbox = colorItem ? colorItem.querySelector('.color-checkbox') : null;
            const colorName = checkbox ? checkbox.value : 'este color';

            if (confirm(`¿Estás seguro de eliminar el color "${colorName}"?`)) {
                // Deshabilitar el botón
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch(`{{ url('admin/available-colors') }}/${colorId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (colorItem) {
                            colorItem.remove();
                            updateSelectedColorsPreview();
                        }
                        showToast('Color eliminado exitosamente', 'success');
                    } else {
                        alert(data.message || 'Error al eliminar el color');
                        button.disabled = false;
                        button.innerHTML = '<i class="bi bi-trash"></i>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el color');
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-trash"></i>';
                });
            }
        }
    });

    // Actualizar vista previa de colores seleccionados
    function updateSelectedColorsPreview() {
        if (!selectedColorsPreview) return;
        
        selectedColorsPreview.innerHTML = '';
        
        document.querySelectorAll('.color-checkbox:checked').forEach(checkbox => {
            const colorName = checkbox.value;
            const colorHex = checkbox.getAttribute('data-hex') || '#000000';
            
            const badge = document.createElement('div');
            badge.className = 'selected-color-badge';
            badge.innerHTML = `
                <span class="color-dot" style="background-color: ${colorHex}"></span>
                ${colorName}
            `;
            selectedColorsPreview.appendChild(badge);
        });

        if (selectedColorsPreview.innerHTML === '') {
            selectedColorsPreview.innerHTML = '<span class="text-muted">Ningún color seleccionado</span>';
        }
    }

    // Escuchar cambios en los checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('color-checkbox')) {
            updateSelectedColorsPreview();
        }
    });

    // Actualizar vista previa al cargar la página
    updateSelectedColorsPreview();

    // Validar que al menos un color esté seleccionado al enviar
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const checkedColors = document.querySelectorAll('.color-checkbox:checked');
            if (checkedColors.length === 0) {
                e.preventDefault();
                alert('Por favor selecciona al menos un color');
                const firstCheckbox = document.querySelector('.color-checkbox');
                if (firstCheckbox) {
                    firstCheckbox.focus();
                }
            }
        });
    }

    // Función para mostrar notificaciones toast
    function showToast(message, type = 'info') {
        // Crear contenedor de toast si no existe
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show`;
        toast.style.cssText = 'min-width: 250px; margin-bottom: 10px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Índices para campos dinámicos
    let sizeIndex = 1;
    let printColorIndex = 1;
    let pricingIndex = 1;

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

    // Si hay una categoría preseleccionada (por ejemplo, desde URL), ejecutar el filtro
    const categorySelect = document.getElementById('category_id');
    if (categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush