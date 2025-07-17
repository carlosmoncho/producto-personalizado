<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Textil',
                'slug' => 'textil',
                'description' => 'Productos textiles personalizables',
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'name' => 'Promocional',
                'slug' => 'promocional',
                'description' => 'Artículos promocionales y corporativos',
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'name' => 'Tecnología',
                'slug' => 'tecnologia',
                'description' => 'Productos tecnológicos personalizables',
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'name' => 'Hogar',
                'slug' => 'hogar',
                'description' => 'Artículos para el hogar personalizables',
                'sort_order' => 4,
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
