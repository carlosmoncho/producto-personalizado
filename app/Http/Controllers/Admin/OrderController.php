<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orderService = new \App\Services\Order\OrderService();

        // Construir query con filtros usando OrderService
        $query = $orderService->buildOrderQuery($request);

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        $breadcrumbs = [
            ['name' => 'Pedidos', 'url' => route('admin.orders.index')]
        ];

        return view('admin.orders.index', compact('orders', 'breadcrumbs'));
    }

    public function create()
    {
        $breadcrumbs = [
            ['name' => 'Pedidos', 'url' => route('admin.orders.index')],
            ['name' => 'Crear Pedido', 'url' => '#']
        ];

        return view('admin.orders.create', compact('breadcrumbs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0'
        ]);

        \DB::beginTransaction();

        try {
            $orderService = new \App\Services\Order\OrderService();

            // Preparar datos del cliente usando OrderService
            $customerData = $orderService->prepareCustomerData($request, $request->customer_id);

            // Crear orden completa con items usando OrderService
            $order = $orderService->createOrder(
                $customerData,
                $request->products,
                $request->notes
            );

            \DB::commit();

            return redirect()->route('admin.orders.show', $order)
                            ->with('success', 'Pedido creado exitosamente con ' . count($request->products) . ' producto(s).');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $order->load(['items.product']);

        $breadcrumbs = [
            ['name' => 'Pedidos', 'url' => route('admin.orders.index')],
            ['name' => $order->order_number, 'url' => '#']
        ];

        return view('admin.orders.show', compact('order', 'breadcrumbs'));
    }

    public function edit(Order $order)
    {
        $breadcrumbs = [
            ['name' => 'Pedidos', 'url' => route('admin.orders.index')],
            ['name' => $order->order_number, 'url' => route('admin.orders.show', $order)],
            ['name' => 'Editar', 'url' => '#']
        ];

        return view('admin.orders.edit', compact('order', 'breadcrumbs'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $order->update([
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'shipping_address' => $request->shipping_address,
            'billing_address' => $request->billing_address,
            'notes' => $request->notes
        ]);

        return redirect()->route('admin.orders.show', $order)
                        ->with('success', 'Pedido actualizado exitosamente.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,approved,in_production,shipped,delivered,cancelled'
        ]);

        $orderService = new \App\Services\Order\OrderService();

        // Preparar datos de actualización usando OrderService
        $orderData = $orderService->updateOrderStatus($order, $request->status);

        $order->update($orderData);

        return redirect()->back()
                        ->with('success', 'Estado del pedido actualizado exitosamente.');
    }

    public function destroy(Order $order)
    {
        $orderService = new \App\Services\Order\OrderService();

        // Validar si el pedido puede eliminarse usando OrderService
        $validation = $orderService->canDelete($order);

        if (!$validation['can_delete']) {
            $message = "No se puede eliminar el pedido '{$order->order_number}' porque está en estado '{$order->status}'.\n\n";
            $message .= $validation['details']['message'];

            return redirect()->route('admin.orders.index')
                            ->with('error', $message);
        }

        try {
            // Eliminar imágenes de diseño de los items
            foreach ($order->items as $item) {
                $item->deleteDesignImage();
            }

            $order->delete();
            return redirect()->route('admin.orders.index')
                            ->with('success', "Pedido '{$order->order_number}' eliminado exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.index')
                            ->with('error', 'Error al eliminar el pedido: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            $orderService = new \App\Services\Order\OrderService();

            // Construir query con filtros usando OrderService (mismo que en index)
            $query = $orderService->buildOrderQuery($request);

            $orders = $query->orderBy('created_at', 'desc')->get();

            // Definir cabeceras del CSV
            $headers = [
                'Número de Pedido',
                'Cliente',
                'Email',
                'Teléfono',
                'Dirección',
                'Estado',
                'Total (€)',
                'Cantidad de Productos',
                'Fecha de Creación',
                'Fecha de Aprobación',
                'Fecha de Envío',
                'Fecha de Entrega',
                'Notas'
            ];

            // Usar CsvExportService para generar el CSV
            $csvService = new \App\Services\Export\CsvExportService();

            return $csvService->export(
                $orders,
                $headers,
                function ($order) {
                    return [
                        $order->order_number,
                        $order->customer_name,
                        $order->customer_email,
                        $order->customer_phone ?? '',
                        $order->customer_address ?? '',
                        $order->status_label,
                        \App\Services\Export\CsvExportService::formatNumber($order->total_amount),
                        $order->items->count(),
                        \App\Services\Export\CsvExportService::formatDate($order->created_at),
                        \App\Services\Export\CsvExportService::formatDate($order->approved_at),
                        \App\Services\Export\CsvExportService::formatDate($order->shipped_at),
                        \App\Services\Export\CsvExportService::formatDate($order->delivered_at),
                        $order->notes ?? ''
                    ];
                },
                'pedidos'
            );

        } catch (\Exception $e) {
            \Log::error('Error exporting orders: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al exportar pedidos: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener información de dependencias para AJAX
     */
    public function dependencies(Order $order)
    {
        $orderService = new \App\Services\Order\OrderService();

        // Validar si puede eliminarse usando OrderService
        $validation = $orderService->canDelete($order);

        $items = $order->items()->with('product')->get();

        return response()->json([
            'can_delete' => $validation['can_delete'],
            'status' => $order->status,
            'items_count' => $items->count(),
            'total_amount' => $order->total_amount,
            'items' => $items->map(function($item) {
                return [
                    'product_name' => $item->product->name ?? 'Producto eliminado',
                    'quantity' => $item->quantity,
                    'price' => $item->price
                ];
            }),
            'restriction_reason' => $validation['can_delete'] ? null : $validation['reason']
        ]);
    }
}
