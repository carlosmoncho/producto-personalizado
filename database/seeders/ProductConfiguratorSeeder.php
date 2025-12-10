<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use App\Models\AttributeDependency;
use Illuminate\Database\Seeder;

class ProductConfiguratorSeeder extends Seeder
{
    public function run()
    {
        $this->seedProductAttributes();
        $this->seedAttributeDependencies();
    }

    private function seedProductAttributes()
    {
        // COLORES - Atributo maestro
        $colors = [
            [
                'type' => 'color',
                'name' => 'Blanco',
                'value' => 'BLANCO',
                'hex_code' => '#FFFFFF',
                'price_modifier' => 0,
                'metadata' => [
                    'luminosity' => 1.0,
                    'certifications' => ['ISO9001'],
                    'recommended_materials' => ['CELULOSA'],
                ],
                'sort_order' => 1,
            ],
            [
                'type' => 'color',
                'name' => 'Negro',
                'value' => 'NEGRO',
                'hex_code' => '#000000',
                'price_modifier' => 0.005,
                'metadata' => [
                    'luminosity' => 0.0,
                    'certifications' => ['ISO9001'],
                    'recommended_materials' => ['CELULOSA'],
                ],
                'sort_order' => 2,
            ],
            [
                'type' => 'color',
                'name' => 'Natural',
                'value' => 'NATURAL',
                'hex_code' => '#F5F5DC',
                'price_modifier' => 0.002,
                'metadata' => [
                    'luminosity' => 0.8,
                    'certifications' => ['ECO', 'BIODEGRADABLE', 'KRAFT'],
                    'recommended_materials' => ['KRAFT_VERJURADO'],
                ],
                'sort_order' => 3,
            ],
            [
                'type' => 'color',
                'name' => 'Chocolate',
                'value' => 'CHOCOLATE',
                'hex_code' => '#D2691E',
                'price_modifier' => 0.008,
                'metadata' => [
                    'luminosity' => 0.3,
                    'certifications' => ['ISO9001'],
                    'recommended_materials' => ['CELULOSA'],
                ],
                'sort_order' => 4,
            ],
        ];

        foreach ($colors as $color) {
            ProductAttribute::create($color);
        }

        // MATERIALES
        $materials = [
            [
                'type' => 'material',
                'name' => 'Celulosa',
                'value' => 'CELULOSA',
                'price_modifier' => 0,
                'metadata' => [
                    'quality' => 'premium',
                    'thickness' => 'standard',
                    'certifications' => ['ISO9001'],
                ],
                'sort_order' => 1,
            ],
            [
                'type' => 'material',
                'name' => 'Kraft Verjurado',
                'value' => 'KRAFT_VERJURADO',
                'price_modifier' => 0.003,
                'metadata' => [
                    'quality' => 'eco',
                    'texture' => 'rough',
                    'certifications' => ['ECO', 'BIODEGRADABLE'],
                ],
                'sort_order' => 2,
            ],
        ];

        foreach ($materials as $material) {
            ProductAttribute::create($material);
        }

        // TAMAÑOS
        $sizes = [
            [
                'type' => 'size',
                'name' => '11x25 CM',
                'value' => '11x25',
                'price_modifier' => 0,
                'metadata' => ['width' => 11, 'height' => 25, 'unit' => 'cm'],
                'sort_order' => 1,
            ],
            [
                'type' => 'size', 
                'name' => '20x20 CM',
                'value' => '20x20',
                'price_modifier' => 0.010,
                'metadata' => ['width' => 20, 'height' => 20, 'unit' => 'cm'],
                'sort_order' => 2,
            ],
            [
                'type' => 'size',
                'name' => '33x33 CM',
                'value' => '33x33',
                'price_modifier' => 0.025,
                'metadata' => ['width' => 33, 'height' => 33, 'unit' => 'cm'],
                'sort_order' => 3,
            ],
        ];

        foreach ($sizes as $size) {
            ProductAttribute::create($size);
        }

        // TINTAS DE IMPRESIÓN
        $inks = [
            // Tintas claras/metálicas (para fondos oscuros)
            [
                'type' => 'ink',
                'name' => 'Oro',
                'value' => 'ORO',
                'hex_code' => '#FFD700',
                'price_modifier' => 0.015,
                'metadata' => [
                    'is_metallic' => true,
                    'luminosity' => 0.9,
                    'opacity' => 'high',
                    'recommended_for_dark_backgrounds' => true,
                ],
                'is_recommended' => true,
                'sort_order' => 1,
            ],
            [
                'type' => 'ink',
                'name' => 'Plata',
                'value' => 'PLATA',
                'hex_code' => '#C0C0C0',
                'price_modifier' => 0.015,
                'metadata' => [
                    'is_metallic' => true,
                    'luminosity' => 0.85,
                    'opacity' => 'high',
                    'recommended_for_dark_backgrounds' => true,
                ],
                'is_recommended' => true,
                'sort_order' => 2,
            ],
            [
                'type' => 'ink',
                'name' => 'Blanco',
                'value' => 'BLANCO_TINTA',
                'hex_code' => '#FFFFFF',
                'price_modifier' => 0.008,
                'metadata' => [
                    'is_metallic' => false,
                    'luminosity' => 1.0,
                    'opacity' => 'high',
                    'recommended_for_dark_backgrounds' => true,
                ],
                'sort_order' => 3,
            ],
            [
                'type' => 'ink',
                'name' => 'Amarillo',
                'value' => 'AMARILLO',
                'hex_code' => '#FFFF00',
                'price_modifier' => 0.005,
                'metadata' => [
                    'is_metallic' => false,
                    'luminosity' => 0.9,
                    'opacity' => 'medium',
                    'recommended_for_dark_backgrounds' => true,
                ],
                'sort_order' => 4,
            ],
            
            // Tintas oscuras (para fondos claros)
            [
                'type' => 'ink',
                'name' => 'Azul Marino',
                'value' => 'AZUL_MARINO',
                'hex_code' => '#000080',
                'price_modifier' => 0.005,
                'metadata' => [
                    'is_metallic' => false,
                    'luminosity' => 0.2,
                    'opacity' => 'high',
                    'recommended_for_light_backgrounds' => true,
                ],
                'is_recommended' => true,
                'sort_order' => 5,
            ],
            [
                'type' => 'ink',
                'name' => 'Rojo',
                'value' => 'ROJO',
                'hex_code' => '#FF0000',
                'price_modifier' => 0.005,
                'metadata' => [
                    'is_metallic' => false,
                    'luminosity' => 0.4,
                    'opacity' => 'high',
                    'recommended_for_light_backgrounds' => true,
                ],
                'sort_order' => 6,
            ],
            [
                'type' => 'ink',
                'name' => 'Verde Bosque',
                'value' => 'VERDE_BOSQUE',
                'hex_code' => '#228B22',
                'price_modifier' => 0.005,
                'metadata' => [
                    'is_metallic' => false,
                    'luminosity' => 0.3,
                    'opacity' => 'high',
                    'recommended_for_light_backgrounds' => true,
                ],
                'sort_order' => 7,
            ],
            [
                'type' => 'ink',
                'name' => 'Negro Intenso',
                'value' => 'NEGRO_INTENSO',
                'hex_code' => '#000000',
                'price_modifier' => 0.003,
                'metadata' => [
                    'is_metallic' => false,
                    'luminosity' => 0.0,
                    'opacity' => 'maximum',
                    'recommended_for_light_backgrounds' => true,
                ],
                'sort_order' => 8,
            ],
        ];

        foreach ($inks as $ink) {
            ProductAttribute::create($ink);
        }

        // CANTIDADES DISPONIBLES
        $quantities = [
            [
                'type' => 'quantity',
                'name' => '16,200 unidades',
                'value' => '16200',
                'price_modifier' => 0, // Sin descuento
                'metadata' => [
                    'packaging' => '54 CARTON de 300 unid.',
                    'unit_price' => 0.133,
                    'is_minimum' => true,
                ],
                'sort_order' => 1,
            ],
            [
                'type' => 'quantity',
                'name' => '32,100 unidades',
                'value' => '32100',
                'price_modifier' => -0.006, // 4.5% descuento
                'price_percentage' => -4.5,
                'metadata' => [
                    'packaging' => '107 CARTON de 300 unid.',
                    'unit_price' => 0.127,
                    'discount_percentage' => 4.5,
                ],
                'is_recommended' => true,
                'sort_order' => 2,
            ],
            [
                'type' => 'quantity',
                'name' => '64,200 unidades',
                'value' => '64200',
                'price_modifier' => -0.012, // 9% descuento
                'price_percentage' => -9.0,
                'metadata' => [
                    'packaging' => '214 CARTON de 300 unid.',
                    'unit_price' => 0.121,
                    'discount_percentage' => 9.0,
                    'best_value' => true,
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($quantities as $quantity) {
            ProductAttribute::create($quantity);
        }
    }

    private function seedAttributeDependencies()
    {
        // REGLA 1: COLOR BLANCO + CELULOSA permite todas las tintas
        $blancoId = ProductAttribute::where('value', 'BLANCO')->first()->id;
        $celulosaId = ProductAttribute::where('value', 'CELULOSA')->first()->id;
        
        // Blanco permite celulosa
        AttributeDependency::create([
            'parent_attribute_id' => $blancoId,
            'dependent_attribute_id' => $celulosaId,
            'condition_type' => 'allows',
            'auto_select' => false,
            'priority' => 1,
        ]);

        // REGLA 2: COLOR NEGRO + CELULOSA solo permite tintas claras/metálicas
        $negroId = ProductAttribute::where('value', 'NEGRO')->first()->id;
        $tintasClaras = ProductAttribute::where('type', 'ink')
            ->whereIn('value', ['ORO', 'PLATA', 'BLANCO_TINTA', 'AMARILLO'])
            ->pluck('id');

        AttributeDependency::create([
            'parent_attribute_id' => $negroId,
            'dependent_attribute_id' => $celulosaId,
            'condition_type' => 'allows',
            'auto_select' => false,
            'priority' => 1,
        ]);

        // Negro bloquea tintas oscuras
        $tintasOscuras = ProductAttribute::where('type', 'ink')
            ->whereIn('value', ['AZUL_MARINO', 'ROJO', 'VERDE_BOSQUE', 'NEGRO_INTENSO'])
            ->pluck('id');

        foreach ($tintasOscuras as $tintaId) {
            AttributeDependency::create([
                'parent_attribute_id' => $negroId,
                'dependent_attribute_id' => $tintaId,
                'condition_type' => 'blocks',
                'priority' => 2,
            ]);
        }

        // REGLA 3: COLOR NATURAL solo permite KRAFT VERJURADO
        $naturalId = ProductAttribute::where('value', 'NATURAL')->first()->id;
        $kraftId = ProductAttribute::where('value', 'KRAFT_VERJURADO')->first()->id;

        AttributeDependency::create([
            'parent_attribute_id' => $naturalId,
            'dependent_attribute_id' => $kraftId,
            'condition_type' => 'allows',
            'auto_select' => true, // Auto-seleccionar si es la única opción
            'priority' => 1,
        ]);

        // Natural bloquea celulosa
        AttributeDependency::create([
            'parent_attribute_id' => $naturalId,
            'dependent_attribute_id' => $celulosaId,
            'condition_type' => 'blocks',
            'priority' => 1,
        ]);

        // REGLA 4: COLOR CHOCOLATE permite celulosa y solo tintas claras
        $chocolateId = ProductAttribute::where('value', 'CHOCOLATE')->first()->id;

        AttributeDependency::create([
            'parent_attribute_id' => $chocolateId,
            'dependent_attribute_id' => $celulosaId,
            'condition_type' => 'allows',
            'auto_select' => false,
            'priority' => 1,
        ]);

        // Chocolate bloquea tintas oscuras (permite solo claras/metálicas)
        foreach ($tintasOscuras as $tintaId) {
            AttributeDependency::create([
                'parent_attribute_id' => $chocolateId,
                'dependent_attribute_id' => $tintaId,
                'condition_type' => 'blocks',
                'priority' => 2,
            ]);
        }

        // REGLAS ESPECIALES DE PRECIOS
        
        // Combinación Natural + Kraft tiene impacto especial en precio
        AttributeDependency::create([
            'parent_attribute_id' => $naturalId,
            'dependent_attribute_id' => $kraftId,
            'condition_type' => 'sets_price',
            'price_impact' => 0.005, // €0.005 adicionales
            'priority' => 5,
        ]);

        // Tintas metálicas en fondos oscuros tienen mayor impacto
        $fondosOscuros = [$negroId, $chocolateId];
        $tintasMetalicas = ProductAttribute::where('type', 'ink')
            ->whereRaw("JSON_EXTRACT(metadata, '$.is_metallic') = true")
            ->pluck('id');

        foreach ($fondosOscuros as $fondoId) {
            foreach ($tintasMetalicas as $tintaId) {
                AttributeDependency::create([
                    'parent_attribute_id' => $fondoId,
                    'dependent_attribute_id' => $tintaId,
                    'condition_type' => 'sets_price',
                    'price_impact' => 0.003, // €0.003 adicionales por contraste
                    'priority' => 6,
                ]);
            }
        }

        // REGLAS DE AUTO-SELECCIÓN
        
        // Si Kraft es la única opción disponible, auto-seleccionarla
        // (ya configurado arriba con auto_select = true)

        // REGLAS DE RESETEO
        
        // Al cambiar color, resetear material y todo lo posterior
        $allColors = ProductAttribute::where('type', 'color')->pluck('id');
        $allMaterials = ProductAttribute::where('type', 'material')->pluck('id');

        foreach ($allColors as $colorId) {
            foreach ($allMaterials as $materialId) {
                // Configurar que al cambiar color, se reseteen materiales incompatibles
                $existing = AttributeDependency::where('parent_attribute_id', $colorId)
                    ->where('dependent_attribute_id', $materialId)
                    ->first();
                
                if ($existing) {
                    $existing->update(['reset_dependents' => true]);
                }
            }
        }
    }
}