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
        Schema::table('order_items', function (Blueprint $table) {
            // Indica si el cliente eligió tinta personalizada
            $table->boolean('has_custom_ink')->default(false)->after('configuration');

            // Código hexadecimal de la tinta personalizada elegida
            $table->string('custom_ink_hex', 7)->nullable()->after('has_custom_ink');

            // Nombre descriptivo opcional dado por el cliente (ej: "Azul corporativo")
            $table->string('custom_ink_name')->nullable()->after('custom_ink_hex');

            // Código Pantone si el cliente lo proporciona
            $table->string('custom_ink_pantone')->nullable()->after('custom_ink_name');

            // Notas adicionales del cliente sobre la tinta
            $table->text('custom_ink_notes')->nullable()->after('custom_ink_pantone');

            // Precio adicional cobrado por la tinta personalizada
            $table->decimal('custom_ink_price', 8, 2)->default(0)->after('custom_ink_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'has_custom_ink',
                'custom_ink_hex',
                'custom_ink_name',
                'custom_ink_pantone',
                'custom_ink_notes',
                'custom_ink_price',
            ]);
        });
    }
};
