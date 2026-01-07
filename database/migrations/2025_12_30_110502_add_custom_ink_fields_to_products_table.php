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
            // Permitir tinta personalizada (color elegido por el cliente)
            $table->boolean('allows_custom_ink')->default(false)->after('max_print_colors');

            // Precio adicional por usar tinta personalizada (fijo)
            $table->decimal('custom_ink_price_modifier', 8, 4)->default(0)->after('allows_custom_ink');

            // Precio adicional porcentual por tinta personalizada
            $table->decimal('custom_ink_price_percentage', 5, 2)->default(0)->after('custom_ink_price_modifier');

            // Días adicionales de producción por tinta personalizada
            $table->integer('custom_ink_extra_days')->default(0)->after('custom_ink_price_percentage');

            // Nota informativa para el cliente sobre tinta personalizada
            $table->text('custom_ink_note')->nullable()->after('custom_ink_extra_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'allows_custom_ink',
                'custom_ink_price_modifier',
                'custom_ink_price_percentage',
                'custom_ink_extra_days',
                'custom_ink_note',
            ]);
        });
    }
};
