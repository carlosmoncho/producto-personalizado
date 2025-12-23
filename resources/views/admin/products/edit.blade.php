@extends('layouts.admin')

@section('title', 'Editar Producto')

@section('content')
<!-- Header with breadcrumb and actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Productos</a></li>
                <li class="breadcrumb-item active">Editar Producto</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-pencil-fill text-warning me-2"></i>Editar Producto</h1>
        <p class="text-muted mb-0">Modifica la información del producto: <strong>{{ $product->name }}</strong></p>
    </div>
    <div>
        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-info me-2">
            <i class="bi bi-eye"></i> Ver Producto
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
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
                <label for="slug" class="form-label">Slug</label>
                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                       id="slug" name="slug" value="{{ old('slug', $product->slug) }}">
                <small class="text-muted">Dejar vacío para generar automáticamente</small>
                @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="4" placeholder="Descripción detallada del producto...">{{ old('description', $product->description) }}</textarea>
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
            <!-- Imágenes actuales -->
            @if($product->images && count($product->images) > 0)
                <div class="mb-4">
                    <h6 class="mb-3">Imágenes Actuales</h6>
                    <div class="row">
                        @foreach($product->getImagesUrls() as $index => $imageUrl)
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <img src="{{ $imageUrl }}" class="card-img-top" alt="Imagen {{ $index + 1 }}" style="height: 150px; object-fit: cover;">
                                    <div class="card-body p-2 text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage('{{ $product->images[$index] }}')">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="remove_images" id="remove_images" value="">
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="images" class="form-label">{{ $product->images && count($product->images) > 0 ? 'Agregar Más Imágenes' : 'Subir Imágenes' }}</label>
                        <input type="file" class="form-control @error('images.*') is-invalid @enderror" 
                               id="images" name="images[]" multiple accept="image/*">
                        <small class="text-muted">Formatos permitidos: JPG, PNG, GIF, WebP. Máximo 2MB por imagen.</small>
                        @error('images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    @if($product->model_3d_file)
                        <div class="mb-3">
                            <h6 class="mb-2">Modelo 3D Actual</h6>
                            <div class="alert alert-info d-flex align-items-center flex-wrap gap-2">
                                <i class="bi bi-file-earmark-3d me-2"></i>
                                <span>Archivo 3D disponible</span>
                                <div class="ms-auto d-flex gap-2">
                                    <a href="{{ $product->getModel3dUrl() }}" class="btn btn-sm btn-primary" download>
                                        <i class="bi bi-download"></i> Descargar
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteModel3d()">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="remove_model_3d" id="remove_model_3d" value="0">
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="model_3d" class="form-label">{{ $product->model_3d_file ? 'Reemplazar' : 'Subir' }} Modelo 3D</label>
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
                       {{ old('active', $product->active) ? 'checked' : '' }}>
                <label class="form-check-label" for="active">
                    <strong>Producto Activo</strong>
                    <small class="text-muted d-block">El producto estará visible y disponible para pedidos</small>
                </label>
            </div>
        </div>
    </div>

    <!-- Configurador de Producto -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-golden border-bottom-0 py-3">
            <div class="d-flex align-items-center">
                <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h5 class="mb-0">Configurador de Producto</h5>
                    <small>Sistema avanzado de configuración de productos</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Configurador Habilitado -->
            <div class="alert alert-info d-flex align-items-center mb-4">
                <input type="hidden" name="has_configurator" value="1">
                <i class="bi bi-gear-fill text-primary me-2"></i>
                <div>
                    <strong>Configurador de Producto Habilitado</strong>
                    <div class="small">Los clientes podrán personalizar este producto seleccionando atributos</div>
                </div>
            </div>

            <!-- Opciones del Configurador -->
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
                                       value="{{ old('configurator_base_price', $product->configurator_base_price) }}">
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
                                    <option value="{{ $i }}" {{ old('max_print_colors', $product->max_print_colors ?? 1) == $i ? 'selected' : '' }}>
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
                                <option value="unit" {{ old('pricing_unit', $product->pricing_unit ?? 'unit') == 'unit' ? 'selected' : '' }}>
                                    Por Unidad (precio/ud)
                                </option>
                                <option value="thousand" {{ old('pricing_unit', $product->pricing_unit ?? 'unit') == 'thousand' ? 'selected' : '' }}>
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
                    @forelse($attributeGroups ?? [] as $group)
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
                                        <a href="{{ route('admin.product-attributes.create', ['group_id' => $group->id]) }}"
                                           class="btn btn-sm btn-primary"
                                           title="Crear nuevo atributo">
                                            <i class="bi bi-plus"></i>
                                        </a>
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
                                                       {{ in_array($attribute->id, old("selected_attributes.{$group->id}", $selectedAttributes[$group->id] ?? [])) ? 'checked' : '' }}
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
                                                    <div class="d-flex align-items-center">
                                                        @if($attribute->stock_quantity !== null)
                                                            <small class="badge bg-{{ $attribute->stock_quantity > 0 ? 'success' : 'danger' }}">
                                                                {{ $attribute->stock_quantity }}
                                                            </small>
                                                        @endif
                                                        @php
                                                            $attrImgData = $attributeImages[$attribute->id] ?? null;
                                                            $imgCount = $attrImgData ? count($attrImgData['images']) : 0;
                                                        @endphp
                                                        <button type="button"
                                                                class="btn {{ $imgCount > 0 ? 'btn-success' : 'btn-outline-secondary' }} ms-2 attr-images-btn d-flex align-items-center gap-1"
                                                                style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                                data-attribute-id="{{ $attribute->id }}"
                                                                data-attribute-name="{{ $attribute->name }}"
                                                                data-pivot-id="{{ $attrImgData['pivot_id'] ?? '' }}"
                                                                data-images="{{ json_encode($attrImgData['images'] ?? []) }}"
                                                                title="Gestionar imágenes">
                                                            <i class="bi bi-images"></i>
                                                            @if($imgCount > 0)
                                                                <span class="badge bg-white text-success">{{ $imgCount }}</span>
                                                            @endif
                                                        </button>
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
                                                    <a href="{{ route('admin.product-attributes.create', ['group_id' => $group->id]) }}"
                                                       class="btn btn-sm btn-primary">
                                                        Añadir primer atributo
                                                    </a>
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
                                   {{ old('allow_file_upload', $product->allow_file_upload) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_file_upload">
                                <strong>Permitir subida de archivos</strong>
                                <div class="form-text">Los clientes pueden subir logos, imágenes, etc.</div>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="file_upload_types" class="form-label">Tipos de archivo permitidos</label>
                        <select class="form-select" id="file_upload_types" name="file_upload_types[]" multiple>
                            @php
                                $selectedTypes = old('file_upload_types', $product->file_upload_types ?? []);
                            @endphp
                            <option value="jpg" {{ in_array('jpg', $selectedTypes) ? 'selected' : '' }}>JPG</option>
                            <option value="png" {{ in_array('png', $selectedTypes) ? 'selected' : '' }}>PNG</option>
                            <option value="svg" {{ in_array('svg', $selectedTypes) ? 'selected' : '' }}>SVG</option>
                            <option value="pdf" {{ in_array('pdf', $selectedTypes) ? 'selected' : '' }}>PDF</option>
                            <option value="ai" {{ in_array('ai', $selectedTypes) ? 'selected' : '' }}>AI</option>
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
                                   {{ old('allow_custom_quantity', $product->allow_custom_quantity ?? false) ? 'checked' : '' }}>
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
                              placeholder="Descripción que verán los clientes sobre las opciones de personalización...">{{ old('configurator_description', $product->configurator_description) }}</textarea>
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

    <!-- Botones de Acción -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-info me-2">
                        <i class="bi bi-eye"></i> Ver Producto
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                </div>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check-circle"></i> Actualizar Producto
                </button>
            </div>
        </div>
    </div>
</form>


@push('scripts')
@vite('resources/js/admin/products.js')
<script>
// Configuración específica para editar producto
window.productConfig = {
    editMode: true,
    currentCategoryId: {{ $product->category_id }},
    currentSubcategoryId: {{ $product->subcategory_id }}
};

// Función para eliminar imágenes
function removeImage(imagePath) {
    if (confirm('¿Está seguro de eliminar esta imagen?')) {
        const removeImagesInput = document.getElementById('remove_images');
        const currentValue = removeImagesInput.value;
        const newValue = currentValue ? currentValue + ',' + imagePath : imagePath;
        removeImagesInput.value = newValue;

        // Ocultar la imagen visualmente
        event.target.closest('.col-md-3').style.display = 'none';
    }
}

// Función para confirmar eliminación del modelo 3D
function confirmDeleteModel3d() {
    if (confirm('¿Está seguro de eliminar el modelo 3D? Esta acción no se puede deshacer.')) {
        document.getElementById('remove_model_3d').value = '1';
        // Ocultar la sección del modelo 3D visualmente
        event.target.closest('.mb-3').style.display = 'none';
        // Mostrar mensaje de confirmación
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning';
        alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>El modelo 3D se eliminará al guardar los cambios.';
        event.target.closest('.col-md-6').prepend(alertDiv);
    }
}

// Funciones del Configurador
function updateConfiguratorPreview() {
    const summarySpan = document.getElementById('configuratorSummary');
    const basePrice = document.getElementById('configurator_base_price')?.value || '0';
    const maxColors = document.getElementById('max_print_colors')?.value || '1';
    const allowFiles = document.getElementById('allow_file_upload')?.checked || false;
    
    // Contar atributos seleccionados
    const selectedAttributes = document.querySelectorAll('input[name^="selected_attributes"]:checked').length;
    
    let summary = `Precio base: €${basePrice}, Max. colores: ${maxColors}, Atributos: ${selectedAttributes}`;
    if (allowFiles) {
        summary += ', Archivos: Sí';
    }
    
    summarySpan.textContent = summary;
}

function previewConfigurator() {
    alert('Vista previa del configurador - Esta funcionalidad se implementará próximamente');
}

// Funciones para dependencias y reglas de precios
function showCreateDependencyModal() {
    alert('Crear dependencia - Esta funcionalidad se implementará próximamente');
}

function showCreatePriceRuleModal() {
    alert('Crear regla de precio - Esta funcionalidad se implementará próximamente');
}

// Funciones para grupos de atributos
function setCurrentGroup(groupId, groupName, groupType) {
    console.log('Configurando grupo:', groupId, groupName, groupType);
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar vista previa del configurador
    updateConfiguratorPreview();

    // Agregar listeners a los campos de configuración
    const basePriceInput = document.getElementById('configurator_base_price');
    const maxColorsInput = document.getElementById('max_print_colors');
    const allowFilesInput = document.getElementById('allow_file_upload');

    if (basePriceInput) basePriceInput.addEventListener('input', updateConfiguratorPreview);
    if (maxColorsInput) maxColorsInput.addEventListener('change', updateConfiguratorPreview);
    if (allowFilesInput) allowFilesInput.addEventListener('change', updateConfiguratorPreview);

    // Agregar listeners a checkboxes/radios de atributos
    const attributeInputs = document.querySelectorAll('input[name^="selected_attributes"]');
    attributeInputs.forEach(input => {
        input.addEventListener('change', updateConfiguratorPreview);
    });
});

// ========== GESTIÓN DE IMÁGENES POR ATRIBUTO ==========
document.querySelectorAll('.attr-images-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const attributeId = this.dataset.attributeId;
        const attributeName = this.dataset.attributeName;
        const pivotId = this.dataset.pivotId;
        const images = JSON.parse(this.dataset.images || '[]');

        // Actualizar modal
        document.getElementById('attrImagesModalLabel').textContent = `Imágenes - ${attributeName}`;
        document.getElementById('modalAttributeId').value = attributeId;
        document.getElementById('modalPivotId').value = pivotId;

        // Renderizar galería
        renderAttrImagesGallery(images, pivotId);

        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('attrImagesModal'));
        modal.show();
    });
});

