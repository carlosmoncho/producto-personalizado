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
                <label for="slug" class="form-label">Slug (URL amigable)</label>
                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                       id="slug" name="slug" value="{{ old('slug') }}"
                       placeholder="se-generara-automaticamente-del-nombre">
                @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Se genera automáticamente a partir del nombre. Puedes modificarlo si es necesario.</small>
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
                        <small class="text-muted">Formatos permitidos: JPG, PNG, GIF, WebP. Máximo 2MB por imagen.</small>
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

    <!-- Configurador de Productos -->
    <div class="card shadow-sm mb-4 border-0" id="configuratorSection">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center">
                <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                    <i class="bi bi-gear-fill"></i>
                </div>
                <div>
                    <h5 class="mb-0">Configurador de Productos</h5>
                    <small>Habilita la personalización dinámica para este producto</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Switch para activar configurador -->
            <div class="alert alert-info d-flex align-items-center mb-4">
                <input type="hidden" name="has_configurator" value="1">
                <i class="bi bi-gear-fill text-primary me-2"></i>
                <div>
                    <strong>Configurador de Producto Habilitado</strong>
                    <div class="small">Los clientes podrán personalizar este producto seleccionando atributos</div>
                </div>
            </div>

            <!-- Opciones del configurador (ocultas por defecto) -->
            <div id="configuratorOptions">
                
                <!-- Configuración básica -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="configurator_base_price" class="form-label">
                                Precio Base <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" step="0.0001" 
                                       class="form-control @error('configurator_base_price') is-invalid @enderror" 
                                       id="configurator_base_price" name="configurator_base_price" 
                                       value="{{ old('configurator_base_price') }}">
                            </div>
                            @error('configurator_base_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Precio base antes de aplicar modificadores de atributos
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="max_print_colors" class="form-label">
                                Máximo Colores de Impresión
                            </label>
                            <select class="form-select @error('max_print_colors') is-invalid @enderror" 
                                    id="max_print_colors" name="max_print_colors">
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('max_print_colors', 1) == $i ? 'selected' : '' }}>
                                        {{ $i }} {{ $i == 1 ? 'color' : 'colores' }}
                                    </option>
                                @endfor
                            </select>
                            @error('max_print_colors')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Unidad de Precio -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pricing_unit" class="form-label">
                                Unidad de Precio
                            </label>
                            <select class="form-select @error('pricing_unit') is-invalid @enderror"
                                    id="pricing_unit" name="pricing_unit">
                                <option value="unit" {{ old('pricing_unit', 'unit') == 'unit' ? 'selected' : '' }}>
                                    Por Unidad (precio/ud)
                                </option>
                                <option value="thousand" {{ old('pricing_unit', 'unit') == 'thousand' ? 'selected' : '' }}>
                                    Por Millar (precio/1000 uds)
                                </option>
                            </select>
                            @error('pricing_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <strong>Por Unidad:</strong> El precio base es por cada unidad individual.<br>
                                <strong>Por Millar:</strong> El precio base es por cada 1000 unidades.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NUEVO SISTEMA: Gestión de atributos por grupos -->
                <div class="row">
                    @forelse($attributeGroups as $group)
                        <div class="col-md-6 mb-4">
                            <div class="card border-{{ $group->active ? 'primary' : 'secondary' }}">
                                <div class="card-header bg-{{ $group->active ? 'primary' : 'secondary' }} bg-opacity-10 d-flex justify-content-between align-items-center py-2">
                                    <div class="d-flex align-items-center">
                                        <h6 class="mb-0 me-2">{{ $group->name }}</h6>
                                        @if($group->is_required)
                                            <span class="badge bg-danger badge-sm">Requerido</span>
                                        @endif
                                        @if($group->affects_price)
                                            <span class="badge bg-warning badge-sm ms-1">€</span>
                                        @endif
                                        @if($group->affects_stock)
                                            <span class="badge bg-info badge-sm ms-1">📦</span>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.attribute-groups.show', $group) }}" 
                                           class="btn btn-sm btn-outline-primary me-1" title="Gestionar grupo">
                                            <i class="bi bi-gear"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#createAttributeModal"
                                                onclick="setCurrentGroup({{ $group->id }}, '{{ $group->name }}', '{{ $group->type }}')">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-2">
                                    @if($group->description)
                                        <div class="small text-muted mb-2">{{ $group->description }}</div>
                                    @endif
                                    
                                    <div class="attributes-container" style="max-height: 200px; overflow-y: auto;">
                                        @forelse($group->attributes as $attribute)
                                            <div class="form-check mb-1" id="attr_{{ $attribute->id }}">
                                                <input class="form-check-input" 
                                                       type="{{ $group->allow_multiple ? 'checkbox' : 'radio' }}" 
                                                       id="attribute_{{ $attribute->id }}" 
                                                       name="{{ $group->allow_multiple ? "selected_attributes[{$group->id}][]" : "selected_attributes[{$group->id}]" }}" 
                                                       value="{{ $attribute->id }}"
                                                       {{ in_array($attribute->id, old("selected_attributes.{$group->id}", [])) ? 'checked' : '' }}
                                                       onchange="updateConfiguratorPreview()"
                                                       data-group-id="{{ $group->id }}"
                                                       data-group-type="{{ $group->type }}"
                                                       data-price-modifier="{{ $attribute->price_modifier }}"
                                                       data-price-percentage="{{ $attribute->price_percentage }}">
                                                <label class="form-check-label d-flex align-items-center justify-content-between w-100" 
                                                       for="attribute_{{ $attribute->id }}">
                                                    <div class="d-flex align-items-center">
                                                        @if(in_array($group->type, ['color', 'ink', 'ink_color']) && $attribute->hex_code)
                                                            <span class="me-2"
                                                                  style="width: 18px; height: 18px; background-color: {{ $attribute->hex_code }};
                                                                         border-radius: {{ $group->type === 'color' ? '50%' : '3px' }};
                                                                         border: 1px solid #ddd; display: inline-block;"></span>
                                                        @endif
                                                        @if($attribute->image_path)
                                                            <img src="{{ Storage::disk(config('filesystems.default', 'public'))->url($attribute->image_path) }}"
                                                                 alt="{{ $attribute->name }}"
                                                                 class="me-2"
                                                                 style="width: 24px; height: 24px; object-fit: cover; border-radius: 3px;"
                                                                 onerror="this.style.display='none'">
                                                        @endif
                                                        <div>
                                                            <span class="fw-medium">{{ $attribute->name }}</span>
                                                            @if($attribute->price_modifier != 0)
                                                                <small class="badge bg-{{ $attribute->price_modifier > 0 ? 'warning' : 'success' }} ms-1">
                                                                    {{ $attribute->price_modifier > 0 ? '+' : '' }}€{{ number_format($attribute->price_modifier, 2) }}
                                                                </small>
                                                            @endif
                                                            @if($attribute->price_percentage != 0)
                                                                <small class="badge bg-info ms-1">
                                                                    {{ $attribute->price_percentage > 0 ? '+' : '' }}{{ $attribute->price_percentage }}%
                                                                </small>
                                                            @endif
                                                            @if($attribute->is_recommended)
                                                                <small class="badge bg-primary ms-1">★</small>
                                                            @endif
                                                            @if($attribute->pantone_code)
                                                                <small class="text-muted d-block">{{ $attribute->pantone_code }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div>
                                                        @if($attribute->stock_quantity !== null)
                                                            <small class="badge bg-{{ $attribute->stock_quantity > 0 ? 'success' : 'danger' }}">
                                                                {{ $attribute->stock_quantity }}
                                                            </small>
                                                        @endif
                                                        <a href="{{ route('admin.product-attributes.edit', $attribute) }}" 
                                                           class="btn btn-sm btn-outline-secondary ms-1" title="Editar">
                                                            <i class="bi bi-pencil" style="font-size: 0.6rem;"></i>
                                                        </a>
                                                    </div>
                                                </label>
                                            </div>
                                        @empty
                                            <div class="text-muted small text-center py-3">
                                                <i class="bi bi-inbox d-block mb-2" style="font-size: 1.5rem; opacity: 0.5;"></i>
                                                No hay atributos en este grupo
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#createAttributeModal"
                                                            onclick="setCurrentGroup({{ $group->id }}, '{{ $group->name }}', '{{ $group->type }}')">
                                                        Añadir primer atributo
                                                    </button>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle me-3 fs-4"></i>
                                    <div>
                                        <h6 class="mb-1">No hay grupos de atributos configurados</h6>
                                        <p class="mb-2">Los grupos de atributos te permiten organizar colores, tamaños, materiales, etc.</p>
                                        <a href="{{ route('admin.attribute-groups.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i>Crear primer grupo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Opciones adicionales -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="hidden" name="allow_file_upload" value="0">
                            <input class="form-check-input" type="checkbox" 
                                   id="allow_file_upload" name="allow_file_upload" value="1" 
                                   {{ old('allow_file_upload', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_file_upload">
                                <strong>Permitir subida de archivos</strong>
                                <div class="form-text">Los clientes pueden subir logos, imágenes, etc.</div>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="file_upload_types" class="form-label">Tipos de archivo permitidos</label>
                        <select class="form-select" id="file_upload_types" name="file_upload_types[]" multiple>
                            <option value="jpg" {{ in_array('jpg', old('file_upload_types', [])) ? 'selected' : '' }}>JPG</option>
                            <option value="png" {{ in_array('png', old('file_upload_types', [])) ? 'selected' : '' }}>PNG</option>
                            <option value="svg" {{ in_array('svg', old('file_upload_types', [])) ? 'selected' : '' }}>SVG</option>
                            <option value="pdf" {{ in_array('pdf', old('file_upload_types', [])) ? 'selected' : '' }}>PDF</option>
                            <option value="ai" {{ in_array('ai', old('file_upload_types', [])) ? 'selected' : '' }}>AI</option>
                        </select>
                        <div class="form-text">Mantén presionado Ctrl/Cmd para seleccionar múltiples</div>
                    </div>
                </div>

                <!-- Cantidad personalizada -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="hidden" name="allow_custom_quantity" value="0">
                            <input class="form-check-input" type="checkbox"
                                   id="allow_custom_quantity" name="allow_custom_quantity" value="1"
                                   {{ old('allow_custom_quantity', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_custom_quantity">
                                <strong>Permitir cantidad personalizada</strong>
                                <div class="form-text">Los clientes pueden escribir una cantidad específica además de las opciones predefinidas</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Descripción del configurador -->
                <div class="mb-3">
                    <label for="configurator_description" class="form-label">Descripción del Configurador</label>
                    <textarea class="form-control @error('configurator_description') is-invalid @enderror" 
                              id="configurator_description" name="configurator_description" rows="3"
                              placeholder="Descripción que verán los clientes sobre las opciones de personalización...">{{ old('configurator_description') }}</textarea>
                    @error('configurator_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Vista previa y validación -->
                <div class="alert alert-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Vista previa:</strong> <span id="configuratorSummary">Selecciona atributos para ver un resumen</span>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="previewConfigurator()">
                            <i class="bi bi-eye me-1"></i>Vista Previa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dependencias entre Atributos -->
    <div class="card shadow-sm mb-4 border-0" id="dependenciesSection">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Dependencias entre Atributos</h5>
                        <small>Configura relaciones entre atributos (permite/bloquea/requiere)</small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="showCreateDependencyModal()">
                    <i class="bi bi-plus"></i> Crear Dependencia
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-sm" id="dependenciesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Atributo Principal</th>
                                    <th>Relación</th>
                                    <th>Atributo Dependiente</th>
                                    <th>Descripción</th>
                                    <th width="120">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="dependenciesTableBody">
                                <tr id="noDependenciesRow">
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No hay dependencias configuradas. Crea dependencias para controlar qué atributos están disponibles según la selección.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reglas de Precios Dinámicos -->
    <div class="card shadow-sm mb-4 border-0" id="priceRulesSection">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Reglas de Precios Dinámicos</h5>
                        <small>Configura reglas automáticas de precios basadas en combinaciones</small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="showCreatePriceRuleModal()">
                    <i class="bi bi-plus"></i> Crear Regla
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-sm" id="priceRulesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Condiciones</th>
                                    <th>Acción</th>
                                    <th>Vigencia</th>
                                    <th width="120">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="priceRulesTableBody">
                                <tr id="noPriceRulesRow">
                                    <td colspan="6" class="text-center text-muted py-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No hay reglas de precios configuradas. Crea reglas para aplicar descuentos o recargos automáticos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

<!-- Modal Universal para Crear Atributos -->
<div class="modal fade" id="createAttributeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAttributeModalTitle">Crear Atributo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createAttributeForm">
                    <input type="hidden" id="attr_type" name="type">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="attr_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="attr_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="attr_value" class="form-label">Valor Técnico <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="attr_value" name="value" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos específicos por tipo -->
                    <div id="type_specific_fields">
                        <!-- Color Configuration -->
                        <div id="modal_colorConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="modal_hex_code" class="form-label">Código de Color <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" 
                                               id="modal_color_picker" onchange="updateModalHexCode()" style="width: 60px;">
                                        <input type="text" class="form-control" 
                                               id="modal_hex_code" name="hex_code" 
                                               placeholder="#FFFFFF" pattern="^#[0-9A-Fa-f]{6}$">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="modal_color_family" class="form-label">Familia de Color</label>
                                    <select class="form-select" id="modal_color_family" name="color_family">
                                        <option value="">Seleccionar familia</option>
                                        <option value="basicos">Básicos</option>
                                        <option value="vibrantes">Vibrantes</option>
                                        <option value="pasteles">Pasteles</option>
                                        <option value="metalicos">Metálicos</option>
                                        <option value="neon">Neón</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Material Configuration -->
                        <div id="modal_materialConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="modal_material_type" class="form-label">Tipo de Material</label>
                                    <select class="form-select" id="modal_material_type" name="material_type">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="papel">Papel</option>
                                        <option value="carton">Cartón</option>
                                        <option value="plastico">Plástico</option>
                                        <option value="tela">Tela</option>
                                        <option value="metal">Metal</option>
                                        <option value="madera">Madera</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="modal_thickness" class="form-label">Grosor/Gramaje</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="modal_thickness" name="thickness" step="0.1" min="0">
                                        <select class="form-select" id="modal_thickness_unit" name="thickness_unit" style="max-width: 80px;">
                                            <option value="mm">mm</option>
                                            <option value="gsm">g/m²</option>
                                            <option value="mic">μm</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Size Configuration -->
                        <div id="modal_sizeConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="modal_width" class="form-label">Ancho</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="modal_width" name="width" step="0.1" min="0">
                                        <select class="form-select" id="modal_width_unit" name="width_unit" style="max-width: 80px;">
                                            <option value="mm">mm</option>
                                            <option value="cm">cm</option>
                                            <option value="m">m</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modal_height" class="form-label">Alto</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="modal_height" name="height" step="0.1" min="0">
                                        <select class="form-select" id="modal_height_unit" name="height_unit" style="max-width: 80px;">
                                            <option value="mm">mm</option>
                                            <option value="cm">cm</option>
                                            <option value="m">m</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modal_size_category" class="form-label">Categoría</label>
                                    <select class="form-select" id="modal_size_category" name="size_category">
                                        <option value="pequeno">Pequeño</option>
                                        <option value="mediano">Mediano</option>
                                        <option value="grande">Grande</option>
                                        <option value="extra_grande">Extra Grande</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Ink Configuration -->
                        <div id="modal_inkConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="modal_ink_hex_code" class="form-label">Color de la Tinta <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" 
                                               id="modal_ink_color_picker" onchange="updateModalInkHexCode()" style="width: 60px;">
                                        <input type="text" class="form-control" 
                                               id="modal_ink_hex_code" name="hex_code" placeholder="#000000">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="modal_ink_type" class="form-label">Tipo de Tinta</label>
                                    <select class="form-select" id="modal_ink_type" name="ink_type">
                                        <option value="agua">Base Agua</option>
                                        <option value="solvente">Base Solvente</option>
                                        <option value="uv">UV</option>
                                        <option value="latex">Látex</option>
                                        <option value="sublimacion">Sublimación</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="modal_is_metallic" name="is_metallic" value="1">
                                        <label class="form-check-label" for="modal_is_metallic">
                                            Tinta Metálica
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="modal_is_fluorescent" name="is_fluorescent" value="1">
                                        <label class="form-check-label" for="modal_is_fluorescent">
                                            Fluorescente
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Configuration -->
                        <div id="modal_quantityConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="modal_quantity_value" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="modal_quantity_value" name="quantity_value" min="1">
                                    <div class="form-text">
                                        <small>Número exacto de unidades</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="modal_packaging" class="form-label">Empaquetado</label>
                                    <input type="text" class="form-control" id="modal_packaging" name="packaging" 
                                           placeholder="ej. 54 CAJAS de 300 unid.">
                                </div>
                            </div>
                        </div>

                        <!-- System Configuration -->
                        <div id="modal_systemConfig" class="type-config" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="modal_system_type" class="form-label">Tipo de Sistema</label>
                                    <select class="form-select" id="modal_system_type" name="system_type">
                                        <option value="offset">Offset</option>
                                        <option value="digital">Digital</option>
                                        <option value="serigrafia">Serigrafía</option>
                                        <option value="flexografia">Flexografía</option>
                                        <option value="sublimacion">Sublimación</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="modal_max_colors" class="form-label">Máximo de Colores</label>
                                    <input type="number" class="form-control" id="modal_max_colors" name="max_colors" value="4" min="1" max="12">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="attr_price_modifier" class="form-label">Modificador Precio (€)</label>
                                <input type="number" step="0.001" class="form-control" id="attr_price_modifier" 
                                       name="price_modifier" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="attr_price_percentage" class="form-label">Modificador (%)</label>
                                <input type="number" step="0.1" class="form-control" id="attr_price_percentage" 
                                       name="price_percentage" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="attr_sort_order" class="form-label">Orden</label>
                                <input type="number" class="form-control" id="attr_sort_order" 
                                       name="sort_order" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="attr_active" 
                                       name="active" value="1" checked>
                                <label class="form-check-label" for="attr_active">Activo</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="attr_is_recommended" 
                                       name="is_recommended" value="1">
                                <label class="form-check-label" for="attr_is_recommended">Recomendado</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="createNewAttribute()">Crear Atributo</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Atributo -->
<div class="modal fade" id="editAttributeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Atributo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editAttributeContent">
                    <!-- Se carga dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear Dependencia -->
<div class="modal fade" id="createDependencyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Dependencia entre Atributos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createDependencyForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dep_parent_attribute" class="form-label">Atributo Principal <span class="text-danger">*</span></label>
                                <select class="form-select" id="dep_parent_attribute" name="parent_attribute_id" required>
                                    <option value="">Seleccionar atributo...</option>
                                </select>
                                <div class="form-text">El atributo que controla la dependencia</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dep_dependent_attribute" class="form-label">Atributo Dependiente <span class="text-danger">*</span></label>
                                <select class="form-select" id="dep_dependent_attribute" name="dependent_attribute_id" required>
                                    <option value="">Seleccionar atributo...</option>
                                </select>
                                <div class="form-text">El atributo que será afectado</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dep_condition_type" class="form-label">Tipo de Relación <span class="text-danger">*</span></label>
                                <select class="form-select" id="dep_condition_type" name="condition_type" required>
                                    <option value="">Seleccionar relación...</option>
                                    <option value="allows">Permite</option>
                                    <option value="blocks">Bloquea</option>
                                    <option value="requires">Requiere</option>
                                    <option value="sets_price">Define Precio</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dep_priority" class="form-label">Prioridad</label>
                                <input type="number" class="form-control" id="dep_priority" name="priority" value="0" min="0">
                                <div class="form-text">0 = mayor prioridad</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dep_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="dep_description" name="description" rows="2"
                                  placeholder="Describe el comportamiento de esta dependencia..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dep_conditions" class="form-label">Condiciones Específicas (JSON)</label>
                        <textarea class="form-control" id="dep_conditions" name="conditions" rows="3"
                                  placeholder='{"min_quantity": 10, "max_quantity": 100}'></textarea>
                        <div class="form-text">Opcional: condiciones adicionales en formato JSON</div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="dep_active" name="active" value="1" checked>
                        <label class="form-check-label" for="dep_active">Activa</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="createDependency()">Crear Dependencia</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear Regla de Precio -->
<div class="modal fade" id="createPriceRuleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Regla de Precio Dinámico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createPriceRuleForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rule_name" class="form-label">Nombre de la Regla <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="rule_name" name="name" required
                                       placeholder="ej. Descuento cantidad alta">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rule_type" class="form-label">Tipo de Regla <span class="text-danger">*</span></label>
                                <select class="form-select" id="rule_type" name="rule_type" required onchange="updateRuleFields()">
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="combination">Combinación de Atributos</option>
                                    <option value="volume">Descuento por Volumen</option>
                                    <option value="attribute_specific">Atributo Específico</option>
                                    <option value="conditional">Condicional</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rule_action_type" class="form-label">Tipo de Acción <span class="text-danger">*</span></label>
                                <select class="form-select" id="rule_action_type" name="action_type" required>
                                    <option value="">Seleccionar acción...</option>
                                    <option value="add_fixed">Sumar Fijo</option>
                                    <option value="add_percentage">Sumar %</option>
                                    <option value="multiply">Multiplicar</option>
                                    <option value="set_fixed">Precio Fijo</option>
                                    <option value="set_percentage">Descuento %</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rule_action_value" class="form-label">Valor <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="rule_action_value" 
                                       name="action_value" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rule_priority" class="form-label">Prioridad</label>
                                <input type="number" class="form-control" id="rule_priority" name="priority" value="0" min="0">
                                <div class="form-text">0 = mayor prioridad</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos específicos por tipo de regla -->
                    <div id="rule_specific_fields"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rule_valid_from" class="form-label">Válida Desde</label>
                                <input type="datetime-local" class="form-control" id="rule_valid_from" name="valid_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rule_valid_until" class="form-label">Válida Hasta</label>
                                <input type="datetime-local" class="form-control" id="rule_valid_until" name="valid_until">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rule_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="rule_description" name="description" rows="2"
                                  placeholder="Describe cuándo se aplica esta regla..."></textarea>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="rule_active" name="active" value="1" checked>
                        <label class="form-check-label" for="rule_active">Activa</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="createPriceRule()">Crear Regla</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/admin/products.js')

    <script>
    // Auto-generar slug a partir del nombre
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                // Solo auto-generar si el slug está vacío o no ha sido modificado manualmente
                if (!slugInput.dataset.modified) {
                    slugInput.value = generateSlug(this.value);
                }
            });

            slugInput.addEventListener('input', function() {
                // Marcar como modificado manualmente
                slugInput.dataset.modified = 'true';
            });
        }

        function generateSlug(text) {
            return text
                .toLowerCase()
                .replace(/[áàäâ]/g, 'a')
                .replace(/[éèëê]/g, 'e')
                .replace(/[íìïî]/g, 'i')
                .replace(/[óòöô]/g, 'o')
                .replace(/[úùüû]/g, 'u')
                .replace(/ñ/g, 'n')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });

    // Variables globales
    let createAttributeModal, editAttributeModal, createDependencyModal, createPriceRuleModal;
    
    // Funciones para el configurador de productos
    function toggleConfiguratorOptions() {
        const checkbox = document.getElementById('has_configurator');
        const options = document.getElementById('configuratorOptions');
        const dependenciesSection = document.getElementById('dependenciesSection');
        const priceRulesSection = document.getElementById('priceRulesSection');
        
        if (checkbox.checked) {
            options.style.display = 'block';
            dependenciesSection.style.display = 'block';
            priceRulesSection.style.display = 'block';
            updateConfiguratorSummary();
            loadDependencies();
            loadPriceRules();
        } else {
            options.style.display = 'none';
            dependenciesSection.style.display = 'none';
            priceRulesSection.style.display = 'none';
            document.getElementById('configuratorSummary').textContent = 'Configurador deshabilitado';
        }
    }
    
    // Funciones para manejo de atributos
    function showCreateAttributeModal(type, label) {
        const modal = document.getElementById('createAttributeModal');
        const title = document.getElementById('createAttributeModalTitle');
        const typeField = document.getElementById('attr_type');
        
        // Limpiar formulario
        document.getElementById('createAttributeForm').reset();
        document.getElementById('attr_active').checked = true;
        
        // Configurar tipo
        title.textContent = `Crear ${label.slice(0, -1)}`;
        typeField.value = type;
        
        // Mostrar/ocultar campos específicos
        showModalTypeSpecificFields(type);
        
        // Set type-specific placeholders
        setModalTypeDefaults(type);
        
        // Mostrar modal
        if (!createAttributeModal) {
            createAttributeModal = new bootstrap.Modal(modal);
        }
        createAttributeModal.show();
    }
    
    function showModalTypeSpecificFields(type) {
        // Ocultar todos los campos específicos
        const typeConfigs = document.querySelectorAll('#type_specific_fields .type-config');
        typeConfigs.forEach(config => config.style.display = 'none');
        
        // Mostrar el campo específico del tipo
        const configElement = document.getElementById(`modal_${type}Config`);
        if (configElement) {
            configElement.style.display = 'block';
        }
    }
    
    function setModalTypeDefaults(type) {
        const nameField = document.getElementById('attr_name');
        const valueField = document.getElementById('attr_value');
        
        // Set type-specific placeholders
        switch(type) {
            case 'color':
                nameField.placeholder = 'ej. Rojo Ferrari, Azul Marino, Verde Menta';
                valueField.placeholder = 'ej. ROJO_FERRARI, AZUL_MARINO';
                break;
            case 'material':
                nameField.placeholder = 'ej. Papel Couché 115g, Cartón Reciclado';
                valueField.placeholder = 'ej. PAPEL_COUCHE_115G, CARTON_RECICLADO';
                break;
            case 'size':
                nameField.placeholder = 'ej. A4 (21x29.7cm), Tarjeta Visita';
                valueField.placeholder = 'ej. A4_21X297, TARJETA_VISITA';
                break;
            case 'ink':
                nameField.placeholder = 'ej. Negro Intenso, Dorado Metálico';
                valueField.placeholder = 'ej. NEGRO_INTENSO, DORADO_METALICO';
                break;
            case 'quantity':
                nameField.placeholder = 'ej. 1,000 unidades, 5,000 unidades';
                valueField.placeholder = 'ej. QTY_1000, QTY_5000';
                break;
            case 'system':
                nameField.placeholder = 'ej. Offset 4 Colores, Digital HP';
                valueField.placeholder = 'ej. OFFSET_4C, DIGITAL_HP';
                break;
        }
    }
    
    function updateModalHexCode() {
        const colorPicker = document.getElementById('modal_color_picker');
        const hexCode = document.getElementById('modal_hex_code');
        if (colorPicker && hexCode) {
            hexCode.value = colorPicker.value;
        }
    }
    
    function updateModalInkHexCode() {
        const colorPicker = document.getElementById('modal_ink_color_picker');
        const hexCode = document.getElementById('modal_ink_hex_code');
        if (colorPicker && hexCode) {
            hexCode.value = colorPicker.value;
        }
    }
    
    
    function createNewAttribute() {
        const form = document.getElementById('createAttributeForm');
        const formData = new FormData(form);
        
        // Añadir CSRF token
        formData.append('_token', '{{ csrf_token() }}');
        
        // Procesar campos especiales según el tipo
        const type = formData.get('type');
        
        fetch('{{ route("admin.product-attributes.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cerrar modal
                createAttributeModal.hide();
                
                // Refrescar la lista de atributos
                refreshAttributes(type);
                
                // Mostrar mensaje de éxito
                showAlert('success', 'Atributo creado exitosamente');
            } else {
                showAlert('error', data.message || 'Error al crear el atributo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error de conexión al crear el atributo');
        });
    }
    
    function refreshAttributes(type) {
        fetch(`{{ route('admin.product-attributes.index') }}?type=${type}&ajax=1`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById(`${type}_attributes`);
            if (data.attributes && data.attributes.length > 0) {
                container.innerHTML = data.attributes.map(attr => `
                    <div class="form-check mb-1" id="attr_${attr.id}">
                        <input class="form-check-input" type="checkbox" 
                               id="available_${type}_${attr.id}" 
                               name="available_${type}s[]" 
                               value="${attr.id}">
                        <label class="form-check-label d-flex align-items-center justify-content-between" 
                               for="available_${type}_${attr.id}">
                            <div class="d-flex align-items-center">
                                ${(type === 'color' || type === 'ink') && attr.hex_code ? `
                                    <span class="me-2" style="width: 16px; height: 16px; background-color: ${attr.hex_code}; 
                                           border-radius: ${type === 'color' ? '50%' : '3px'}; border: 1px solid #ddd; display: inline-block;"></span>
                                ` : ''}
                                <div>
                                    <span class="fw-medium">${attr.name}</span>
                                    ${attr.price_modifier != 0 ? `
                                        <small class="badge bg-${attr.price_modifier > 0 ? 'warning' : 'success'} ms-1">
                                            ${attr.price_modifier > 0 ? '+' : ''}€${parseFloat(attr.price_modifier).toFixed(3)}
                                        </small>
                                    ` : ''}
                                    ${attr.is_recommended ? '<small class="badge bg-primary ms-1">Recomendado</small>' : ''}
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1" 
                                        onclick="editAttribute(${attr.id})" title="Editar">
                                    <i class="bi bi-pencil" style="font-size: 0.7rem;"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteAttribute(${attr.id}, '${attr.name}')" title="Eliminar">
                                    <i class="bi bi-trash" style="font-size: 0.7rem;"></i>
                                </button>
                            </div>
                        </label>
                    </div>
                `).join('');
                
                // Ocultar mensaje de "no hay atributos"
                const noMessage = document.getElementById(`no_${type}_message`);
                if (noMessage) noMessage.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error al refrescar atributos:', error);
        });
    }
    
    function editAttribute(id) {
        fetch(`{{ url('admin/product-attributes') }}/${id}/edit`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editAttributeContent').innerHTML = html;
            
            if (!editAttributeModal) {
                editAttributeModal = new bootstrap.Modal(document.getElementById('editAttributeModal'));
            }
            editAttributeModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error al cargar el atributo');
        });
    }
    
    function deleteAttribute(id, name) {
        if (!confirm(`¿Estás seguro de que deseas eliminar el atributo "${name}"?`)) {
            return;
        }
        
        fetch(`{{ url('admin/product-attributes') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remover elemento del DOM
                const element = document.getElementById(`attr_${id}`);
                if (element) element.remove();
                
                showAlert('success', 'Atributo eliminado exitosamente');
            } else {
                showAlert('error', data.message || 'Error al eliminar el atributo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error de conexión al eliminar el atributo');
        });
    }
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 1070; max-width: 400px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert:last-child');
            if (alert) alert.remove();
        }, 5000);
    }
    
    // Auto-generar valor técnico desde el nombre
    document.addEventListener('DOMContentLoaded', function() {
        const nameField = document.getElementById('attr_name');
        const valueField = document.getElementById('attr_value');
        
        if (nameField && valueField) {
            nameField.addEventListener('input', function() {
                const value = this.value
                    .toUpperCase()
                    .replace(/[^A-Z0-9]/g, '_')
                    .replace(/_+/g, '_')
                    .replace(/^_|_$/g, '');
                
                valueField.value = value;
            });
        }
    });

    // NUEVA FUNCIÓN: Actualizar vista previa del configurador
    function updateConfiguratorPreview() {
        updateConfiguratorSummary();
        // Aquí podrías añadir más lógica de preview si es necesaria
    }

    function updateConfiguratorSummary() {
        const summary = document.getElementById('configuratorSummary');
        if (!summary) return;

        const hasConfigurator = document.getElementById('has_configurator')?.checked;
        
        if (!hasConfigurator) {
            summary.textContent = 'Configurador deshabilitado';
            return;
        }

        let summaryParts = [];
        let totalAttributes = 0;
        let totalPrice = 0;

        // Obtener precio base
        const basePrice = parseFloat(document.getElementById('configurator_base_price')?.value || 0);
        totalPrice = basePrice;

        // NUEVO: Contar atributos seleccionados por grupos
        const selectedInputs = document.querySelectorAll('input[name^="selected_attributes["]:checked');
        const groupCounts = {};
        
        selectedInputs.forEach(input => {
            const groupId = input.dataset.groupId;
            const priceModifier = parseFloat(input.dataset.priceModifier || 0);
            const pricePercentage = parseFloat(input.dataset.pricePercentage || 0);
            
            // Contar por grupo
            if (!groupCounts[groupId]) {
                groupCounts[groupId] = {
                    count: 0,
                    name: input.closest('.card').querySelector('h6').textContent,
                    price: 0
                };
            }
            groupCounts[groupId].count++;
            groupCounts[groupId].price += priceModifier + (basePrice * pricePercentage / 100);
            totalAttributes++;
            totalPrice += priceModifier + (basePrice * pricePercentage / 100);
        });

        // Generar resumen por grupos
        Object.values(groupCounts).forEach(group => {
            if (group.count > 0) {
                let groupText = `${group.count} ${group.name}`;
                if (group.price !== 0) {
                    groupText += ` (+€${group.price.toFixed(2)})`;
                }
                summaryParts.push(groupText);
            }
        });

        // Obtener colores de impresión máximos
        const maxColors = document.getElementById('max_print_colors')?.value || 1;
        
        // Verificar subida de archivos
        const fileUpload = document.getElementById('allow_file_upload')?.checked;

        let summaryText = '';
        
        if (totalAttributes === 0) {
            summaryText = 'Selecciona atributos para habilitar el configurador';
        } else {
            summaryText = `${totalAttributes} atributo${totalAttributes > 1 ? 's' : ''} seleccionado${totalAttributes > 1 ? 's' : ''}: `;
            summaryText += summaryParts.join(', ');
            
            if (basePrice > 0) {
                summaryText += ` | Precio total: €${totalPrice.toFixed(2)}`;
            }
            
            summaryText += ` | Máx. ${maxColors} color${maxColors > 1 ? 'es' : ''} de impresión`;
            
            if (fileUpload) {
                summaryText += ' | Permite archivos';
            }
        }

        summary.textContent = summaryText;
    }


    function previewConfigurator() {
        const hasConfigurator = document.getElementById('has_configurator').checked;
        
        if (!hasConfigurator) {
            alert('Primero debes habilitar el configurador');
            return;
        }

        // Validar configuración mínima usando los campos reales
        const colorsSelected = document.querySelectorAll('input[name*="selected_attributes"]:checked[data-group-type="color"]').length;
        const materialsSelected = document.querySelectorAll('input[name*="selected_attributes"]:checked[data-group-type="material"]').length;
        const sizesSelected = document.querySelectorAll('input[name*="selected_attributes"]:checked[data-group-type="size"]').length;
        const quantitiesSelected = document.querySelectorAll('input[name*="selected_attributes"]:checked[data-group-type="quantity"]').length;
        const basePrice = parseFloat(document.getElementById('configurator_base_price').value) || 0;

        let errors = [];
        if (colorsSelected === 0) errors.push('Debe seleccionar al menos un color');
        if (materialsSelected === 0) errors.push('Debe seleccionar al menos un material');
        if (sizesSelected === 0) errors.push('Debe seleccionar al menos un tamaño');
        if (quantitiesSelected === 0) errors.push('Debe seleccionar al menos una cantidad');
        if (basePrice <= 0) errors.push('Debe especificar un precio base válido');
        
        if (errors.length > 0) {
            alert('Errores de configuración:\n\n• ' + errors.join('\n• ') + 
                  '\n\nCorrige estos errores antes de previsualizar.');
            return;
        }

        // Mostrar información de preview simple
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Vista Previa del Configurador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <h6><i class="bi bi-check-circle me-2"></i>Configuración válida</h6>
                            <p class="mb-0">Este producto puede usar el configurador dinámico.</p>
                        </div>
                        
                        <h6>Atributos Disponibles:</h6>
                        <ul>
                            <li><strong>Colores:</strong> ${colorsSelected} disponibles</li>
                            <li><strong>Materiales:</strong> ${materialsSelected} disponibles</li>
                            <li><strong>Tamaños:</strong> ${sizesSelected} disponibles</li>
                            <li><strong>Cantidades:</strong> ${quantitiesSelected} disponibles</li>
                        </ul>
                        
                        <h6>Configuración de Precios:</h6>
                        <p>Precio base: €${basePrice.toFixed(2)}</p>
                        
                        <div class="alert alert-info">
                            <small><i class="bi bi-info-circle me-1"></i>
                            Después de guardar el producto, podrás acceder al configurador completo 
                            desde el botón "Configurador" en la vista del producto.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Limpiar modal al cerrarse
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }

    // Event listeners para actualizar el resumen en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        // Actualizar resumen cuando cambien los checkboxes de atributos
        const attributeCheckboxes = document.querySelectorAll('input[name^="available_"]');
        attributeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateConfiguratorSummary);
        });

        // Actualizar resumen cuando cambien otros campos
        const configuratorFields = ['configurator_base_price', 'max_print_colors', 'allow_file_upload'];
        configuratorFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('change', updateConfiguratorSummary);
                if (field.type === 'number' || field.type === 'text') {
                    field.addEventListener('input', updateConfiguratorSummary);
                }
            }
        });

        // Actualizar resumen inicial
        updateConfiguratorSummary();

        // Inicializar modales
        createAttributeModal = null;
        editAttributeModal = null;
        createDependencyModal = null;
        createPriceRuleModal = null;
    });
    
    // ==========================================
    // FUNCIONES PARA DEPENDENCIAS
    // ==========================================
    
    function showCreateDependencyModal() {
        const modal = document.getElementById('createDependencyModal');
        
        // Limpiar formulario
        document.getElementById('createDependencyForm').reset();
        document.getElementById('dep_active').checked = true;
        
        // Cargar atributos disponibles
        loadAttributesForDependencies();
        
        // Mostrar modal
        if (!createDependencyModal) {
            createDependencyModal = new bootstrap.Modal(modal);
        }
        createDependencyModal.show();
    }
    
    function loadAttributesForDependencies() {
        fetch('{{ route("admin.product-attributes.index") }}?ajax=1')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const parentSelect = document.getElementById('dep_parent_attribute');
            const dependentSelect = document.getElementById('dep_dependent_attribute');
            
            if (!parentSelect || !dependentSelect) {
                console.error('Elementos select no encontrados');
                return;
            }
            
            // Limpiar opciones actuales
            parentSelect.innerHTML = '<option value="">Seleccionar atributo...</option>';
            dependentSelect.innerHTML = '<option value="">Seleccionar atributo...</option>';
            
            if (data.success && data.attributes) {
                data.attributes.forEach(attr => {
                    const typeLabels = {
                        'color': 'Color',
                        'material': 'Material',
                        'size': 'Tamaño',
                        'ink': 'Tinta',
                        'quantity': 'Cantidad',
                        'system': 'Sistema'
                    };
                    
                    const typeLabel = typeLabels[attr.type] || attr.type;
                    const optionText = `${attr.name} (${typeLabel})`;
                    
                    const option1 = new Option(optionText, attr.id);
                    const option2 = new Option(optionText, attr.id);
                    parentSelect.appendChild(option1);
                    dependentSelect.appendChild(option2);
                });
                console.log(`Cargados ${data.attributes.length} atributos para dependencias`);
            } else {
                console.warn('No se encontraron atributos o respuesta incorrecta:', data);
                showAlert('warning', 'No hay atributos disponibles para crear dependencias');
            }
        })
        .catch(error => {
            console.error('Error al cargar atributos:', error);
            showAlert('error', 'Error al cargar atributos disponibles: ' + error.message);
        });
    }
    
    function createDependency() {
        const form = document.getElementById('createDependencyForm');
        const formData = new FormData(form);
        
        // Añadir CSRF token
        formData.append('_token', '{{ csrf_token() }}');
        
        // Validar condiciones JSON si se proporciona
        const conditions = formData.get('conditions');
        if (conditions && conditions.trim()) {
            try {
                JSON.parse(conditions);
            } catch (e) {
                showAlert('error', 'Las condiciones deben ser un JSON válido');
                return;
            }
        }
        
        fetch('{{ route("admin.attribute-dependencies.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                createDependencyModal.hide();
                loadDependencies();
                showAlert('success', 'Dependencia creada exitosamente');
            } else {
                console.error('Error en respuesta:', data);
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    showAlert('error', 'Errores de validación:\n' + errorMessages);
                } else {
                    showAlert('error', data.message || 'Error al crear la dependencia');
                }
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            showAlert('error', 'Error de conexión: ' + error.message);
        });
    }
    
    function loadDependencies() {
        fetch('{{ route("admin.attribute-dependencies.index") }}?ajax=1')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('dependenciesTableBody');
            const noDataRow = document.getElementById('noDependenciesRow');
            
            if (data.dependencies && data.dependencies.length > 0) {
                if (noDataRow) noDataRow.style.display = 'none';
                
                tbody.innerHTML = data.dependencies.map(dep => `
                    <tr id="dependency_${dep.id}">
                        <td>
                            <strong>${dep.parent_attribute.name}</strong>
                            <small class="text-muted d-block">${dep.parent_attribute.type}</small>
                        </td>
                        <td>
                            <span class="badge bg-${getRelationBadgeClass(dep.condition_type)}">${getRelationLabel(dep.condition_type)}</span>
                        </td>
                        <td>
                            <strong>${dep.dependent_attribute.name}</strong>
                            <small class="text-muted d-block">${dep.dependent_attribute.type}</small>
                        </td>
                        <td>
                            <small>${dep.description || '-'}</small>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteDependency(${dep.id})" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                if (noDataRow) noDataRow.style.display = '';
                tbody.innerHTML = '<tr id="noDependenciesRow"><td colspan="5" class="text-center text-muted py-3"><i class="bi bi-info-circle me-2"></i>No hay dependencias configuradas.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al cargar dependencias:', error);
        });
    }
    
    function getRelationBadgeClass(type) {
        const classes = {
            'allows': 'success',
            'blocks': 'danger', 
            'requires': 'warning',
            'sets_price': 'info'
        };
        return classes[type] || 'secondary';
    }
    
    function getRelationLabel(type) {
        const labels = {
            'allows': 'Permite',
            'blocks': 'Bloquea',
            'requires': 'Requiere',
            'sets_price': 'Define Precio'
        };
        return labels[type] || type;
    }
    
    function deleteDependency(id) {
        if (!confirm('¿Estás seguro de que deseas eliminar esta dependencia?')) {
            return;
        }
        
        fetch(`{{ url('admin/attribute-dependencies') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const element = document.getElementById(`dependency_${id}`);
                if (element) element.remove();
                
                // Verificar si quedan dependencias
                const tbody = document.getElementById('dependenciesTableBody');
                if (tbody.children.length === 0) {
                    tbody.innerHTML = '<tr id="noDependenciesRow"><td colspan="5" class="text-center text-muted py-3"><i class="bi bi-info-circle me-2"></i>No hay dependencias configuradas.</td></tr>';
                }
                
                showAlert('success', 'Dependencia eliminada exitosamente');
            } else {
                showAlert('error', data.message || 'Error al eliminar la dependencia');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error de conexión al eliminar la dependencia');
        });
    }
    
    // ==========================================
    // FUNCIONES PARA REGLAS DE PRECIO
    // ==========================================
    
    function showCreatePriceRuleModal() {
        const modal = document.getElementById('createPriceRuleModal');
        
        // Limpiar formulario
        document.getElementById('createPriceRuleForm').reset();
        document.getElementById('rule_active').checked = true;
        
        // Mostrar modal
        if (!createPriceRuleModal) {
            createPriceRuleModal = new bootstrap.Modal(modal);
        }
        createPriceRuleModal.show();
    }
    
    function updateRuleFields() {
        const ruleType = document.getElementById('rule_type').value;
        const fieldsContainer = document.getElementById('rule_specific_fields');
        
        let fieldsHtml = '';
        
        switch(ruleType) {
            case 'combination':
                fieldsHtml = `
                    <div class="mb-3">
                        <label class="form-label">Atributos Requeridos</label>
                        <div class="border rounded p-2" id="combination_attributes">
                            <div class="text-muted small">Cargando atributos...</div>
                        </div>
                    </div>
                `;
                break;
                
            case 'volume':
                fieldsHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rule_min_quantity" class="form-label">Cantidad Mínima</label>
                                <input type="number" class="form-control" id="rule_min_quantity" name="min_quantity" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rule_max_quantity" class="form-label">Cantidad Máxima</label>
                                <input type="number" class="form-control" id="rule_max_quantity" name="max_quantity">
                                <div class="form-text">Dejar vacío para sin límite</div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'attribute_specific':
                fieldsHtml = `
                    <div class="mb-3">
                        <label for="rule_target_attribute" class="form-label">Atributo Específico</label>
                        <select class="form-select" id="rule_target_attribute" name="target_attribute_id">
                            <option value="">Seleccionar atributo...</option>
                        </select>
                    </div>
                `;
                break;
                
            case 'conditional':
                fieldsHtml = `
                    <div class="mb-3">
                        <label for="rule_condition_script" class="form-label">Condición (JavaScript)</label>
                        <textarea class="form-control font-monospace" id="rule_condition_script" 
                                  name="condition_script" rows="4"
                                  placeholder="// Ejemplo: quantity > 100 && attributes.color === 'ROJO'
return quantity > 100 && attributes.color === 'ROJO';"></textarea>
                        <div class="form-text">Código JavaScript que debe devolver true/false</div>
                    </div>
                `;
                break;
        }
        
        fieldsContainer.innerHTML = fieldsHtml;
        
        // Cargar datos específicos según el tipo
        if (ruleType === 'combination') {
            loadAttributesForPriceRules('combination');
        } else if (ruleType === 'attribute_specific') {
            loadAttributesForPriceRules('specific');
        }
    }
    
    function loadAttributesForPriceRules(context) {
        fetch('{{ route("admin.product-attributes.index") }}?ajax=1')
        .then(response => response.json())
        .then(data => {
            if (context === 'combination') {
                const container = document.getElementById('combination_attributes');
                if (data.attributes && data.attributes.length > 0) {
                    container.innerHTML = data.attributes.map(attr => `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   id="combo_attr_${attr.id}" 
                                   name="combination_attributes[]" 
                                   value="${attr.id}">
                            <label class="form-check-label" for="combo_attr_${attr.id}">
                                <strong>${attr.name}</strong> <small class="text-muted">(${attr.type})</small>
                            </label>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<div class="text-muted small">No hay atributos disponibles</div>';
                }
            } else if (context === 'specific') {
                const select = document.getElementById('rule_target_attribute');
                select.innerHTML = '<option value="">Seleccionar atributo...</option>';
                
                if (data.attributes) {
                    data.attributes.forEach(attr => {
                        const option = new Option(`${attr.name} (${attr.type})`, attr.id);
                        select.appendChild(option);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar atributos:', error);
        });
    }
    
    function createPriceRule() {
        const form = document.getElementById('createPriceRuleForm');
        const formData = new FormData(form);
        
        // Añadir CSRF token
        formData.append('_token', '{{ csrf_token() }}');
        
        // Procesar condiciones específicas según el tipo
        const ruleType = formData.get('rule_type');
        let conditions = {};
        
        if (ruleType === 'combination') {
            const selectedAttrs = Array.from(document.querySelectorAll('input[name="combination_attributes[]"]:checked'))
                                       .map(cb => parseInt(cb.value));
            conditions.required_attributes = selectedAttrs;
        } else if (ruleType === 'volume') {
            const minQty = formData.get('min_quantity');
            const maxQty = formData.get('max_quantity');
            if (minQty) conditions.min_quantity = parseInt(minQty);
            if (maxQty) conditions.max_quantity = parseInt(maxQty);
        } else if (ruleType === 'attribute_specific') {
            const targetAttr = formData.get('target_attribute_id');
            if (targetAttr) conditions.target_attribute_id = parseInt(targetAttr);
        } else if (ruleType === 'conditional') {
            const script = formData.get('condition_script');
            if (script) conditions.condition_script = script;
        }
        
        // Añadir condiciones como JSON
        formData.append('conditions', JSON.stringify(conditions));
        
        fetch('{{ route("admin.price-rules.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                createPriceRuleModal.hide();
                loadPriceRules();
                showAlert('success', 'Regla de precio creada exitosamente');
            } else {
                showAlert('error', data.message || 'Error al crear la regla de precio');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error de conexión al crear la regla de precio');
        });
    }
    
    function loadPriceRules() {
        fetch('{{ route("admin.price-rules.index") }}?ajax=1')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('priceRulesTableBody');
            const noDataRow = document.getElementById('noPriceRulesRow');
            
            if (data.rules && data.rules.length > 0) {
                if (noDataRow) noDataRow.style.display = 'none';
                
                tbody.innerHTML = data.rules.map(rule => `
                    <tr id="price_rule_${rule.id}">
                        <td>
                            <strong>${rule.name}</strong>
                            ${rule.active ? '<span class="badge bg-success ms-1">Activa</span>' : '<span class="badge bg-secondary ms-1">Inactiva</span>'}
                        </td>
                        <td>
                            <span class="badge bg-primary">${getRuleTypeLabel(rule.rule_type)}</span>
                        </td>
                        <td>
                            <small>${getRuleConditionsText(rule.conditions, rule.rule_type)}</small>
                        </td>
                        <td>
                            <span class="badge bg-${getActionBadgeClass(rule.action_type)}">
                                ${getActionLabel(rule.action_type)} ${rule.action_value}
                            </span>
                        </td>
                        <td>
                            <small>
                                ${rule.valid_from ? new Date(rule.valid_from).toLocaleDateString() : 'Sin límite'} - 
                                ${rule.valid_until ? new Date(rule.valid_until).toLocaleDateString() : 'Sin límite'}
                            </small>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="deletePriceRule(${rule.id})" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                if (noDataRow) noDataRow.style.display = '';
                tbody.innerHTML = '<tr id="noPriceRulesRow"><td colspan="6" class="text-center text-muted py-3"><i class="bi bi-info-circle me-2"></i>No hay reglas de precios configuradas.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al cargar reglas de precios:', error);
        });
    }
    
    function getRuleTypeLabel(type) {
        const labels = {
            'combination': 'Combinación',
            'volume': 'Volumen',
            'attribute_specific': 'Específico',
            'conditional': 'Condicional'
        };
        return labels[type] || type;
    }
    
    function getRuleConditionsText(conditions, ruleType) {
        try {
            const cond = typeof conditions === 'string' ? JSON.parse(conditions) : conditions;
            
            switch(ruleType) {
                case 'volume':
                    return `Cantidad: ${cond.min_quantity || 0} - ${cond.max_quantity || '∞'}`;
                case 'combination':
                    return `${cond.required_attributes ? cond.required_attributes.length : 0} atributos requeridos`;
                case 'attribute_specific':
                    return `Atributo ID: ${cond.target_attribute_id || 'No especificado'}`;
                case 'conditional':
                    return 'Condición personalizada';
                default:
                    return '-';
            }
        } catch (e) {
            return '-';
        }
    }
    
    function getActionBadgeClass(type) {
        const classes = {
            'add_fixed': 'success',
            'add_percentage': 'info',
            'multiply': 'warning',
            'set_fixed': 'primary',
            'set_percentage': 'danger'
        };
        return classes[type] || 'secondary';
    }
    
    function getActionLabel(type) {
        const labels = {
            'add_fixed': '+€',
            'add_percentage': '+%',
            'multiply': '×',
            'set_fixed': '=€',
            'set_percentage': '-%'
        };
        return labels[type] || type;
    }
    
    function deletePriceRule(id) {
        if (!confirm('¿Estás seguro de que deseas eliminar esta regla de precio?')) {
            return;
        }
        
        fetch(`{{ url('admin/price-rules') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const element = document.getElementById(`price_rule_${id}`);
                if (element) element.remove();
                
                // Verificar si quedan reglas
                const tbody = document.getElementById('priceRulesTableBody');
                if (tbody.children.length === 0) {
                    tbody.innerHTML = '<tr id="noPriceRulesRow"><td colspan="6" class="text-center text-muted py-3"><i class="bi bi-info-circle me-2"></i>No hay reglas de precios configuradas.</td></tr>';
                }
                
                showAlert('success', 'Regla de precio eliminada exitosamente');
            } else {
                showAlert('error', data.message || 'Error al eliminar la regla de precio');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error de conexión al eliminar la regla de precio');
        });
    }

</script>
@endpush