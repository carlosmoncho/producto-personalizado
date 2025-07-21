<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('colors')->nullable()->after('color');
        });

        DB::table('products')->get()->each(function ($product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update(['colors' => json_encode([$product->color])]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('color')->after('colors');
        });

        DB::table('products')->get()->each(function ($product) {
            $colors = json_decode($product->colors, true);
            DB::table('products')
                ->where('id', $product->id)
                ->update(['color' => $colors[0] ?? '']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('colors');
        });
    }
};