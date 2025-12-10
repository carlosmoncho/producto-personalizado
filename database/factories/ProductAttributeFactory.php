<?php

namespace Database\Factories;

use App\Models\AttributeGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductAttribute>
 */
class ProductAttributeFactory extends Factory
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

        $values = [
            'color' => ['Blanco', 'Negro', 'Rojo', 'Azul', 'Verde'],
            'material' => ['Algodón', 'Poliéster', 'Mezcla'],
            'size' => ['XS', 'S', 'M', 'L', 'XL'],
            'ink' => ['Negro', 'Blanco', 'Pantone', 'CMYK'],
            'quantity' => ['50', '100', '250', '500'],
            'system' => ['Serigrafía', 'DTG', 'Bordado'],
        ];

        $value = fake()->randomElement($values[$type]);
        $slug = \Illuminate\Support\Str::slug($value . '-' . fake()->unique()->numberBetween(1, 9999));

        return [
            'attribute_group_id' => AttributeGroup::factory()->state(['type' => $type]),
            'type' => $type,
            'name' => $value,
            'value' => $value,
            'slug' => $slug,
            'hex_code' => $type === 'color' || $type === 'ink' ? fake()->hexColor() : null,
            'metadata' => null,
            'sort_order' => fake()->numberBetween(0, 100),
            'active' => true,
            'is_recommended' => fake()->boolean(20), // 20% are recommended
        ];
    }

    /**
     * Create a color attribute
     */
    public function color(string $name = null, string $hexCode = null): static
    {
        return $this->state(function (array $attributes) use ($name, $hexCode) {
            // Generate unique color name to avoid constraint violation
            // Use uniqid() with more_entropy=true + random number
            $uniqueId = uniqid('', true) . mt_rand(1000, 9999);
            $colorName = $name ?? 'Color ' . fake()->colorName() . ' ' . $uniqueId;

            return [
                'type' => 'color',
                'name' => $colorName,
                'value' => $colorName,
                'slug' => \Illuminate\Support\Str::slug($colorName),
                'hex_code' => $hexCode ?? fake()->hexColor(),
            ];
        });
    }

    /**
     * Create a material attribute
     */
    public function material(string $name = null): static
    {
        return $this->state(function (array $attributes) use ($name) {
            // Generate unique material name to avoid constraint violation
            $uniqueId = uniqid('', true) . mt_rand(1000, 9999);
            $materialName = $name ?? fake()->randomElement(['Algodón', 'Poliéster', 'Mezcla', 'Lino']) . ' ' . $uniqueId;

            return [
                'type' => 'material',
                'name' => $materialName,
                'value' => $materialName,
                'slug' => \Illuminate\Support\Str::slug($materialName),
                'hex_code' => null,
            ];
        });
    }

    /**
     * Create a size attribute
     */
    public function size(string $name = null): static
    {
        return $this->state(function (array $attributes) use ($name) {
            // Generate unique size name to avoid constraint violation
            $uniqueId = uniqid('', true) . mt_rand(1000, 9999);
            $sizeName = $name ?? fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']) . ' ' . $uniqueId;

            return [
                'type' => 'size',
                'name' => $sizeName,
                'value' => $sizeName,
                'slug' => \Illuminate\Support\Str::slug($sizeName),
                'hex_code' => null,
            ];
        });
    }

    /**
     * Indicate that the attribute is recommended.
     */
    public function recommended(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recommended' => true,
        ]);
    }

    /**
     * Indicate that the attribute is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
