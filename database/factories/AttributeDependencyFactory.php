<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeDependency>
 */
class AttributeDependencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'parent_attribute_id' => ProductAttribute::factory(),
            'dependent_attribute_id' => ProductAttribute::factory(),
            'condition_type' => fake()->randomElement(['allows', 'blocks', 'requires', 'sets_price']),
            'conditions' => null,
            'price_impact' => fake()->randomFloat(2, 0, 50),
            'priority' => fake()->numberBetween(0, 100),
            'auto_select' => fake()->boolean(30),
            'reset_dependents' => true,
            'active' => true,
        ];
    }

    /**
     * Create an 'allows' dependency
     */
    public function allows(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_type' => 'allows',
        ]);
    }

    /**
     * Create a 'blocks' dependency
     */
    public function blocks(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_type' => 'blocks',
        ]);
    }

    /**
     * Create a 'requires' dependency
     */
    public function requires(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_type' => 'requires',
        ]);
    }
}
