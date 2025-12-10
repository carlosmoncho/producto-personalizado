<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // para usuarios anónimos
            $table->unsignedBigInteger('user_id')->nullable(); // para usuarios registrados
            $table->unsignedBigInteger('product_id');
            
            // Atributos base
            $table->json('attributes_base'); // color, material, tamaño
            
            // Personalización  
            $table->json('personalization')->nullable(); // sistema_impresion, numero_colores, tintas, etc.
            
            // Archivos
            $table->json('files')->nullable(); // logos, archivos adicionales
            
            // Valores calculados
            $table->json('calculated')->nullable(); // precios, tiempos, certificaciones
            
            // Estado de la configuración
            $table->string('status')->default('draft'); // draft, completed, ordered
            $table->boolean('is_valid')->default(false);
            $table->json('validation_errors')->nullable();
            
            // Metadatos
            $table->timestamp('expires_at')->nullable(); // expiración de sesión
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index(['session_id', 'product_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_configurations');
    }
};