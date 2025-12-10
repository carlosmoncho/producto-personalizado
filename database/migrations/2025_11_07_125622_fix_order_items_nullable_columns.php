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
        // Usar raw SQL para asegurar que las columnas sean nullable
        \DB::statement('ALTER TABLE order_items MODIFY COLUMN selected_size VARCHAR(255) NULL');
        \DB::statement('ALTER TABLE order_items MODIFY COLUMN selected_color VARCHAR(255) NULL');
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
