<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraph(3),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-????')),
            'colors' => ['Blanco', 'Negro'],
            'materials' => ['Algodón'],
            'sizes' => ['S', 'M', 'L'],
            'face_count' => fake()->numberBetween(1, 4),
            'print_colors_count' => fake()->numberBetween(1, 6),
            'print_colors' => ['Negro'],
            'images' => [],
            'model_3d_file' => null,
            'active' => true,
            'category_id' => Category::factory(),
            'subcategory_id' => Subcategory::factory(),
            // Configurator fields
            'has_configurator' => false,
            'available_colors' => [],
            'available_materials' => [],
            'available_sizes' => [],
            'available_inks' => [],
            'available_quantities' => [],
            'available_systems' => [],
            'configurator_rules' => [],
            'base_pricing' => [],
            'max_print_colors' => 1,
            'allow_file_upload' => false,
            'file_upload_types' => ['jpg', 'png', 'pdf'],
            'configurator_base_price' => null,
            'price_modifiers' => [],
            'configurator_description' => null,
            'configurator_settings' => [],
        ];
    }

    /**
     * Indicate that the product has a configurator.
     */
    public function withConfigurator(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_configurator' => true,
            'configurator_base_price' => fake()->randomFloat(2, 5, 50),
            'max_print_colors' => fake()->numberBetween(1, 6),
            'allow_file_upload' => true,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
