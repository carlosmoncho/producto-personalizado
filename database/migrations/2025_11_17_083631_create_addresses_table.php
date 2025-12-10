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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['shipping', 'billing'])->default('shipping');
            $table->string('name'); // Nombre completo del destinatario
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('address_line_1'); // Calle, número, piso, etc.
            $table->string('address_line_2')->nullable(); // Información adicional
            $table->string('city');
            $table->string('state')->nullable(); // Provincia/Estado
            $table->string('postal_code');
            $table->string('country')->default('España');
            $table->text('notes')->nullable(); // Notas de entrega
            $table->boolean('is_default')->default(false); // Dirección por defecto
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index(['customer_id', 'type']);
            $table->index(['customer_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
