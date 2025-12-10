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
            $table->decimal('price_percentage', 8, 2)->nullable()->after('price_modifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attribute_dependencies', function (Blueprint $table) {
            $table->dropColumn('price_percentage');
        });
    }
};
