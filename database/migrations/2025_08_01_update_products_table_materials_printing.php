<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Cambiar material a JSON para múltiples materiales
            $table->json('materials')->nullable()->after('colors');
            
            // Agregar relación con sistema de impresión
            $table->unsignedBigInteger('printing_system_id')->nullable()->after('sizes');
            $table->foreign('printing_system_id')->references('id')->on('printing_systems')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['printing_system_id']);
            $table->dropColumn(['materials', 'printing_system_id']);
        });
    }
};