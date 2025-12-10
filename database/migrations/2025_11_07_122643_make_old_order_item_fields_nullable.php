<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Hacer nullable los campos del sistema antiguo ya que ahora usamos configuration
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('selected_size')->nullable()->change();
            $table->string('selected_color')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('selected_size')->nullable(false)->change();
            $table->string('selected_color')->nullable(false)->change();
        });
    }
};
