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
        $query = Order::with(['items.product']);

        // Filtros
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

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
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $orderData = [
            'order_number' => Order::generateOrderNumber(),
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'status' => 'pending',
            'total_amount' => 0,
            'notes' => $request->notes
        ];

        $order = Order::create($orderData);

        return redirect()->route('admin.orders.show', $order)
                        ->with('success', 'Pedido creado exitosamente.');
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
            'customer_address' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $order->update([
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
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

        $orderData = ['status' => $request->status];

        // Actualizar timestamps según el estado
        switch ($request->status) {
            case 'approved':
                $orderData['approved_at'] = now();
                break;
            case 'shipped':
                $orderData['shipped_at'] = now();
                break;
            case 'delivered':
                $orderData['delivered_at'] = now();
                break;
        }

        $order->update($orderData);

        return redirect()->back()
                        ->with('success', 'Estado del pedido actualizado exitosamente.');
    }

    public function destroy(Order $order)
    {
        try {
            // Eliminar imágenes de diseño de los items
            foreach ($order->items as $item) {
                $item->deleteDesignImage();
            }
            
            $order->delete();
            return redirect()->route('admin.orders.index')
                            ->with('success', 'Pedido eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.index')
                            ->with('error', 'Error al eliminar el pedido.');
        }
    }

    public function export(Request $request)
    {
        $query = Order::with(['items.product']);

        // Aplicar filtros si existen
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $csvData = [];
        $csvData[] = [
            'Número de Pedido',
            'Cliente',
            'Email',
            'Teléfono',
            'Estado',
            'Total',
            'Fecha de Creación',
            'Fecha de Aprobación',
            'Fecha de Envío',
            'Fecha de Entrega'
        ];

        foreach ($orders as $order) {
            $csvData[] = [
                $order->order_number,
                $order->customer_name,
                $order->customer_email,
                $order->customer_phone,
                $order->getStatusLabelAttribute(),
                '€' . number_format($order->total_amount, 2),
                $order->created_at->format('d/m/Y H:i'),
                $order->approved_at ? $order->approved_at->format('d/m/Y H:i') : '',
                $order->shipped_at ? $order->shipped_at->format('d/m/Y H:i') : '',
                $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : ''
            ];
        }

        $filename = 'pedidos_' . date('Y-m-d_H-i-s') . '.csv';

        $handle = fopen('php://memory', 'r+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row, ';');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
