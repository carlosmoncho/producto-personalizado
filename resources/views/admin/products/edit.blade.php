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

                    <!-- Material y sistema de impresión -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="material" class="form-label">Material <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('material') is-invalid @enderror" 
                                       id="material" name="material" value="{{ old('material', $product->material) }}" required>
                                @error('material')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="printing_system" class="form-label">Sistema de Impresión <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('printing_system') is-invalid @enderror" 
                                       id="printing_system" name="printing_system" value="{{ old('printing_system', $product->printing_system) }}" required>
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

                    <!-- Impresión -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="face_count" class="form-label">Número de Caras <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('face_count') is-invalid @enderror" 
                                       id="face_count" name="face_count" value="{{ old('face_count', $product->face_count) }}" min="1" required>
                                @error('face_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="print_colors_count" class="form-label">Número de Colores de Impresión <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('print_colors_count') is-invalid @enderror" 
                                       id="print_colors_count" name="print_colors_count" value="{{ old('print_colors_count', $product->print_colors_count) }}" min="1" required>
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
                            @if(old('print_colors', $product->print_colors))
                                @foreach(old('print_colors', $product->print_colors) as $index => $color)
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
                                @if($product->getImagesUrls())
                                    <div class="mb-2">
                                        <p class="text-muted">Imágenes actuales:</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($product->getImagesUrls() as $imageUrl)
                                                <img src="{{ $imageUrl }}" alt="Imagen del producto" 
                                                     class="img-thumbnail" style="max-width: 100px;">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('images') is-invalid @enderror" 
                                       id="images" name="images[]" accept="image/*" multiple>
                                <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB por imagen. Dejar vacío para mantener las imágenes actuales.</div>
                                @error('images')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="model_3d" class="form-label">Modelo 3D</label>
                                @if($product->getModel3DUrl())
                                    <div class="mb-2">
                                        <p class="text-muted">Modelo 3D actual: 
                                            <a href="{{ $product->getModel3DUrl() }}" target="_blank">Ver archivo</a>
                                        </p>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('model_3d') is-invalid @enderror" 
                                       id="model_3d" name="model_3d" accept=".glb,.gltf">
                                <div class="form-text">Formatos permitidos: GLB, GLTF. Tamaño máximo: 10MB. Dejar vacío para mantener el modelo actual.</div>
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
                            @if(old('pricing', $product->pricing))
                                @foreach(old('pricing', $product->pricing) as $index => $pricing)
                                    <div class="row mb-2 pricing-row">
                                        <div class="col-md-2">
                                            <input type="number" class="form-control" name="pricing[{{ $index }}][quantity_from]" 
                                                   value="{{ $pricing['quantity_from'] ?? $pricing->quantity_from ?? '' }}" placeholder="Desde" min="1">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control" name="pricing[{{ $index }}][quantity_to]" 
                                                   value="{{ $pricing['quantity_to'] ?? $pricing->quantity_to ?? '' }}" placeholder="Hasta" min="1">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="pricing[{{ $index }}][price]" 
                                                   value="{{ $pricing['price'] ?? $pricing->price ?? '' }}" placeholder="Precio Total" step="0.01" min="0">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="pricing[{{ $index }}][unit_price]" 
                                                   value="{{ $pricing['unit_price'] ?? $pricing->unit_price ?? '' }}" placeholder="Precio Unitario" step="0.01" min="0">
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
                                   {{ old('active', $product->active) ? 'checked' : '' }}>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copiar toda la lógica de JavaScript del create.blade.php
    // Aquí va todo el JavaScript necesario para el formulario de edición
    
    let sizeIndex = {{ $product->sizes ? count($product->sizes) : 1 }};
    let printColorIndex = {{ $product->print_colors ? count($product->print_colors) : 1 }};
    let pricingIndex = {{ $product->pricing->count() }};

    // Todo el código JavaScript del formulario create adaptado para edit
    // ... (incluir todo el código JavaScript necesario)
    
    // Ejecutar filtro de subcategorías al cargar la página
    const categorySelect = document.getElementById('category_id');
    if (categorySelect && categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush