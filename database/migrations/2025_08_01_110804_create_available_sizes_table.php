<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla de tamaños disponibles
        Schema::create('available_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable(); // Por si quieren usar códigos como S, M, L, XL
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['active', 'sort_order']);
        });

        // Modificar la tabla de productos para cambiar 'sizes' a 'size_ids' si es necesario
        // Esto es opcional, depende si quieres mantener el campo JSON o relacionarlo
    }

    public function down(): void
    {
        Schema::dropIfExists('available_sizes');
    }
};