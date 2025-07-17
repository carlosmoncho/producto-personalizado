<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subcategory;
use App\Models\Category;

class SubcategorySeeder extends Seeder
{
    public function run()
    {
        $textil = Category::where('slug', 'textil')->first();
        $promocional = Category::where('slug', 'promocional')->first();
        $tecnologia = Category::where('slug', 'tecnologia')->first();
        $hogar = Category::where('slug', 'hogar')->first();

        $subcategories = [
            // Textil
            [
                'name' => 'Camisetas',
                'slug' => 'camisetas',
                'description' => 'Camisetas personalizables',
                'category_id' => $textil->id,
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'name' => 'Sudaderas',
                'slug' => 'sudaderas',
                'description' => 'Sudaderas personalizables',
                'category_id' => $textil->id,
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'name' => 'Gorras',
                'slug' => 'gorras',
                'description' => 'Gorras personalizables',
                'category_id' => $textil->id,
                'sort_order' => 3,
                'active' => true,
            ],
            
            // Promocional
            [
                'name' => 'Bolígrafos',
                'slug' => 'boligrafos',
                'description' => 'Bolígrafos promocionales',
                'category_id' => $promocional->id,
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'name' => 'Llaveros',
                'slug' => 'llaveros',
                'description' => 'Llaveros personalizables',
                'category_id' => $promocional->id,
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'name' => 'Libretas',
                'slug' => 'libretas',
                'description' => 'Libretas corporativas',
                'category_id' => $promocional->id,
                'sort_order' => 3,
                'active' => true,
            ],
            
            // Tecnología
            [
                'name' => 'Fundas móvil',
                'slug' => 'fundas-movil',
                'description' => 'Fundas para móviles personalizables',
                'category_id' => $tecnologia->id,
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'name' => 'Powerbanks',
                'slug' => 'powerbanks',
                'description' => 'Baterías externas personalizables',
                'category_id' => $tecnologia->id,
                'sort_order' => 2,
                'active' => true,
            ],
            
            // Hogar
            [
                'name' => 'Tazas',
                'slug' => 'tazas',
                'description' => 'Tazas personalizables',
                'category_id' => $hogar->id,
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'name' => 'Cojines',
                'slug' => 'cojines',
                'description' => 'Cojines personalizables',
                'category_id' => $hogar->id,
                'sort_order' => 2,
                'active' => true,
            ],
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create($subcategory);
        }
    }
}