function renderAttrImagesGallery(images, pivotId) {
    const gallery = document.getElementById('attrImagesGallery');

    if (!images || images.length === 0) {
        gallery.innerHTML = `
            <div class="text-center text-muted py-5 bg-light rounded">
                <i class="bi bi-images" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="mt-2 mb-0">Sin imágenes asignadas</p>
                <small class="text-muted">Sube imágenes para mostrar cuando se seleccione este atributo</small>
            </div>
        `;
        return;
    }

    let html = '<div class="row g-3">';
    images.forEach((imageUrl, index) => {
        html += `
            <div class="col-6 col-md-4">
                <div class="position-relative border rounded overflow-hidden shadow-sm">
                    <img src="${imageUrl}" alt="Imagen ${index + 1}"
                         class="img-fluid" style="aspect-ratio: 1; object-fit: cover; width: 100%;">
                    <button type="button" class="btn btn-danger position-absolute top-0 end-0 m-1 delete-attr-img-btn"
                            data-pivot-id="${pivotId}" data-image-index="${index}"
                            style="padding: 0.25rem 0.5rem; font-size: 0.875rem; border-radius: 50%;">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    gallery.innerHTML = html;

    // Añadir event listeners para eliminar
    gallery.querySelectorAll('.delete-attr-img-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('¿Eliminar esta imagen?')) return;

            const pivotId = this.dataset.pivotId;
            const imageIndex = this.dataset.imageIndex;

            try {
                const response = await fetch(`/admin/products/{{ $product->slug }}/attribute-images/${pivotId}/${imageIndex}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    renderAttrImagesGallery(data.images, pivotId);
                    updateAttrImageButton(document.getElementById('modalAttributeId').value, data.images);
                } else {
                    alert(data.message || 'Error al eliminar');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar la imagen');
            }
        });
    });
}

