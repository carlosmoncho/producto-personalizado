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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: TEXT ya soporta hasta 1GB
            \DB::statement('ALTER TABLE order_items ALTER COLUMN design_image TYPE TEXT');
        } else {
            // MySQL: cambiar a LONGTEXT (hasta 4GB)
            \DB::statement('ALTER TABLE order_items MODIFY COLUMN design_image LONGTEXT NULL');
        }
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
