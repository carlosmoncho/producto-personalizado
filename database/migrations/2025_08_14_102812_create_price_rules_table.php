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
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Tipo de regla
            $table->enum('rule_type', ['combination', 'volume', 'attribute_specific', 'conditional'])
                  ->default('combination');
            
            // Condiciones de la regla (JSON)
            $table->json('conditions'); // Ej: {"attributes": [1,2,3], "quantity_min": 100}
            
            // Acción de precio
            $table->enum('action_type', ['add_fixed', 'add_percentage', 'multiply', 'set_fixed', 'set_percentage'])
                  ->default('add_percentage');
            $table->decimal('action_value', 10, 4); // Valor de la acción
            
            // Prioridad (mayor número = mayor prioridad)
            $table->integer('priority')->default(0);
            
            // Filtros opcionales
            $table->unsignedBigInteger('product_id')->nullable(); // Para reglas específicas de producto
            $table->unsignedBigInteger('category_id')->nullable(); // Para reglas de categoría
            $table->integer('quantity_min')->nullable(); // Cantidad mínima para aplicar
            $table->integer('quantity_max')->nullable(); // Cantidad máxima para aplicar
            
            // Validez temporal
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            
            // Estado
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->index(['active', 'priority', 'sort_order']);
            $table->index(['product_id', 'active']);
            $table->index(['category_id', 'active']);
            $table->index(['rule_type', 'active']);
            
            // Claves foráneas
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_rules');
    }
};
