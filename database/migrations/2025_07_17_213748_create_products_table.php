<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->string('color');
            $table->string('material');
            $table->json('sizes'); // Array de tamaños disponibles
            $table->string('printing_system');
            $table->integer('face_count');
            $table->integer('print_colors_count');
            $table->json('print_colors'); // Array de colores seleccionados
            $table->json('images')->nullable(); // Array de rutas de imágenes
            $table->string('model_3d_file')->nullable(); // Ruta del archivo 3D
            $table->boolean('active')->default(true);
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcategory_id')->constrained()->cascadeOnDelete();
            $table->json('custom_fields')->nullable(); // Para campos personalizados
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
