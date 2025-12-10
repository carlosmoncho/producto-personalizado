<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\AttributeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAttributePriceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_base_price_when_no_modifiers_exist()
    {
        $group = AttributeGroup::factory()->color()->create();
        $attribute = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $group->id,
        ]);

        $basePrice = 100.00;
        $result = $attribute->calculatePrice($basePrice);

        $this->assertEquals(100.00, $result);
    }

    /** @test */
    public function it_applies_custom_price_modifier_from_pivot()
    {
        $product = Product::factory()->withConfigurator()->create();
        $group = AttributeGroup::factory()->color()->create();
        $attribute = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $group->id,
        ]);

        // Crear relación en product_attribute_values con modificador
        \DB::table('product_attribute_values')->insert([
            'product_id' => $product->id,
            'attribute_group_id' => $group->id,
            'product_attribute_id' => $attribute->id,
            'custom_price_modifier' => 15.00,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $basePrice = 100.00;
        $result = $attribute->calculatePrice($basePrice, 1, $product->id);

        $this->assertEquals(115.00, $result); // 100 + 15
    }

    /** @test */
    public function it_applies_custom_price_percentage_from_pivot()
    {
        $product = Product::factory()->withConfigurator()->create();
        $group = AttributeGroup::factory()->color()->create();
        $attribute = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $group->id,
        ]);

        // Crear relación en product_attribute_values con porcentaje
        \DB::table('product_attribute_values')->insert([
            'product_id' => $product->id,
            'attribute_group_id' => $group->id,
            'product_attribute_id' => $attribute->id,
            'custom_price_percentage' => 20.00, // 20%
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $basePrice = 100.00;
        $result = $attribute->calculatePrice($basePrice, 1, $product->id);

        $this->assertEquals(120.00, $result); // 100 * 1.20
    }

    /** @test */
    public function it_applies_both_modifier_and_percentage()
    {
        $product = Product::factory()->withConfigurator()->create();
        $group = AttributeGroup::factory()->color()->create();
        $attribute = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $group->id,
        ]);

        // Crear relación con ambos modificadores
        \DB::table('product_attribute_values')->insert([
            'product_id' => $product->id,
            'attribute_group_id' => $group->id,
            'product_attribute_id' => $attribute->id,
            'custom_price_modifier' => 10.00,    // Primero suma 10
            'custom_price_percentage' => 20.00,  // Luego aplica 20%
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $basePrice = 100.00;
        $result = $attribute->calculatePrice($basePrice, 1, $product->id);

        // (100 + 10) * 1.20 = 132
        $this->assertEquals(132.00, $result);
    }

    /** @test */
    public function it_ignores_unavailable_pivot_data()
    {
        $product = Product::factory()->withConfigurator()->create();
        $group = AttributeGroup::factory()->color()->create();
        $attribute = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $group->id,
        ]);

        // Crear relación con is_available = false
        \DB::table('product_attribute_values')->insert([
            'product_id' => $product->id,
            'attribute_group_id' => $group->id,
            'product_attribute_id' => $attribute->id,
            'custom_price_modifier' => 50.00,
            'is_available' => false, // NO disponible
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $basePrice = 100.00;
        $result = $attribute->calculatePrice($basePrice, 1, $product->id);

        $this->assertEquals(100.00, $result); // Sin cambios
    }
}
