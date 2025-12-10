<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Aumenta la precisión decimal de unit_price para soportar
     * productos con precio por millar (ej: 0.03832€/ud = 38.32€/millar)
     */
    public function up(): void
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->change();
        });
    }
};
