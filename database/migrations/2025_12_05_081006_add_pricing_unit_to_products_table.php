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
        Schema::table('products', function (Blueprint $table) {
            // Unidad de precio: 'unit' = por unidad, 'thousand' = por millar (1000 unidades)
            $table->enum('pricing_unit', ['unit', 'thousand'])->default('unit')->after('configurator_base_price');
            // Cantidad que representa una unidad de venta (1 para unit, 1000 para thousand)
            $table->integer('pricing_unit_quantity')->default(1)->after('pricing_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['pricing_unit', 'pricing_unit_quantity']);
        });
    }
};
