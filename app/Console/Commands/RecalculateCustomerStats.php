<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;

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
            // Usar el método del modelo que busca por email
            $customer->updateOrderStats();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Estadísticas recalculadas para ' . $customers->count() . ' clientes');

        return 0;
    }
}
