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
        // Agregar campo configuration a order_items para guardar atributos configurados
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('configuration')->nullable()->after('design_comments');
        });

        // Agregar campos separados para shipping y billing address a orders
        Schema::table('orders', function (Blueprint $table) {
            $table->text('shipping_address')->nullable()->after('customer_address');
            $table->text('billing_address')->nullable()->after('shipping_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('configuration');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_address', 'billing_address']);
        });
    }
};
