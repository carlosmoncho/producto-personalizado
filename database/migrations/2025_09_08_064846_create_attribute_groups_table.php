<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Grupos de atributos para mejor organización
        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type'); // color, size, material, etc.
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->boolean('allow_multiple')->default(false);
            $table->boolean('affects_price')->default(false);
            $table->boolean('affects_stock')->default(false);
            $table->boolean('show_in_filter')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'active']);
            $table->index('slug');
        });

        // Añadir columnas mejoradas a product_attributes
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->foreignId('attribute_group_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->string('slug')->nullable()->after('value');
            $table->string('image_path')->nullable()->after('hex_code');
            $table->string('thumbnail_path')->nullable()->after('image_path');
            $table->text('description')->nullable()->after('name');
            $table->string('sku_suffix')->nullable()->after('value');
            $table->integer('stock_quantity')->nullable();
            $table->decimal('weight_modifier', 8, 3)->default(0);
            $table->json('compatible_materials')->nullable();
            $table->json('incompatible_with')->nullable();
            $table->boolean('requires_file_upload')->default(false);
            $table->string('pantone_code')->nullable();
            $table->string('ral_code')->nullable();
            
            $table->index('attribute_group_id');
            $table->index('slug');
            $table->unique(['attribute_group_id', 'slug']);
        });
    }

    public function down()
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropForeign(['attribute_group_id']);
            $table->dropColumn([
                'attribute_group_id',
                'slug',
                'image_path',
                'thumbnail_path',
                'description',
                'sku_suffix',
                'stock_quantity',
                'weight_modifier',
                'compatible_materials',
                'incompatible_with',
                'requires_file_upload',
                'pantone_code',
                'ral_code'
            ]);
        });
        
        Schema::dropIfExists('attribute_groups');
    }
};