<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AvailablePrintColor;

class AvailablePrintColorSeeder extends Seeder
{
    public function run()
    {
        $colors = [
            ['name' => 'Negro', 'hex_code' => '#000000', 'sort_order' => 1],
            ['name' => 'Blanco', 'hex_code' => '#FFFFFF', 'sort_order' => 2],
            ['name' => 'Rojo', 'hex_code' => '#FF0000', 'sort_order' => 3],
            ['name' => 'Azul', 'hex_code' => '#0000FF', 'sort_order' => 4],
            ['name' => 'Verde', 'hex_code' => '#00FF00', 'sort_order' => 5],
            ['name' => 'Amarillo', 'hex_code' => '#FFFF00', 'sort_order' => 6],
            ['name' => 'Cian', 'hex_code' => '#00FFFF', 'sort_order' => 7],
            ['name' => 'Magenta', 'hex_code' => '#FF00FF', 'sort_order' => 8],
            ['name' => 'Naranja', 'hex_code' => '#FFA500', 'sort_order' => 9],
            ['name' => 'Gris', 'hex_code' => '#808080', 'sort_order' => 10],
            ['name' => 'Dorado', 'hex_code' => '#FFD700', 'sort_order' => 11],
            ['name' => 'Plateado', 'hex_code' => '#C0C0C0', 'sort_order' => 12],
            ['name' => 'Pantone 032 C', 'hex_code' => '#EF3340', 'sort_order' => 13],
            ['name' => 'Pantone 286 C', 'hex_code' => '#0033A0', 'sort_order' => 14],
            ['name' => 'Pantone 354 C', 'hex_code' => '#00B140', 'sort_order' => 15],
        ];

        foreach ($colors as $color) {
            AvailablePrintColor::firstOrCreate(
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