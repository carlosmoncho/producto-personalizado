@extends('layouts.admin')

@section('title', 'Categorías')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Categorías</h2>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nueva Categoría
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Lista de Categorías</h5>
    </div>
    <div class="card-body">
        @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Slug</th>
                            <th>Subcategorías</th>
                            <th>Productos</th>
                            <th>Estado</th>
                            <th>Orden</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>
                                    @if($category->getImageUrl())
                                        <img src="{{ $category->getImageUrl() }}" alt="{{ $category->name }}" 
                                             class="rounded" width="50" height="50" style="object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                    @if($category->description)
                                        <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>
                                    <span class="badge bg-info">{{ $category->subcategories_count }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $category->products_count }}</span>
                                </td>
                                <td>
                                    @if($category->active)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td>{{ $category->sort_order }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.categories.show', $category) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $category) }}" 
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm btn-delete" 
                                                    data-item-name="{{ $category->name }}">
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
                    Mostrando {{ $categories->firstItem() ?? 0 }} a {{ $categories->lastItem() ?? 0 }} de {{ $categories->total() }} resultados
                </div>
                <div>
                    {{ $categories->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-folder display-1 text-muted"></i>
                <h4 class="mt-3">No hay categorías</h4>
                <p class="text-muted">Comienza creando tu primera categoría.</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Crear Primera Categoría
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
            
            const categoryName = this.dataset.itemName;
            const form = this.closest('form');
            const categoryId = form.action.split('/').pop();
            
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
            fetch(`{{ url('admin/categories') }}/${categoryId}/dependencies`)
                .then(response => response.json())
                .then(data => {
                    let html = `¿Está seguro de eliminar la categoría <strong>"${categoryName}"</strong>?`;
                    let canDelete = data.can_delete;
                    
                    if (!canDelete) {
                        html += `<br><br><div class="alert alert-warning text-start mt-3 mb-0">`;
                        html += `<strong><i class="bi bi-exclamation-triangle me-2"></i>¡Atención!</strong><br>`;
                        html += `Esta categoría tiene dependencias:<br><br>`;
                        
                        if (data.subcategories_count > 0) {
                            html += `• <strong>${data.subcategories_count} subcategoría(s)</strong><br>`;
                            
                            // Mostrar subcategorías con sus productos
                            data.subcategories.slice(0, 3).forEach(subcategory => {
                                html += `&nbsp;&nbsp;◦ ${subcategory.name}`;
                                if (subcategory.products_count > 0) {
                                    html += ` (${subcategory.products_count} productos)`;
                                }
                                html += `<br>`;
                            });
                            
                            if (data.subcategories_count > 3) {
                                html += `&nbsp;&nbsp;◦ Y ${data.subcategories_count - 3} subcategoría(s) más<br>`;
                            }
                        }
                        
                        if (data.products_count > 0) {
                            html += `• <strong>${data.products_count} producto(s) directo(s)</strong><br>`;
                        }
                        
                        if (data.total_products > 0) {
                            html += `<br><strong>Total: ${data.total_products} producto(s) afectado(s)</strong><br>`;
                        }
                        
                        html += `<br><small>Primero debe eliminar o reasignar estos elementos.</small>`;
                        html += `</div>`;
                    }
                    
                    Swal.fire({
                        title: canDelete ? '¿Eliminar Categoría?' : 'No se puede eliminar',
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
                        title: '¿Eliminar Categoría?',
                        html: `¿Está seguro de eliminar la categoría <strong>"${categoryName}"</strong>?<br><small class="text-muted">No se pudieron verificar las dependencias</small>`,
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