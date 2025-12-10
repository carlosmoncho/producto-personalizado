<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // color, material, size, ink, system, etc.
            $table->string('name');
            $table->string('value');
            $table->string('hex_code')->nullable(); // para colores y tintas
            $table->decimal('price_modifier', 8, 4)->default(0); // modificador de precio
            $table->decimal('price_percentage', 5, 2)->default(0); // porcentaje adicional
            $table->json('metadata')->nullable(); // datos adicionales como certificaciones, recomendaciones
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('is_recommended')->default(false);
            $table->timestamps();
            
            $table->index(['type', 'active']);
            $table->unique(['type', 'value']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_attributes');
    }
};