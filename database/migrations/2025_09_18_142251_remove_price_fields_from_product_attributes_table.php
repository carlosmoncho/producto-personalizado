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
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropColumn(['price_modifier', 'price_percentage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->decimal('price_modifier', 8, 4)->nullable()->after('thumbnail_path');
            $table->decimal('price_percentage', 5, 2)->nullable()->after('price_modifier');
        });
    }
};
