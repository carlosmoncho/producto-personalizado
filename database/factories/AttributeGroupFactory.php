<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeGroup>
 */
class AttributeGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['color', 'material', 'size', 'ink', 'quantity', 'system'];
        $type = fake()->randomElement($types);

        $typeNames = [
            'color' => 'Colores',
            'material' => 'Materiales',
            'size' => 'Tamaños',
            'ink' => 'Tintas',
            'quantity' => 'Cantidades',
            'system' => 'Sistemas de Impresión',
        ];

        $name = $typeNames[$type];
        $slug = \Illuminate\Support\Str::slug($name . '-' . fake()->unique()->numberBetween(1, 9999));

        return [
            'type' => $type,
            'name' => $name,
            'slug' => $slug,
            'description' => 'Grupo de ' . strtolower($name),
            'is_required' => fake()->boolean(70), // 70% required
            'allow_multiple' => fake()->boolean(30), // 30% allow multiple
            'sort_order' => fake()->numberBetween(0, 100),
            'affects_price' => fake()->boolean(50),
            'affects_stock' => fake()->boolean(30),
            'show_in_filter' => true,
            'active' => true,
        ];
    }

    /**
     * Create color group
     */
    public function color(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'color',
            'name' => 'Colores',
            'slug' => 'colores-' . fake()->unique()->numberBetween(1, 9999),
            'description' => 'Colores disponibles para el producto',
        ]);
    }

    /**
     * Create material group
     */
    public function material(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'material',
            'name' => 'Materiales',
            'slug' => 'materiales-' . fake()->unique()->numberBetween(1, 9999),
            'description' => 'Materiales disponibles para el producto',
        ]);
    }

    /**
     * Create size group
     */
    public function size(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'size',
            'name' => 'Tamaños',
            'slug' => 'tamanos-' . fake()->unique()->numberBetween(1, 9999),
            'description' => 'Tamaños disponibles para el producto',
        ]);
    }

    /**
     * Indicate that the group is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
