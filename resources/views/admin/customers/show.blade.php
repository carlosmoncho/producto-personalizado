@extends('layouts.admin')

@section('title', 'Cliente: ' . $customer->name)

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
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.customers.index') }}">Clientes</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $customer->name }}</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-person-fill"></i>
            </div>
            <div>
                <h2 class="mb-0">{{ $customer->name }}</h2>
                <small class="text-muted">Información detallada del cliente</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Editar Cliente
        </a>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información del Cliente -->
    <div class="col-md-8">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Información Personal</h5>
                        <small>Datos de contacto y empresa</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8">{{ $customer->name }}</dd>
                            
                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">
                                <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                            </dd>
                            
                            <dt class="col-sm-4">Teléfono:</dt>
                            <dd class="col-sm-8">
                                @if($customer->phone)
                                    <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                                @else
                                    <span class="text-muted">No especificado</span>
                                @endif
                            </dd>
                            
                            <dt class="col-sm-4">Empresa:</dt>
                            <dd class="col-sm-8">{{ $customer->company ?: 'No especificada' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">NIF/CIF:</dt>
                            <dd class="col-sm-8">{{ $customer->tax_id ?: 'No especificado' }}</dd>
                            
                            <dt class="col-sm-4">País:</dt>
                            <dd class="col-sm-8">{{ $customer->country }}</dd>
                            
                            <dt class="col-sm-4">Estado:</dt>
                            <dd class="col-sm-8">
                                @if($customer->active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </dd>
                            
                            <dt class="col-sm-4">Registro:</dt>
                            <dd class="col-sm-8">{{ $customer->created_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
                
                @if($customer->address)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <dt>Dirección:</dt>
                        <dd class="mt-2">
                            {{ $customer->address }}
                            @if($customer->city || $customer->postal_code)
                                <br>
                                {{ $customer->postal_code ? $customer->postal_code . ' - ' : '' }}{{ $customer->city }}
                            @endif
                        </dd>
                    </div>
                </div>
                @endif
                
                @if($customer->notes)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <dt>Notas:</dt>
                        <dd class="mt-2">{{ $customer->notes }}</dd>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Estadísticas -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Estadísticas</h5>
                        <small>Resumen de actividad</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 mb-0 text-primary">{{ $customer->total_orders_count }}</div>
                            <small class="text-muted">Total Pedidos</small>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 mb-0 text-success">€{{ number_format($customer->total_orders_amount, 2) }}</div>
                            <small class="text-muted">Total Gastado</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="h6 mb-0 text-info">
                                @if($customer->last_order_at)
                                    {{ $customer->last_order_at->format('d/m/Y') }}
                                @else
                                    Nunca
                                @endif
                            </div>
                            <small class="text-muted">Último Pedido</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Pedidos -->
@if($customer->orders->count() > 0)
<div class="card shadow-sm border-0">
    <div class="card-header bg-golden border-bottom-0 py-3">
        <div class="d-flex align-items-center">
            <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <h5 class="mb-0">Historial de Pedidos ({{ $customer->orders->count() }})</h5>
                <small>Todos los pedidos realizados por este cliente</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->orders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                                <br><small class="text-muted">{{ $order->items->count() }} productos</small>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'approved' => 'success',
                                        'in_production' => 'primary',
                                        'shipped' => 'info',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $color = $statusColors[$order->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $order->status_label }}</span>
                            </td>
                            <td>
                                <strong>€{{ number_format($order->total_amount, 2) }}</strong>
                            </td>
                            <td>
                                {{ $order->created_at->format('d/m/Y H:i') }}
                                <br><small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection