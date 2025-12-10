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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            \DB::statement('ALTER TABLE order_items ALTER COLUMN selected_size DROP NOT NULL');
            \DB::statement('ALTER TABLE order_items ALTER COLUMN selected_color DROP NOT NULL');
        } else {
            \DB::statement('ALTER TABLE order_items MODIFY COLUMN selected_size VARCHAR(255) NULL');
            \DB::statement('ALTER TABLE order_items MODIFY COLUMN selected_color VARCHAR(255) NULL');
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
