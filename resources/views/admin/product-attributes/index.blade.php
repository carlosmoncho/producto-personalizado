@extends('layouts.admin')

@section('title', 'Atributos de Productos')

@section('content')
<!-- Header con Breadcrumb -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Atributos de Productos</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-palette-fill"></i>
            </div>
            <div>
                <h2 class="mb-0">Gestión de Atributos</h2>
                <small class="text-muted">Configura colores, materiales, tamaños y otros atributos para el configurador</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.product-attributes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nuevo Atributo
        </a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots"></i> Más
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="importAttributes()">
                    <i class="bi bi-upload me-2"></i>Importar Atributos
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportAttributes()">
                    <i class="bi bi-download me-2"></i>Exportar Atributos
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Estadísticas rápidas -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card text-center border-0 bg-light">
            <div class="card-body py-3">
                <div class="text-primary display-6 mb-2"><i class="bi bi-palette"></i></div>
                <h6 class="text-muted mb-1">Colores</h6>
                <h4 class="mb-0">{{ $totalCounts['color'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-0 bg-light">
            <div class="card-body py-3">
                <div class="text-success display-6 mb-2"><i class="bi bi-layers"></i></div>
                <h6 class="text-muted mb-1">Materiales</h6>
                <h4 class="mb-0">{{ $totalCounts['material'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-0 bg-light">
            <div class="card-body py-3">
                <div class="text-warning display-6 mb-2"><i class="bi bi-rulers"></i></div>
                <h6 class="text-muted mb-1">Tamaños</h6>
                <h4 class="mb-0">{{ $totalCounts['size'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-0 bg-light">
            <div class="card-body py-3">
                <div class="text-info display-6 mb-2"><i class="bi bi-droplet"></i></div>
                <h6 class="text-muted mb-1">Tintas</h6>
                <h4 class="mb-0">{{ $totalCounts['ink'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-0 bg-light">
            <div class="card-body py-3">
                <div class="text-secondary display-6 mb-2"><i class="bi bi-boxes"></i></div>
                <h6 class="text-muted mb-1">Cantidades</h6>
                <h4 class="mb-0">{{ $totalCounts['quantity'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-0 bg-light">
            <div class="card-body py-3">
                <div class="text-dark display-6 mb-2"><i class="bi bi-brush"></i></div>
                <h6 class="text-muted mb-1">Acabados</h6>
                <h4 class="mb-0">{{ $totalCounts['finish'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-golden border-bottom-0 py-3">
        <div class="d-flex align-items-center">
            <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                <i class="bi bi-funnel-fill"></i>
            </div>
            <div>
                <h5 class="mb-0">Filtros de Búsqueda</h5>
                <small>Buscar atributos por tipo y características</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.product-attributes.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nombre o valor">
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Tipo</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Todos los tipos</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="active" class="form-label">Estado</label>
                    <select class="form-select" id="active" name="active">
                        <option value="">Todos</option>
                        <option value="active" {{ request('active') == 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('active') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin.product-attributes.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Limpiar
                        </a>
                        <button type="button" class="btn btn-outline-info" onclick="toggleBulkActions()">
                            <i class="bi bi-check-square me-1"></i>Acciones en Lote
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Acciones en lote (ocultas por defecto) -->
<div id="bulkActionsCard" class="card shadow-sm mb-4 border-0" style="display: none;">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <input type="checkbox" class="form-check-input me-3" id="selectAll">
                <span class="text-muted">Seleccionados: <span id="selectedCount">0</span></span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-success btn-sm" onclick="bulkActivate()">
                    <i class="bi bi-check-circle me-1"></i>Activar
                </button>
                <button class="btn btn-outline-warning btn-sm" onclick="bulkDeactivate()">
                    <i class="bi bi-dash-circle me-1"></i>Desactivar
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="bulkDelete()">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de atributos -->
<div class="card shadow-sm border-0">
    <div class="card-header border-bottom-0 bg-white py-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0">
                <i class="bi bi-table me-2"></i>
                Atributos ({{ $attributes->total() }} total{{ $attributes->total() != 1 ? 'es' : '' }})
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="refreshTable()">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="sortByOrder()">
                            <i class="bi bi-sort-numeric-down me-2"></i>Ordenar por Posición
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="sortByType()">
                            <i class="bi bi-funnel me-2"></i>Agrupar por Tipo
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if($attributes->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="attributesTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <div class="form-check d-none" id="bulkCheckHeader">
                                <input class="form-check-input" type="checkbox" id="selectAllTable">
                            </div>
                        </th>
                        <th width="60">Vista Previa</th>
                        <th>Nombre</th>
                        <th>Grupo</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Orden</th>
                        <th width="180">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attributes as $attribute)
                        <tr data-id="{{ $attribute->id }}">
                            <td>
                                <div class="form-check d-none bulk-checkbox">
                                    <input class="form-check-input" type="checkbox" value="{{ $attribute->id }}">
                                </div>
                            </td>
                            <td>
                                @if($attribute->type === 'color' && $attribute->hex_code)
                                    <div class="color-preview"
                                         style="width: 30px; height: 30px; background-color: {{ $attribute->hex_code }}; border-radius: 6px; border: 2px solid #ddd;">
                                    </div>
                                @elseif($attribute->type === 'ink' && $attribute->hex_code)
                                    <div class="ink-preview"
                                         style="width: 30px; height: 30px; background-color: {{ $attribute->hex_code }}; border-radius: 4px; border: 2px solid #ddd;">
                                    </div>
                                @elseif($attribute->type === 'ink_color' && $attribute->hex_code)
                                    <div class="color-preview"
                                         style="width: 30px; height: 30px; background-color: {{ $attribute->hex_code }}; border-radius: 6px; border: 2px solid #ddd;">
                                    </div>
                                @else
                                    <div class="attribute-icon text-muted">
                                        @switch($attribute->type)
                                            @case('material')
                                                <i class="bi bi-layers fs-4"></i>
                                                @break
                                            @case('size')
                                                <i class="bi bi-rulers fs-4"></i>
                                                @break
                                            @case('quantity')
                                                <i class="bi bi-boxes fs-4"></i>
                                                @break
                                            @case('system')
                                                <i class="bi bi-gear fs-4"></i>
                                                @break
                                            @case('cliche')
                                                <i class="bi bi-stamp fs-4"></i>
                                                @break
                                            @default
                                                <i class="bi bi-circle fs-4"></i>
                                        @endswitch
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $attribute->name }}</span>
                                    @if($attribute->is_recommended)
                                        <small class="text-success">
                                            <i class="bi bi-star-fill me-1"></i>Recomendado
                                        </small>
                                    @endif
                                    @if($attribute->metadata && isset($attribute->metadata['certifications']))
                                        <div class="mt-1">
                                            @foreach($attribute->metadata['certifications'] as $cert)
                                                <span class="badge bg-light text-dark me-1" style="font-size: 0.7rem;">{{ $cert }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($attribute->attributeGroup)
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium">{{ $attribute->attributeGroup->name }}</span>
                                        <small class="text-muted">{{ ucfirst($attribute->attributeGroup->type) }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">Sin grupo asignado</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $attribute->type === 'color' ? 'primary' : ($attribute->type === 'material' ? 'success' : ($attribute->type === 'size' ? 'warning' : ($attribute->type === 'ink' ? 'info' : ($attribute->type === 'ink_color' ? 'primary' : 'secondary')))) }}">
                                    {{ $types[$attribute->type] ?? $attribute->type }}
                                </span>
                            </td>
                            <td>
                                <code>{{ $attribute->value }}</code>
                            </td>
                            <td>
                                <small class="text-muted">Por dependencias</small>
                            </td>
                            <td>
                                @if($attribute->active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <input type="number" class="form-control form-control-sm order-input"
                                           id="order-{{ $attribute->id }}"
                                           value="{{ $attribute->sort_order ?? 0 }}"
                                           min="0" max="9999"
                                           onchange="updateOrder({{ $attribute->id }}, this.value)"
                                           style="width: 60px;">
                                    <div class="btn-group-vertical" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1"
                                                onclick="moveUp({{ $attribute->id }})"
                                                title="Subir">
                                            <i class="bi bi-chevron-up" style="font-size: 0.7rem;"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1"
                                                onclick="moveDown({{ $attribute->id }})"
                                                title="Bajar">
                                            <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.product-attributes.show', $attribute) }}" 
                                       class="btn btn-outline-primary btn-sm"
                                       title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.product-attributes.edit', $attribute) }}" 
                                       class="btn btn-outline-secondary btn-sm"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.product-attributes.duplicate', $attribute) }}" 
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info btn-sm"
                                                title="Duplicar">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.product-attributes.destroy', $attribute) }}" 
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-delete" 
                                                data-item-name="{{ $attribute->name }}"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="card-footer bg-white border-top-0">
            {{ $attributes->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Estado vacío -->
        <div class="card-body text-center py-5">
            <div class="mb-3">
                <i class="bi bi-palette display-1 text-muted"></i>
            </div>
            <h5 class="text-muted mb-3">No hay atributos configurados</h5>
            <p class="text-muted mb-4">Comienza creando atributos como colores, materiales y tamaños para el configurador de productos.</p>
            <a href="{{ route('admin.product-attributes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Crear Primer Atributo
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
let bulkActionsVisible = false;

// Toggle bulk actions
function toggleBulkActions() {
    bulkActionsVisible = !bulkActionsVisible;
    const card = document.getElementById('bulkActionsCard');
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    const header = document.getElementById('bulkCheckHeader');
    
    if (bulkActionsVisible) {
        card.style.display = 'block';
        checkboxes.forEach(cb => cb.classList.remove('d-none'));
        header.classList.remove('d-none');
    } else {
        card.style.display = 'none';
        checkboxes.forEach(cb => cb.classList.add('d-none'));
        header.classList.add('d-none');
        // Deselect all
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        updateSelectedCount();
    }
}

// Update selected count
function updateSelectedCount() {
    const selected = document.querySelectorAll('.bulk-checkbox input:checked').length;
    document.getElementById('selectedCount').textContent = selected;
}

// Select all functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const selectAllTable = document.getElementById('selectAllTable');
    
    [selectAll, selectAllTable].forEach(checkbox => {
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                const isChecked = this.checked;
                document.querySelectorAll('.bulk-checkbox input').forEach(cb => {
                    cb.checked = isChecked;
                });
                // Sync both select all checkboxes
                [selectAll, selectAllTable].forEach(cb => cb.checked = isChecked);
                updateSelectedCount();
            });
        }
    });
    
    // Individual checkbox change
    document.querySelectorAll('.bulk-checkbox input').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
});

// Update order
function updateOrder(attributeId, newOrder) {
    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch('{{ route("admin.product-attributes.updateOrder") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            orders: [{
                id: attributeId,
                sort_order: parseInt(newOrder)
            }]
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success feedback
            showToast('Orden actualizado correctamente', 'success');
        } else {
            showToast(data.message || 'Error al actualizar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error al actualizar el orden', 'error');
    });
}

// Move attribute up
function moveUp(attributeId) {
    const input = document.getElementById(`order-${attributeId}`);
    const currentValue = parseInt(input.value);
    const newValue = Math.max(0, currentValue - 1);
    input.value = newValue;
    updateOrder(attributeId, newValue);
}

// Move attribute down
function moveDown(attributeId) {
    const input = document.getElementById(`order-${attributeId}`);
    const currentValue = parseInt(input.value);
    const newValue = currentValue + 1;
    input.value = newValue;
    updateOrder(attributeId, newValue);
}

// Bulk actions
function bulkActivate() {
    const selected = getSelectedIds();
    if (selected.length === 0) {
        alert('Selecciona al menos un atributo');
        return;
    }
    
    if (confirm(`¿Activar ${selected.length} atributo(s)?`)) {
        // Implement bulk activate
        showToast('Atributos activados', 'success');
    }
}

function bulkDeactivate() {
    const selected = getSelectedIds();
    if (selected.length === 0) {
        alert('Selecciona al menos un atributo');
        return;
    }
    
    if (confirm(`¿Desactivar ${selected.length} atributo(s)?`)) {
        // Implement bulk deactivate
        showToast('Atributos desactivados', 'warning');
    }
}

function bulkDelete() {
    const selected = getSelectedIds();
    if (selected.length === 0) {
        alert('Selecciona al menos un atributo');
        return;
    }
    
    if (confirm(`¿ELIMINAR ${selected.length} atributo(s)? Esta acción no se puede deshacer.`)) {
        // Implement bulk delete
        showToast('Atributos eliminados', 'success');
    }
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.bulk-checkbox input:checked')).map(cb => cb.value);
}

// Utility functions
function refreshTable() {
    location.reload();
}

function sortByOrder() {
    window.location.href = '{{ route("admin.product-attributes.index") }}?sort=order';
}

function sortByType() {
    window.location.href = '{{ route("admin.product-attributes.index") }}?sort=type';
}

function importAttributes() {
    // Implement import functionality
    showToast('Función de importación en desarrollo', 'info');
}

function exportAttributes() {
    // Implement export functionality
    showToast('Función de exportación en desarrollo', 'info');
}

function showToast(message, type = 'info') {
    // Simple toast implementation
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 5000);
}

// Handle delete buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const itemName = this.dataset.itemName;
            
            if (confirm(`¿Estás seguro de eliminar el atributo "${itemName}"?`)) {
                this.closest('form').submit();
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.icon-square {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.color-preview, .ink-preview {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    color: #6c757d;
    background-color: #f8f9fa !important;
}

.pagination-wrapper .pagination {
    margin-bottom: 0;
}

.btn-group .btn {
    border: 1px solid #dee2e6;
}

.btn-group .btn:hover {
    z-index: 1;
}

.order-input::-webkit-inner-spin-button,
.order-input::-webkit-outer-spin-button {
    opacity: 1;
}

.btn-group-vertical .btn {
    padding: 2px 6px;
    line-height: 1;
    font-size: 0.7rem;
}

.btn-group-vertical .btn:hover {
    background-color: var(--bs-secondary);
    border-color: var(--bs-secondary);
    color: white;
}
</style>
@endpush