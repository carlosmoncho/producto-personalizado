<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('available_print_colors')) {
            Schema::create('available_print_colors', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('hex_code');
                $table->boolean('active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                $table->index('active');
                $table->index('sort_order');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('available_print_colors');
    }
};