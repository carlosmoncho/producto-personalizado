<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Subtotal sin IVA (suma de items con extras)
            $table->decimal('subtotal', 10, 2)->default(0)->after('status');
            // Porcentaje de IVA aplicado
            $table->decimal('tax_rate', 5, 2)->default(21.00)->after('subtotal');
            // Importe del IVA
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');
        });

        // Migrar datos existentes: asumir que total_amount actual es sin IVA
        // y calcular los nuevos campos
        \DB::statement('UPDATE orders SET subtotal = total_amount, tax_amount = ROUND(total_amount * 0.21, 2), total_amount = ROUND(total_amount * 1.21, 2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir: convertir total_amount de vuelta a sin IVA
        \DB::statement('UPDATE orders SET total_amount = subtotal');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_rate', 'tax_amount']);
        });
    }
};
