<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\AvailableColor;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Primero, crear algunos colores disponibles si no existen
        $this->createAvailableColors();

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

        // Crear más productos completos con diferentes especificaciones
        $products = [
            // Productos Textil - Camisetas
            [
                'name' => 'Camiseta Básica Algodón',
                'slug' => 'camiseta-basica-algodon',
                'description' => 'Camiseta 100% algodón, perfecta para personalización con serigrafía o vinilo. Corte clásico y gran durabilidad.',
                'sku' => 'CAM-001',
                'colors' => ['Blanco', 'Negro', 'Azul', 'Rojo', 'Gris', 'Verde'],
                'materials' => ['Algodón 100%'],
                'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'face_count' => 2,
                'print_colors_count' => 3,
                'print_colors' => ['Negro', 'Blanco', 'Azul'],
                'category_id' => $textil->id,
                'subcategory_id' => $camisetas->id,
                'active' => true,
            ],
            [
                'name' => 'Camiseta Premium Orgánica',
                'slug' => 'camiseta-premium-organica',
                'description' => 'Camiseta de algodón orgánico certificado, ideal para marcas sostenibles y conscientes con el medio ambiente.',
                'sku' => 'CAM-002',
                'colors' => ['Blanco', 'Negro', 'Gris', 'Azul Marino', 'Verde'],
                'materials' => ['Algodón Orgánico 100%'],
                'sizes' =>['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'face_count' => 2,
                'print_colors_count' => 2,
                'print_colors' => ['Negro', 'Blanco'],
                'category_id' => $textil->id,
                'subcategory_id' => $camisetas->id,
                'active' => true,
            ],
            [
                'name' => 'Polo Técnico Deportivo',
                'slug' => 'polo-tecnico-deportivo',
                'description' => 'Polo técnico de poliéster con tecnología de secado rápido. Perfecto para eventos deportivos y empresas activas.',
                'sku' => 'CAM-003',
                'colors' => ['Blanco', 'Negro', 'Azul', 'Rojo', 'Amarillo'],
                'materials' => ['Poliéster técnico'],
                'sizes' =>['S', 'M', 'L', 'XL', 'XXL'],
                'face_count' => 2,
                'print_colors_count' => 4,
                'print_colors' => ['Cian', 'Magenta', 'Amarillo', 'Negro'],
                'category_id' => $textil->id,
                'subcategory_id' => $camisetas->id,
                'active' => true,
            ],

            // Productos Textil - Sudaderas
            [
                'name' => 'Sudadera con Capucha Premium',
                'slug' => 'sudadera-con-capucha-premium',
                'description' => 'Sudadera premium con capucha de algodón-poliéster. Interior afelpado, cordones ajustables y bolsillo canguro.',
                'sku' => 'SUD-001',
                'colors' => ['Gris', 'Negro', 'Azul Marino', 'Blanco'],
                'materials' => ['Algodón-Poliéster (80/20)'],
                'sizes' =>['S', 'M', 'L', 'XL', 'XXL'],
                'face_count' => 3,
                'print_colors_count' => 4,
                'print_colors' => ['Azul', 'Blanco', 'Negro', 'Dorado'],
                'category_id' => $textil->id,
                'subcategory_id' => $sudaderas->id,
                'active' => true,
            ],
            [
                'name' => 'Chaqueta Softshell Corporativa',
                'slug' => 'chaqueta-softshell-corporativa',
                'description' => 'Chaqueta softshell transpirable y cortavientos. Ideal para uniformes corporativos y eventos al aire libre.',
                'sku' => 'SUD-002',
                'colors' => ['Negro', 'Azul Marino', 'Gris'],
                'materials' => ['Softshell (96% poliéster, 4% elastano)'],
                'sizes' =>['S', 'M', 'L', 'XL', 'XXL'],
                'face_count' => 2,
                'print_colors_count' => 2,
                'print_colors' => ['Blanco', 'Plateado'],
                'category_id' => $textil->id,
                'subcategory_id' => $sudaderas->id,
                'active' => true,
            ],

            // Productos Textil - Gorras
            [
                'name' => 'Gorra Snapback Urbana',
                'slug' => 'gorra-snapback-urbana',
                'description' => 'Gorra snapback urbana con visera plana y cierre ajustable. Estilo moderno para marcas jóvenes.',
                'sku' => 'GOR-001',
                'colors' => ['Negro', 'Rojo', 'Azul', 'Blanco'],
                'materials' => ['Algodón 100%'],
                'sizes' =>['Única'],
                'face_count' => 3,
                'print_colors_count' => 2,
                'print_colors' => ['Blanco', 'Negro'],
                'category_id' => $textil->id,
                'subcategory_id' => $gorras->id,
                'active' => true,
            ],
            [
                'name' => 'Gorra Trucker Mesh',
                'slug' => 'gorra-trucker-mesh',
                'description' => 'Gorra trucker clásica con parte trasera de malla. Transpirable y cómoda para uso diario.',
                'sku' => 'GOR-002',
                'colors' => ['Negro', 'Azul', 'Rojo', 'Blanco'],
                'materials' => ['Algodón/Mesh'],
                'sizes' =>['Única'],
                'face_count' => 2,
                'print_colors_count' => 3,
                'print_colors' => ['Blanco', 'Negro', 'Rojo'],
                'category_id' => $textil->id,
                'subcategory_id' => $gorras->id,
                'active' => true,
            ],

            // Productos Promocional - Bolígrafos
            [
                'name' => 'Bolígrafo Metálico Executive',
                'slug' => 'boligrafo-metalico-executive',
                'description' => 'Bolígrafo metálico premium con grabado láser. Acabado elegante para eventos corporativos de alto nivel.',
                'sku' => 'BOL-001',
                'colors' => ['Plateado', 'Dorado', 'Negro'],
                'materials' => ['Metal premium'],
                'sizes' =>['Única'],
                'face_count' => 2,
                'print_colors_count' => 1,
                'print_colors' => ['Grabado'],
                'category_id' => $promocional->id,
                'subcategory_id' => $boligrafos->id,
                'active' => true,
            ],
            [
                'name' => 'Bolígrafo Ecológico Bambú',
                'slug' => 'boligrafo-ecologico-bambu',
                'description' => 'Bolígrafo ecológico de bambú natural con clip metálico. Perfecto para empresas sostenibles.',
                'sku' => 'BOL-002',
                'colors' => ['Natural', 'Negro'],
                'materials' => ['Bambú'],
                'sizes' =>['Única'],
                'face_count' => 1,
                'print_colors_count' => 1,
                'print_colors' => ['Grabado'],
                'category_id' => $promocional->id,
                'subcategory_id' => $boligrafos->id,
                'active' => true,
            ],

            // Productos Promocional - Llaveros
            [
                'name' => 'Llavero Acrílico Personalizado',
                'slug' => 'llavero-acrilico-personalizado',
                'description' => 'Llavero de acrílico transparente con impresión digital a todo color. Formas personalizables.',
                'sku' => 'LLA-001',
                'colors' => ['Transparente', 'Blanco'],
                'materials' => ['Acrílico 3mm'],
                'sizes' =>['5x5cm', '6x6cm', '7x7cm'],
                'face_count' => 2,
                'print_colors_count' => 4,
                'print_colors' => ['Cian', 'Magenta', 'Amarillo', 'Negro'],
                'category_id' => $promocional->id,
                'subcategory_id' => $llaveros->id,
                'active' => true,
            ],
            [
                'name' => 'Llavero Metal Esmaltado',
                'slug' => 'llavero-metal-esmaltado',
                'description' => 'Llavero metálico con esmalte a fuego. Alta calidad y durabilidad para marcas premium.',
                'sku' => 'LLA-002',
                'colors' => ['Dorado', 'Plateado', 'Negro'],
                'materials' => ['Metal con esmalte'],
                'sizes' =>['3x3cm', '4x4cm', '5x5cm'],
                'face_count' => 2,
                'print_colors_count' => 5,
                'print_colors' => ['Rojo', 'Azul', 'Verde', 'Amarillo', 'Negro'],
                'category_id' => $promocional->id,
                'subcategory_id' => $llaveros->id,
                'active' => true,
            ],

            // Productos Tecnología - Fundas
            [
                'name' => 'Funda iPhone Silicona Premium',
                'slug' => 'funda-iphone-silicona-premium',
                'description' => 'Funda de silicona TPU de alta calidad con impresión UV resistente. Compatible con carga inalámbrica.',
                'sku' => 'FUN-001',
                'colors' => ['Transparente', 'Negro', 'Blanco'],
                'materials' => ['Silicona TPU premium'],
                'sizes' =>['iPhone 12', 'iPhone 13', 'iPhone 14', 'iPhone 15'],
                'face_count' => 1,
                'print_colors_count' => 4,
                'print_colors' => ['Cian', 'Magenta', 'Amarillo', 'Negro'],
                'category_id' => $tecnologia->id,
                'subcategory_id' => $fundas->id,
                'active' => true,
            ],
            [
                'name' => 'Power Bank Personalizable',
                'slug' => 'power-bank-personalizable',
                'description' => 'Batería externa de 10000mAh con superficie personalizable. Incluye cable USB-C y LED indicador.',
                'sku' => 'TEC-001',
                'colors' => ['Negro', 'Blanco', 'Azul'],
                'materials' => ['ABS + Polímero de litio'],
                'sizes' =>['10000mAh'],
                'face_count' => 2,
                'print_colors_count' => 4,
                'print_colors' => ['Cian', 'Magenta', 'Amarillo', 'Negro'],
                'category_id' => $tecnologia->id,
                'subcategory_id' => $fundas->id,
                'active' => true,
            ],

            // Productos Hogar - Tazas
            [
                'name' => 'Taza Cerámica Sublimación',
                'slug' => 'taza-ceramica-sublimacion',
                'description' => 'Taza de cerámica blanca premium con recubrimiento especial para sublimación. Colores vibrantes garantizados.',
                'sku' => 'TAZ-001',
                'colors' => ['Blanco', 'Negro', 'Rojo', 'Azul'],
                'materials' => ['Cerámica A1'],
                'sizes' =>['300ml', '350ml', '400ml'],
                'face_count' => 2,
                'print_colors_count' => 4,
                'print_colors' => ['Cian', 'Magenta', 'Amarillo', 'Negro'],
                'category_id' => $hogar->id,
                'subcategory_id' => $tazas->id,
                'active' => true,
            ],
            [
                'name' => 'Taza Térmica Personalizada',
                'slug' => 'taza-termica-personalizada',
                'description' => 'Taza térmica de acero inoxidable con doble pared. Mantiene la temperatura 6 horas. Ideal para oficinas.',
                'sku' => 'TAZ-002',
                'colors' => ['Negro', 'Blanco', 'Plateado', 'Azul'],
                'materials' => ['Acero inoxidable'],
                'sizes' =>['400ml', '500ml'],
                'face_count' => 2,
                'print_colors_count' => 1,
                'print_colors' => ['Grabado'],
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

    private function createAvailableColors()
    {
        $colors = [
            ['name' => 'Blanco', 'hex_code' => '#FFFFFF', 'sort_order' => 1],
            ['name' => 'Negro', 'hex_code' => '#000000', 'sort_order' => 2],
            ['name' => 'Rojo', 'hex_code' => '#FF0000', 'sort_order' => 3],
            ['name' => 'Azul', 'hex_code' => '#0000FF', 'sort_order' => 4],
            ['name' => 'Verde', 'hex_code' => '#00FF00', 'sort_order' => 5],
            ['name' => 'Amarillo', 'hex_code' => '#FFFF00', 'sort_order' => 6],
            ['name' => 'Naranja', 'hex_code' => '#FFA500', 'sort_order' => 7],
            ['name' => 'Rosa', 'hex_code' => '#FFC0CB', 'sort_order' => 8],
            ['name' => 'Morado', 'hex_code' => '#800080', 'sort_order' => 9],
            ['name' => 'Gris', 'hex_code' => '#808080', 'sort_order' => 10],
            ['name' => 'Azul Marino', 'hex_code' => '#000080', 'sort_order' => 11],
            ['name' => 'Plateado', 'hex_code' => '#C0C0C0', 'sort_order' => 12],
            ['name' => 'Dorado', 'hex_code' => '#FFD700', 'sort_order' => 13],
            ['name' => 'Transparente', 'hex_code' => '#FFFFFF', 'sort_order' => 14],
        ];

        foreach ($colors as $color) {
            AvailableColor::firstOrCreate(
                ['name' => $color['name']],
                [
                    'hex_code' => $color['hex_code'],
                    'sort_order' => $color['sort_order'],
                    'active' => true
                ]
            );
        }
    }
}