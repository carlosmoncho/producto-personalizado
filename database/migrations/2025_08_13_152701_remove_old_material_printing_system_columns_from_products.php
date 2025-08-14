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
        Schema::table('products', function (Blueprint $table) {
            // Eliminar las columnas obsoletas que ya no se usan
            if (Schema::hasColumn('products', 'material')) {
                $table->dropColumn('material');
            }
            if (Schema::hasColumn('products', 'printing_system')) {
                $table->dropColumn('printing_system');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restaurar las columnas en caso de rollback
            $table->string('material')->nullable();
            $table->string('printing_system')->nullable();
        });
    }
};
