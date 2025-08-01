<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Categorías
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['active', 'sort_order']);
        });

        // 2. Subcategorías
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['category_id', 'active']);
            $table->index('sort_order');
        });

        // 3. Colores disponibles
        Schema::create('available_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hex_code');
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['active', 'sort_order']);
        });

        // 4. Colores de impresión disponibles
        Schema::create('available_print_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hex_code');
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['active', 'sort_order']);
        });

        // 5. Productos
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->json('colors');
            $table->string('material');
            $table->json('sizes');
            $table->string('printing_system');
            $table->integer('face_count');
            $table->integer('print_colors_count');
            $table->json('print_colors');
            $table->json('images')->nullable();
            $table->string('model_3d_file')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcategory_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->index(['category_id', 'subcategory_id']);
            $table->index('active');
            $table->index('sku');
        });

        // 6. Precios de productos
        Schema::create('product_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_from');
            $table->integer('quantity_to');
            $table->decimal('price', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
            
            $table->index(['product_id', 'quantity_from', 'quantity_to']);
        });

        // 7. Pedidos
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->enum('status', [
                'pending', 
                'processing', 
                'approved', 
                'in_production', 
                'shipped', 
                'delivered', 
                'cancelled'
            ])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->index('order_number');
            $table->index('status');
            $table->index('customer_email');
        });

        // 8. Items de pedidos
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('selected_size');
            $table->string('selected_color');
            $table->json('selected_print_colors')->nullable();
            $table->string('design_image')->nullable();
            $table->text('design_comments')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('product_pricing');
        Schema::dropIfExists('products');
        Schema::dropIfExists('available_print_colors');
        Schema::dropIfExists('available_colors');
        Schema::dropIfExists('subcategories');
        Schema::dropIfExists('categories');
    }
};
