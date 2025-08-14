<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            SubcategorySeeder::class,
            AvailableColorSeeder::class,
            AvailablePrintColorSeeder::class,
            AvailableSizeSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class, // Crear clientes antes de pedidos
            OrderSeeder::class,
        ]);
    }
}