function updateAttrImageButton(attributeId, images) {
    const btn = document.querySelector(`.attr-images-btn[data-attribute-id="${attributeId}"]`);
    if (!btn) return;

    const count = images ? images.length : 0;
    btn.dataset.images = JSON.stringify(images || []);
    btn.className = `btn ${count > 0 ? 'btn-success' : 'btn-outline-secondary'} ms-2 attr-images-btn d-flex align-items-center gap-1`;
    btn.title = 'Gestionar imágenes';

    // Actualizar badge
    let badge = btn.querySelector('.badge');
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge bg-white text-success';
            btn.appendChild(badge);
        }
        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
}

// Subir imágenes
console.log('Buscando formulario attrImagesUploadForm...');
const uploadForm = document.getElementById('attrImagesUploadForm');
console.log('Formulario encontrado:', uploadForm);

if (uploadForm) {
    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('Formulario submit triggered');

        const pivotId = document.getElementById('modalPivotId').value;
        const attributeId = document.getElementById('modalAttributeId').value;
        const fileInput = document.getElementById('attrImagesInput');
        const submitBtn = this.querySelector('button[type="submit"]');

        console.log('pivotId:', pivotId, 'attributeId:', attributeId, 'files:', fileInput?.files?.length);

        if (!fileInput.files.length) {
            alert('Selecciona al menos una imagen');
            return;
        }

        if (!pivotId) {
            alert('Debes guardar el producto primero antes de subir imágenes para este atributo');
            return;
        }

        const formData = new FormData();
        for (let file of fileInput.files) {
            formData.append('images[]', file);
        }
        formData.append('_token', '{{ csrf_token() }}');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const url = `/admin/products/{{ $product->slug }}/attribute-images/${pivotId}`;
        console.log('Enviando a URL:', url);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);

            if (data.success) {
                renderAttrImagesGallery(data.images, pivotId);
                updateAttrImageButton(attributeId, data.images);
                fileInput.value = '';
            } else {
                alert(data.message || 'Error al subir las imágenes');
            }
        } catch (error) {
            console.error('Error completo:', error);
            alert('Error al subir las imágenes: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-upload"></i> Subir';
        }
    });
} else {
    console.error('No se encontró el formulario attrImagesUploadForm');
}

