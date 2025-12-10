<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Order;

class RecalculateCustomerStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:recalculate-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcular estadísticas de pedidos para todos los clientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculando estadísticas de clientes...');

        $customers = Customer::all();
        $bar = $this->output->createProgressBar($customers->count());

        foreach ($customers as $customer) {
            // Obtener pedidos del cliente
            $orders = Order::where('customer_id', $customer->id)->get();

            // Calcular estadísticas
            $totalOrders = $orders->count();
            $totalAmount = $orders->sum('total_amount');
            $lastOrder = $orders->sortByDesc('created_at')->first();

            // Actualizar customer
            $customer->update([
                'total_orders_count' => $totalOrders,
                'total_orders_amount' => $totalAmount,
                'last_order_at' => $lastOrder ? $lastOrder->created_at : null,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Estadísticas recalculadas para ' . $customers->count() . ' clientes');

        return 0;
    }
}
