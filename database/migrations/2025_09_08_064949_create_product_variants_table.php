<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabla para gestionar variantes (combinaciones de atributos)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('variant_sku')->unique();
            $table->string('variant_name');
            $table->json('attribute_combination'); // IDs de los atributos que forman esta variante
            $table->decimal('price', 10, 4);
            $table->decimal('compare_price', 10, 4)->nullable();
            $table->decimal('cost', 10, 4)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            $table->decimal('weight', 8, 3)->nullable();
            $table->string('barcode')->nullable();
            $table->string('image_path')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->integer('production_days')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('min_order_quantity')->default(1);
            $table->integer('max_order_quantity')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'is_active']);
            $table->index(['product_id', 'is_default']);
            $table->index('variant_sku');
            $table->index('barcode');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
};