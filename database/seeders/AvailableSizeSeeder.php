<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AvailableSize;

class AvailableSizeSeeder extends Seeder
{
    public function run()
    {
        $sizes = [
            ['name' => 'Extra Pequeño', 'code' => 'XS', 'description' => 'Talla extra pequeña', 'sort_order' => 1],
            ['name' => 'Pequeño', 'code' => 'S', 'description' => 'Talla pequeña', 'sort_order' => 2],
            ['name' => 'Mediano', 'code' => 'M', 'description' => 'Talla mediana', 'sort_order' => 3],
            ['name' => 'Grande', 'code' => 'L', 'description' => 'Talla grande', 'sort_order' => 4],
            ['name' => 'Extra Grande', 'code' => 'XL', 'description' => 'Talla extra grande', 'sort_order' => 5],
            ['name' => 'Doble Extra Grande', 'code' => 'XXL', 'description' => 'Talla doble extra grande', 'sort_order' => 6],
            ['name' => 'Triple Extra Grande', 'code' => 'XXXL', 'description' => 'Talla triple extra grande', 'sort_order' => 7],
            ['name' => 'Único', 'code' => 'UNICO', 'description' => 'Tamaño único', 'sort_order' => 8],
            ['name' => '5x5 cm', 'code' => null, 'description' => 'Tamaño 5x5 centímetros', 'sort_order' => 9],
            ['name' => '6x6 cm', 'code' => null, 'description' => 'Tamaño 6x6 centímetros', 'sort_order' => 10],
            ['name' => '7x7 cm', 'code' => null, 'description' => 'Tamaño 7x7 centímetros', 'sort_order' => 11],
            ['name' => '10x10 cm', 'code' => null, 'description' => 'Tamaño 10x10 centímetros', 'sort_order' => 12],
            ['name' => '15x15 cm', 'code' => null, 'description' => 'Tamaño 15x15 centímetros', 'sort_order' => 13],
            ['name' => '20x20 cm', 'code' => null, 'description' => 'Tamaño 20x20 centímetros', 'sort_order' => 14],
            ['name' => 'A4', 'code' => 'A4', 'description' => 'Tamaño A4 (21x29.7 cm)', 'sort_order' => 15],
            ['name' => 'A5', 'code' => 'A5', 'description' => 'Tamaño A5 (14.8x21 cm)', 'sort_order' => 16],
            ['name' => 'A6', 'code' => 'A6', 'description' => 'Tamaño A6 (10.5x14.8 cm)', 'sort_order' => 17],
            ['name' => '300ml', 'code' => null, 'description' => 'Capacidad 300 mililitros', 'sort_order' => 18],
            ['name' => '350ml', 'code' => null, 'description' => 'Capacidad 350 mililitros', 'sort_order' => 19],
            ['name' => '400ml', 'code' => null, 'description' => 'Capacidad 400 mililitros', 'sort_order' => 20],
            ['name' => '500ml', 'code' => null, 'description' => 'Capacidad 500 mililitros', 'sort_order' => 21],
            ['name' => '1L', 'code' => null, 'description' => 'Capacidad 1 litro', 'sort_order' => 22],
        ];

        foreach ($sizes as $size) {
            AvailableSize::firstOrCreate(
                ['name' => $size['name']],
                [
                    'code' => $size['code'],
                    'description' => $size['description'],
                    'sort_order' => $size['sort_order'],
                    'active' => true
                ]
            );
        }
    }
}