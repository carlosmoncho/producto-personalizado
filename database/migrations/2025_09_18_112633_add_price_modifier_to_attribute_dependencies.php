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
        Schema::table('attribute_dependencies', function (Blueprint $table) {
            // Añadir campo para modificador de precio directo (sin necesidad de dependiente)
            $table->decimal('price_modifier', 10, 4)->nullable()->after('price_impact')
                ->comment('Modificador de precio cuando se selecciona este atributo (+/- euros)');

            // Añadir campo para indicar si es una regla de precio simple
            $table->boolean('is_price_rule')->default(false)->after('condition_type')
                ->comment('Indica si es una regla simple de precio sin dependencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attribute_dependencies', function (Blueprint $table) {
            $table->dropColumn(['price_modifier', 'is_price_rule']);
        });
    }
};
