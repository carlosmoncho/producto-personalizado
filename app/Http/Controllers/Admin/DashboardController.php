<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas generales
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_amount');

        // Pedidos por estado
        $ordersByStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Pedidos recientes
        $recentOrders = Order::with(['items.product'])
            ->latest()
            ->take(10)
            ->get();

        // Productos más vendidos
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->take(10)
            ->get();

        // Ventas por mes (últimos 12 meses)
        $salesByMonth = Order::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::create($item->year, $item->month)->format('M Y'),
                    'total' => $item->total
                ];
            });

        // Estadísticas de esta semana vs semana anterior
        $thisWeekStart = Carbon::now()->startOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        $thisWeekOrders = Order::where('created_at', '>=', $thisWeekStart)->count();
        $lastWeekOrders = Order::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();
        $ordersGrowth = $lastWeekOrders > 0 ? (($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100 : 0;

        $thisWeekRevenue = Order::where('created_at', '>=', $thisWeekStart)->sum('total_amount');
        $lastWeekRevenue = Order::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->sum('total_amount');
        $revenueGrowth = $lastWeekRevenue > 0 ? (($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100 : 0;

        // Productos con stock bajo (si tienes un campo de stock)
        $lowStockProducts = Product::where('active', true)
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalCategories', 
            'totalOrders',
            'totalRevenue',
            'ordersByStatus',
            'recentOrders',
            'topProducts',
            'salesByMonth',
            'ordersGrowth',
            'revenueGrowth',
            'lowStockProducts'
        ));
    }

    public function salesData(Request $request)
    {
        try {
            // Log para debugging
            \Log::info('salesData method called', [
                'period' => $request->get('period'),
                'category' => $request->get('category')
            ]);

            // Verificar que hay pedidos
            $orderCount = Order::count();
            if ($orderCount === 0) {
                \Log::warning('No orders found in database');
                return response()->json([
                    'labels' => [],
                    'data' => [],
                    'periodSales' => 0,
                    'avgOrderValue' => 0,
                    'growthRate' => 0,
                    'topProducts' => [],
                    'debug' => 'No orders in database'
                ]);
            }

            $period = $request->get('period', '30d');
            $category = $request->get('category', 'all');
            
            // Definir fechas según el período
            $endDate = Carbon::now();
            switch ($period) {
                case '7d':
                    $startDate = Carbon::now()->subDays(7);
                    $format = 'M d';
                    $groupByClause = 'DATE(created_at)';
                    $orderByClause = 'DATE(created_at)';
                    break;
                case '30d':
                    $startDate = Carbon::now()->subDays(30);
                    $format = 'M d';
                    $groupByClause = 'DATE(created_at)';
                    $orderByClause = 'DATE(created_at)';
                    break;
                case '6m':
                    $startDate = Carbon::now()->subMonths(6);
                    $format = 'M Y';
                    $groupByClause = 'YEAR(created_at), MONTH(created_at)';
                    $orderByClause = 'YEAR(created_at), MONTH(created_at)';
                    break;
                case '1y':
                    $startDate = Carbon::now()->subYear();
                    $format = 'M Y';
                    $groupByClause = 'YEAR(created_at), MONTH(created_at)';
                    $orderByClause = 'YEAR(created_at), MONTH(created_at)';
                    break;
                default:
                    $startDate = Carbon::now()->subDays(30);
                    $format = 'M d';
                    $groupByClause = 'DATE(created_at)';
                    $orderByClause = 'DATE(created_at)';
            }
            
            \Log::info('Date range', [
                'startDate' => $startDate->toDateString(),
                'endDate' => $endDate->toDateString()
            ]);

            // Construir query base
            $query = Order::whereBetween('created_at', [$startDate, $endDate]);
            
            // Filtrar por categoría si se especifica
            if ($category !== 'all') {
                $query->whereHas('items.product', function($q) use ($category) {
                    $q->where('category_id', $category);
                });
            }
            
            // Obtener datos de ventas agrupados - CORREGIDO para MySQL strict mode
            if ($period === '6m' || $period === '1y') {
                $salesData = $query->select(
                        DB::raw('YEAR(created_at) as year'),
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('SUM(total_amount) as total'),
                        DB::raw('COUNT(*) as orders_count'),
                        DB::raw('MIN(DATE(created_at)) as date')
                    )
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'asc')
                    ->orderBy('month', 'asc')
                    ->get();
            } else {
                $salesData = $query->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('SUM(total_amount) as total'),
                        DB::raw('COUNT(*) as orders_count')
                    )
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy(DB::raw('DATE(created_at)'), 'asc')
                    ->get();
            }

            \Log::info('Sales data query result', [
                'count' => $salesData->count(),
                'data' => $salesData->toArray()
            ]);
            
            // Formatear datos para el gráfico
            $labels = [];
            $data = [];
            
            if ($salesData->isEmpty()) {
                // Datos de fallback si no hay ventas en el período
                $fallbackOrders = Order::latest()->take(5)->get();
                foreach ($fallbackOrders as $order) {
                    $labels[] = $order->created_at->format('M d');
                    $data[] = (float) $order->total_amount;
                }
                
                return response()->json([
                    'labels' => $labels,
                    'data' => $data,
                    'periodSales' => $fallbackOrders->sum('total_amount'),
                    'avgOrderValue' => $fallbackOrders->count() > 0 ? $fallbackOrders->avg('total_amount') : 0,
                    'growthRate' => 0,
                    'topProducts' => [],
                    'debug' => 'Using fallback data - no sales in selected period'
                ]);
            }
            
            foreach ($salesData as $item) {
                if ($period === '6m' || $period === '1y') {
                    $date = Carbon::create($item->year, $item->month, 1);
                    $labels[] = $date->format($format);
                } else {
                    $labels[] = Carbon::parse($item->date)->format($format);
                }
                $data[] = (float) $item->total;
            }
            
            // Calcular métricas del período
            $periodSales = $salesData->sum('total');
            $totalOrders = $salesData->sum('orders_count');
            $avgOrderValue = $totalOrders > 0 ? $periodSales / $totalOrders : 0;
            
            // Calcular tasa de crecimiento (comparar con período anterior)
            $daysDiff = $endDate->diffInDays($startDate);
            $previousStartDate = $startDate->copy()->subDays($daysDiff);
            $previousPeriodSales = Order::whereBetween('created_at', [$previousStartDate, $startDate])
                ->when($category !== 'all', function($q) use ($category) {
                    $q->whereHas('items.product', function($subQ) use ($category) {
                        $subQ->where('category_id', $category);
                    });
                })
                ->sum('total_amount');
            
            $growthRate = $previousPeriodSales > 0 
                ? (($periodSales - $previousPeriodSales) / $previousPeriodSales) * 100 
                : 0;
            
            // Top 5 productos del período
            $topProducts = [];
            try {
                $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->with('product:id,name')
                    ->whereHas('order', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->when($category !== 'all', function($q) use ($category) {
                        $q->whereHas('product', function($subQ) use ($category) {
                            $subQ->where('category_id', $category);
                        });
                    })
                    ->groupBy('product_id')
                    ->orderBy('total_quantity', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function($item) {
                        return [
                            'name' => $item->product->name ?? 'Producto eliminado',
                            'sales' => $item->total_quantity
                        ];
                    });
            } catch (\Exception $e) {
                \Log::error('Error getting top products', ['error' => $e->getMessage()]);
            }
            
            $response = [
                'labels' => $labels,
                'data' => $data,
                'periodSales' => (float) $periodSales,
                'avgOrderValue' => (float) $avgOrderValue,
                'growthRate' => (float) $growthRate,
                'topProducts' => $topProducts,
                'debug' => 'Success'
            ];

            \Log::info('Final response', $response);
            
            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Error in salesData method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'labels' => [],
                'data' => [],
                'periodSales' => 0,
                'avgOrderValue' => 0,
                'growthRate' => 0,
                'topProducts' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }
}