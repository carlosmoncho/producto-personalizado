@extends('layouts.admin')

@section('title', $attributeGroup->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.attribute-groups.index') }}">Grupos de Atributos</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $attributeGroup->name }}</li>
                </ol>
            </nav>
            
            <div class="d-flex align-items-center">
                <div class="icon-square bg-primary text-white rounded-3 me-3">
                    <i class="bi bi-collection-fill"></i>
                </div>
                <div>
                    <h2 class="mb-0">{{ $attributeGroup->name }}</h2>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span class="badge bg-{{ $attributeGroup->active ? 'success' : 'secondary' }}">
                            {{ $attributeGroup->active ? 'Activo' : 'Inactivo' }}
                        </span>
                        @if($attributeGroup->is_required)
                            <span class="badge bg-danger">Requerido</span>
                        @endif
                        @if($attributeGroup->affects_price)
                            <span class="badge bg-warning">Afecta Precio</span>
                        @endif
                        @if($attributeGroup->affects_stock)
                            <span class="badge bg-info">Afecta Stock</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attribute-groups.edit', $attributeGroup) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Editar Grupo
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttributeModal">
                <i class="bi bi-plus-circle"></i> Añadir Atributo
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Información del grupo -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Información del Grupo</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Tipo:</dt>
                        <dd class="col-sm-7">{{ ucfirst($attributeGroup->type) }}</dd>

                        <dt class="col-sm-5">Slug:</dt>
                        <dd class="col-sm-7"><code>{{ $attributeGroup->slug }}</code></dd>

                        <dt class="col-sm-5">Orden:</dt>
                        <dd class="col-sm-7">{{ $attributeGroup->sort_order }}</dd>

                        <dt class="col-sm-5">Atributos:</dt>
                        <dd class="col-sm-7">{{ $attributeGroup->attributes->count() }} items</dd>

                        <dt class="col-sm-5">Selección:</dt>
                        <dd class="col-sm-7">
                            {{ $attributeGroup->allow_multiple ? 'Múltiple' : 'Única' }}
                        </dd>

                        <dt class="col-sm-5">En filtros:</dt>
                        <dd class="col-sm-7">
                            {{ $attributeGroup->show_in_filter ? 'Sí' : 'No' }}
                        </dd>
                    </dl>

                    @if($attributeGroup->description)
                        <hr>
                        <h6>Descripción:</h6>
                        <p class="text-muted">{{ $attributeGroup->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Lista de atributos -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Atributos del Grupo</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleReorderMode()">
                        <i class="bi bi-arrows-move"></i> Reordenar
                    </button>
                </div>
                <div class="card-body">
                    @if($attributeGroup->attributes->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="mt-3 text-muted">No hay atributos en este grupo</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttributeModal">
                                <i class="bi bi-plus-circle"></i> Añadir Primer Atributo
                            </button>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40" class="drag-column" style="display: none;"></th>
                                        <th>Vista</th>
                                        <th>Nombre</th>
                                        <th>Valor</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                        <th width="120">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="attributesList">
                                    @foreach($attributeGroup->attributes as $attribute)
                                        <tr data-attribute-id="{{ $attribute->id }}">
                                            <td class="drag-handle" style="display: none; cursor: move;">
                                                <i class="bi bi-grip-vertical text-muted"></i>
                                            </td>
                                            <td>
                                                @if($attributeGroup->type === 'color' && $attribute->hex_code)
                                                    <div class="d-flex align-items-center">
                                                        <div style="width: 30px; height: 30px; background-color: {{ $attribute->hex_code }}; 
                                                                    border-radius: 4px; border: 1px solid #ddd;"></div>
                                                        @if($attribute->pantone_code)
                                                            <small class="ms-2 text-muted">{{ $attribute->pantone_code }}</small>
                                                        @endif
                                                    </div>
                                                @elseif($attribute->image_path)
                                                    <img src="{{ Storage::url($attribute->image_path) }}" 
                                                         alt="{{ $attribute->name }}" 
                                                         class="img-thumbnail" 
                                                         style="max-width: 50px;">
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $attribute->name }}</strong>
                                                @if($attribute->is_recommended)
                                                    <span class="badge bg-primary ms-1">Recomendado</span>
                                                @endif
                                            </td>
                                            <td><code>{{ $attribute->value }}</code></td>
                                            <td>
                                                @if($attribute->price_modifier != 0)
                                                    <span class="badge bg-warning">
                                                        {{ $attribute->price_modifier > 0 ? '+' : '' }}€{{ number_format($attribute->price_modifier, 2) }}
                                                    </span>
                                                @endif
                                                @if($attribute->price_percentage != 0)
                                                    <span class="badge bg-info">
                                                        {{ $attribute->price_percentage > 0 ? '+' : '' }}{{ $attribute->price_percentage }}%
                                                    </span>
                                                @endif
                                                @if($attribute->price_modifier == 0 && $attribute->price_percentage == 0)
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attribute->stock_quantity !== null)
                                                    <span class="badge bg-{{ $attribute->stock_quantity > 0 ? 'success' : 'danger' }}">
                                                        {{ $attribute->stock_quantity }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">∞</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $attribute->active ? 'success' : 'secondary' }}">
                                                    {{ $attribute->active ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.product-attributes.edit', $attribute) }}" 
                                                       class="btn btn-outline-secondary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteAttribute({{ $attribute->id }}, '{{ $attribute->name }}')" 
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para añadir atributo -->
<div class="modal fade" id="addAttributeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Atributo a {{ $attributeGroup->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.attribute-groups.add-attribute', $attributeGroup) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="value" class="form-label">Valor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="value" name="value" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>

                    @if($attributeGroup->type === 'color')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hex_code" class="form-label">Código de Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           id="color_picker" onchange="updateHexCode()" style="width: 60px;">
                                    <input type="text" class="form-control" id="hex_code" name="hex_code" 
                                           placeholder="#FFFFFF">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Imagen/Textura</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price_modifier" class="form-label">Modificador de Precio (€)</label>
                            <input type="number" class="form-control" id="price_modifier" name="price_modifier" 
                                   step="0.01" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Disponible</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                   min="0" placeholder="Dejar vacío para stock ilimitado">
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_recommended" name="is_recommended" value="1">
                        <label class="form-check-label" for="is_recommended">
                            Marcar como recomendado
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Añadir Atributo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
let reorderMode = false;
let sortable = null;

function toggleReorderMode() {
    reorderMode = !reorderMode;
    const handles = document.querySelectorAll('.drag-handle');
    const column = document.querySelector('.drag-column');
    
    if (reorderMode) {
        handles.forEach(handle => handle.style.display = 'table-cell');
        if (column) column.style.display = 'table-cell';
        initSortable();
    } else {
        handles.forEach(handle => handle.style.display = 'none');
        if (column) column.style.display = 'none';
        if (sortable) {
            sortable.destroy();
            // Aquí guardarías el orden
        }
    }
}

function initSortable() {
    const list = document.getElementById('attributesList');
    sortable = Sortable.create(list, {
        handle: '.drag-handle',
        animation: 150
    });
}

function updateHexCode() {
    const picker = document.getElementById('color_picker');
    const input = document.getElementById('hex_code');
    input.value = picker.value;
}

function deleteAttribute(id, name) {
    if (confirm(`¿Estás seguro de eliminar el atributo "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/product-attributes/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-generar valor desde el nombre
document.getElementById('name')?.addEventListener('input', function() {
    const value = document.getElementById('value');
    if (!value.dataset.manual) {
        value.value = this.value
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '_')
            .replace(/_+/g, '_')
            .trim();
    }
});

document.getElementById('value')?.addEventListener('input', function() {
    this.dataset.manual = 'true';
});
</script>
@endpush