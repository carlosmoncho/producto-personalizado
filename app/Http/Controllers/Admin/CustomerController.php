<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Filtros
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'active') {
                $query->where('active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('active', false);
            }
        }

        if ($request->has('has_orders') && $request->has_orders !== '') {
            if ($request->has_orders === 'yes') {
                $query->where('total_orders_count', '>', 0);
            } elseif ($request->has_orders === 'no') {
                $query->where('total_orders_count', 0);
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'active' => 'boolean'
        ]);

        $customer = Customer::create($request->all());

        return redirect()->route('admin.customers.index')
                        ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        // Eager load orders with items to prevent N+1 queries in the view
        $customer->load(['orders' => function($query) {
            $query->with('items')->orderBy('created_at', 'desc');
        }]);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'active' => 'boolean'
        ]);

        $customer->update($request->all());

        return redirect()->route('admin.customers.show', $customer)
                        ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Verificar dependencias - revisar si el cliente tiene pedidos
        $ordersCount = $customer->orders()->count();
        
        if ($ordersCount > 0) {
            $orderNumbers = $customer->orders()
                ->pluck('order_number')
                ->take(5);
                
            $ordersList = $orderNumbers->implode(', ');
            
            if ($ordersCount > 5) {
                $ordersList .= " y " . ($ordersCount - 5) . " más";
            }
            
            $totalAmount = $customer->total_orders_amount ?? 0;
            
            $message = "No se puede eliminar el cliente '{$customer->name}' porque tiene {$ordersCount} pedido(s) asociado(s):\n\n";
            $message .= "• Pedidos: {$ordersList}\n";
            $message .= "• Importe total: €" . number_format($totalAmount, 2) . "\n\n";
            $message .= "Los clientes con historial de pedidos no pueden eliminarse para mantener la integridad de los datos.";
            
            return redirect()->route('admin.customers.index')
                            ->with('error', $message);
        }

        try {
            $customer->delete();
            return redirect()->route('admin.customers.index')
                            ->with('success', "Cliente '{$customer->name}' eliminado exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('admin.customers.index')
                            ->with('error', 'Error al eliminar el cliente: ' . $e->getMessage());
        }
    }

    /**
     * Export customers to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Customer::query();

            // Aplicar filtros
            if ($request->has('search') && $request->search) {
                $query->search($request->search);
            }

            if ($request->has('status') && $request->status !== '') {
                if ($request->status === 'active') {
                    $query->where('active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('active', false);
                }
            }

            if ($request->has('has_orders') && $request->has_orders !== '') {
                if ($request->has_orders === 'yes') {
                    $query->where('total_orders_count', '>', 0);
                } elseif ($request->has_orders === 'no') {
                    $query->where('total_orders_count', 0);
                }
            }

            $customers = $query->orderBy('created_at', 'desc')->get();

            // Definir cabeceras del CSV
            $headers = [
                'ID',
                'Nombre',
                'Email',
                'Teléfono',
                'Empresa',
                'Dirección',
                'Ciudad',
                'Código Postal',
                'País',
                'NIF/CIF',
                'Estado',
                'Total Pedidos',
                'Importe Total (€)',
                'Último Pedido',
                'Fecha de Registro',
                'Notas'
            ];

            // Usar CsvExportService para generar el CSV
            $csvService = new \App\Services\Export\CsvExportService();

            return $csvService->export(
                $customers,
                $headers,
                function ($customer) {
                    return [
                        $customer->id,
                        $customer->name,
                        $customer->email,
                        $customer->phone ?? '',
                        $customer->company ?? '',
                        $customer->address ?? '',
                        $customer->city ?? '',
                        $customer->postal_code ?? '',
                        $customer->country ?? '',
                        $customer->tax_id ?? '',
                        $customer->active ? 'Activo' : 'Inactivo',
                        $customer->total_orders_count,
                        \App\Services\Export\CsvExportService::formatNumber($customer->total_orders_amount),
                        \App\Services\Export\CsvExportService::formatDate($customer->last_order_at),
                        \App\Services\Export\CsvExportService::formatDate($customer->created_at),
                        $customer->notes ?? ''
                    ];
                },
                'clientes'
            );

        } catch (\Exception $e) {
            \Log::error('Error exporting customers: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al exportar clientes: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener información de dependencias para AJAX
     */
    public function dependencies(Customer $customer)
    {
        $orders = $customer->orders()->get(['id', 'order_number', 'total_amount']);
        
        return response()->json([
            'can_delete' => $orders->count() === 0,
            'orders_count' => $orders->count(),
            'total_amount' => $customer->total_orders_amount ?? 0,
            'orders' => $orders->map(function($order) {
                return [
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount
                ];
            })
        ]);
    }
}
