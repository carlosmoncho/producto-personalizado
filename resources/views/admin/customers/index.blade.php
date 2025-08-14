@extends('layouts.admin')

@section('title', 'Clientes')

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
                <li class="breadcrumb-item active" aria-current="page">Clientes</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <h2 class="mb-0">Gestión de Clientes</h2>
                <small class="text-muted">Administra la base de datos de clientes del sistema</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
           class="btn btn-outline-success export-csv-btn" 
           title="Exportar {{ $customers->total() }} cliente{{ $customers->total() !== 1 ? 's' : '' }}{{ request()->has('status') || request()->has('search') || request()->has('has_orders') ? ' filtrados' : '' }} a CSV">
            <i class="bi bi-download me-2"></i>Exportar CSV
            @if($customers->total() > 0)
                <span class="badge bg-success ms-2">{{ $customers->total() }}</span>
            @endif
        </a>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nuevo Cliente
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
                <small>Buscar clientes por diferentes criterios</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.customers.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nombre, email, empresa o teléfono">
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
                    <label for="has_orders" class="form-label">Con Pedidos</label>
                    <select class="form-select" id="has_orders" name="has_orders">
                        <option value="">Todos</option>
                        <option value="yes" {{ request('has_orders') == 'yes' ? 'selected' : '' }}>Con pedidos</option>
                        <option value="no" {{ request('has_orders') == 'no' ? 'selected' : '' }}>Sin pedidos</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
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
                <h5 class="mb-0">Lista de Clientes ({{ $customers->total() }})</h5>
                <small>Todos los clientes registrados en el sistema</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($customers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Empresa</th>
                            <th>Pedidos</th>
                            <th>Total Gastado</th>
                            <th>Estado</th>
                            <th>Último Pedido</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person-fill text-muted"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $customer->name }}</strong>
                                            @if($customer->tax_id)
                                                <br><small class="text-muted">{{ $customer->tax_id }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $customer->email }}</strong>
                                    @if($customer->phone)
                                        <br><small class="text-muted">{{ $customer->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $customer->company ?: '-' }}
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $customer->total_orders_count }}</span>
                                </td>
                                <td>
                                    <strong>€{{ number_format($customer->total_orders_amount, 2) }}</strong>
                                </td>
                                <td>
                                    @if($customer->active)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->last_order_at)
                                        {{ $customer->last_order_at->format('d/m/Y') }}
                                        <br><small class="text-muted">{{ $customer->last_order_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Nunca</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.customers.show', $customer) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.customers.edit', $customer) }}" 
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm btn-delete" 
                                                    data-item-name="{{ $customer->name }}">
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
                    Mostrando {{ $customers->firstItem() ?? 0 }} a {{ $customers->lastItem() ?? 0 }} de {{ $customers->total() }} resultados
                </div>
                <div>
                    {{ $customers->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <h4 class="mt-3">No hay clientes</h4>
                <p class="text-muted">Los clientes aparecerán aquí cuando se registren en el sistema.</p>
                <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Agregar Primer Cliente
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSV Export button functionality
    const exportBtn = document.querySelector('.export-csv-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            // Show loading state
            const originalHtml = this.innerHTML;
            const icon = this.querySelector('i');
            
            // Change to loading state
            icon.className = 'bi bi-hourglass-split me-2';
            const badge = this.querySelector('.badge');
            if (badge) badge.classList.add('d-none');
            
            this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Exportando...';
            this.disabled = true;
            
            // Reset button after download
            setTimeout(() => {
                this.innerHTML = originalHtml;
                this.disabled = false;
                
                // Show success notification
                if (typeof toastr !== 'undefined') {
                    toastr.success('CSV de clientes exportado correctamente');
                }
            }, 2000);
        });
    }
    
    // Manejar botones de eliminar con SweetAlert2
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const customerName = this.dataset.itemName;
            const form = this.closest('form');
            const customerId = form.action.split('/').pop();
            
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
            fetch(`{{ url('admin/customers') }}/${customerId}/dependencies`)
                .then(response => response.json())
                .then(data => {
                    let html = `¿Está seguro de eliminar el cliente <strong>"${customerName}"</strong>?`;
                    let canDelete = data.can_delete;
                    
                    if (!canDelete) {
                        html += `<br><br><div class="alert alert-warning text-start mt-3 mb-0">`;
                        html += `<strong><i class="bi bi-exclamation-triangle me-2"></i>¡Atención!</strong><br>`;
                        html += `Este cliente tiene <strong>${data.orders_count} pedido(s)</strong> asociado(s):<br><br>`;
                        
                        // Mostrar hasta 5 pedidos
                        data.orders.slice(0, 5).forEach(order => {
                            html += `• ${order.order_number} (€${parseFloat(order.total_amount).toFixed(2)})<br>`;
                        });
                        
                        if (data.orders_count > 5) {
                            html += `• Y ${data.orders_count - 5} pedido(s) más<br>`;
                        }
                        
                        html += `<br><strong>Importe total: €${parseFloat(data.total_amount).toFixed(2)}</strong><br>`;
                        html += `<br><small>Los clientes con historial de pedidos no pueden eliminarse para mantener la integridad de los datos.</small>`;
                        html += `</div>`;
                    }
                    
                    Swal.fire({
                        title: canDelete ? '¿Eliminar Cliente?' : 'No se puede eliminar',
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
                        title: '¿Eliminar Cliente?',
                        html: `¿Está seguro de eliminar el cliente <strong>"${customerName}"</strong>?<br><small class="text-muted">No se pudieron verificar las dependencias</small>`,
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