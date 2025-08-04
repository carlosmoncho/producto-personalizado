@extends('layouts.admin')

@section('title', 'Crear Producto')

@section('content')
<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <!-- Información Básica -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Información Básica</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
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

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id" required>
                            <option value="">Seleccione una categoría</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            <option value="">Seleccione una subcategoría</option>
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
        </div>
    </div>

    <!-- Especificaciones del Producto -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Especificaciones del Producto</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Materiales -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Materiales <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
                                <i class="bi bi-plus"></i> Agregar Material
                            </button>
                        </div>
                        <div id="materialsContainer">
                            <div class="row">
                                @php
                                    $availableMaterials = \App\Models\AvailableMaterial::where('active', true)->orderBy('sort_order')->get();
                                @endphp
                                @foreach($availableMaterials as $material)
                                    <div class="col-md-4 mb-2" id="material-item-{{ $material->id }}">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="materials[]" 
                                                   value="{{ $material->name }}" id="material_{{ $material->id }}"
                                                   {{ in_array($material->name, old('materials', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="material_{{ $material->id }}">
                                                {{ $material->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('materials')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Sistemas de Impresión (MÚLTIPLE SELECCIÓN) -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Sistemas de Impresión <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPrintingSystemModal">
                                <i class="bi bi-plus"></i> Nuevo Sistema
                            </button>
                        </div>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="row" id="printingSystemsContainer">
                                @php
                                    $printingSystems = \App\Models\PrintingSystem::where('active', true)->orderBy('sort_order')->get();
                                @endphp
                                @foreach($printingSystems as $system)
                                    <div class="col-12 mb-2" id="printing-system-item-{{ $system->id }}">
                                        <div class="d-flex align-items-start">
                                            <div class="form-check flex-grow-1">
                                                <input class="form-check-input printing-system-checkbox @error('printing_systems') is-invalid @enderror" 
                                                    type="checkbox" 
                                                    name="printing_systems[]" 
                                                    value="{{ $system->id }}" 
                                                    id="printing_system_{{ $system->id }}"
                                                    data-colors="{{ $system->total_colors }}"
                                                    data-min-units="{{ $system->min_units }}"
                                                    data-price="{{ $system->price_per_unit }}"
                                                    {{ (is_array(old('printing_systems')) && in_array($system->id, old('printing_systems'))) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="printing_system_{{ $system->id }}">
                                                    <strong>{{ $system->name }}</strong>
                                                    <small class="text-muted d-block">
                                                        {{ $system->total_colors }} colores, mín. {{ $system->min_units }} uds, €{{ number_format($system->price_per_unit, 2) }}/ud
                                                    </small>
                                                </label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                                    onclick="deletePrintingSystem({{ $system->id }}, '{{ $system->name }}')"
                                                    title="Eliminar sistema">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('printing_systems')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        
                        <!-- Información de los sistemas seleccionados -->
                        <div id="printing-systems-info" class="mt-2" style="display: none;">
                            <div class="alert alert-info">
                                <strong>Sistemas seleccionados:</strong>
                                <div id="systems-info-text"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="face_count" class="form-label">Número de Caras <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('face_count') is-invalid @enderror" 
                               id="face_count" name="face_count" value="{{ old('face_count', 1) }}" min="1" required>
                        @error('face_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
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
        </div>
    </div>

    <!-- Colores y Tamaños -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Colores y Tamaños</h5>
        </div>
        <div class="card-body">
            <!-- Colores Disponibles -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Colores Disponibles <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addColorModal">
                        <i class="bi bi-plus"></i> Agregar Color
                    </button>
                </div>
                <div class="row" id="colorsContainer">
                    @php
                        $availableColors = \App\Models\AvailableColor::where('active', true)->orderBy('sort_order')->get();
                    @endphp
                    @foreach($availableColors as $color)
                        <div class="col-md-3 mb-2" id="color-item-{{ $color->id }}">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="colors[]" 
                                       value="{{ $color->name }}" id="color_{{ $color->id }}"
                                       {{ in_array($color->name, old('colors', [])) ? 'checked' : '' }}>
                                <label class="form-check-label d-flex align-items-center" for="color_{{ $color->id }}">
                                    <span class="badge me-2" style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}; width: 20px; height: 20px; display: inline-block;"></span>
                                    {{ $color->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('colors')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Colores de Impresión -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Colores de Impresión <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPrintColorModal">
                        <i class="bi bi-plus"></i> Agregar Color de Impresión
                    </button>
                </div>
                <div class="row" id="printColorsContainer">
                    @php
                        $availablePrintColors = \App\Models\AvailablePrintColor::where('active', true)->orderBy('sort_order')->get();
                    @endphp
                    @foreach($availablePrintColors as $color)
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="print_colors[]" 
                                       value="{{ $color->name }}" id="print_color_{{ $color->id }}"
                                       {{ in_array($color->name, old('print_colors', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="print_color_{{ $color->id }}">
                                    <span class="badge" style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}">
                                        {{ $color->name }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('print_colors')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tamaños Disponibles -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Tamaños Disponibles <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSizeModal">
                        <i class="bi bi-plus"></i> Agregar Tamaño
                    </button>
                </div>
                <div id="sizesContainer">
                    <div class="row">
                        @php
                            $availableSizes = \App\Models\AvailableSize::where('active', true)->orderBy('sort_order')->get();
                        @endphp
                        @foreach($availableSizes as $size)
                            <div class="col-md-2 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sizes[]" 
                                           value="{{ $size->name }}" id="size_{{ $size->id }}"
                                           {{ in_array($size->name, old('sizes', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="size_{{ $size->id }}">
                                        {{ $size->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('sizes')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Precios -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Tabla de Precios</h5>
        </div>
        <div class="card-body">
            <div id="pricing-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Cantidad Desde</th>
                            <th>Cantidad Hasta</th>
                            <th>Precio Total</th>
                            <th>Precio Unitario</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="pricing-rows">
                        @if(old('pricing'))
                            @foreach(old('pricing') as $index => $price)
                                <tr>
                                    <td>
                                        <input type="number" class="form-control" name="pricing[{{ $index }}][quantity_from]" 
                                               value="{{ $price['quantity_from'] }}" min="1" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="pricing[{{ $index }}][quantity_to]" 
                                               value="{{ $price['quantity_to'] }}" min="1" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control" name="pricing[{{ $index }}][price]" 
                                               value="{{ $price['price'] }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control" name="pricing[{{ $index }}][unit_price]" 
                                               value="{{ $price['unit_price'] }}" min="0" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removePricingRow(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td><input type="number" class="form-control" name="pricing[0][quantity_from]" value="1" min="1" required></td>
                                <td><input type="number" class="form-control" name="pricing[0][quantity_to]" value="10" min="1" required></td>
                                <td><input type="number" step="0.01" class="form-control" name="pricing[0][price]" value="0" min="0" required></td>
                                <td><input type="number" step="0.01" class="form-control" name="pricing[0][unit_price]" value="0" min="0" required></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removePricingRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-primary" onclick="addPricingRow()">
                    <i class="bi bi-plus"></i> Agregar Rango de Precio
                </button>
            </div>
        </div>
    </div>

    <!-- Imágenes y Archivos -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Imágenes y Archivos</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="images" class="form-label">Imágenes del Producto</label>
                <input type="file" class="form-control @error('images.*') is-invalid @enderror" 
                       id="images" name="images[]" multiple accept="image/*">
                <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB por imagen.</small>
                @error('images.*')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="model_3d" class="form-label">Modelo 3D</label>
                <input type="file" class="form-control @error('model_3d') is-invalid @enderror" 
                       id="model_3d" name="model_3d" accept=".glb,.gltf">
                <small class="text-muted">Formatos permitidos: GLB, GLTF. Máximo 10MB.</small>
                @error('model_3d')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Estado -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Estado</h5>
        </div>
        <div class="card-body">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                       {{ old('active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="active">
                    Producto activo
                </label>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Guardar Producto
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Cancelar
        </a>
    </div>
</form>

<!-- Modal Agregar Material -->
<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMaterialForm">
                    <div class="mb-3">
                        <label for="material_name" class="form-label">Nombre del Material <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="material_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="material_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="material_description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addMaterial()">Agregar Material</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Color -->
<div class="modal fade" id="addColorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Color</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addColorForm">
                    <div class="mb-3">
                        <label for="color_name" class="form-label">Nombre del Color <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="color_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="color_hex" class="form-label">Código Hexadecimal <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="color_picker" value="#000000">
                            <input type="text" class="form-control" id="color_hex" value="#000000" pattern="^#[0-9A-Fa-f]{6}$" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addColor()">Agregar Color</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Color de Impresión -->
<div class="modal fade" id="addPrintColorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Color de Impresión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPrintColorForm">
                    <div class="mb-3">
                        <label for="print_color_name" class="form-label">Nombre del Color <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="print_color_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="print_color_hex" class="form-label">Código Hexadecimal <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="print_color_picker" value="#000000">
                            <input type="text" class="form-control" id="print_color_hex" value="#000000" pattern="^#[0-9A-Fa-f]{6}$" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addPrintColor()">Agregar Color</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Tamaño -->
<div class="modal fade" id="addSizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Tamaño</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSizeForm">
                    <div class="mb-3">
                        <label for="size_name" class="form-label">Nombre del Tamaño <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="size_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="size_code" class="form-label">Código (opcional)</label>
                        <input type="text" class="form-control" id="size_code" placeholder="Ej: S, M, L, XL">
                    </div>
                    <div class="mb-3">
                        <label for="size_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="size_description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addSize()">Agregar Tamaño</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Sistema de Impresión -->
<div class="modal fade" id="addPrintingSystemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Sistema de Impresión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPrintingSystemForm">
                    <div class="mb-3">
                        <label for="system_name" class="form-label">Nombre del Sistema <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="system_name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="system_total_colors" class="form-label">Total de Colores <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="system_total_colors" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="system_min_units" class="form-label">Unidades Mínimas <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="system_min_units" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="system_price_per_unit" class="form-label">Precio/Unidad (€) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="system_price_per_unit" value="0" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="system_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="system_description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addPrintingSystem()">Agregar Sistema</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Declarar funciones globalmente
let pricingIndex = {{ old('pricing') ? count(old('pricing')) : 1 }};

// Función para actualizar información de sistemas de impresión
function updateSystemsInfo() {
    const checkboxes = document.querySelectorAll('.printing-system-checkbox');
    const infoDiv = document.getElementById('printing-systems-info');
    const infoText = document.getElementById('systems-info-text');
    
    if (!infoDiv || !infoText) {
        return; // Salir si los elementos no existen
    }
    
    const selectedSystems = [];
    let maxColors = 0;
    let minUnits = Infinity;
    let avgPrice = 0;
    let selectedCount = 0;
    
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            const label = checkbox.nextElementSibling;
            const systemName = label.querySelector('strong')?.textContent || '';
            const colors = parseInt(checkbox.getAttribute('data-colors') || 0);
            const units = parseInt(checkbox.getAttribute('data-min-units') || 0);
            const price = parseFloat(checkbox.getAttribute('data-price') || 0);
            
            if (systemName) {
                selectedSystems.push({
                    name: systemName,
                    colors: colors,
                    units: units,
                    price: price
                });
                
                maxColors = Math.max(maxColors, colors);
                minUnits = Math.min(minUnits, units);
                avgPrice += price;
                selectedCount++;
            }
        }
    });
    
    if (selectedCount > 0) {
        avgPrice = avgPrice / selectedCount;
        
        let html = '<ul class="mb-0">';
        selectedSystems.forEach(system => {
            html += `<li>${system.name} (${system.colors} colores, €${system.price.toFixed(2)}/ud)</li>`;
        });
        html += '</ul>';
        html += `<hr class="my-2">`;
        html += `<small>`;
        html += `• Máximo de colores disponible: ${maxColors}<br>`;
        html += `• Cantidad mínima requerida: ${minUnits} unidades<br>`;
        html += `• Precio promedio: €${avgPrice.toFixed(2)}/unidad`;
        html += `</small>`;
        
        infoText.innerHTML = html;
        infoDiv.style.display = 'block';
        
        // Actualizar el campo de colores de impresión si existe
        const printColorsCount = document.getElementById('print_colors_count');
        if (printColorsCount) {
            printColorsCount.value = maxColors;
        }
    } else {
        infoDiv.style.display = 'none';
        const printColorsCount = document.getElementById('print_colors_count');
        if (printColorsCount) {
            printColorsCount.value = 1;
        }
    }
}

// Inicializar sistemas de impresión al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Agregar event listeners a los checkboxes de sistemas de impresión
    const checkboxes = document.querySelectorAll('.printing-system-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSystemsInfo);
    });
    
    // Ejecutar al cargar la página
    updateSystemsInfo();
    
    // Filtrar subcategorías según categoría seleccionada
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            const subcategorySelect = document.getElementById('subcategory_id');
            const subcategoryOptions = subcategorySelect.querySelectorAll('option');
            
            subcategoryOptions.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                } else if (option.dataset.category === categoryId || !categoryId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Reset subcategoría si la actual no pertenece a la categoría seleccionada
            const currentSubcategory = subcategorySelect.value;
            if (currentSubcategory && subcategorySelect.querySelector(`option[value="${currentSubcategory}"]`).style.display === 'none') {
                subcategorySelect.value = '';
            }
        });
        
        // Ejecutar al cargar
        const event = new Event('change');
        categorySelect.dispatchEvent(event);
    }
    
    // Sincronizar color pickers
    const colorPicker = document.getElementById('color_picker');
    const colorHex = document.getElementById('color_hex');
    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value;
        });
        
        colorHex.addEventListener('input', function() {
            if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                colorPicker.value = this.value;
            }
        });
    }
    
    // Sincronizar color picker de impresión
    const printColorPicker = document.getElementById('print_color_picker');
    const printColorHex = document.getElementById('print_color_hex');
    if (printColorPicker && printColorHex) {
        printColorPicker.addEventListener('input', function() {
            printColorHex.value = this.value;
        });
        
        printColorHex.addEventListener('input', function() {
            if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                printColorPicker.value = this.value;
            }
        });
    }
});

// Funciones para tabla de precios
function addPricingRow() {
    const tbody = document.getElementById('pricing-rows');
    const row = document.createElement('tr');
    
    row.innerHTML = `
        <td><input type="number" class="form-control" name="pricing[${pricingIndex}][quantity_from]" min="1" required></td>
        <td><input type="number" class="form-control" name="pricing[${pricingIndex}][quantity_to]" min="1" required></td>
        <td><input type="number" step="0.01" class="form-control" name="pricing[${pricingIndex}][price]" min="0" required></td>
        <td><input type="number" step="0.01" class="form-control" name="pricing[${pricingIndex}][unit_price]" min="0" required></td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removePricingRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    pricingIndex++;
}

function removePricingRow(button) {
    const tbody = document.getElementById('pricing-rows');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
    } else {
        alert('Debe mantener al menos un rango de precio');
    }
}

// Función para agregar material
function addMaterial() {
    const name = document.getElementById('material_name').value;
    const description = document.getElementById('material_description').value;
    
    if (!name) {
        alert('Por favor ingrese el nombre del material');
        return;
    }
    
    fetch('{{ route("admin.available-materials.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: name,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Agregar el nuevo material a la lista
            const container = document.getElementById('materialsContainer').querySelector('.row');
            const materialHtml = `
                <div class="col-md-4 mb-2" id="material-item-${data.material.id}">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="materials[]" 
                               value="${data.material.name}" id="material_${data.material.id}" checked>
                        <label class="form-check-label" for="material_${data.material.id}">
                            ${data.material.name}
                        </label>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', materialHtml);
            
            // Cerrar modal y limpiar formulario
            bootstrap.Modal.getInstance(document.getElementById('addMaterialModal')).hide();
            document.getElementById('addMaterialForm').reset();
            
            // Mostrar mensaje de éxito
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message);
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error(data.message);
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Error al agregar el material');
        } else {
            alert('Error al agregar el material');
        }
    });
}

// Función para agregar color
function addColor() {
    const name = document.getElementById('color_name').value;
    const hexCode = document.getElementById('color_hex').value;
    
    if (!name || !hexCode) {
        alert('Por favor complete todos los campos');
        return;
    }
    
    fetch('{{ route("admin.available-colors.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: name,
            hex_code: hexCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Agregar el nuevo color a la lista
            const container = document.getElementById('colorsContainer');
            const colorHtml = `
                <div class="col-md-3 mb-2" id="color-item-${data.color.id}">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="colors[]" 
                               value="${data.color.name}" id="color_${data.color.id}" checked>
                        <label class="form-check-label d-flex align-items-center" for="color_${data.color.id}">
                            <span class="badge me-2" style="background-color: ${data.color.hex_code}; color: ${data.color.hex_code == '#FFFFFF' ? '#000' : '#FFF'}; width: 20px; height: 20px; display: inline-block;"></span>
                            ${data.color.name}
                        </label>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', colorHtml);
            
            // Cerrar modal y limpiar formulario
            bootstrap.Modal.getInstance(document.getElementById('addColorModal')).hide();
            document.getElementById('addColorForm').reset();
            document.getElementById('color_picker').value = '#000000';
            
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message);
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error(data.message);
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Error al agregar el color');
        } else {
            alert('Error al agregar el color');
        }
    });
}

// Función para agregar color de impresión
function addPrintColor() {
    const name = document.getElementById('print_color_name').value;
    const hexCode = document.getElementById('print_color_hex').value;
    
    if (!name || !hexCode) {
        alert('Por favor complete todos los campos');
        return;
    }
    
    fetch('{{ route("admin.available-print-colors.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: name,
            hex_code: hexCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Agregar el nuevo color a la lista
            const container = document.getElementById('printColorsContainer');
            const colorHtml = `
                <div class="col-md-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="print_colors[]" 
                               value="${data.color.name}" id="print_color_${data.color.id}" checked>
                        <label class="form-check-label" for="print_color_${data.color.id}">
                            <span class="badge" style="background-color: ${data.color.hex_code}; color: ${data.color.hex_code == '#FFFFFF' ? '#000' : '#FFF'}">
                                ${data.color.name}
                            </span>
                        </label>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', colorHtml);
            
            // Cerrar modal y limpiar formulario
            bootstrap.Modal.getInstance(document.getElementById('addPrintColorModal')).hide();
            document.getElementById('addPrintColorForm').reset();
            document.getElementById('print_color_picker').value = '#000000';
            
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message);
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error(data.message);
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Error al agregar el color de impresión');
        } else {
            alert('Error al agregar el color de impresión');
        }
    });
}

// Función para agregar tamaño
function addSize() {
    const name = document.getElementById('size_name').value;
    const code = document.getElementById('size_code').value;
    const description = document.getElementById('size_description').value;
    
    if (!name) {
        alert('Por favor ingrese el nombre del tamaño');
        return;
    }
    
    fetch('{{ route("admin.available-sizes.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: name,
            code: code,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Agregar el nuevo tamaño a la lista
            const container = document.getElementById('sizesContainer').querySelector('.row');
            const sizeHtml = `
                <div class="col-md-2 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="sizes[]" 
                               value="${data.size.name}" id="size_${data.size.id}" checked>
                        <label class="form-check-label" for="size_${data.size.id}">
                            ${data.size.name}
                        </label>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', sizeHtml);
            
            // Cerrar modal y limpiar formulario
            bootstrap.Modal.getInstance(document.getElementById('addSizeModal')).hide();
            document.getElementById('addSizeForm').reset();
            
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message);
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error(data.message);
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Error al agregar el tamaño');
        } else {
            alert('Error al agregar el tamaño');
        }
    });
}

// Función para agregar sistema de impresión
function addPrintingSystem() {
    const name = document.getElementById('system_name').value;
    const totalColors = document.getElementById('system_total_colors').value;
    const minUnits = document.getElementById('system_min_units').value;
    const pricePerUnit = document.getElementById('system_price_per_unit').value;
    const description = document.getElementById('system_description').value;
    
    if (!name) {
        alert('Por favor ingrese el nombre del sistema');
        return;
    }
    
    fetch('{{ route("admin.printing-systems.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: name,
            total_colors: totalColors,
            min_units: minUnits,
            price_per_unit: pricePerUnit,
            description: description,
            active: true
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Buscar el contenedor
            const container = document.getElementById('printingSystemsContainer');
            
            if (container) {
                const newDiv = document.createElement('div');
                newDiv.className = 'col-12 mb-2';
                newDiv.id = `printing-system-item-${data.printingSystem.id}`;
                newDiv.innerHTML = `
                    <div class="d-flex align-items-start">
                        <div class="form-check flex-grow-1">
                            <input class="form-check-input printing-system-checkbox" 
                                   type="checkbox" 
                                   name="printing_systems[]" 
                                   value="${data.printingSystem.id}" 
                                   id="printing_system_${data.printingSystem.id}"
                                   data-colors="${data.printingSystem.total_colors}"
                                   data-min-units="${data.printingSystem.min_units}"
                                   data-price="${data.printingSystem.price_per_unit}"
                                   checked>
                            <label class="form-check-label" for="printing_system_${data.printingSystem.id}">
                                <strong>${data.printingSystem.name}</strong>
                                <small class="text-muted d-block">
                                    ${data.printingSystem.total_colors} colores, mín. ${data.printingSystem.min_units} uds, €${parseFloat(data.printingSystem.price_per_unit).toFixed(2)}/ud
                                </small>
                            </label>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                onclick="deletePrintingSystem(${data.printingSystem.id}, '${data.printingSystem.name}')"
                                title="Eliminar sistema">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
                container.appendChild(newDiv);
                
                // Agregar event listener al nuevo checkbox
                const newCheckbox = newDiv.querySelector('.printing-system-checkbox');
                newCheckbox.addEventListener('change', updateSystemsInfo);
                
                // Actualizar la información
                updateSystemsInfo();
            } else {
                // Si no hay contenedor, recargar la página
                location.reload();
            }
            
            // Cerrar modal y limpiar formulario
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPrintingSystemModal'));
            if (modal) {
                modal.hide();
            }
            document.getElementById('addPrintingSystemForm').reset();
            
            // Mostrar mensaje de éxito con toastr si está disponible
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message);
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error(data.message || 'Error al agregar el sistema de impresión');
            } else {
                alert(data.message || 'Error al agregar el sistema de impresión');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
function deletePrintingSystem(systemId, systemName) {
    if (!confirm(`¿Está seguro de eliminar el sistema "${systemName}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }
    
    // Usar la ruta correcta generada por resource
    fetch(`/admin/printing-systems/${systemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        // Primero verificar si la respuesta es JSON
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
        } else {
            throw new Error("La respuesta no es JSON");
        }
    })
    .then(data => {
        if (data.success) {
            // Eliminar el elemento del DOM
            const element = document.getElementById(`printing-system-item-${systemId}`);
            if (element) {
                element.remove();
            }
            
            // Actualizar la información
            updateSystemsInfo();
            
            // Mostrar mensaje de éxito
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message || 'Sistema eliminado exitosamente');
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error(data.message || 'Error al eliminar el sistema');
            } else {
                alert(data.message || 'Error al eliminar el sistema');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Error al eliminar el sistema de impresión');
        } else {
            alert('Error al eliminar el sistema de impresión');
        }
    });
}
</script>
@endpush