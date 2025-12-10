<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductConfiguration>
 */
class ProductConfigurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => fake()->uuid(),
            'user_id' => null,
            'product_id' => Product::factory(),
            'attributes_base' => [
                'color' => 'Blanco',
                'material' => 'Algodón',
                'size' => 'M',
            ],
            'personalization' => [
                'print_system' => 'Serigrafía',
                'print_colors' => 1,
                'inks' => ['Negro'],
            ],
            'files' => null,
            'calculated' => [
                'base_price' => 15.50,
                'total_price' => 18.75,
            ],
            'status' => 'draft',
            'is_valid' => false,
            'validation_errors' => null,
            'expires_at' => now()->addHours(24),
        ];
    }

    /**
     * Indicate that the configuration is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'is_valid' => true,
            'validation_errors' => null,
        ]);
    }

    /**
     * Indicate that the configuration belongs to a user.
     */
    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }
}
