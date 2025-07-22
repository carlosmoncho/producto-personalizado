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
                        
                        <!-- Selector de colores de impresión dinámico -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Agregar Nuevo Color de Impresión</h6>
                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <label for="new_print_color_name" class="form-label">Nombre del Color</label>
                                        <input type="text" class="form-control" id="new_print_color_name" 
                                               placeholder="Ej: Pantone 123 C">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="new_print_color_hex" class="form-label">Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" 
                                                   id="new_print_color_hex" value="#000000">
                                            <input type="text" class="form-control" id="new_print_color_hex_text" 
                                                   value="#000000" placeholder="#000000" maxlength="7">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-primary" id="add_available_print_color">
                                            <i class="bi bi-plus-circle me-1"></i>Agregar Color
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de colores de impresión disponibles -->
                        <div id="available-print-colors-container">
                            <div class="row" id="print-colors-list">
                                @php
                                    $availablePrintColors = \App\Models\AvailablePrintColor::where('active', true)
                                        ->orderBy('sort_order')
                                        ->orderBy('name')
                                        ->get();
                                @endphp
                                @foreach($availablePrintColors as $color)
                                    <div class="col-md-3 mb-3 print-color-item" data-print-color-id="{{ $color->id }}">
                                        <div class="form-check">
                                            <input class="form-check-input print-color-checkbox" type="checkbox" 
                                                   name="print_colors[]" value="{{ $color->name }}" 
                                                   id="print_color_{{ $color->id }}"
                                                   data-hex="{{ $color->hex_code }}"
                                                   {{ in_array($color->name, old('print_colors', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label d-flex align-items-center" 
                                                   for="print_color_{{ $color->id }}">
                                                <span class="color-preview me-2" 
                                                      style="display: inline-block; width: 20px; height: 20px; 
                                                             background-color: {{ $color->hex_code }}; 
                                                             border: 1px solid #ddd; border-radius: 3px;">
                                                </span>
                                                {{ $color->name }}
                                                <button type="button" class="btn btn-link btn-sm text-danger ms-auto remove-print-color-btn" 
                                                        data-print-color-id="{{ $color->id }}" title="Eliminar color">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Colores de impresión seleccionados (resumen visual) -->
                        <div class="mt-3">
                            <label class="form-label">Colores de Impresión Seleccionados:</label>
                            <div id="selected-print-colors-preview" class="d-flex flex-wrap gap-2">
                                <!-- Se llenará dinámicamente con JavaScript -->
                            </div>
                        </div>

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
                                <!-- Vista previa de imágenes -->
                                <div id="image-preview-container" class="mt-2 d-flex flex-wrap gap-2"></div>
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
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Cantidad Desde</th>
                                        <th>Cantidad Hasta</th>
                                        <th>Precio Total</th>
                                        <th>Precio Unitario</th>
                                        <th width="50">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="pricing-container">
                                    @if(old('pricing'))
                                        @foreach(old('pricing') as $index => $pricing)
                                            <tr class="pricing-row">
                                                <td>
                                                    <input type="number" class="form-control" name="pricing[{{ $index }}][quantity_from]" 
                                                           value="{{ $pricing['quantity_from'] ?? '' }}" placeholder="1" min="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control" name="pricing[{{ $index }}][quantity_to]" 
                                                           value="{{ $pricing['quantity_to'] ?? '' }}" placeholder="100" min="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control pricing-total" name="pricing[{{ $index }}][price]" 
                                                           value="{{ $pricing['price'] ?? '' }}" placeholder="0.00" step="0.01" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control pricing-unit" name="pricing[{{ $index }}][unit_price]" 
                                                           value="{{ $pricing['unit_price'] ?? '' }}" placeholder="0.00" step="0.01" min="0" required>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-pricing">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="pricing-row">
                                            <td>
                                                <input type="number" class="form-control" name="pricing[0][quantity_from]" placeholder="1" min="1" value="1" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="pricing[0][quantity_to]" placeholder="100" min="1" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control pricing-total" name="pricing[0][price]" placeholder="0.00" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control pricing-unit" name="pricing[0][unit_price]" placeholder="0.00" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-pricing">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-pricing">
                            <i class="bi bi-plus-circle me-1"></i>Agregar Rango de Precio
                        </button>
                        @error('pricing')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campos personalizados -->
                    @if($customFields->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">Campos Personalizados</label>
                            <div class="card">
                                <div class="card-body">
                                    @foreach($customFields as $field)
                                        <div class="mb-3">
                                            <label class="form-label">
                                                {{ $field->name }}
                                                @if($field->required) <span class="text-danger">*</span> @endif
                                            </label>
                                            
                                            @switch($field->field_type)
                                                @case('text')
                                                    <input type="text" class="form-control" 
                                                           name="custom_fields[{{ $field->id }}]"
                                                           placeholder="{{ $field->placeholder }}"
                                                           {{ $field->required ? 'required' : '' }}
                                                           value="{{ old('custom_fields.' . $field->id) }}">
                                                    @break
                                                    
                                                @case('number')
                                                    <input type="number" class="form-control" 
                                                           name="custom_fields[{{ $field->id }}]"
                                                           placeholder="{{ $field->placeholder }}"
                                                           {{ $field->required ? 'required' : '' }}
                                                           value="{{ old('custom_fields.' . $field->id) }}">
                                                    @break
                                                    
                                                @case('textarea')
                                                    <textarea class="form-control" 
                                                              name="custom_fields[{{ $field->id }}]"
                                                              rows="3"
                                                              placeholder="{{ $field->placeholder }}"
                                                              {{ $field->required ? 'required' : '' }}>{{ old('custom_fields.' . $field->id) }}</textarea>
                                                    @break
                                                    
                                                @case('select')
                                                    <select class="form-select" 
                                                            name="custom_fields[{{ $field->id }}]"
                                                            {{ $field->required ? 'required' : '' }}>
                                                        <option value="">Seleccionar...</option>
                                                        @foreach($field->options as $option)
                                                            <option value="{{ $option }}" 
                                                                    {{ old('custom_fields.' . $field->id) == $option ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @break
                                                    
                                                @case('checkbox')
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" 
                                                               name="custom_fields[{{ $field->id }}]"
                                                               id="custom_field_{{ $field->id }}"
                                                               value="1"
                                                               {{ old('custom_fields.' . $field->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="custom_field_{{ $field->id }}">
                                                            {{ $field->placeholder ?? 'Sí' }}
                                                        </label>
                                                    </div>
                                                    @break
                                            @endswitch
                                            
                                            @if($field->help_text)
                                                <small class="form-text text-muted">{{ $field->help_text }}</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

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

<!-- CSS para los colores y la interfaz -->
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
    
    .color-item .form-check-label,
    .print-color-item .form-check-label {
        width: 100%;
        cursor: pointer;
        padding: 5px;
        border-radius: 4px;
    }
    
    .color-item .form-check-label:hover,
    .print-color-item .form-check-label:hover {
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
    
    .remove-color,
    .remove-print-color-btn {
        padding: 0;
        margin: 0;
        line-height: 1;
    }
    
    .remove-color:hover,
    .remove-print-color-btn:hover {
        color: #dc3545 !important;
    }
    
    .image-preview {
        position: relative;
        display: inline-block;
    }
    
    .image-preview img {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .image-preview .remove-preview {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
    }
    
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== GESTIÓN DE COLORES DEL PRODUCTO =====
    
    // Obtener elementos del DOM para colores del producto
    const colorHexInput = document.getElementById('new_color_hex');
    const colorHexText = document.getElementById('new_color_hex_text');
    const colorNameInput = document.getElementById('new_color_name');
    const addColorBtn = document.getElementById('add_available_color');
    const colorsList = document.getElementById('colors-list');
    const selectedColorsPreview = document.getElementById('selected-colors-preview');

    // Verificar que todos los elementos existan
    if (colorHexInput && colorHexText && colorNameInput && addColorBtn && colorsList && selectedColorsPreview) {
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
    }

    // ===== GESTIÓN DE COLORES DE IMPRESIÓN =====
    
    // Obtener elementos del DOM para colores de impresión
    const printColorHexInput = document.getElementById('new_print_color_hex');
    const printColorHexText = document.getElementById('new_print_color_hex_text');
    const printColorNameInput = document.getElementById('new_print_color_name');
    const addPrintColorBtn = document.getElementById('add_available_print_color');
    const printColorsList = document.getElementById('print-colors-list');
    const selectedPrintColorsPreview = document.getElementById('selected-print-colors-preview');

    if (printColorHexInput && printColorHexText && printColorNameInput && addPrintColorBtn && printColorsList) {
        // Sincronizar el selector de color con el campo de texto
        printColorHexInput.addEventListener('input', function() {
            printColorHexText.value = this.value.toUpperCase();
        });

        printColorHexText.addEventListener('input', function() {
            const value = this.value.toUpperCase();
            if (/^#[0-9A-F]{6}$/i.test(value)) {
                printColorHexInput.value = value;
            }
        });

        // Formatear el texto hex mientras se escribe
        printColorHexText.addEventListener('keyup', function() {
            let value = this.value.toUpperCase();
            
            if (value.length > 0 && value[0] !== '#') {
                value = '#' + value;
            }
            
            value = value.replace(/[^#0-9A-F]/g, '');
            
            if (value.length > 7) {
                value = value.substr(0, 7);
            }
            
            this.value = value;
        });

        // Agregar nuevo color de impresión
        addPrintColorBtn.addEventListener('click', function() {
            const colorName = printColorNameInput.value.trim();
            const colorHex = printColorHexText.value.toUpperCase();

            if (!colorName) {
                alert('Por favor ingresa un nombre para el color de impresión');
                printColorNameInput.focus();
                return;
            }

            if (!/^#[0-9A-F]{6}$/i.test(colorHex)) {
                alert('Por favor ingresa un código de color válido (ej: #FF0000)');
                printColorHexText.focus();
                return;
            }

            // Verificar si el color ya existe
            const existingColor = Array.from(document.querySelectorAll('.print-color-checkbox')).find(
                cb => cb.value.toLowerCase() === colorName.toLowerCase()
            );

            if (existingColor) {
                alert('Este color de impresión ya existe');
                return;
            }

            // Deshabilitar el botón mientras se procesa
            addPrintColorBtn.disabled = true;
            addPrintColorBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

            // Guardar el color en la base de datos via AJAX
            fetch('{{ route("admin.available-print-colors.store") }}', {
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
                    colorDiv.className = 'col-md-3 mb-3 print-color-item';
                    colorDiv.setAttribute('data-print-color-id', colorId);
                    colorDiv.innerHTML = `
                        <div class="form-check">
                            <input class="form-check-input print-color-checkbox" type="checkbox" 
                                   name="print_colors[]" value="${colorName}" 
                                   id="print_color_${colorId}"
                                   data-hex="${colorHex}">
                            <label class="form-check-label d-flex align-items-center" 
                                   for="print_color_${colorId}">
                                <span class="color-preview me-2" 
                                      style="display: inline-block; width: 20px; height: 20px; 
                                             background-color: ${colorHex}; 
                                             border: 1px solid #ddd; border-radius: 3px;">
                                </span>
                                ${colorName}
                                <button type="button" class="btn btn-link btn-sm text-danger ms-auto remove-print-color-btn" 
                                        data-print-color-id="${colorId}" title="Eliminar color">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </label>
                        </div>
                    `;
                    printColorsList.appendChild(colorDiv);

                    // Limpiar campos
                    printColorNameInput.value = '';
                    printColorHexInput.value = '#000000';
                    printColorHexText.value = '#000000';

                    // Marcar el nuevo color como seleccionado
                    setTimeout(() => {
                        const newCheckbox = document.getElementById(`print_color_${colorId}`);
                        if (newCheckbox) {
                            newCheckbox.checked = true;
                            updateSelectedPrintColorsPreview();
                        }
                    }, 100);

                    // Mostrar mensaje de éxito
                    showToast('Color de impresión agregado exitosamente', 'success');
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
                addPrintColorBtn.disabled = false;
                addPrintColorBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i>Agregar Color';
            });
        });
    }

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

        // Eliminar color de impresión
        if (e.target.closest('.remove-print-color-btn')) {
            e.preventDefault();
            const button = e.target.closest('.remove-print-color-btn');
            const colorId = button.getAttribute('data-print-color-id');
            const colorItem = button.closest('.print-color-item');
            const checkbox = colorItem ? colorItem.querySelector('.print-color-checkbox') : null;
            const colorName = checkbox ? checkbox.value : 'este color';

            if (confirm(`¿Estás seguro de eliminar el color de impresión "${colorName}"?`)) {
                // Deshabilitar el botón
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch(`{{ url('admin/available-print-colors') }}/${colorId}`, {
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
                            updateSelectedPrintColorsPreview();
                        }
                        showToast('Color de impresión eliminado exitosamente', 'success');
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

    // Actualizar vista previa de colores de impresión seleccionados
    function updateSelectedPrintColorsPreview() {
        if (!selectedPrintColorsPreview) return;
        
        selectedPrintColorsPreview.innerHTML = '';
        
        document.querySelectorAll('.print-color-checkbox:checked').forEach(checkbox => {
            const colorName = checkbox.value;
            const colorHex = checkbox.getAttribute('data-hex') || '#000000';
            
            const badge = document.createElement('div');
            badge.className = 'selected-color-badge';
            badge.innerHTML = `
                <span class="color-dot" style="background-color: ${colorHex}"></span>
                ${colorName}
            `;
            selectedPrintColorsPreview.appendChild(badge);
        });

        if (selectedPrintColorsPreview.innerHTML === '') {
            selectedPrintColorsPreview.innerHTML = '<span class="text-muted">Ningún color de impresión seleccionado</span>';
        }
    }

    // Escuchar cambios en los checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('color-checkbox')) {
            updateSelectedColorsPreview();
        }
        if (e.target.classList.contains('print-color-checkbox')) {
            updateSelectedPrintColorsPreview();
        }
    });

    // Actualizar vistas previas al cargar la página
    updateSelectedColorsPreview();
    updateSelectedPrintColorsPreview();

    // ===== GESTIÓN DE CAMPOS DINÁMICOS =====
    
    // Índices para campos dinámicos
    let sizeIndex = document.querySelectorAll('.size-row').length || 1;
    let pricingIndex = document.querySelectorAll('.pricing-row').length || 1;

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

    // Gestión de precios
    document.getElementById('add-pricing').addEventListener('click', function() {
        const container = document.getElementById('pricing-container');
        const newRow = document.createElement('tr');
        newRow.className = 'pricing-row';
        newRow.innerHTML = `
            <td>
                <input type="number" class="form-control" name="pricing[${pricingIndex}][quantity_from]" placeholder="1" min="1" required>
            </td>
            <td>
                <input type="number" class="form-control" name="pricing[${pricingIndex}][quantity_to]" placeholder="100" min="1" required>
            </td>
            <td>
                <input type="number" class="form-control pricing-total" name="pricing[${pricingIndex}][price]" placeholder="0.00" step="0.01" min="0" required>
            </td>
            <td>
                <input type="number" class="form-control pricing-unit" name="pricing[${pricingIndex}][unit_price]" placeholder="0.00" step="0.01" min="0" required>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm remove-pricing">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        container.appendChild(newRow);
        pricingIndex++;
    });

    // Event delegation para remover elementos
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-size')) {
            e.target.closest('.size-row').remove();
        }
        if (e.target.closest('.remove-pricing')) {
            const row = e.target.closest('.pricing-row');
            // No permitir eliminar si es la última fila
            if (document.querySelectorAll('.pricing-row').length > 1) {
                row.remove();
            } else {
                alert('Debe mantener al menos un rango de precio');
            }
        }
    });

    // ===== FILTRAR SUBCATEGORÍAS POR CATEGORÍA =====
    
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    
    if (categorySelect && subcategorySelect) {
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            const options = subcategorySelect.querySelectorAll('option[data-category]');
            
            // Resetear subcategoría
            subcategorySelect.value = '';
            
            // Mostrar/ocultar subcategorías según la categoría seleccionada
            options.forEach(option => {
                if (option.dataset.category === categoryId || categoryId === '') {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        });

        // Si hay una categoría preseleccionada, ejecutar el filtro
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    }

    // ===== VISTA PREVIA DE IMÁGENES =====
    
    const imagesInput = document.getElementById('images');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    
    if (imagesInput && imagePreviewContainer) {
        imagesInput.addEventListener('change', function(e) {
            imagePreviewContainer.innerHTML = '';
            
            const files = Array.from(e.target.files);
            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.createElement('div');
                        preview.className = 'image-preview';
                        preview.innerHTML = `
                            <img src="${e.target.result}" alt="Preview ${index + 1}">
                            <button type="button" class="remove-preview" data-index="${index}">
                                <i class="bi bi-x"></i>
                            </button>
                        `;
                        imagePreviewContainer.appendChild(preview);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Remover preview (nota: esto no remueve el archivo del input)
        imagePreviewContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-preview')) {
                e.target.closest('.image-preview').remove();
            }
        });
    }

    // ===== VALIDACIONES DEL FORMULARIO =====
    
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validar que al menos un color esté seleccionado
            const checkedColors = document.querySelectorAll('.color-checkbox:checked');
            if (checkedColors.length === 0) {
                e.preventDefault();
                alert('Por favor selecciona al menos un color para el producto');
                return;
            }

            // Validar que al menos un color de impresión esté seleccionado
            const checkedPrintColors = document.querySelectorAll('.print-color-checkbox:checked');
            if (checkedPrintColors.length === 0) {
                e.preventDefault();
                alert('Por favor selecciona al menos un color de impresión');
                return;
            }

            // Validar rangos de precios
            const pricingRows = document.querySelectorAll('.pricing-row');
            let validPricing = true;
            let previousTo = 0;

            pricingRows.forEach((row, index) => {
                const from = parseInt(row.querySelector('input[name$="[quantity_from]"]').value) || 0;
                const to = parseInt(row.querySelector('input[name$="[quantity_to]"]').value) || 0;

                if (from <= previousTo) {
                    validPricing = false;
                    alert(`El rango de cantidad ${index + 1} se superpone con el anterior`);
                }

                if (from > to) {
                    validPricing = false;
                    alert(`En el rango ${index + 1}, la cantidad "desde" no puede ser mayor que "hasta"`);
                }

                previousTo = to;
            });

            if (!validPricing) {
                e.preventDefault();
                return;
            }
        });
    }

    // ===== FUNCIÓN PARA MOSTRAR NOTIFICACIONES TOAST =====
    
    function showToast(message, type = 'info') {
        // Crear contenedor de toast si no existe
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
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
        
        // Auto-cerrar después de 3 segundos
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ===== CALCULADORA DE PRECIOS =====
    
    // Auto-calcular precio unitario cuando se ingresa el precio total
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('pricing-total')) {
            const row = e.target.closest('.pricing-row');
            const quantityFrom = parseInt(row.querySelector('input[name$="[quantity_from]"]').value) || 1;
            const totalPrice = parseFloat(e.target.value) || 0;
            const unitPriceInput = row.querySelector('.pricing-unit');
            
            if (quantityFrom > 0 && totalPrice > 0) {
                unitPriceInput.value = (totalPrice / quantityFrom).toFixed(2);
            }
        }
    });

    // ===== INICIALIZACIÓN =====
    
    // Enfocar el primer campo al cargar la página
    const firstInput = document.getElementById('name');
    if (firstInput) {
        firstInput.focus();
    }
});
</script>
@endpush