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

        // Migrar datos existentes del campo antiguo al nuevo (compatible con MySQL y PostgreSQL)
        $items = DB::table('order_items')
            ->where('has_custom_ink', true)
            ->whereNotNull('custom_ink_hex')
            ->get(['id', 'custom_ink_hex', 'custom_ink_name', 'custom_ink_pantone']);

        foreach ($items as $item) {
            $customInks = [
                [
                    'hex' => $item->custom_ink_hex,
                    'name' => $item->custom_ink_name,
                    'pantone' => $item->custom_ink_pantone,
                ]
            ];

            DB::table('order_items')
                ->where('id', $item->id)
                ->update(['custom_inks' => json_encode($customInks)]);
        }
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
