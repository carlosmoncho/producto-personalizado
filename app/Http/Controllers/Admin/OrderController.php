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
            // Si se seleccionó un cliente existente, usar su información
            if ($request->customer_id) {
                $customer = \App\Models\Customer::find($request->customer_id);
                $customerData = [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                    'customer_address' => $customer->address,
                ];
            } else {
                // Crear o buscar cliente
                $customer = \App\Models\Customer::firstOrCreate(
                    ['email' => $request->customer_email],
                    [
                        'name' => $request->customer_name,
                        'phone' => $request->customer_phone,
                        'address' => $request->customer_address,
                    ]
                );
                
                $customerData = [
                    'customer_id' => $customer->id,
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'customer_address' => $request->customer_address,
                ];
            }

            // Calcular el total del pedido
            $totalAmount = 0;
            foreach ($request->products as $productData) {
                $subtotal = $productData['quantity'] * $productData['price'];
                $totalAmount += $subtotal;
            }

            // Crear el pedido
            $orderData = array_merge($customerData, [
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'notes' => $request->notes
            ]);

            $order = Order::create($orderData);

            // Agregar los productos al pedido
            foreach ($request->products as $productData) {
                $product = \App\Models\Product::find($productData['id']);
                
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['price'],
                    'total_price' => $productData['quantity'] * $productData['price'],
                    'selected_size' => $product->sizes ? $product->sizes[0] : null,
                    'selected_color' => $product->colors ? $product->colors[0] : null,
                    'selected_print_colors' => $product->print_colors ?? [],
                    'design_comments' => null
                ]);
            }

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
        // Verificar si el pedido se puede eliminar según políticas del negocio
        if ($order->status === 'completed' || $order->status === 'shipped') {
            $message = "No se puede eliminar el pedido '{$order->order_number}' porque está en estado '{$order->status}'.\n\n";
            $message .= "Los pedidos completados o enviados no pueden eliminarse para mantener el historial de transacciones.";
            
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
            $query = Order::with(['items.product']);

            // Aplicar los mismos filtros que en la vista principal
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

            $orders = $query->orderBy('created_at', 'desc')->get();

            // Crear el archivo CSV con BOM para UTF-8
            $filename = 'pedidos_' . date('Y-m-d_H-i-s') . '.csv';
            
            $handle = fopen('php://temp', 'r+');
            
            // Agregar BOM UTF-8
            fwrite($handle, "\xEF\xBB\xBF");
            
            // Cabeceras
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
            fputcsv($handle, $headers, ';');

            // Datos
            foreach ($orders as $order) {
                $row = [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_email,
                    $order->customer_phone ?? '',
                    $order->customer_address ?? '',
                    $order->status_label,
                    number_format($order->total_amount, 2, ',', '.'),
                    $order->items->count(),
                    $order->created_at->format('d/m/Y H:i'),
                    $order->approved_at ? $order->approved_at->format('d/m/Y H:i') : '',
                    $order->shipped_at ? $order->shipped_at->format('d/m/Y H:i') : '',
                    $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : '',
                    $order->notes ?? ''
                ];
                fputcsv($handle, $row, ';');
            }
            
            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            return response($csv, 200)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

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
        $canDelete = !in_array($order->status, ['completed', 'shipped']);
        $items = $order->items()->with('product')->get();
        
        return response()->json([
            'can_delete' => $canDelete,
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
            'restriction_reason' => $canDelete ? null : 'Los pedidos completados o enviados no pueden eliminarse'
        ]);
    }
}
