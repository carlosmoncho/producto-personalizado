<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_dependencies', function (Blueprint $table) {
            $table->enum('price_applies_to', ['unit', 'total'])->default('unit')->after('price_modifier');
        });
    }

    public function down(): void
    {
        Schema::table('attribute_dependencies', function (Blueprint $table) {
            $table->dropColumn('price_applies_to');
        });
    }
};
