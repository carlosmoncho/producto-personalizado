<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceRule>
 */
class PriceRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ruleTypes = ['combination', 'volume', 'attribute_specific', 'conditional'];
        $actionTypes = ['add_fixed', 'add_percentage', 'multiply', 'set_fixed', 'set_percentage'];

        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(10),
            'rule_type' => fake()->randomElement($ruleTypes),
            'conditions' => ['quantity_min' => 100],
            'action_type' => fake()->randomElement($actionTypes),
            'action_value' => fake()->randomFloat(2, 1, 50),
            'priority' => fake()->numberBetween(0, 100),
            'product_id' => null,
            'category_id' => null,
            'quantity_min' => null,
            'quantity_max' => null,
            'valid_from' => null,
            'valid_until' => null,
            'active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Create a volume-based price rule
     */
    public function volume(int $minQty = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'volume',
            'quantity_min' => $minQty,
            'action_type' => 'add_percentage',
            'action_value' => -10, // 10% discount
        ]);
    }

    /**
     * Create a temporal price rule
     */
    public function temporal(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => now(),
            'valid_until' => now()->addDays(30),
        ]);
    }
}
