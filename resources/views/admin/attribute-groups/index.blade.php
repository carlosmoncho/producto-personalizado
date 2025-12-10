@extends('layouts.admin')

@section('title', 'Grupos de Atributos')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-house-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Grupos de Atributos</li>
                </ol>
            </nav>
            
            <div class="d-flex align-items-center">
                <div class="icon-square bg-primary text-white rounded-3 me-3">
                    <i class="bi bi-collection-fill"></i>
                </div>
                <div>
                    <h2 class="mb-0">Grupos de Atributos</h2>
                    <small class="text-muted">Organiza y gestiona los atributos por categorías</small>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attribute-groups.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Grupo
            </a>
            <button class="btn btn-outline-secondary" onclick="toggleReorderMode()">
                <i class="bi bi-arrows-move"></i> Reordenar
            </button>
        </div>
    </div>

    <!-- Filtros rápidos -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="searchGroups" placeholder="Buscar grupos...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" id="filterType">
                        <option value="">Todos los tipos</option>
                        <option value="color">Colores</option>
                        <option value="size">Tamaños</option>
                        <option value="material">Materiales</option>
                        <option value="finish">Acabados</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-primary w-100" onclick="applyFilters()">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de grupos -->
    <div class="row" id="groupsList">
        @forelse($groups as $group)
            <div class="col-lg-6 mb-4 group-item" data-group-id="{{ $group->id }}" data-sort-order="{{ $group->sort_order }}">
                <div class="card h-100 shadow-sm {{ !$group->active ? 'opacity-75' : '' }}">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="drag-handle me-3" style="cursor: move; display: none;">
                                    <i class="bi bi-grip-vertical text-muted"></i>
                                </div>
                                <h5 class="mb-0">
                                    <i class="bi bi-{{ $group->type === 'color' ? 'palette' : ($group->type === 'size' ? 'rulers' : 'collection') }} me-2"></i>
                                    {{ $group->name }}
                                </h5>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($group->is_required)
                                    <span class="badge bg-danger">Requerido</span>
                                @endif
                                @if($group->affects_price)
                                    <span class="badge bg-warning">Afecta Precio</span>
                                @endif
                                @if($group->affects_stock)
                                    <span class="badge bg-info">Afecta Stock</span>
                                @endif
                                <span class="badge bg-{{ $group->active ? 'success' : 'secondary' }}">
                                    {{ $group->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Tipo:</small>
                                <div class="fw-semibold">{{ ucfirst($group->type) }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Atributos:</small>
                                <div class="fw-semibold">{{ $group->attributes_count }} items</div>
                            </div>
                        </div>
                        
                        @if($group->description)
                            <p class="text-muted small mb-3">{{ Str::limit($group->description, 100) }}</p>
                        @endif

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($group->allow_multiple)
                                    <span class="badge bg-light text-dark me-1">
                                        <i class="bi bi-check2-square"></i> Selección Múltiple
                                    </span>
                                @endif
                                @if($group->show_in_filter)
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-funnel"></i> En Filtros
                                    </span>
                                @endif
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.attribute-groups.show', $group) }}" 
                                   class="btn btn-outline-primary" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.attribute-groups.edit', $group) }}" 
                                   class="btn btn-outline-secondary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="deleteGroup({{ $group->id }}, '{{ $group->name }}')" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-collection display-1 text-muted mb-3"></i>
                        <h5>No hay grupos de atributos</h5>
                        <p class="text-muted">Crea tu primer grupo para organizar los atributos de productos</p>
                        <a href="{{ route('admin.attribute-groups.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Crear Grupo
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-center">
        {{ $groups->links() }}
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
    
    if (reorderMode) {
        handles.forEach(handle => handle.style.display = 'block');
        initSortable();
    } else {
        handles.forEach(handle => handle.style.display = 'none');
        if (sortable) {
            sortable.destroy();
            saveOrder();
        }
    }
}

function initSortable() {
    const list = document.getElementById('groupsList');
    sortable = Sortable.create(list, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function(evt) {
            // El orden se guardará cuando se desactive el modo reordenar
        }
    });
}

function saveOrder() {
    const items = document.querySelectorAll('.group-item');
    const groups = [];
    
    items.forEach((item, index) => {
        groups.push({
            id: item.dataset.groupId,
            sort_order: index
        });
    });
    
    fetch('{{ route("admin.attribute-groups.reorder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ groups: groups })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Orden actualizado correctamente');
        }
    });
}

function deleteGroup(id, name) {
    if (confirm(`¿Estás seguro de eliminar el grupo "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/attribute-groups/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function applyFilters() {
    const search = document.getElementById('searchGroups').value.toLowerCase();
    const type = document.getElementById('filterType').value;
    const status = document.getElementById('filterStatus').value;
    
    const items = document.querySelectorAll('.group-item');
    
    items.forEach(item => {
        const card = item.querySelector('.card');
        const name = item.querySelector('h5').textContent.toLowerCase();
        const groupType = item.querySelector('.fw-semibold').textContent.toLowerCase();
        const isActive = !card.classList.contains('opacity-75');
        
        let show = true;
        
        if (search && !name.includes(search)) {
            show = false;
        }
        
        if (type && !groupType.includes(type)) {
            show = false;
        }
        
        if (status) {
            if (status === 'active' && !isActive) show = false;
            if (status === 'inactive' && isActive) show = false;
        }
        
        item.style.display = show ? 'block' : 'none';
    });
}

// Búsqueda en tiempo real
document.getElementById('searchGroups').addEventListener('input', applyFilters);
</script>
@endpush

@push('styles')
<style>
.icon-square {
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.group-item.sortable-ghost {
    opacity: 0.4;
}

.group-item.sortable-drag {
    background: white;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
</style>
@endpush