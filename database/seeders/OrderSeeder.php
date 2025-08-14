<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Carbon\Carbon;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();
        $customers = Customer::all();

        if ($products->isEmpty()) {
            $this->command->error('No products found. Please run ProductSeeder first.');
            return;
        }

        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Please run CustomerSeeder first.');
            return;
        }

        $faker = Faker::create('es_ES');

        // Verificar si ya existen pedidos y limpiarlos
        if (Order::count() > 0) {
            $this->command->info('Eliminando pedidos existentes...');
            Order::query()->delete();
        }

        // Reset customer statistics
        Customer::query()->update([
            'total_orders_count' => 0,
            'total_orders_amount' => 0,
            'last_order_at' => null
        ]);

        // Crear pedidos vinculados a clientes reales
        $orders = [];
        
        // Pedidos de hace 50 días
        for ($i = 0; $i < 8; $i++) {
            $customer = $customers->random();
            $orders[] = [
                'order_number' => 'ORD-2025-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone ?: $faker->phoneNumber(),
                'customer_address' => $customer->address ?: $faker->address(),
                'status' => 'delivered',
                'total_amount' => $faker->randomFloat(2, 150, 800),
                'notes' => $faker->optional(0.3)->sentence(),
                'created_at' => Carbon::now()->subDays(50 + $faker->numberBetween(-5, 5)),
                'approved_at' => Carbon::now()->subDays(48 + $faker->numberBetween(-3, 3)),
                'shipped_at' => Carbon::now()->subDays(46 + $faker->numberBetween(-2, 2)),
                'delivered_at' => Carbon::now()->subDays(44 + $faker->numberBetween(-2, 2)),
            ];
        }

        // Pedidos de hace 30 días
        for ($i = 0; $i < 12; $i++) {
            $customer = $customers->random();
            $status = $faker->randomElement(['delivered', 'shipped']);
            $orders[] = [
                'order_number' => 'ORD-2025-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone ?: $faker->phoneNumber(),
                'customer_address' => $customer->address ?: $faker->address(),
                'status' => $status,
                'total_amount' => $faker->randomFloat(2, 200, 900),
                'notes' => $faker->optional(0.4)->sentence(),
                'created_at' => Carbon::now()->subDays(30 + $faker->numberBetween(-8, 8)),
                'approved_at' => Carbon::now()->subDays(28 + $faker->numberBetween(-3, 3)),
                'shipped_at' => Carbon::now()->subDays(26 + $faker->numberBetween(-2, 2)),
                'delivered_at' => $status === 'delivered' ? Carbon::now()->subDays(24 + $faker->numberBetween(-2, 2)) : null,
            ];
        }

        // Pedidos de hace 15 días
        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            $status = $faker->randomElement(['approved', 'shipped', 'delivered']);
            $orders[] = [
                'order_number' => 'ORD-2025-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone ?: $faker->phoneNumber(),
                'customer_address' => $customer->address ?: $faker->address(),
                'status' => $status,
                'total_amount' => $faker->randomFloat(2, 120, 650),
                'notes' => $faker->optional(0.5)->sentence(),
                'created_at' => Carbon::now()->subDays(15 + $faker->numberBetween(-5, 5)),
                'approved_at' => Carbon::now()->subDays(13 + $faker->numberBetween(-2, 2)),
                'shipped_at' => in_array($status, ['shipped', 'delivered']) ? Carbon::now()->subDays(11 + $faker->numberBetween(-2, 2)) : null,
                'delivered_at' => $status === 'delivered' ? Carbon::now()->subDays(9 + $faker->numberBetween(-2, 2)) : null,
            ];
        }

        // Pedidos de los últimos 7 días
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            $status = $faker->randomElement(['pending', 'processing', 'approved', 'shipped']);
            
            $orders[] = [
                'order_number' => 'ORD-2025-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone ?: $faker->phoneNumber(),
                'customer_address' => $customer->address ?: $faker->address(),
                'status' => $status,
                'total_amount' => $faker->randomFloat(2, 80, 450),
                'notes' => $faker->optional(0.6)->sentence(),
                'created_at' => Carbon::now()->subDays($faker->numberBetween(1, 7))->subHours($faker->numberBetween(0, 23)),
                'approved_at' => in_array($status, ['approved', 'shipped']) ? Carbon::now()->subDays($faker->numberBetween(0, 6))->subHours($faker->numberBetween(0, 23)) : null,
                'shipped_at' => $status === 'shipped' ? Carbon::now()->subDays($faker->numberBetween(0, 5))->subHours($faker->numberBetween(0, 23)) : null,
                'delivered_at' => null,
            ];
        }

        // Pedidos de hoy
        for ($i = 0; $i < 8; $i++) {
            $customer = $customers->random();
            $status = $faker->randomElement(['pending', 'processing', 'approved']);
            $orders[] = [
                'order_number' => 'ORD-2025-' . str_pad(count($orders) + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone ?: $faker->phoneNumber(),
                'customer_address' => $customer->address ?: $faker->address(),
                'status' => $status,
                'total_amount' => $faker->randomFloat(2, 95, 380),
                'notes' => $faker->optional(0.7)->sentence(),
                'created_at' => Carbon::now()->subHours($faker->numberBetween(1, 12)),
                'approved_at' => $status === 'approved' ? Carbon::now()->subHours($faker->numberBetween(1, 8)) : null,
                'shipped_at' => null,
                'delivered_at' => null,
            ];
        }

        // Crear los pedidos
        foreach ($orders as $orderData) {
            $order = Order::create($orderData);
            $this->createOrderItems($order, $products);
        }

        // Actualizar estadísticas de clientes
        $this->updateCustomerStatistics();

        $this->command->info('Se han creado ' . count($orders) . ' pedidos con sus items distribuidos en diferentes fechas.');
    }

    private function updateCustomerStatistics()
    {
        $customers = Customer::all();
        
        foreach ($customers as $customer) {
            $orders = Order::where('customer_id', $customer->id)->get();
            
            if ($orders->isNotEmpty()) {
                $totalAmount = $orders->sum('total_amount');
                $totalCount = $orders->count();
                $lastOrder = $orders->sortByDesc('created_at')->first();
                
                $customer->update([
                    'total_orders_count' => $totalCount,
                    'total_orders_amount' => $totalAmount,
                    'last_order_at' => $lastOrder->created_at
                ]);
            }
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