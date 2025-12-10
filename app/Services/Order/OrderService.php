<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Servicio de lógica de negocio para pedidos
 *
 * Centraliza operaciones comunes como:
 * - Preparación de datos de clientes
 * - Cálculo de totales
 * - Creación de órdenes con items
 * - Gestión de estados
 * - Validaciones de negocio
 *
 * @package App\Services\Order
 */
class OrderService
{
    /**
     * Estados que no permiten eliminación
     */
    private const NON_DELETABLE_STATUSES = ['completed', 'shipped'];

    /**
     * Preparar datos de cliente para una orden
     *
     * Si se proporciona un customer_id existente, usa sus datos.
     * Si no, busca o crea un cliente según el email.
     *
     * @param Request $request
     * @param int|null $customerId ID de cliente existente (opcional)
     * @return array Datos del cliente preparados para la orden
     */
    public function prepareCustomerData(Request $request, ?int $customerId = null): array
    {
        if ($customerId) {
            // Usar cliente existente
            $customer = Customer::findOrFail($customerId);

            return [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'customer_address' => $customer->address,
                'shipping_address' => $request->shipping_address ?? $customer->address,
                'billing_address' => $request->billing_address ?? $customer->address,
                'company_name' => $request->company_name,
                'nif_cif' => $request->nif_cif,
            ];
        } else {
            // Crear o buscar cliente por email
            $customer = Customer::updateOrCreate(
                ['email' => $request->customer_email],
                [
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'company' => $request->company_name,
                    'tax_id' => $request->nif_cif,
                    'address' => $request->shipping_address,
                    'city' => $this->extractCityFromAddress($request->shipping_address),
                    'state' => $this->extractStateFromAddress($request->shipping_address),
                    'postal_code' => $this->extractPostalCodeFromAddress($request->shipping_address),
                    'country' => 'España',
                ]
            );

            return [
                'customer_id' => $customer->id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address ?? $request->billing_address ?? $request->shipping_address,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'company_name' => $request->company_name,
                'nif_cif' => $request->nif_cif,
            ];
        }
    }

    /**
     * Calcular total de una orden según sus productos
     *
     * @param array $products Array con ['quantity' => int, 'price' => float]
     * @return float Total calculado
     */
    public function calculateOrderTotal(array $products): float
    {
        $total = 0;

        foreach ($products as $productData) {
            $subtotal = $productData['quantity'] * $productData['price'];
            $total += $subtotal;
        }

        return round($total, 2);
    }

    /**
     * Crear orden completa con items
     *
     * @param array $customerData Datos del cliente preparados
     * @param array $products Array de productos con id, quantity, price
     * @param string|null $notes Notas adicionales
     * @return Order Orden creada con sus items
     * @throws \Exception Si hay error en creación
     */
    public function createOrder(array $customerData, array $products, ?string $notes = null): Order
    {
        // Calcular total
        $totalAmount = $this->calculateOrderTotal($products);

        // Preparar datos de la orden
        $orderData = array_merge($customerData, [
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'total_amount' => $totalAmount,
            'notes' => $notes
        ]);

        // Crear orden
        $order = Order::create($orderData);

        // Crear items de la orden
        foreach ($products as $productData) {
            $product = Product::findOrFail($productData['id']);

            $itemData = $this->prepareOrderItemData($product, $productData);

            $order->items()->create($itemData);
        }

        // Actualizar estadísticas del customer
        if (isset($customerData['customer_id'])) {
            $customer = Customer::find($customerData['customer_id']);
            if ($customer) {
                $customer->increment('total_orders_count');
                $customer->increment('total_orders_amount', $totalAmount);
                $customer->update(['last_order_at' => now()]);
            }
        }

        return $order;
    }

    /**
     * Preparar datos de un item de orden
     *
     * @param Product $product Producto a agregar
     * @param array $itemData Datos del item ['quantity' => int, 'price' => float, 'configuration' => array (opcional)]
     * @return array Datos preparados para crear el item
     */
    public function prepareOrderItemData(Product $product, array $itemData): array
    {
        $quantity = $itemData['quantity'];
        $unitPrice = $itemData['price'];

        // Preparar datos base del item
        $data = [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
        ];

        // Si tiene configuración del nuevo sistema de atributos, guardarla
        if (isset($itemData['configuration']) && !empty($itemData['configuration'])) {
            $data['configuration'] = $itemData['configuration'];
        }

        // Si tiene logo/diseño de impresión, guardarlo en design_image
        if (isset($itemData['design_logo']) && !empty($itemData['design_logo'])) {
            $data['design_image'] = $itemData['design_logo'];
        }

        // Si tiene preview 3D personalizado, guardarlo
        if (isset($itemData['preview_3d']) && !empty($itemData['preview_3d'])) {
            $data['preview_3d'] = $itemData['preview_3d'];
        }

        // Si tiene configuración del modelo 3D, guardarla
        if (isset($itemData['model_3d_config']) && !empty($itemData['model_3d_config'])) {
            $data['model_3d_config'] = $itemData['model_3d_config'];
        }

        // Campos del sistema antiguo (por compatibilidad)
        $data['selected_size'] = $product->sizes ? $product->sizes[0] : null;
        $data['selected_color'] = $product->colors ? $product->colors[0] : null;
        $data['selected_print_colors'] = $product->print_colors ?? [];
        $data['design_comments'] = null;

        return $data;
    }

