<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $textil = Category::where('slug', 'textil')->first();
        $camisetas = Subcategory::where('slug', 'camisetas')->first();
        $sudaderas = Subcategory::where('slug', 'sudaderas')->first();
        $gorras = Subcategory::where('slug', 'gorras')->first();

        $promocional = Category::where('slug', 'promocional')->first();
        $boligrafos = Subcategory::where('slug', 'boligrafos')->first();
        $llaveros = Subcategory::where('slug', 'llaveros')->first();

        $tecnologia = Category::where('slug', 'tecnologia')->first();
        $fundas = Subcategory::where('slug', 'fundas-movil')->first();

        $hogar = Category::where('slug', 'hogar')->first();
        $tazas = Subcategory::where('slug', 'tazas')->first();

        $products = [
            [
                'name' => 'Camiseta Básica Algodón',
                'slug' => 'camiseta-basica-algodon',
                'description' => 'Camiseta 100% algodón, perfecta para personalización con serigrafía o vinilo',
                'sku' => 'CAM-001',
                'color' => 'Blanco',
                'material' => 'Algodón 100%',
                'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'printing_system' => 'Serigrafía',
                'face_count' => 2,
                'print_colors_count' => 1,
                'print_colors' => ['Negro'],
                'category_id' => $textil->id,
                'subcategory_id' => $camisetas->id,
                'active' => true,
            ],
            [
                'name' => 'Sudadera con Capucha',
                'slug' => 'sudadera-con-capucha',
                'description' => 'Sudadera con capucha de algodón-poliéster, ideal para bordado',
                'sku' => 'SUD-001',
                'color' => 'Gris',
                'material' => 'Algodón-Poliéster',
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'printing_system' => 'Bordado',
                'face_count' => 3,
                'print_colors_count' => 2,
                'print_colors' => ['Azul', 'Blanco'],
                'category_id' => $textil->id,
                'subcategory_id' => $sudaderas->id,
                'active' => true,
            ],
            [
                'name' => 'Gorra Snapback',
                'slug' => 'gorra-snapback',
                'description' => 'Gorra snapback ajustable con visera plana',
                'sku' => 'GOR-001',
                'color' => 'Negro',
                'material' => 'Algodón',
                'sizes' => ['Única'],
                'printing_system' => 'Bordado',
                'face_count' => 2,
                'print_colors_count' => 1,
                'print_colors' => ['Blanco'],
                'category_id' => $textil->id,
                'subcategory_id' => $gorras->id,
                'active' => true,
            ],
            [
                'name' => 'Bolígrafo Metálico',
                'slug' => 'boligrafo-metalico',
                'description' => 'Bolígrafo metálico de alta calidad con grabado láser',
                'sku' => 'BOL-001',
                'color' => 'Plateado',
                'material' => 'Metal',
                'sizes' => ['Única'],
                'printing_system' => 'Grabado láser',
                'face_count' => 1,
                'print_colors_count' => 1,
                'print_colors' => ['Grabado'],
                'category_id' => $promocional->id,
                'subcategory_id' => $boligrafos->id,
                'active' => true,
            ],
            [
                'name' => 'Llavero Acrílico',
                'slug' => 'llavero-acrilico',
                'description' => 'Llavero de acrílico transparente con impresión digital',
                'sku' => 'LLA-001',
                'color' => 'Transparente',
                'material' => 'Acrílico',
                'sizes' => ['5x5cm', '6x6cm', '7x7cm'],
                'printing_system' => 'Impresión digital',
                'face_count' => 2,
                'print_colors_count' => 4,
                'print_colors' => ['Cian', 'Magenta', 'Amarillo', 'Negro'],
                'category_id' => $promocional->id,
                'subcategory_id' => $llaveros->id,
                'active' => true,
            ],
            [
                'name' => 'Funda iPhone Silicona',
                'slug' => 'funda-iphone-silicona',
                'description' => 'Funda de silicona para iPhone con impresión UV',
                'sku' => 'FUN-001',
                'color' => 'Transparente',
                'material' => 'Silicona TPU',
                'sizes' => ['iPhone 12', 'iPhone 13', 'iPhone 14', 'iPhone 15'],
                'printing_system' => 'Impresión UV',
                'face_count' => 1,
                'print_colors_count' => 4,
                'print_colors' => ['Cian', 'Magenta', 'Amarillo', 'Negro'],
                'category_id' => $tecnologia->id,
                'subcategory_id' => $fundas->id,
                'active' => true,
            ],
            [
                'name' => 'Taza Cerámica Blanca',
                'slug' => 'taza-ceramica-blanca',
                'description' => 'Taza de cerámica blanca con sublimación',
                'sku' => 'TAZ-001',
                'color' => 'Blanco',
                'material' => 'Cerámica',
                'sizes' => ['300ml', '350ml', '400ml'],
                'printing_system' => 'Sublimación',
                'face_count' => 2,
                'print_colors_count' => 4,
                'print_colors' => ['Cian', 'Magenta', 'Amarillo', 'Negro'],
                'category_id' => $hogar->id,
                'subcategory_id' => $tazas->id,
                'active' => true,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            
            // Crear precios para cada producto
            $this->createPricingForProduct($product);
        }
    }

    private function createPricingForProduct($product)
    {
        $pricingRanges = [
            ['from' => 1, 'to' => 24, 'price' => 15.00, 'unit_price' => 15.00],
            ['from' => 25, 'to' => 49, 'price' => 12.50, 'unit_price' => 12.50],
            ['from' => 50, 'to' => 99, 'price' => 10.00, 'unit_price' => 10.00],
            ['from' => 100, 'to' => 199, 'price' => 8.50, 'unit_price' => 8.50],
            ['from' => 200, 'to' => 499, 'price' => 7.00, 'unit_price' => 7.00],
            ['from' => 500, 'to' => 999, 'price' => 6.00, 'unit_price' => 6.00],
            ['from' => 1000, 'to' => 9999, 'price' => 5.00, 'unit_price' => 5.00],
        ];

        foreach ($pricingRanges as $range) {
            $product->pricing()->create([
                'quantity_from' => $range['from'],
                'quantity_to' => $range['to'],
                'price' => $range['price'],
                'unit_price' => $range['unit_price'],
            ]);
        }
    }
}
