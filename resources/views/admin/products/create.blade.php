@extends('layouts.admin')

@section('title', 'Crear Producto')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            @foreach($breadcrumbs as $breadcrumb)
                @if($loop->last)
                    <li class="breadcrumb-item active">{{ $breadcrumb['name'] }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Crear Nuevo Producto</h1>
        </div>
    </div>

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
                              id="description" name="description" rows="4">{{ old('description') }}</textarea>
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
                                <option value="">Primero selecciona una categoría</option>
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
                                        <span class="badge me-2" style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}">
                                            {{ $color->name }}
                                        </span>
                                        @if(!$color->isInUse())
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-1 delete-color-btn" 
                                                    data-id="{{ $color->id }}" data-name="{{ $color->name }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif
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
                            $printColors = \App\Models\AvailablePrintColor::where('active', true)->orderBy('sort_order')->get();
                        @endphp
                        @foreach($printColors as $color)
                            <div class="col-md-3 mb-2" id="print-color-item-{{ $color->id }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="print_colors[]" 
                                           value="{{ $color->name }}" id="print_color_{{ $color->id }}"
                                           {{ in_array($color->name, old('print_colors', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label d-flex align-items-center" for="print_color_{{ $color->id }}">
                                        <span class="badge me-2" style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}">
                                            {{ $color->name }}
                                        </span>
                                        @if(!$color->isInUse())
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-1 delete-print-color-btn" 
                                                    data-id="{{ $color->id }}" data-name="{{ $color->name }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif
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
                                <div class="col-md-2 mb-2" id="size-item-{{ $size->id }}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sizes[]" 
                                               value="{{ $size->name }}" id="size_{{ $size->id }}"
                                               {{ in_array($size->name, old('sizes', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label d-flex align-items-center" for="size_{{ $size->id }}">
                                            <span class="me-1">{{ $size->name }}</span>
                                            @if($size->code)
                                                <small class="text-muted">({{ $size->code }})</small>
                                            @endif
                                            @if(!$size->isInUse())
                                                <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-1 delete-size-btn" 
                                                        data-id="{{ $size->id }}" data-name="{{ $size->name }}">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
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

        <!-- Precios por Cantidad -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Precios por Cantidad</h5>
                <button type="button" class="btn btn-sm btn-primary" id="add-price-row">
                    <i class="bi bi-plus"></i> Agregar Rango
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Cantidad Desde</th>
                                <th>Cantidad Hasta</th>
                                <th>Precio Total</th>
                                <th>Precio Unitario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="pricing-tbody">
                            @if(old('pricing'))
                                @foreach(old('pricing') as $index => $price)
                                    <tr>
                                        <td>
                                            <input type="number" name="pricing[{{ $index }}][quantity_from]" 
                                                   class="form-control form-control-sm" value="{{ $price['quantity_from'] }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="pricing[{{ $index }}][quantity_to]" 
                                                   class="form-control form-control-sm" value="{{ $price['quantity_to'] }}" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="pricing[{{ $index }}][price]" 
                                                   class="form-control form-control-sm" value="{{ $price['price'] }}" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="pricing[{{ $index }}][unit_price]" 
                                                   class="form-control form-control-sm" value="{{ $price['unit_price'] }}" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-price-row">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>
                                        <input type="number" name="pricing[0][quantity_from]" 
                                               class="form-control form-control-sm" value="1" required>
                                    </td>
                                    <td>
                                        <input type="number" name="pricing[0][quantity_to]" 
                                               class="form-control form-control-sm" value="10" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="pricing[0][price]" 
                                               class="form-control form-control-sm" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="pricing[0][unit_price]" 
                                               class="form-control form-control-sm" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-price-row">
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
                        Producto Activo
                    </label>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Crear Producto
            </button>
        </div>
    </form>
</div>

<!-- Modal para agregar color -->
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
                        <label for="colorName" class="form-label">Nombre del Color</label>
                        <input type="text" class="form-control" id="colorName" required>
                    </div>
                    <div class="mb-3">
                        <label for="colorHex" class="form-label">Código Hexadecimal</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="colorPicker" value="#000000">
                            <input type="text" class="form-control" id="colorHex" value="#000000" pattern="^#[0-9A-F]{6}$" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveColorBtn">Guardar Color</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar color de impresión -->
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
                        <label for="printColorName" class="form-label">Nombre del Color</label>
                        <input type="text" class="form-control" id="printColorName" required>
                    </div>
                    <div class="mb-3">
                        <label for="printColorHex" class="form-label">Código Hexadecimal</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="printColorPicker" value="#000000">
                            <input type="text" class="form-control" id="printColorHex" value="#000000" pattern="^#[0-9A-F]{6}$" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="savePrintColorBtn">Guardar Color</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar tamaño -->
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
                        <label for="sizeName" class="form-label">Nombre del Tamaño</label>
                        <input type="text" class="form-control" id="sizeName" required>
                    </div>
                    <div class="mb-3">
                        <label for="sizeCode" class="form-label">Código (opcional)</label>
                        <input type="text" class="form-control" id="sizeCode" placeholder="Ej: S, M, L, XL">
                    </div>
                    <div class="mb-3">
                        <label for="sizeDescription" class="form-label">Descripción (opcional)</label>
                        <textarea class="form-control" id="sizeDescription" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveSizeBtn">Guardar Tamaño</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar token CSRF para peticiones AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Cargar subcategorías cuando se selecciona una categoría
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        subcategorySelect.innerHTML = '<option value="">Cargando...</option>';
        
        if (categoryId) {
            fetch(`/admin/products/subcategories/${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    subcategorySelect.innerHTML = '<option value="">Seleccionar subcategoría</option>';
                    data.forEach(subcategory => {
                        const option = new Option(subcategory.name, subcategory.id);
                        subcategorySelect.add(option);
                    });
                });
        } else {
            subcategorySelect.innerHTML = '<option value="">Primero selecciona una categoría</option>';
        }
    });

    // Manejo de precios dinámicos
    let priceIndex = document.querySelectorAll('#pricing-tbody tr').length;
    
    document.getElementById('add-price-row').addEventListener('click', function() {
        const tbody = document.getElementById('pricing-tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="number" name="pricing[${priceIndex}][quantity_from]" 
                       class="form-control form-control-sm" required>
            </td>
            <td>
                <input type="number" name="pricing[${priceIndex}][quantity_to]" 
                       class="form-control form-control-sm" required>
            </td>
            <td>
                <input type="number" step="0.01" name="pricing[${priceIndex}][price]" 
                       class="form-control form-control-sm" required>
            </td>
            <td>
                <input type="number" step="0.01" name="pricing[${priceIndex}][unit_price]" 
                       class="form-control form-control-sm" required>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-price-row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        priceIndex++;
    });

    // Eliminar fila de precio
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-price-row') || e.target.closest('.remove-price-row')) {
            const row = e.target.closest('tr');
            if (document.querySelectorAll('#pricing-tbody tr').length > 1) {
                row.remove();
            } else {
                alert('Debe haber al menos un rango de precios');
            }
        }
    });

    // Sincronizar color picker con input de texto
    document.getElementById('colorPicker').addEventListener('input', function() {
        document.getElementById('colorHex').value = this.value.toUpperCase();
    });
    
    document.getElementById('colorHex').addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-F]{6}$/i)) {
            document.getElementById('colorPicker').value = this.value;
        }
    });

    document.getElementById('printColorPicker').addEventListener('input', function() {
        document.getElementById('printColorHex').value = this.value.toUpperCase();
    });
    
    document.getElementById('printColorHex').addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-F]{6}$/i)) {
            document.getElementById('printColorPicker').value = this.value;
        }
    });

    // Guardar nuevo color
    document.getElementById('saveColorBtn').addEventListener('click', function() {
        const name = document.getElementById('colorName').value;
        const hexCode = document.getElementById('colorHex').value;

        if (!name || !hexCode) {
            alert('Por favor complete todos los campos');
            return;
        }

        fetch('/admin/available-colors', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
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
                const colorCount = document.querySelectorAll('#colorsContainer .col-md-3').length;
                const newColorHtml = `
                    <div class="col-md-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="colors[]" 
                                   value="${data.color.name}" id="color_${data.color.id}" checked>
                            <label class="form-check-label" for="color_${data.color.id}">
                                <span class="badge" style="background-color: ${data.color.hex_code}; color: ${data.color.hex_code == '#FFFFFF' ? '#000' : '#FFF'}">
                                    ${data.color.name}
                                </span>
                            </label>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', newColorHtml);
                
                // Cerrar el modal y limpiar el formulario
                const modal = bootstrap.Modal.getInstance(document.getElementById('addColorModal'));
                modal.hide();
                document.getElementById('addColorForm').reset();
                document.getElementById('colorPicker').value = '#000000';
                document.getElementById('colorHex').value = '#000000';
                
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar el color');
        });
    });

    // Guardar nuevo color de impresión
    document.getElementById('savePrintColorBtn').addEventListener('click', function() {
        const name = document.getElementById('printColorName').value;
        const hexCode = document.getElementById('printColorHex').value;

        if (!name || !hexCode) {
            alert('Por favor complete todos los campos');
            return;
        }

        fetch('/admin/available-print-colors', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
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
                const newColorHtml = `
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
                container.insertAdjacentHTML('beforeend', newColorHtml);
                
                // Cerrar el modal y limpiar el formulario
                const modal = bootstrap.Modal.getInstance(document.getElementById('addPrintColorModal'));
                modal.hide();
                document.getElementById('addPrintColorForm').reset();
                document.getElementById('printColorPicker').value = '#000000';
                document.getElementById('printColorHex').value = '#000000';
                
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar el color de impresión');
        });
    });

    // Guardar nuevo tamaño
    document.getElementById('saveSizeBtn').addEventListener('click', function() {
        const name = document.getElementById('sizeName').value;
        const code = document.getElementById('sizeCode').value;
        const description = document.getElementById('sizeDescription').value;

        if (!name) {
            alert('Por favor ingrese el nombre del tamaño');
            return;
        }

        fetch('/admin/available-sizes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
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
                const container = document.querySelector('#sizesContainer .row');
                const newSizeHtml = `
                    <div class="col-md-2 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sizes[]" 
                                   value="${data.size.name}" id="size_${data.size.id}" checked>
                            <label class="form-check-label" for="size_${data.size.id}">
                                ${data.size.name}
                                ${data.size.code ? `<small class="text-muted">(${data.size.code})</small>` : ''}
                            </label>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', newSizeHtml);
                
                // Cerrar el modal y limpiar el formulario
                const modal = bootstrap.Modal.getInstance(document.getElementById('addSizeModal'));
                modal.hide();
                document.getElementById('addSizeForm').reset();
                
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar el tamaño');
        });
    });

    // Eliminar color
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-color-btn')) {
            const btn = e.target.closest('.delete-color-btn');
            const id = btn.dataset.id;
            const name = btn.dataset.name;

            if (confirm(`¿Está seguro de eliminar el color "${name}"?`)) {
                fetch(`/admin/available-colors/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`color-item-${id}`).remove();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el color');
                });
            }
        }
    });

    // Eliminar color de impresión
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-print-color-btn')) {
            const btn = e.target.closest('.delete-print-color-btn');
            const id = btn.dataset.id;
            const name = btn.dataset.name;

            if (confirm(`¿Está seguro de eliminar el color de impresión "${name}"?`)) {
                fetch(`/admin/available-print-colors/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`print-color-item-${id}`).remove();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el color de impresión');
                });
            }
        }
    });

    // Eliminar tamaño
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-size-btn')) {
            const btn = e.target.closest('.delete-size-btn');
            const id = btn.dataset.id;
            const name = btn.dataset.name;

            if (confirm(`¿Está seguro de eliminar el tamaño "${name}"?`)) {
                fetch(`/admin/available-sizes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`size-item-${id}`).remove();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el tamaño');
                });
            }
        }
    });
});
</script>
@endpush
@endsection