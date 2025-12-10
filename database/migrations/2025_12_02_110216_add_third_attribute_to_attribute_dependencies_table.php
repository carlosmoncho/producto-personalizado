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
            $table->foreignId('third_attribute_id')
                ->nullable()
                ->after('dependent_attribute_id')
                ->constrained('product_attributes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attribute_dependencies', function (Blueprint $table) {
            $table->dropForeign(['third_attribute_id']);
            $table->dropColumn('third_attribute_id');
        });
    }
};
