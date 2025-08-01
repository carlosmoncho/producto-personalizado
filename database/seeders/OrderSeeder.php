<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->error('No products found. Please run ProductSeeder first.');
            return;
        }

        // Verificar si ya existen pedidos y limpiarlos
        if (Order::count() > 0) {
            $this->command->info('Eliminando pedidos existentes...');
            Order::query()->delete();
        }

        // Crear pedidos con fechas variadas en los últimos 60 días
        $orders = [];
        
        // Pedidos de hace 50 días
        for ($i = 0; $i < 3; $i++) {
            $orders[] = [
                'order_number' => 'ORD-2024-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_name' => 'Cliente ' . (count($orders) + 1),
                'customer_email' => 'cliente' . (count($orders) + 1) . '@email.com',
                'customer_phone' => '+34 6' . rand(10000000, 99999999),
                'customer_address' => 'Dirección ' . (count($orders) + 1),
                'status' => 'delivered',
                'total_amount' => rand(100, 500),
                'notes' => 'Pedido histórico',
                'created_at' => Carbon::now()->subDays(50 - $i),
                'approved_at' => Carbon::now()->subDays(48 - $i),
                'shipped_at' => Carbon::now()->subDays(46 - $i),
                'delivered_at' => Carbon::now()->subDays(44 - $i),
            ];
        }

        // Pedidos de hace 30 días
        for ($i = 0; $i < 5; $i++) {
            $orders[] = [
                'order_number' => 'ORD-2024-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_name' => 'Cliente ' . (count($orders) + 1),
                'customer_email' => 'cliente' . (count($orders) + 1) . '@email.com',
                'customer_phone' => '+34 6' . rand(10000000, 99999999),
                'customer_address' => 'Dirección ' . (count($orders) + 1),
                'status' => rand(0, 1) ? 'delivered' : 'shipped',
                'total_amount' => rand(150, 600),
                'notes' => 'Pedido del mes pasado',
                'created_at' => Carbon::now()->subDays(30 - $i),
                'approved_at' => Carbon::now()->subDays(28 - $i),
                'shipped_at' => Carbon::now()->subDays(26 - $i),
                'delivered_at' => rand(0, 1) ? Carbon::now()->subDays(24 - $i) : null,
            ];
        }

        // Pedidos de hace 15 días
        for ($i = 0; $i < 4; $i++) {
            $orders[] = [
                'order_number' => 'ORD-2024-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_name' => 'Cliente ' . (count($orders) + 1),
                'customer_email' => 'cliente' . (count($orders) + 1) . '@email.com',
                'customer_phone' => '+34 6' . rand(10000000, 99999999),
                'customer_address' => 'Dirección ' . (count($orders) + 1),
                'status' => ['approved', 'shipped', 'delivered'][rand(0, 2)],
                'total_amount' => rand(200, 400),
                'notes' => 'Pedido reciente',
                'created_at' => Carbon::now()->subDays(15 - $i),
                'approved_at' => Carbon::now()->subDays(13 - $i),
                'shipped_at' => rand(0, 1) ? Carbon::now()->subDays(11 - $i) : null,
                'delivered_at' => null,
            ];
        }

        // Pedidos de los últimos 7 días
        for ($i = 0; $i < 6; $i++) {
            $statuses = ['pending', 'processing', 'approved', 'shipped'];
            $status = $statuses[rand(0, 3)];
            
            $orders[] = [
                'order_number' => 'ORD-2024-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_name' => 'Cliente ' . (count($orders) + 1),
                'customer_email' => 'cliente' . (count($orders) + 1) . '@email.com',
                'customer_phone' => '+34 6' . rand(10000000, 99999999),
                'customer_address' => 'Dirección ' . (count($orders) + 1),
                'status' => $status,
                'total_amount' => rand(80, 350),
                'notes' => 'Pedido muy reciente',
                'created_at' => Carbon::now()->subDays(7 - $i),
                'approved_at' => in_array($status, ['approved', 'shipped']) ? Carbon::now()->subDays(6 - $i) : null,
                'shipped_at' => $status === 'shipped' ? Carbon::now()->subDays(5 - $i) : null,
                'delivered_at' => null,
            ];
        }

        // Pedidos de hoy
        for ($i = 0; $i < 3; $i++) {
            $orders[] = [
                'order_number' => 'ORD-2024-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_name' => 'Cliente ' . (count($orders) + 1),
                'customer_email' => 'cliente' . (count($orders) + 1) . '@email.com',
                'customer_phone' => '+34 6' . rand(10000000, 99999999),
                'customer_address' => 'Dirección ' . (count($orders) + 1),
                'status' => ['pending', 'processing'][rand(0, 1)],
                'total_amount' => rand(120, 280),
                'notes' => 'Pedido de hoy',
                'created_at' => Carbon::now()->subHours(rand(1, 10)),
                'approved_at' => null,
                'shipped_at' => null,
                'delivered_at' => null,
            ];
        }

        // Crear los pedidos
        foreach ($orders as $orderData) {
            $order = Order::create($orderData);
            $this->createOrderItems($order, $products);
        }

        $this->command->info('Se han creado ' . count($orders) . ' pedidos con sus items distribuidos en diferentes fechas.');
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
    
                // Seleccionar un color aleatorio del producto
                $selectedColor = $product->colors[array_rand($product->colors)] ?? 'Negro';
                
                // Seleccionar colores de impresión aleatorios
                $selectedPrintColors = [];
                if ($product->print_colors && count($product->print_colors) > 0) {
                    $numColors = min(rand(1, 2), count($product->print_colors));
                    $selectedPrintColors = array_rand(array_flip($product->print_colors), $numColors);
                    if (!is_array($selectedPrintColors)) {
                        $selectedPrintColors = [$selectedPrintColors];
                    }
                }
    
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $pricing->unit_price,
                    'total_price' => $itemTotal,
                    'selected_size' => $product->sizes[array_rand($product->sizes)] ?? 'M',
                    'selected_color' => $selectedColor,
                    'selected_print_colors' => $selectedPrintColors,
                    'design_comments' => 'Diseño según especificaciones del cliente',
                ]);
            }
        }
    
        // Actualizar el total del pedido
        $order->update(['total_amount' => $totalAmount]);
    }
}