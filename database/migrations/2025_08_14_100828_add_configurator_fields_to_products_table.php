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
            // Configuración del configurador
            $table->boolean('has_configurator')->default(false)->after('active');
            $table->json('available_colors')->nullable()->after('has_configurator');
            $table->json('available_materials')->nullable()->after('available_colors');
            $table->json('available_sizes')->nullable()->after('available_materials');
            $table->json('available_inks')->nullable()->after('available_sizes');
            $table->json('available_quantities')->nullable()->after('available_inks');
            $table->json('available_systems')->nullable()->after('available_quantities');
            
            // Configuración específica del producto
            $table->json('configurator_rules')->nullable()->after('available_systems');
            $table->json('base_pricing')->nullable()->after('configurator_rules');
            $table->integer('max_print_colors')->default(1)->after('base_pricing');
            $table->boolean('allow_file_upload')->default(false)->after('max_print_colors');
            $table->json('file_upload_types')->nullable()->after('allow_file_upload');
            
            // Configuración de precios dinámicos
            $table->decimal('configurator_base_price', 10, 4)->nullable()->after('file_upload_types');
            $table->json('price_modifiers')->nullable()->after('configurator_base_price');
            
            // Metadatos
            $table->text('configurator_description')->nullable()->after('price_modifiers');
            $table->json('configurator_settings')->nullable()->after('configurator_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'has_configurator',
                'available_colors',
                'available_materials', 
                'available_sizes',
                'available_inks',
                'available_quantities',
                'available_systems',
                'configurator_rules',
                'base_pricing',
                'max_print_colors',
                'allow_file_upload',
                'file_upload_types',
                'configurator_base_price',
                'price_modifiers',
                'configurator_description',
                'configurator_settings'
            ]);
        });
    }
};