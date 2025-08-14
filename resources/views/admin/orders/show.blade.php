@extends('layouts.admin')

@section('title', 'Detalles del Pedido')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Pedido</h5>
            </div>
            <div class="card-body">
                <h4>{{ $order->order_number }}</h4>
                
                <div class="mb-3">
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
                    <span class="badge bg-{{ $color }} fs-6">{{ $order->status_label }}</span>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-5"><strong>Total:</strong></div>
                    <div class="col-sm-7">
                        <h5 class="text-success mb-0">€{{ number_format($order->total_amount, 2) }}</h5>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-sm-5"><strong>Fecha:</strong></div>
                    <div class="col-sm-7">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>

                @if($order->approved_at)
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Aprobado:</strong></div>
                        <div class="col-sm-7">{{ $order->approved_at->format('d/m/Y H:i') }}</div>
                    </div>
                @endif

                @if($order->shipped_at)
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Enviado:</strong></div>
                        <div class="col-sm-7">{{ $order->shipped_at->format('d/m/Y H:i') }}</div>
                    </div>
                @endif

                @if($order->delivered_at)
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Entregado:</strong></div>
                        <div class="col-sm-7">{{ $order->delivered_at->format('d/m/Y H:i') }}</div>
                    </div>
                @endif

                <div class="mt-4">
                    <h6>Cambiar Estado:</h6>
                    <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                        @csrf
                        @method('PATCH')
                        <div class="input-group">
                            <select name="status" class="form-select form-select-sm">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Procesando</option>
                                <option value="approved" {{ $order->status == 'approved' ? 'selected' : '' }}>Aprobado</option>
                                <option value="in_production" {{ $order->status == 'in_production' ? 'selected' : '' }}>En Producción</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Enviado</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Entregado</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i>Editar
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Timeline del pedido -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Timeline del Pedido</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Pedido Creado</h6>
                            <p class="timeline-text">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($order->approved_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pedido Aprobado</h6>
                                <p class="timeline-text">{{ $order->approved_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($order->shipped_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pedido Enviado</h6>
                                <p class="timeline-text">{{ $order->shipped_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($order->delivered_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pedido Entregado</h6>
                                <p class="timeline-text">{{ $order->delivered_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Información del cliente -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Información del Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nombre:</strong> 
                        @if($order->customer_id && $order->customer)
                            <a href="{{ route('admin.customers.show', $order->customer) }}" class="text-decoration-none">
                                {{ $order->customer_name }}
                                <i class="bi bi-external-link ms-1"></i>
                            </a>
                        @else
                            {{ $order->customer_name }}
                        @endif
                        <br>
                        <strong>Email:</strong> {{ $order->customer_email }}<br>
                        <strong>Teléfono:</strong> {{ $order->customer_phone }}
                    </div>
                    <div class="col-md-6">
                        <strong>Dirección:</strong><br>
                        {{ $order->customer_address }}
                        
                        @if($order->customer_id && $order->customer)
                            <div class="mt-2">
                                <a href="{{ route('admin.customers.show', $order->customer) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-person me-1"></i>Ver Ficha Cliente
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                @if($order->notes)
                    <div class="mt-3">
                        <strong>Notas:</strong><br>
                        <div class="alert alert-info">{{ $order->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items del pedido -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Productos del Pedido ({{ $order->items->count() }})</h5>
            </div>
            <div class="card-body">
                @if($order->items->count() > 0)
                    @foreach($order->items as $item)
                        <div class="border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-2">
                                    @if($item->product->getFirstImageUrl())
                                        <img src="{{ $item->product->getFirstImageUrl() }}" 
                                             alt="{{ $item->product->name }}" 
                                             class="img-fluid rounded">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 100%; height: 80px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h6>
                                        <a href="{{ route('admin.products.show', $item->product) }}" class="text-decoration-none">
                                            {{ $item->product->name }}
                                            <i class="bi bi-external-link ms-1"></i>
                                        </a>
                                    </h6>
                                    <p class="text-muted mb-1">SKU: {{ $item->product->sku }}</p>
                                    <p class="mb-1"><strong>Tamaño:</strong> {{ $item->selected_size }}</p>
                                    <p class="mb-1"><strong>Cantidad:</strong> {{ $item->quantity }}</p>
                                    
                                    <div class="mt-2">
                                        <a href="{{ route('admin.products.show', $item->product) }}" class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-box me-1"></i>Ver Producto
                                        </a>
                                    </div>
                                    
                                    @if($item->design_comments)
                                        <p class="mb-1 mt-2"><strong>Comentarios:</strong> {{ $item->design_comments }}</p>
                                    @endif
                                </div>
                                <div class="col-md-2 text-center">
                                    @if($item->getDesignImageUrl())
                                        <img src="{{ $item->getDesignImageUrl() }}" 
                                             alt="Diseño" class="img-fluid rounded">
                                        <small class="d-block mt-1">Diseño</small>
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 100%; height: 80px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                        <small class="d-block mt-1 text-muted">Sin diseño</small>
                                    @endif
                                </div>
                                <div class="col-md-2 text-end">
                                    <h6>€{{ number_format($item->unit_price, 2) }}</h6>
                                    <small class="text-muted">por unidad</small>
                                    <hr>
                                    <h5>€{{ number_format($item->total_price, 2) }}</h5>
                                    <small class="text-muted">total</small>
                                </div>
                            </div>

                            @if($item->custom_field_values && count($item->custom_field_values) > 0)
                                <div class="mt-3">
                                    <h6>Campos Personalizados:</h6>
                                    <div class="row">
                                        @foreach($item->custom_field_values as $fieldKey => $fieldValue)
                                            <div class="col-md-6">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $fieldKey)) }}:</strong> {{ $fieldValue }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <!-- Total del pedido -->
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <strong>Total del Pedido:</strong>
                                        <strong class="text-success">€{{ number_format($order->total_amount, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-cart display-4 text-muted"></i>
                        <h6 class="mt-2">No hay productos en este pedido</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -31px;
    top: 18px;
    width: 4px;
    height: calc(100% + 20px);
    background-color: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-title {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0;
}
</style>
@endsection
