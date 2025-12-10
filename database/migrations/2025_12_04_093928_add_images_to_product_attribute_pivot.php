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
        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->json('images')->nullable()->after('custom_price_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
