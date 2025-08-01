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
                        @php
                            $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
                        @endphp
                        <h3 class="card-title h2 mb-0">{{ $pendingOrders }}</h3>
                        <p class="card-text">Pendientes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock"></i>
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
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        Análisis de Ventas
                    </h5>
                    <div class="d-flex gap-2">
                        <!-- Selector de período -->
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="period" id="period7d" value="7d" autocomplete="off">
                            <label class="btn btn-outline-secondary" for="period7d">7D</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period30d" value="30d" autocomplete="off" checked>
                            <label class="btn btn-outline-secondary" for="period30d">30D</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period6m" value="6m" autocomplete="off">
                            <label class="btn btn-outline-secondary" for="period6m">6M</label>
                            
                            <input type="radio" class="btn-check" name="period" id="period1y" value="1y" autocomplete="off">
                            <label class="btn btn-outline-secondary" for="period1y">1A</label>
                        </div>
                        
                        <!-- Selector de categoría -->
                        <select class="form-select form-select-sm" id="categoryFilter" style="width: 150px;">
                            <option value="all">Todas las categorías</option>
                            @foreach(\App\Models\Category::where('active', true)->get() as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        
                        <!-- Selector de tipo de gráfica -->
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="chartType" id="chartBar" value="bar" autocomplete="off" checked>
                            <label class="btn btn-outline-secondary" for="chartBar"><i class="bi bi-bar-chart"></i></label>
                            
                            <input type="radio" class="btn-check" name="chartType" id="chartLine" value="line" autocomplete="off">
                            <label class="btn btn-outline-secondary" for="chartLine"><i class="bi bi-graph-up"></i></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <div class="border-end">
                            <!-- AQUÍ ESTABA EL PROBLEMA: DATOS HARDCODEADOS -->
                            <h4 class="text-primary total-sales" data-value="{{ $totalRevenue }}">
                                €{{ number_format($totalRevenue, 2, ',', '.') }}
                            </h4>
                            <p class="text-muted mb-0">Ventas Totales</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h4 class="text-success" id="periodSales">€0</h4>
                            <p class="text-muted mb-0">Ventas Período</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h4 class="text-warning" id="avgOrderValue">€0</h4>
                            <p class="text-muted mb-0">Ticket Medio</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div>
                            <h4 class="text-info" id="growthRate">0%</h4>
                            <p class="text-muted mb-0">Crecimiento</p>
                        </div>
                    </div>
                </div>
                <hr>
                <!-- Contenedor con altura fija para el gráfico -->
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="salesChart"></canvas>
                </div>
                
                <!-- Leyenda de productos más vendidos -->
                <div class="mt-3" id="topProductsLegend" style="display: none;">
                    <small class="text-muted">Top 5 productos del período:</small>
                    <div class="d-flex flex-wrap gap-2 mt-1" id="topProductsList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Pedidos por Estado -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2"></i>
                    Pedidos por Estado
                </h5>
            </div>
            <div class="card-body">
                @php
                    $orderStatuses = [
                        'pending' => ['label' => 'Pendientes', 'color' => 'warning', 'icon' => 'clock'],
                        'processing' => ['label' => 'Procesando', 'color' => 'info', 'icon' => 'gear'],
                        'approved' => ['label' => 'Aprobados', 'color' => 'primary', 'icon' => 'check-circle'],
                        'shipped' => ['label' => 'Enviados', 'color' => 'success', 'icon' => 'truck'],
                        'delivered' => ['label' => 'Entregados', 'color' => 'success', 'icon' => 'check-all'],
                        'cancelled' => ['label' => 'Cancelados', 'color' => 'danger', 'icon' => 'x-circle']
                    ];
                @endphp
                
                @foreach($orderStatuses as $status => $info)
                    @php
                        $count = \App\Models\Order::where('status', $status)->count();
                        $percentage = $totalOrders > 0 ? ($count / $totalOrders) * 100 : 0;
                    @endphp
                    
                    @if($count > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="d-flex align-items-center">
                                <i class="bi bi-{{ $info['icon'] }} text-{{ $info['color'] }} me-2"></i>
                                {{ $info['label'] }}
                            </span>
                            <strong>{{ $count }}</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $info['color'] }}" 
                                 style="width: {{ $percentage }}%"
                                 data-bs-toggle="tooltip" 
                                 title="{{ number_format($percentage, 1) }}%">
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
                
                @if($totalOrders == 0)
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <p class="text-muted mt-2">No hay pedidos aún</p>
                    </div>
                @endif
                
                <hr class="mt-4">
                
                <div class="text-center">
                    <small class="text-muted">Total de pedidos</small>
                    <h4 class="text-primary mb-0">{{ $totalOrders }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Últimos Pedidos -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Últimos Pedidos
                </h5>
                @if(Route::has('admin.orders.index'))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-light">
                    Ver todos
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nº Pedido</th>
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
                                            @if(Route::has('admin.orders.show'))
                                            <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">
                                                {{ $order->order_number }}
                                            </a>
                                            @else
                                                {{ $order->order_number }}
                                            @endif
                                        </td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td><strong>€{{ number_format($order->total_amount, 2) }}</strong></td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pendiente</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-info">Procesando</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-primary">Aprobado</span>
                                                    @break
                                                @case('shipped')
                                                    <span class="badge bg-success">Enviado</span>
                                                    @break
                                                @case('delivered')
                                                    <span class="badge bg-success">Entregado</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Cancelado</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
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

    <!-- Top Productos -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-trophy me-2"></i>
                    Top Productos
                </h5>
            </div>
            <div class="card-body">
                @if($topProducts->count() > 0)
                    @foreach($topProducts as $productItem)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">{{ $productItem->product->name ?? 'Producto eliminado' }}</h6>
                                <small class="text-muted">{{ $productItem->product->sku ?? 'N/A' }}</small>
                            </div>
                            <span class="badge bg-primary">{{ $productItem->total_quantity }} unidades</span>
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
                    @if(Route::has('admin.orders.index'))
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.orders.index') }}?status=pending" class="btn btn-outline-warning w-100">
                            <i class="bi bi-clock me-2"></i>
                            Pedidos Pendientes
                        </a>
                    </div>
                    @endif
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-folder2-open me-2"></i>
                            Nueva Subcategoría
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Animaciones para las cards */
    .stats-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(194, 137, 40, 0.2);
    }
    
    /* Efecto de brillo en los números */
    .total-sales {
        font-size: 1.8rem;
        font-weight: 700;
        background: linear-gradient(135deg, #c28928 0%, #c08025 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    /* Animación de carga para las cards de stats */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .stats-card {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .stats-card:nth-child(1) { animation-delay: 0.1s; }
    .stats-card:nth-child(2) { animation-delay: 0.2s; }
    .stats-card:nth-child(3) { animation-delay: 0.3s; }
    .stats-card:nth-child(4) { animation-delay: 0.4s; }
    
    /* Efecto hover en filas de tabla */
    .table-hover tbody tr {
        transition: background-color 0.3s ease;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(194, 137, 40, 0.05);
    }
    
    /* Badges animados */
    .badge {
        transition: all 0.3s ease;
    }
    
    .badge:hover {
        transform: scale(1.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard script loaded');
    
    let salesChart = null;
    const ctx = document.getElementById('salesChart');
    
    if (!ctx) {
        console.error('Canvas element salesChart not found');
        return;
    }
    
    const chartContext = ctx.getContext('2d');
    
    // Configuración inicial
    let currentPeriod = '30d';
    let currentCategory = 'all';
    let currentChartType = 'bar';
    
    // Función para obtener datos via AJAX
    async function fetchSalesData(period, category) {
        console.log('Fetching sales data:', { period, category });
        
        try {
            const url = `/admin/sales-data?period=${period}&category=${category}`;
            console.log('Request URL:', url);
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response not ok:', response.status, errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }
            
            const data = await response.json();
            console.log('Response data:', data);
            
            return data;
            
        } catch (error) {
            console.error('Fetch error:', error);
            return {
                labels: [],
                data: [],
                periodSales: 0,
                avgOrderValue: 0,
                growthRate: 0,
                topProducts: [],
                error: error.message
            };
        }
    }
    
    // Función para actualizar la gráfica
    async function updateChart() {
        console.log('Updating chart...');
        
        const chartContainer = document.getElementById('salesChart').parentElement;
        chartContainer.style.opacity = '0.5';
        
        try {
            const data = await fetchSalesData(currentPeriod, currentCategory);
            console.log('Chart data received:', data);
            
            // Verificar si hay datos
            if (!data.labels || data.labels.length === 0) {
                console.warn('No chart data available');
                chartContainer.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-inbox display-4"></i><p>No hay datos para el período seleccionado</p></div>';
                chartContainer.style.opacity = '1';
                return;
            }
            
            // Restaurar el canvas si fue reemplazado
            if (!document.getElementById('salesChart')) {
                chartContainer.innerHTML = '<canvas id="salesChart"></canvas>';
            }
            
            chartContainer.style.opacity = '1';
            
            // Actualizar métricas
            document.getElementById('periodSales').textContent = '€' + data.periodSales.toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            document.getElementById('avgOrderValue').textContent = '€' + data.avgOrderValue.toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            const growthElement = document.getElementById('growthRate');
            growthElement.textContent = (data.growthRate >= 0 ? '+' : '') + data.growthRate.toFixed(1) + '%';
            growthElement.className = data.growthRate >= 0 ? 'text-success' : 'text-danger';
            
            // Actualizar top productos
            const topProductsList = document.getElementById('topProductsList');
            if (data.topProducts && data.topProducts.length > 0) {
                topProductsList.innerHTML = data.topProducts.map((product, index) => 
                    `<span class="badge bg-light text-dark border">
                        ${index + 1}. ${product.name} <strong>(${product.sales})</strong>
                    </span>`
                ).join('');
                document.getElementById('topProductsLegend').style.display = 'block';
            } else {
                document.getElementById('topProductsLegend').style.display = 'none';
            }
            
            // Crear gráfico con Chart.js
            const chartConfig = {
                type: currentChartType,
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Ventas',
                        data: data.data,
                        backgroundColor: currentChartType === 'bar' 
                            ? 'rgba(194, 137, 40, 0.8)' 
                            : 'rgba(194, 137, 40, 0.1)',
                        borderColor: 'rgb(194, 137, 40)',
                        borderWidth: currentChartType === 'bar' ? 0 : 3,
                        tension: currentChartType === 'line' ? 0.4 : 0,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Ventas: €' + context.parsed.y.toLocaleString('es-ES', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '€' + value;
                                }
                            }
                        }
                    }
                }
            };
            
            // Destruir gráfico anterior si existe
            if (salesChart) {
                salesChart.destroy();
            }
            
            // Crear nuevo gráfico
            const newCtx = document.getElementById('salesChart').getContext('2d');
            salesChart = new Chart(newCtx, chartConfig);
            console.log('Chart created successfully');
            
        } catch (error) {
            console.error('Error updating chart:', error);
            chartContainer.innerHTML = '<div class="text-center text-danger py-5"><i class="bi bi-exclamation-triangle display-4"></i><p>Error al cargar los datos: ' + error.message + '</p></div>';
            chartContainer.style.opacity = '1';
        }
    }
    
    // Event listeners para los filtros
    document.querySelectorAll('input[name="period"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Period changed to:', this.value);
            currentPeriod = this.value;
            updateChart();
        });
    });
    
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            console.log('Category changed to:', this.value);
            currentCategory = this.value;
            updateChart();
        });
    }
    
    document.querySelectorAll('input[name="chartType"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Chart type changed to:', this.value);
            currentChartType = this.value;
            updateChart();
        });
    });
    
    // Cargar gráfico inicial
    console.log('Loading initial chart...');
    updateChart();
});
</script>
@endpush