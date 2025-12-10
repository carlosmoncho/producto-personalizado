<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;

class TestProductSeeder extends Seeder
{
    public function run()
    {
        // Obtener o crear categoría y subcategoría
        $category = Category::firstOrCreate(
            ['name' => 'Ropa'],
            ['slug' => 'ropa', 'description' => 'Categoría de ropa', 'active' => true, 'sort_order' => 1]
        );

        $subcategory = Subcategory::where('slug', 'camisetas')->first();
        if (!$subcategory) {
            $subcategory = Subcategory::create([
                'name' => 'Camisetas',
                'category_id' => $category->id,
                'slug' => 'camisetas-demo',
                'description' => 'Camisetas personalizables',
                'active' => true,
                'sort_order' => 1
            ]);
        }

        // Crear producto de prueba con configurador
        $product = Product::updateOrCreate(
            ['sku' => 'TEST-CONFIG-001'],
            [
                'name' => 'Camiseta Personalizable Demo',
                'slug' => 'camiseta-personalizable-demo',
                'description' => 'Camiseta de alta calidad con múltiples opciones de personalización',
                'category_id' => $category->id,
                'subcategory_id' => $subcategory->id,
                'active' => true,

                // Configuración del configurador
                'has_configurator' => true,
                'configurator_base_price' => 15.00,
                'max_print_colors' => 3,
                'allow_file_upload' => true,
                'file_upload_types' => ['jpg', 'png', 'pdf'],
                'configurator_description' => 'Personaliza tu camiseta con diferentes colores, materiales y tamaños',

                // Arrays de opciones disponibles (por ahora vacíos, se llenarán con atributos)
                'available_colors' => [],
                'available_materials' => [],
                'available_sizes' => [],
                'available_inks' => [],
                'available_quantities' => [100, 500, 1000, 5000],

                // Campos legacy
                'colors' => ['Blanco', 'Negro', 'Rojo', 'Azul'],
                'materials' => ['Algodón 100%', 'Poliéster', 'Mezcla 50/50'],
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'face_count' => 2,
                'print_colors_count' => 4,
                'print_colors' => ['CMYK'],
            ]
        );

        // Agregar algunos precios
        $product->pricing()->delete(); // Limpiar precios existentes
        $product->pricing()->createMany([
            ['quantity_from' => 1, 'quantity_to' => 99, 'unit_price' => 20.00, 'price' => 20.00],
            ['quantity_from' => 100, 'quantity_to' => 499, 'unit_price' => 15.00, 'price' => 15.00],
            ['quantity_from' => 500, 'quantity_to' => 999, 'unit_price' => 12.00, 'price' => 12.00],
            ['quantity_from' => 1000, 'quantity_to' => 99999, 'unit_price' => 10.00, 'price' => 10.00],
        ]);

        $this->command->info("Producto de prueba creado: ID {$product->id} - {$product->name}");
        $this->command->info("URL del configurador demo: /admin/configurator-demo/{$product->id}");
    }
}