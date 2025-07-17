<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();

        $orders = [
            [
                'order_number' => 'ORD-2024-000001',
                'customer_name' => 'Juan Pérez',
                'customer_email' => 'juan.perez@email.com',
                'customer_phone' => '+34 612 345 678',
                'customer_address' => 'Calle Mayor 123, 28001 Madrid',
                'status' => 'pending',
                'total_amount' => 150.00,
                'notes' => 'Cliente solicita entrega urgente',
            ],
            [
                'order_number' => 'ORD-2024-000002',
                'customer_name' => 'María González',
                'customer_email' => 'maria.gonzalez@email.com',
                'customer_phone' => '+34 623 456 789',
                'customer_address' => 'Avenida Libertad 456, 08001 Barcelona',
                'status' => 'processing',
                'total_amount' => 89.50,
                'notes' => null,
            ],
            [
                'order_number' => 'ORD-2024-000003',
                'customer_name' => 'Carlos López',
                'customer_email' => 'carlos.lopez@email.com',
                'customer_phone' => '+34 634 567 890',
                'customer_address' => 'Plaza España 789, 46001 Valencia',
                'status' => 'approved',
                'total_amount' => 275.00,
                'notes' => 'Pedido corporativo - Empresa XYZ',
                'approved_at' => now()->subDays(2),
            ],
        ];

        foreach ($orders as $orderData) {
            $order = Order::create($orderData);
            
            // Crear items para cada pedido
            $this->createOrderItems($order, $products);
        }
    }

    private function createOrderItems($order, $products)
    {
        $itemsCount = rand(1, 3);
        $totalAmount = 0;

        for ($i = 0; $i < $itemsCount; $i++) {
            $product = $products->random();
            $quantity = rand(25, 100);
            $pricing = $product->getPriceForQuantity($quantity);
            
            if ($pricing) {
                $itemTotal = $pricing->unit_price * $quantity;
                $totalAmount += $itemTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $pricing->unit_price,
                    'total_price' => $itemTotal,
                    'selected_size' => $product->sizes[0] ?? 'Única',
                    'design_comments' => 'Diseño según especificaciones del cliente',
                    'custom_field_values' => [
                        'texto_personalizado' => 'Logo de la empresa',
                        'posicion_texto' => 'Centrado',
                        'tamano_fuente' => 'Mediano',
                    ],
                ]);
            }
        }

        // Actualizar el total del pedido
        $order->update(['total_amount' => $totalAmount]);
    }
}
