<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Añadir nuevo campo JSON para múltiples tintas personalizadas
            // Formato: [{"hex": "#FF0000", "name": "Rojo corporativo"}, {"hex": "#00FF00"}]
            $table->json('custom_inks')->nullable()->after('custom_ink_price');
        });

        // Migrar datos existentes del campo antiguo al nuevo
        DB::statement("
            UPDATE order_items
            SET custom_inks = JSON_ARRAY(JSON_OBJECT('hex', custom_ink_hex, 'name', custom_ink_name, 'pantone', custom_ink_pantone))
            WHERE has_custom_ink = true AND custom_ink_hex IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('custom_inks');
        });
    }
};
