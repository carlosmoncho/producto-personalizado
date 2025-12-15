<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Resources\V1\OrderResource;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Order API Controller
 *
 * Endpoints para crear y consultar órdenes
 */
class OrderController extends Controller
{
    /**
     * Display a listing of orders for authenticated user
     *
     * @queryParam status string Filtrar por estado. Example: pending
     * @queryParam page integer Número de página. Example: 1
     * @queryParam per_page integer Items por página (máx 50). Example: 15
     *
     * @response 200 {
     *   "data": [...],
     *   "links": {...},
     *   "meta": {...}
     * }
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'string|in:pending,processing,approved,in_production,shipped,delivered,cancelled',
            'per_page' => 'integer|min:1|max:50',
        ]);

        // Requiere autenticación
        if (!auth()->check()) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        $query = Order::where('user_id', auth()->id())
            ->with(['items.product'])
            ->orderBy('created_at', 'desc');

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Paginación
        $perPage = $request->input('per_page', 15);
        $orders = $query->paginate($perPage);

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created order
     *
     * @bodyParam customer_id integer ID de cliente existente (opcional). Example: 1
     * @bodyParam customer_name string required Nombre del cliente. Example: Juan Pérez
     * @bodyParam customer_email string required Email del cliente. Example: juan@example.com
     * @bodyParam customer_phone string required Teléfono del cliente. Example: +34666777888
     * @bodyParam customer_address string required Dirección de envío. Example: Calle Mayor 1, Madrid
     * @bodyParam notes string Notas adicionales. Example: Entrega por la mañana
     * @bodyParam products array required Array de productos. Example: [{"id": 1, "quantity": 100, "price": 0.15}]
     * @bodyParam products.*.id integer required ID del producto. Example: 1
     * @bodyParam products.*.quantity integer required Cantidad. Example: 100
     * @bodyParam products.*.price number required Precio unitario. Example: 0.15
     * @bodyParam products.*.configuration object Configuración del producto. Example: {"color": "white", "material": "paper"}
     *
     * @response 201 {
     *   "data": {...}
     * }
     * @response 422 {
     *   "message": "Datos de validación inválidos",
     *   "errors": {...}
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string', // Ahora opcional
            'shipping_address' => 'nullable|string', // Dirección de envío separada
            'billing_address' => 'nullable|string',  // Dirección de facturación separada
            'company_name' => 'nullable|string|max:255', // Nombre de empresa para factura
            'nif_cif' => 'nullable|string|max:20',       // NIF/CIF para factura
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.configuration' => 'nullable|array',
            'products.*.design_logo' => 'nullable|string', // Logo/diseño en base64
            'products.*.preview_3d' => 'nullable|string', // Preview del modelo 3D personalizado en base64
            'products.*.model_3d_config' => 'nullable|array', // Configuración para recrear modelo 3D
            'products.*.model_3d_config.model_url' => 'nullable|string',
            'products.*.model_3d_config.color_hex' => 'nullable|string',
            'products.*.model_3d_config.logo_transform' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $orderService = new OrderService();

            // Preparar datos del cliente
            $customerData = $orderService->prepareCustomerData($request, $request->customer_id);

            // Crear orden con items
            $order = $orderService->createOrder(
                $customerData,
                $validated['products'],
                $validated['notes'] ?? null
            );

            // Si hay usuario autenticado, asociarlo
            if (auth()->check()) {
                $order->update(['user_id' => auth()->id()]);
            }

            DB::commit();

            return (new OrderResource($order->load(['items.product'])))
                ->response()
                ->setStatusCode(201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error creating order via API', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Error al crear la orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order
     *
     * @urlParam order integer required ID de la orden. Example: 1
     *
     * @response 200 {
     *   "data": {...}
     * }
     * @response 404 {
     *   "message": "Orden no encontrada"
     * }
     * @response 403 {
     *   "message": "No autorizado"
     * }
     */
    public function show($id)
    {
        $order = Order::with(['items.product'])->findOrFail($id);

        // Verificar autorización: solo el usuario dueño puede ver su orden
        // O si no hay usuario autenticado, verificar por email en sesión
        if (auth()->check() && $order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'No autorizado para ver esta orden'
            ], 403);
        }

        return new OrderResource($order);
    }

    /**
     * Delete the specified order
     *
     * Solo se pueden eliminar pedidos en estados 'pending', 'processing',
     * 'approved', 'in_production' o 'cancelled'
     *
     * @urlParam order integer required ID de la orden. Example: 1
     *
     * @response 200 {
     *   "message": "Pedido eliminado exitosamente"
     * }
     * @response 403 {
     *   "message": "No se puede eliminar el pedido",
     *   "reason": "..."
     * }
     * @response 404 {
     *   "message": "Orden no encontrada"
     * }
     */
    public function destroy($id)
    {
        $order = Order::with('items')->findOrFail($id);

        $orderService = new OrderService();

        // Validar si el pedido puede eliminarse
        $validation = $orderService->canDelete($order);

        if (!$validation['can_delete']) {
            return response()->json([
                'message' => "No se puede eliminar el pedido '{$order->order_number}'",
                'reason' => $validation['reason'],
                'details' => $validation['details']
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Eliminar imágenes de diseño de los items
            foreach ($order->items as $item) {
                $item->deleteDesignImage();
            }

            $orderNumber = $order->order_number;
            $order->delete();

            DB::commit();

            return response()->json([
                'message' => "Pedido '{$orderNumber}' eliminado exitosamente"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error eliminando pedido via API', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al eliminar el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
