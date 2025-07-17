@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Estadísticas principales -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="card-title h2 mb-0">{{ $totalProducts }}</h3>
                        <p class="card-text">Productos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-box"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="card-title h2 mb-0">{{ $totalCategories }}</h3>
                        <p class="card-text">Categorías</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-folder"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="card-title h2 mb-0">{{ $totalOrders }}</h3>
                        <p class="card-text">Pedidos</p>
                        @if($ordersGrowth != 0)
                            <small class="text-white-50">
                                <i class="bi bi-arrow-{{ $ordersGrowth > 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($ordersGrowth), 1) }}% vs semana anterior
                            </small>
                        @endif
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="card-title h2 mb-0">€{{ number_format($totalRevenue, 2) }}</h3>
                        <p class="card-text">Ingresos</p>
                        @if($revenueGrowth != 0)
                            <small class="text-white-50">
                                <i class="bi bi-arrow-{{ $revenueGrowth > 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($revenueGrowth), 1) }}% vs semana anterior
                            </small>
                        @endif
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-euro"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de ventas -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Ventas por Mes
                </h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Pedidos por estado -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2"></i>
                    Pedidos por Estado
                </h5>
            </div>
            <div class="card-body">
                @foreach($ordersByStatus as $status => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-{{ $status === 'pending' ? 'warning' : ($status === 'approved' ? 'success' : 'secondary') }}">
                            {{ ucfirst($status) }}
                        </span>
                        <span class="fw-bold">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pedidos recientes -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Pedidos Recientes
                </h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-light">
                    Ver todos
                </a>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>€{{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'approved' ? 'success' : 'secondary') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No hay pedidos recientes</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Productos más vendidos -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-trophy me-2"></i>
                    Productos Más Vendidos
                </h5>
            </div>
            <div class="card-body">
                @if($topProducts->count() > 0)
                    @foreach($topProducts as $item)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">{{ $item->product->name }}</h6>
                                <small class="text-muted">{{ $item->product->sku }}</small>
                            </div>
                            <span class="badge bg-primary">{{ $item->total_quantity }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-4">No hay datos de ventas</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nuevo Producto
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-folder-plus me-2"></i>
                            Nueva Categoría
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.orders.index') }}?status=pending" class="btn btn-outline-warning w-100">
                            <i class="bi bi-clock me-2"></i>
                            Pedidos Pendientes
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.custom-fields.create') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-sliders me-2"></i>
                            Nuevo Campo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de ventas
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesByMonth->pluck('month')) !!},
            datasets: [{
                label: 'Ventas (€)',
                data: {!! json_encode($salesByMonth->pluck('total')) !!},
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '€' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
