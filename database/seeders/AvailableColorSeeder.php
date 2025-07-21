<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AvailableColor;

class AvailableColorSeeder extends Seeder
{
    public function run()
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
            ['name' => 'Marrón', 'hex_code' => '#964B00', 'sort_order' => 15],
            ['name' => 'Turquesa', 'hex_code' => '#40E0D0', 'sort_order' => 16],
            ['name' => 'Coral', 'hex_code' => '#FF7F50', 'sort_order' => 17],
            ['name' => 'Beige', 'hex_code' => '#F5F5DC', 'sort_order' => 18],
            ['name' => 'Verde Oscuro', 'hex_code' => '#006400', 'sort_order' => 19],
            ['name' => 'Rojo Vino', 'hex_code' => '#722F37', 'sort_order' => 20],
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