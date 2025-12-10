<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attribute_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_attribute_id'); // atributo que condiciona
            $table->unsignedBigInteger('dependent_attribute_id'); // atributo que depende
            $table->string('condition_type')->default('allows'); // allows, blocks, requires, sets_price
            $table->json('conditions')->nullable(); // condiciones específicas
            $table->decimal('price_impact', 8, 4)->default(0);
            $table->integer('priority')->default(0); // para resolver conflictos
            $table->boolean('auto_select')->default(false); // auto-seleccionar si es la única opción
            $table->boolean('reset_dependents')->default(true); // resetear dependientes al cambiar
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->foreign('parent_attribute_id')->references('id')->on('product_attributes')->onDelete('cascade');
            $table->foreign('dependent_attribute_id')->references('id')->on('product_attributes')->onDelete('cascade');
            $table->index(['parent_attribute_id', 'active']);
            $table->index(['dependent_attribute_id', 'active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attribute_dependencies');
    }
};