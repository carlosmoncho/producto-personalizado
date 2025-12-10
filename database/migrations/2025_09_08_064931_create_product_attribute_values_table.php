<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabla pivot mejorada para relacionar productos con atributos
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('attribute_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_attribute_id')->constrained()->onDelete('cascade');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_available')->default(true);
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();
            $table->decimal('custom_price_modifier', 10, 4)->nullable();
            $table->decimal('custom_price_percentage', 5, 2)->nullable();
            $table->integer('additional_production_days')->default(0);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['product_id', 'attribute_group_id', 'product_attribute_id'], 'product_attribute_unique');
            $table->index(['product_id', 'is_available']);
            $table->index(['product_id', 'is_default']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_attribute_values');
    }
};