    /**
     * Actualizar estado de orden con timestamps automáticos
     *
     * Actualiza el estado y los timestamps correspondientes:
     * - approved: approved_at
     * - shipped: shipped_at
     * - delivered: delivered_at
     *
     * @param Order $order Orden a actualizar
     * @param string $status Nuevo estado
     * @return array Datos actualizados
     */
    public function updateOrderStatus(Order $order, string $status): array
    {
        $orderData = ['status' => $status];

        // Actualizar timestamps según el estado
        switch ($status) {
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

        return $orderData;
    }

    /**
     * Validar si una orden puede ser eliminada
     *
     * Una orden NO puede eliminarse si está en estado 'completed' o 'shipped'
     *
     * @param Order $order
     * @return array ['can_delete' => bool, 'reason' => string|null, 'details' => array]
     */
    public function canDelete(Order $order): array
    {
        if (in_array($order->status, self::NON_DELETABLE_STATUSES)) {
            return [
                'can_delete' => false,
                'reason' => "El pedido está en estado '{$order->status}'",
                'details' => [
                    'status' => $order->status,
                    'order_number' => $order->order_number,
                    'message' => "Los pedidos completados o enviados no pueden eliminarse para mantener el historial de transacciones."
                ]
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
            'details' => []
        ];
    }

    /**
     * Construir query base con filtros para órdenes
     *
     * Útil para reutilizar en index() y export()
     *
     * @param Request $request Request con filtros
     * @return \Illuminate\Database\Eloquent\Builder Query builder configurado
     */
    public function buildOrderQuery(Request $request)
    {
        $query = Order::with(['items.product']);

        // Filtro por estado
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filtro de búsqueda por texto
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por fecha desde
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filtro por fecha hasta
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * Obtener estadísticas de una orden
     *
     * @param Order $order
     * @return array Array con estadísticas
     */
    public function getOrderStats(Order $order): array
    {
        return [
            'items_count' => $order->items()->count(),
            'total_quantity' => $order->items()->sum('quantity'),
            'total_amount' => $order->total_amount,
            'status' => $order->status,
            'days_since_created' => now()->diffInDays($order->created_at),
            'days_since_approved' => $order->approved_at ? now()->diffInDays($order->approved_at) : null,
        ];
    }

    /**
     * Validar si una orden está lista para envío
     *
     * @param Order $order
     * @return array ['ready' => bool, 'missing' => array]
     */
    public function validateReadyForShipping(Order $order): array
    {
        $missing = [];

        // Debe estar aprobada
        if ($order->status !== 'approved' && $order->status !== 'in_production') {
            $missing[] = 'El pedido debe estar aprobado o en producción';
        }

        // Debe tener dirección
        if (!$order->customer_address) {
            $missing[] = 'Falta dirección de envío';
        }

        // Debe tener items
        if ($order->items()->count() === 0) {
            $missing[] = 'El pedido no tiene productos';
        }

        return [
            'ready' => empty($missing),
            'missing' => $missing
        ];
    }

    /**
     * Buscar órdenes por término
     *
     * @param string $term Término de búsqueda
     * @param int $limit Límite de resultados
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchOrders(string $term, int $limit = 20)
    {
        return Order::where('order_number', 'like', "%{$term}%")
            ->orWhere('customer_name', 'like', "%{$term}%")
            ->orWhere('customer_email', 'like', "%{$term}%")
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Extraer código postal de una dirección formateada
     * Formato esperado: "Calle X, 12345 Ciudad, Provincia"
     *
     * @param string|null $address
     * @return string|null
     */
    private function extractPostalCodeFromAddress(?string $address): ?string
    {
        if (!$address) return null;

        // Buscar un patrón de 5 dígitos
        if (preg_match('/\b(\d{5})\b/', $address, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extraer ciudad de una dirección formateada
     * Formato esperado: "Calle X, 12345 Ciudad, Provincia"
     *
     * @param string|null $address
     * @return string|null
     */
    private function extractCityFromAddress(?string $address): ?string
    {
        if (!$address) return null;

        // Buscar texto después del código postal y antes de la última coma
        if (preg_match('/\d{5}\s+([^,]+)/', $address, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extraer provincia de una dirección formateada
     * Formato esperado: "Calle X, 12345 Ciudad, Provincia"
     *
     * @param string|null $address
     * @return string|null
     */
    private function extractStateFromAddress(?string $address): ?string
    {
        if (!$address) return null;

        // Tomar el último elemento después de la última coma
        $parts = explode(',', $address);
        if (count($parts) >= 2) {
            return trim(end($parts));
        }

        return null;
    }
}
