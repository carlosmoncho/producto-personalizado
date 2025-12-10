<?php

namespace Database\Seeders;

use App\Models\AttributeGroup;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;

class AttributeGroupSeeder extends Seeder
{
    public function run()
    {
        // Grupo de Colores
        $colorGroup = AttributeGroup::create([
            'name' => 'Colores del Producto',
            'slug' => 'colores-producto',
            'description' => 'Colores disponibles para personalización del producto',
            'type' => 'color',
            'sort_order' => 1,
            'is_required' => true,
            'allow_multiple' => false,
            'affects_price' => true,
            'affects_stock' => true,
            'show_in_filter' => true,
            'active' => true
        ]);

        // Añadir algunos colores al grupo
        $colors = [
            ['name' => 'Blanco', 'value' => 'BLANCO', 'hex_code' => '#FFFFFF', 'pantone_code' => 'White', 'price_modifier' => 0],
            ['name' => 'Negro', 'value' => 'NEGRO', 'hex_code' => '#000000', 'pantone_code' => 'Black', 'price_modifier' => 0],
            ['name' => 'Rojo', 'value' => 'ROJO', 'hex_code' => '#FF0000', 'pantone_code' => '186C', 'price_modifier' => 0.50],
            ['name' => 'Azul', 'value' => 'AZUL', 'hex_code' => '#0000FF', 'pantone_code' => '286C', 'price_modifier' => 0.50],
            ['name' => 'Verde', 'value' => 'VERDE', 'hex_code' => '#00FF00', 'pantone_code' => '361C', 'price_modifier' => 0.50],
            ['name' => 'Amarillo', 'value' => 'AMARILLO', 'hex_code' => '#FFFF00', 'pantone_code' => 'Yellow C', 'price_modifier' => 0.50],
            ['name' => 'Naranja', 'value' => 'NARANJA', 'hex_code' => '#FFA500', 'pantone_code' => '151C', 'price_modifier' => 0.75],
            ['name' => 'Rosa', 'value' => 'ROSA', 'hex_code' => '#FFC0CB', 'pantone_code' => '182C', 'price_modifier' => 0.75],
            ['name' => 'Morado', 'value' => 'MORADO', 'hex_code' => '#800080', 'pantone_code' => '2685C', 'price_modifier' => 0.75],
            ['name' => 'Gris', 'value' => 'GRIS', 'hex_code' => '#808080', 'pantone_code' => 'Cool Gray 9C', 'price_modifier' => 0.25],
        ];

        foreach ($colors as $index => $color) {
            ProductAttribute::create([
                'attribute_group_id' => $colorGroup->id,
                'type' => 'color',
                'name' => $color['name'],
                'value' => $color['value'],
                'slug' => strtolower($color['value']),
                'hex_code' => $color['hex_code'],
                'pantone_code' => $color['pantone_code'],
                'price_modifier' => $color['price_modifier'],
                'sort_order' => $index,
                'active' => true,
                'is_recommended' => in_array($color['value'], ['BLANCO', 'NEGRO'])
            ]);
        }

        // Grupo de Tamaños
        $sizeGroup = AttributeGroup::create([
            'name' => 'Tamaños',
            'slug' => 'tamanos',
            'description' => 'Tamaños disponibles del producto',
            'type' => 'size',
            'sort_order' => 2,
            'is_required' => true,
            'allow_multiple' => false,
            'affects_price' => true,
            'affects_stock' => true,
            'show_in_filter' => true,
            'active' => true
        ]);

        $sizes = [
            ['name' => 'Extra Pequeño (XS)', 'value' => 'XS', 'price_modifier' => -2.00],
            ['name' => 'Pequeño (S)', 'value' => 'S', 'price_modifier' => -1.00],
            ['name' => 'Mediano (M)', 'value' => 'M', 'price_modifier' => 0],
            ['name' => 'Grande (L)', 'value' => 'L', 'price_modifier' => 1.00],
            ['name' => 'Extra Grande (XL)', 'value' => 'XL', 'price_modifier' => 2.00],
            ['name' => 'Doble Extra Grande (XXL)', 'value' => 'XXL', 'price_modifier' => 3.00],
        ];

        foreach ($sizes as $index => $size) {
            ProductAttribute::create([
                'attribute_group_id' => $sizeGroup->id,
                'type' => 'size',
                'name' => $size['name'],
                'value' => $size['value'],
                'slug' => strtolower($size['value']),
                'price_modifier' => $size['price_modifier'],
                'sort_order' => $index,
                'active' => true,
                'is_recommended' => $size['value'] === 'M'
            ]);
        }

        // Grupo de Materiales
        $materialGroup = AttributeGroup::create([
            'name' => 'Materiales',
            'slug' => 'materiales',
            'description' => 'Materiales de fabricación disponibles',
            'type' => 'material',
            'sort_order' => 3,
            'is_required' => true,
            'allow_multiple' => false,
            'affects_price' => true,
            'affects_stock' => false,
            'show_in_filter' => true,
            'active' => true
        ]);

        $materials = [
            ['name' => 'Algodón 100%', 'value' => 'ALGODON_100', 'price_modifier' => 0, 'weight_modifier' => 0],
            ['name' => 'Poliéster', 'value' => 'POLIESTER', 'price_modifier' => -1.50, 'weight_modifier' => -0.05],
            ['name' => 'Algodón/Poliéster (50/50)', 'value' => 'ALGODON_POLIESTER', 'price_modifier' => -0.75, 'weight_modifier' => -0.02],
            ['name' => 'Algodón Orgánico', 'value' => 'ALGODON_ORGANICO', 'price_modifier' => 3.00, 'weight_modifier' => 0.05],
            ['name' => 'Bambú', 'value' => 'BAMBU', 'price_modifier' => 4.00, 'weight_modifier' => -0.10],
            ['name' => 'Lino', 'value' => 'LINO', 'price_modifier' => 5.00, 'weight_modifier' => 0.10],
        ];

        foreach ($materials as $index => $material) {
            ProductAttribute::create([
                'attribute_group_id' => $materialGroup->id,
                'type' => 'material',
                'name' => $material['name'],
                'value' => $material['value'],
                'slug' => strtolower($material['value']),
                'price_modifier' => $material['price_modifier'],
                'weight_modifier' => $material['weight_modifier'],
                'sort_order' => $index,
                'active' => true,
                'is_recommended' => in_array($material['value'], ['ALGODON_100', 'ALGODON_POLIESTER'])
            ]);
        }

        // Grupo de Tintas de Impresión (permite múltiples colores de tinta)
        $inkGroup = AttributeGroup::create([
            'name' => 'Tintas de Impresión',
            'slug' => 'tintas-impresion',
            'description' => 'Colores de tinta disponibles para la impresión (puedes elegir varios)',
            'type' => 'ink',
            'sort_order' => 4,
            'is_required' => true,
            'allow_multiple' => true, // PERMITE MÚLTIPLE
            'affects_price' => true,
            'affects_stock' => false,
            'show_in_filter' => false,
            'active' => true
        ]);

        $inks = [
            ['name' => 'Negro', 'value' => 'TINTA_NEGRO', 'hex_code' => '#000000', 'price_modifier' => 0],
            ['name' => 'Blanco', 'value' => 'TINTA_BLANCO', 'hex_code' => '#FFFFFF', 'price_modifier' => 0.50],
            ['name' => 'Dorado Metálico', 'value' => 'TINTA_DORADO', 'hex_code' => '#FFD700', 'price_modifier' => 2.00],
            ['name' => 'Plateado Metálico', 'value' => 'TINTA_PLATEADO', 'hex_code' => '#C0C0C0', 'price_modifier' => 2.00],
            ['name' => 'CMYK Full Color', 'value' => 'TINTA_CMYK', 'hex_code' => null, 'price_modifier' => 5.00],
        ];

        foreach ($inks as $index => $ink) {
            ProductAttribute::create([
                'attribute_group_id' => $inkGroup->id,
                'type' => 'ink',
                'name' => $ink['name'],
                'value' => $ink['value'],
                'slug' => strtolower($ink['value']),
                'hex_code' => $ink['hex_code'],
                'price_modifier' => $ink['price_modifier'],
                'sort_order' => $index,
                'active' => true,
                'metadata' => [
                    'is_metallic' => str_contains($ink['value'], 'METALICO'),
                    'opacity' => $ink['value'] === 'TINTA_BLANCO' ? 'opaca' : 'normal'
                ]
            ]);
        }

        // Grupo de Cantidades
        $quantityGroup = AttributeGroup::create([
            'name' => 'Cantidades',
            'slug' => 'cantidades',
            'description' => 'Cantidades de pedido disponibles',
            'type' => 'quantity',
            'sort_order' => 5,
            'is_required' => true,
            'allow_multiple' => false,
            'affects_price' => true,
            'affects_stock' => false,
            'show_in_filter' => false,
            'active' => true
        ]);

        $quantities = [
            ['name' => '100 unidades', 'value' => 'QTY_100', 'price_percentage' => 0],
            ['name' => '250 unidades', 'value' => 'QTY_250', 'price_percentage' => -5],
            ['name' => '500 unidades', 'value' => 'QTY_500', 'price_percentage' => -10],
            ['name' => '1,000 unidades', 'value' => 'QTY_1000', 'price_percentage' => -15],
            ['name' => '2,500 unidades', 'value' => 'QTY_2500', 'price_percentage' => -20],
            ['name' => '5,000 unidades', 'value' => 'QTY_5000', 'price_percentage' => -25],
        ];

        foreach ($quantities as $index => $quantity) {
            ProductAttribute::create([
                'attribute_group_id' => $quantityGroup->id,
                'type' => 'quantity',
                'name' => $quantity['name'],
                'value' => $quantity['value'],
                'slug' => strtolower($quantity['value']),
                'price_percentage' => $quantity['price_percentage'],
                'sort_order' => $index,
                'active' => true,
                'metadata' => [
                    'min_quantity' => intval(str_replace(['QTY_', ','], '', $quantity['value'])),
                    'packaging' => $index > 2 ? 'Cajas de 100 unidades' : 'Bolsas individuales'
                ]
            ]);
        }

        // Grupo de Acabados (permite múltiples opciones)
        $finishGroup = AttributeGroup::create([
            'name' => 'Acabados del Producto',
            'slug' => 'acabados-producto',
            'description' => 'Acabados especiales que se pueden aplicar (puedes elegir varios)',
            'type' => 'finish',
            'sort_order' => 6,
            'is_required' => false,
            'allow_multiple' => true, // PERMITE MÚLTIPLE
            'affects_price' => true,
            'affects_stock' => false,
            'show_in_filter' => true,
            'active' => true
        ]);

        $finishes = [
            ['name' => 'Laminado Mate', 'value' => 'LAMINADO_MATE', 'price_modifier' => 1.50],
            ['name' => 'Laminado Brillante', 'value' => 'LAMINADO_BRILLANTE', 'price_modifier' => 1.50],
            ['name' => 'Barniz UV', 'value' => 'BARNIZ_UV', 'price_modifier' => 2.00],
            ['name' => 'Relieve/Realce', 'value' => 'RELIEVE', 'price_modifier' => 3.00],
            ['name' => 'Troquelado', 'value' => 'TROQUELADO', 'price_modifier' => 2.50],
            ['name' => 'Cosido', 'value' => 'COSIDO', 'price_modifier' => 1.75],
        ];

        foreach ($finishes as $index => $finish) {
            ProductAttribute::create([
                'attribute_group_id' => $finishGroup->id,
                'type' => 'finish',
                'name' => $finish['name'],
                'value' => $finish['value'],
                'slug' => strtolower($finish['value']),
                'price_modifier' => $finish['price_modifier'],
                'sort_order' => $index,
                'active' => true,
                'metadata' => [
                    'production_time_days' => $index > 3 ? 2 : 1,
                    'requires_approval' => $finish['value'] === 'TROQUELADO'
                ]
            ]);
        }

        $this->command->info('Grupos de atributos y atributos de ejemplo creados exitosamente.');
    }
}