</script>
@endpush

<!-- Modal para imágenes de atributo -->
<div class="modal fade" id="attrImagesModal" tabindex="-1" aria-labelledby="attrImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="attrImagesModalLabel">
                    <i class="bi bi-images me-2"></i>Imágenes del Atributo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalAttributeId">
                <input type="hidden" id="modalPivotId">

                <div class="alert alert-info py-2 mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    <small>Estas imágenes se mostrarán en el configurador cuando el cliente seleccione este atributo.</small>
                </div>

                <!-- Galería de imágenes -->
                <div id="attrImagesGallery" class="mb-4">
                    <div class="text-center text-muted py-5 bg-light rounded">
                        <i class="bi bi-images" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mt-2 mb-0">Sin imágenes asignadas</p>
                    </div>
                </div>

                <!-- Formulario de subida -->
                <form id="attrImagesUploadForm" class="bg-light p-3 rounded">
                    <label class="form-label fw-bold"><i class="bi bi-cloud-upload me-1"></i>Subir nuevas imágenes</label>
                    <div class="input-group">
                        <input type="file" class="form-control" id="attrImagesInput" name="images[]" multiple accept="image/*">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload me-1"></i> Subir
                        </button>
                    </div>
                    <div class="form-text">Formatos: JPG, PNG, GIF, WebP. Puedes seleccionar múltiples archivos.</div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection