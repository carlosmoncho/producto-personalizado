<?php
// database/migrations/2024_xx_xx_add_colors_json_to_products_table_if_not_exists.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Verificar si la columna colors ya existe
        if (!Schema::hasColumn('products', 'colors')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('colors')->nullable()->after('sku');
            });
        }

        // Si aún existe la columna color, migrar los datos
        if (Schema::hasColumn('products', 'color')) {
            DB::table('products')->whereNotNull('color')->get()->each(function ($product) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['colors' => json_encode([$product->color])]);
            });

            // Eliminar la columna color antigua
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        }
    }

    public function down()
    {
        // Solo revertir si existe la columna colors
        if (Schema::hasColumn('products', 'colors')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('color')->nullable()->after('sku');
            });

            DB::table('products')->whereNotNull('colors')->get()->each(function ($product) {
                $colors = json_decode($product->colors, true);
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['color' => $colors[0] ?? '']);
            });

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('colors');
            });
        }
    }
};