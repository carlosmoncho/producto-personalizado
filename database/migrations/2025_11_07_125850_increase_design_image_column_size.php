<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Aumentar el tamaño de design_image para soportar imágenes grandes en base64
     */
    public function up(): void
    {
        // Usar raw SQL para cambiar a LONGTEXT (hasta 4GB)
        \DB::statement('ALTER TABLE order_items MODIFY COLUMN design_image LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
};
