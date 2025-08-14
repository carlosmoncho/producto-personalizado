@extends('layouts.admin')

@section('title', 'Subcategorías')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Subcategorías</h2>
    <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nueva Subcategoría
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.subcategories.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nombre o descripción">
                </div>
                <div class="col-md-4">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Lista de Subcategorías ({{ $subcategories->total() }})</h5>
    </div>
    <div class="card-body">
        @if($subcategories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Productos</th>
                            <th>Estado</th>
                            <th>Orden</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subcategories as $subcategory)
                            <tr>
                                <td>
                                    @if($subcategory->getImageUrl())
                                        <img src="{{ $subcategory->getImageUrl() }}" alt="{{ $subcategory->name }}" 
                                             class="rounded" width="50" height="50" style="object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $subcategory->name }}</strong>
                                    @if($subcategory->description)
                                        <br><small class="text-muted">{{ Str::limit($subcategory->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $subcategory->category->name }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $subcategory->products->count() }}</span>
                                </td>
                                <td>
                                    @if($subcategory->active)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td>{{ $subcategory->sort_order }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.subcategories.show', $subcategory) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.subcategories.edit', $subcategory) }}" 
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.subcategories.destroy', $subcategory) }}" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm btn-delete" 
                                                    data-item-name="{{ $subcategory->name }}">
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
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando {{ $subcategories->firstItem() ?? 0 }} a {{ $subcategories->lastItem() ?? 0 }} de {{ $subcategories->total() }} resultados
                </div>
                <div>
                    {{ $subcategories->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-folder2 display-1 text-muted"></i>
                <h4 class="mt-3">No hay subcategorías</h4>
                <p class="text-muted">Comienza creando tu primera subcategoría.</p>
                <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Crear Primera Subcategoría
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar botones de eliminar con SweetAlert2
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const subcategoryName = this.dataset.itemName;
            const form = this.closest('form');
            const subcategoryId = form.action.split('/').pop();
            
            // Mostrar loading mientras se verifica dependencias
            Swal.fire({
                title: 'Verificando dependencias...',
                text: 'Por favor espere',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Verificar dependencias via AJAX
            fetch(`{{ url('admin/subcategories') }}/${subcategoryId}/dependencies`)
                .then(response => response.json())
                .then(data => {
                    let html = `¿Está seguro de eliminar la subcategoría <strong>"${subcategoryName}"</strong>?`;
                    let canDelete = data.can_delete;
                    
                    if (!canDelete) {
                        html += `<br><br><div class="alert alert-warning text-start mt-3 mb-0">`;
                        html += `<strong><i class="bi bi-exclamation-triangle me-2"></i>¡Atención!</strong><br>`;
                        html += `Esta subcategoría tiene <strong>${data.products_count} producto(s)</strong> asociados:<br><br>`;
                        
                        // Mostrar hasta 5 productos
                        data.products.slice(0, 5).forEach(product => {
                            html += `• ${product.name} (${product.sku})<br>`;
                        });
                        
                        if (data.products_count > 5) {
                            html += `• Y ${data.products_count - 5} producto(s) más<br>`;
                        }
                        
                        html += `<br><small>Primero debe eliminar o reasignar estos productos.</small>`;
                        html += `</div>`;
                    }
                    
                    Swal.fire({
                        title: canDelete ? '¿Eliminar Subcategoría?' : 'No se puede eliminar',
                        html: html,
                        icon: canDelete ? 'warning' : 'error',
                        showCancelButton: true,
                        confirmButtonColor: canDelete ? '#dc3545' : '#6c757d',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: canDelete ? '<i class="bi bi-trash me-2"></i>Sí, eliminar' : '<i class="bi bi-check me-2"></i>Entendido',
                        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Cancelar',
                        focusCancel: true,
                        customClass: {
                            icon: canDelete ? 'swal-icon-warning' : 'swal-icon-error'
                        }
                    }).then((result) => {
                        if (result.isConfirmed && canDelete) {
                            form.submit();
                        }
                    });
                })
                .catch(error => {
                    console.error('Error verificando dependencias:', error);
                    Swal.fire({
                        title: '¿Eliminar Subcategoría?',
                        html: `¿Está seguro de eliminar la subcategoría <strong>"${subcategoryName}"</strong>?<br><small class="text-muted">No se pudieron verificar las dependencias</small>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-trash me-2"></i>Sí, eliminar',
                        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Cancelar',
                        focusCancel: true,
                        customClass: {
                            icon: 'swal-icon-warning'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
        });
    });
});
</script>
@endpush