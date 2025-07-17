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
            CustomFieldSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
