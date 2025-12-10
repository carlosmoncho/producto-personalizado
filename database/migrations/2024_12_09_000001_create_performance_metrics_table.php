<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->enum('strategy', ['mobile', 'desktop'])->default('mobile');

            // Lighthouse scores (0-100)
            $table->unsignedTinyInteger('performance_score')->nullable();
            $table->unsignedTinyInteger('accessibility_score')->nullable();
            $table->unsignedTinyInteger('best_practices_score')->nullable();
            $table->unsignedTinyInteger('seo_score')->nullable();

            // Core Web Vitals and other metrics (in milliseconds)
            $table->float('first_contentful_paint')->nullable(); // FCP
            $table->float('largest_contentful_paint')->nullable(); // LCP
            $table->float('total_blocking_time')->nullable(); // TBT
            $table->float('cumulative_layout_shift')->nullable(); // CLS
            $table->float('speed_index')->nullable();
            $table->float('time_to_interactive')->nullable(); // TTI

            // Raw API response for detailed analysis
            $table->json('raw_data')->nullable();

            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['url', 'created_at']);
            $table->index(['strategy', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
