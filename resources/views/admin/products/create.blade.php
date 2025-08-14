@extends('layouts.admin')

@section('title', 'Crear Producto')

@section('content')
<!-- Header with breadcrumb and actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Productos</a></li>
                <li class="breadcrumb-item active">Crear Producto</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Crear Nuevo Producto</h1>
        <p class="text-muted mb-0">Complete la información para crear un nuevo producto personalizable</p>
    </div>
    <div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <!-- Información Básica -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center">
                <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <div>
                    <h5 class="mb-0">Información Básica</h5>
                    <small>Datos fundamentales del producto</small>
                </div>
            </div>
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

    <!-- Especificaciones Técnicas -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center">
                <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                    <i class="bi bi-gear-fill"></i>
                </div>
                <div>
                    <h5 class="mb-0">Especificaciones Técnicas</h5>
                    <small>Características y especificaciones del producto</small>
                </div>
            </div>
        </div>
        <div class="card-body">
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

    <!-- Materiales y Sistemas -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center">
                <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <h5 class="mb-0">Materiales y Sistemas</h5>
                    <small>Materiales disponibles y sistemas de impresión</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Materiales -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Materiales Disponibles <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
                        <i class="bi bi-plus"></i> Agregar Material
                    </button>
                </div>
                <div class="row" id="materialsContainer">
                    @php
                        $availableMaterials = \App\Models\AvailableMaterial::where('active', true)->orderBy('sort_order')->get();
                    @endphp
                    @foreach($availableMaterials as $material)
                        <div class="col-md-4 mb-2" id="material-item-{{ $material->id }}">
                            <div class="d-flex align-items-center">
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input" type="checkbox" name="materials[]" 
                                           value="{{ $material->name }}" id="material_{{ $material->id }}"
                                           {{ in_array($material->name, old('materials', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="material_{{ $material->id }}">
                                        {{ $material->name }}
                                        @if($material->description)
                                            <small class="text-muted d-block">{{ $material->description }}</small>
                                        @endif
                                    </label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                        onclick="deleteMaterial({{ $material->id }}, '{{ $material->name }}')"
                                        title="Eliminar material">
                                    <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('materials')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Sistemas de Impresión -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Sistemas de Impresión <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPrintingSystemModal">
                        <i class="bi bi-plus"></i> Agregar Sistema
                    </button>
                </div>
                <div class="row" id="printingSystemsContainer">
                    @php
                        $printingSystems = \App\Models\PrintingSystem::where('active', true)->orderBy('sort_order')->get();
                    @endphp
                    @foreach($printingSystems as $system)
                        <div class="col-md-4 mb-2" id="printing-system-item-{{ $system->id }}">
                            <div class="d-flex align-items-start">
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input printing-system-checkbox" type="checkbox" name="printing_systems[]" 
                                           value="{{ $system->id }}" id="printing_system_{{ $system->id }}"
                                           data-colors="{{ $system->total_colors }}"
                                           data-min-units="{{ $system->min_units }}"
                                           data-price="{{ $system->price_per_unit }}"
                                           {{ (is_array(old('printing_systems')) && in_array($system->id, old('printing_systems'))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="printing_system_{{ $system->id }}">
                                        <strong>{{ $system->name }}</strong>
                                        @if($system->description)
                                            <small class="text-muted d-block">{{ $system->description }}</small>
                                        @endif
                                        <small class="text-muted d-block">
                                            {{ $system->total_colors }} colores, mín. {{ $system->min_units }} uds, €{{ number_format($system->price_per_unit, 2) }}/ud
                                        </small>
                                    </label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                        onclick="deletePrintingSystem({{ $system->id }}, '{{ $system->name }}')"
                                        title="Eliminar sistema">
                                    <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('printing_systems')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Colores y Tamaños -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center">
                <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                    <i class="bi bi-palette-fill"></i>
                </div>
                <div>
                    <h5 class="mb-0">Colores y Tamaños</h5>
                    <small>Opciones de personalización disponibles</small>
                </div>
            </div>
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
                            <div class="d-flex align-items-center">
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input" type="checkbox" name="colors[]" 
                                           value="{{ $color->name }}" id="color_{{ $color->id }}"
                                           {{ in_array($color->name, old('colors', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label d-flex align-items-center" for="color_{{ $color->id }}">
                                        <span class="badge me-2" style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}; width: 20px; height: 20px; display: inline-block;"></span>
                                        {{ $color->name }}
                                    </label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                        onclick="deleteColor({{ $color->id }}, '{{ $color->name }}')"
                                        title="Eliminar color">
                                    <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                                </button>
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
                        <div class="col-md-3 mb-2" id="print-color-item-{{ $color->id }}">
                            <div class="d-flex align-items-center">
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input" type="checkbox" name="print_colors[]" 
                                           value="{{ $color->name }}" id="print_color_{{ $color->id }}"
                                           {{ in_array($color->name, old('print_colors', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label d-flex align-items-center" for="print_color_{{ $color->id }}">
                                        <span class="badge me-2" style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}; width: 20px; height: 20px; display: inline-block;"></span>
                                        {{ $color->name }}
                                    </label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                        onclick="deletePrintColor({{ $color->id }}, '{{ $color->name }}')"
                                        title="Eliminar color de impresión">
                                    <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                                </button>
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
                            <div class="col-md-2 mb-2" id="size-item-{{ $size->id }}">
                                <div class="d-flex align-items-center">
                                    <div class="form-check flex-grow-1">
                                        <input class="form-check-input" type="checkbox" name="sizes[]" 
                                               value="{{ $size->name }}" id="size_{{ $size->id }}"
                                               {{ in_array($size->name, old('sizes', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="size_{{ $size->id }}">
                                            {{ $size->name }}
                                            @if($size->code)
                                                <small class="text-muted">({{ $size->code }})</small>
                                            @endif
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-1" 
                                            onclick="deleteSize({{ $size->id }}, '{{ $size->name }}')"
                                            title="Eliminar tamaño">
                                        <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                                    </button>
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
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-currency-euro"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Estructura de Precios</h5>
                        <small>Configure los precios por cantidad</small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="add-price-row">
                    <i class="bi bi-plus"></i> Agregar Rango
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Cantidad Desde</th>
                            <th>Cantidad Hasta</th>
                            <th>Precio Total (€)</th>
                            <th>Precio Unitario (€)</th>
                            <th width="100">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pricing-tbody">
                        @if(old('pricing'))
                            @foreach(old('pricing') as $index => $price)
                                <tr>
                                    <td>
                                        <input type="number" name="pricing[{{ $index }}][quantity_from]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $price['quantity_from'] }}" min="1" required>
                                    </td>
                                    <td>
                                        <input type="number" name="pricing[{{ $index }}][quantity_to]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $price['quantity_to'] }}" min="1" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="pricing[{{ $index }}][price]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $price['price'] }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="pricing[{{ $index }}][unit_price]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $price['unit_price'] }}" min="0" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-price-row">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>
                                    <input type="number" name="pricing[0][quantity_from]" 
                                           class="form-control form-control-sm" 
                                           value="1" min="1" required>
                                </td>
                                <td>
                                    <input type="number" name="pricing[0][quantity_to]" 
                                           class="form-control form-control-sm" 
                                           value="10" min="1" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="pricing[0][price]" 
                                           class="form-control form-control-sm" 
                                           value="0" min="0" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="pricing[0][unit_price]" 
                                           class="form-control form-control-sm" 
                                           value="0" min="0" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-price-row">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    </table>
                </div>
            @error('pricing')
                <div class="text-danger">{{ $message }}</div>
            @enderror
            </div>
        </div>
    </div>

    <!-- Imágenes y Archivos -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center">
                <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                    <i class="bi bi-images"></i>
                </div>
                <div>
                    <h5 class="mb-0">Multimedia</h5>
                    <small>Imágenes y modelos 3D del producto</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="images" class="form-label">Subir Imágenes</label>
                        <input type="file" class="form-control @error('images.*') is-invalid @enderror" 
                               id="images" name="images[]" multiple accept="image/*">
                        <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB por imagen.</small>
                        @error('images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="model_3d" class="form-label">Subir Modelo 3D</label>
                        <input type="file" class="form-control @error('model_3d') is-invalid @enderror" 
                               id="model_3d" name="model_3d" accept=".glb,.gltf">
                        <small class="text-muted">Formatos permitidos: GLB, GLTF. Máximo 10MB.</small>
                        @error('model_3d')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado y Configuración -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center">
                <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                    <i class="bi bi-toggles"></i>
                </div>
                <div>
                    <h5 class="mb-0">Estado y Configuración</h5>
                    <small>Configuración de visibilidad y estado</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-check form-switch">
                <input type="hidden" name="active" value="0">
                <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                       {{ old('active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="active">
                    <strong>Producto Activo</strong>
                    <small class="text-muted d-block">El producto estará visible y disponible para pedidos</small>
                </label>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Crear Producto
                </button>
            </div>
        </div>
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
    @vite('resources/js/admin/products.js')
@endpush