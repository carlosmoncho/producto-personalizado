@extends('layouts.admin')

@section('title', 'Productos')

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
                <li class="breadcrumb-item active" aria-current="page">Productos</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div>
                <h2 class="mb-0">Gestión de Productos</h2>
                <small class="text-muted">Administra el catálogo de productos personalizables</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nuevo Producto
        </a>
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
                <small>Buscar productos por diferentes criterios</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.products.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nombre, SKU o descripción">
                </div>
                <div class="col-md-3">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="subcategory_id" class="form-label">Subcategoría</label>
                    <select class="form-select" id="subcategory_id" name="subcategory_id">
                        <option value="">Todas</option>
                        @foreach($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                {{ $subcategory->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="has_attributes" class="form-label">Atributos</label>
                    <select class="form-select" id="has_attributes" name="has_attributes">
                        <option value="">Todos</option>
                        <option value="1" {{ request('has_attributes') == '1' ? 'selected' : '' }}>Con Atributos</option>
                        <option value="0" {{ request('has_attributes') == '0' ? 'selected' : '' }}>Sin Atributos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-golden border-bottom-0 py-3">
        <div class="d-flex align-items-center">
            <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                <i class="bi bi-list-ul"></i>
            </div>
            <div>
                <h5 class="mb-0">Lista de Productos ({{ $products->total() }})</h5>
                <small>Catálogo completo de productos personalizables</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Atributos</th>
                            <th>Precio Base</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    @if($product->images && count($product->images) > 0)
                                        <img src="{{ $product->getFirstImageUrl() }}" 
                                             alt="{{ $product->name }}" 
                                             class="img-thumbnail" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <div class="d-flex align-items-center">
                                            <strong>{{ $product->name }}</strong>
                                            <span class="badge bg-primary ms-2" title="Producto personalizable">
                                                <i class="bi bi-gear-fill"></i> Personalizable
                                            </span>
                                        </div>
                                        <small class="text-muted">SKU: {{ $product->sku }}</small>
                                        <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $product->category->name }}</strong>
                                    <br><small class="text-muted">{{ $product->subcategory->name }}</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($product->productAttributes->count() > 0)
                                            @php
                                                $groupCounts = $product->productAttributes->groupBy(function($attr) {
                                                    return $attr->attributeGroup->name;
                                                })->map(function($group) {
                                                    return $group->count();
                                                });
                                            @endphp
                                            @foreach($groupCounts as $groupName => $count)
                                                <span class="badge bg-info">{{ $count }} {{ $groupName }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Sin atributos</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $product->printingSystems->pluck('name')->implode(', ') ?: 'No especificado' }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($product->configurator_base_price)
                                        <strong>€{{ number_format($product->configurator_base_price, 2) }}</strong>
                                        <br><small class="text-muted">precio base</small>
                                    @else
                                        <span class="text-muted">Sin precio</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->active)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.products.show', $product) }}"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.products.edit', $product) }}"
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm btn-delete"
                                                    data-item-name="{{ $product->name }}">
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
                    Mostrando {{ $products->firstItem() ?? 0 }} a {{ $products->lastItem() ?? 0 }} de {{ $products->total() }} resultados
                </div>
                <div>
                    {{ $products->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-box-seam display-1 text-muted"></i>
                <h4 class="mt-3">No hay productos</h4>
                <p class="text-muted">Los productos aparecerán aquí cuando los agregues al catálogo.</p>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Agregar Primer Producto
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtro dinámico de subcategorías
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    
    if (categorySelect && subcategorySelect) {
        // Guardar todas las opciones de subcategorías originales
        const allSubcategories = Array.from(subcategorySelect.options).slice(1); // Sin la opción "Todas"
        
        categorySelect.addEventListener('change', function() {
            const selectedCategoryId = this.value;
            
            // Limpiar subcategorías excepto la primera opción
            subcategorySelect.innerHTML = '<option value="">Todas</option>';
            
            if (selectedCategoryId) {
                // Filtrar subcategorías por categoría seleccionada
                fetch(`{{ url('admin/products/subcategories') }}/${selectedCategoryId}`)
                    .then(response => response.json())
                    .then(subcategories => {
                        subcategories.forEach(subcategory => {
                            const option = new Option(subcategory.name, subcategory.id);
                            subcategorySelect.add(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error cargando subcategorías:', error);
                        // Fallback: mostrar todas las subcategorías
                        allSubcategories.forEach(option => {
                            subcategorySelect.add(option.cloneNode(true));
                        });
                    });
            } else {
                // Si no hay categoría seleccionada, mostrar todas las subcategorías
                allSubcategories.forEach(option => {
                    subcategorySelect.add(option.cloneNode(true));
                });
            }
        });
    }
    
    // Funcionalidad de búsqueda en tiempo real mejorada
    const searchInput = document.getElementById('search');
    const searchForm = searchInput ? searchInput.closest('form') : null;
    
    if (searchInput && searchForm) {
        let searchTimeout;
        
        // Desactivar búsqueda automática en tiempo real que puede estar causando problemas
        // En su lugar, permitir búsqueda con Enter o botón de buscar
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                searchForm.submit();
            }
        });
        
        // Opcional: búsqueda automática desactivada por defecto
        // Si quieres reactivarla, descomenta las siguientes líneas:
        /*
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            // Solo buscar si hay al menos 3 caracteres para evitar búsquedas excesivas
            if (searchTerm.length >= 3) {
                searchTimeout = setTimeout(() => {
                    searchForm.submit();
                }, 1000); // Aumentado a 1 segundo para dar más tiempo al usuario
            } else if (searchTerm.length === 0) {
                // Si se borra todo, limpiar después de un tiempo
                searchTimeout = setTimeout(() => {
                    searchForm.submit();
                }, 1000);
            }
        });
        */
    }
    
    // Limpiar todos los filtros
    const clearBtn = document.querySelector('a[href="{{ route("admin.products.index") }}"]');
    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Limpiar todos los campos del formulario
            document.getElementById('search').value = '';
            document.getElementById('category_id').value = '';
            document.getElementById('subcategory_id').value = '';
            document.getElementById('status').value = '';
            document.getElementById('has_attributes').value = '';
            
            // Enviar formulario limpio
            window.location.href = '{{ route("admin.products.index") }}';
        });
    }
    
    // Manejar botones de eliminar con SweetAlert2
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productName = this.dataset.itemName;
            const form = this.closest('form');
            const productId = form.action.split('/').pop();
            
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
            fetch(`{{ url('admin/products') }}/${productId}/dependencies`)
                .then(response => response.json())
                .then(data => {
                    let html = `¿Está seguro de eliminar el producto <strong>"${productName}"</strong>?`;
                    let canDelete = data.can_delete;
                    
                    if (!canDelete) {
                        html += `<br><br><div class="alert alert-warning text-start mt-3 mb-0">`;
                        html += `<strong><i class="bi bi-exclamation-triangle me-2"></i>¡Atención!</strong><br>`;
                        html += `Este producto está incluido en <strong>${data.order_items_count} pedido(s)</strong>:<br><br>`;
                        
                        // Mostrar hasta 5 pedidos
                        data.orders.slice(0, 5).forEach(order => {
                            html += `• Pedido: ${order.order_number}<br>`;
                        });
                        
                        if (data.order_items_count > 5) {
                            html += `• Y ${data.order_items_count - 5} pedido(s) más<br>`;
                        }
                        
                        html += `<br><small>Los productos con historial de pedidos no pueden eliminarse para mantener la integridad de los datos.</small>`;
                        html += `</div>`;
                    }
                    
                    Swal.fire({
                        title: canDelete ? '¿Eliminar Producto?' : 'No se puede eliminar',
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
                        title: '¿Eliminar Producto?',
                        html: `¿Está seguro de eliminar el producto <strong>"${productName}"</strong>?<br><small class="text-muted">No se pudieron verificar las dependencias</small>`,
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