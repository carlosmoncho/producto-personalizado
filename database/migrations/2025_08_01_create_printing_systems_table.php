<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printing_systems', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('total_colors')->default(1);
            $table->integer('min_units')->default(1);
            $table->decimal('price_per_unit', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printing_systems');
    }
};