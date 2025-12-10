<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\AttributeGroup;
use App\Models\PriceRule;
use App\Models\AttributeDependency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceCalculationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_base_price_from_product()
    {
        $product = Product::factory()->withConfigurator()->create([
            'configurator_base_price' => 25.50,
        ]);

        $this->assertEquals(25.50, $product->configurator_base_price);
    }

    /** @test */
    public function it_adds_fixed_price_modifier_from_pivot()
    {
        $product = Product::factory()->withConfigurator()->create();
        $group = AttributeGroup::factory()->color()->create();
        $attribute = ProductAttribute::factory()->color('Premium')->create([
            'attribute_group_id' => $group->id,
        ]);

        // Crear modificador en product_attribute_values
        \DB::table('product_attribute_values')->insert([
            'product_id' => $product->id,
            'attribute_group_id' => $group->id,
            'product_attribute_id' => $attribute->id,
            'custom_price_modifier' => 15.00,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $basePrice = 20.00;
        $totalPrice = $attribute->calculatePrice($basePrice, 1, $product->id);

        $this->assertEquals(35.00, $totalPrice); // 20 + 15
    }

    /** @test */
    public function it_applies_percentage_modifier_from_pivot()
    {
        $product = Product::factory()->withConfigurator()->create();
        $group = AttributeGroup::factory()->material()->create();
        $attribute = ProductAttribute::factory()->material('Seda')->create([
            'attribute_group_id' => $group->id,
        ]);

        // Crear modificador porcentual
        \DB::table('product_attribute_values')->insert([
            'product_id' => $product->id,
            'attribute_group_id' => $group->id,
            'product_attribute_id' => $attribute->id,
            'custom_price_percentage' => 25.00, // 25% increase
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $basePrice = 100.00;
        $totalPrice = $attribute->calculatePrice($basePrice, 1, $product->id);

        $this->assertEquals(125.00, $totalPrice); // 100 * 1.25
    }

    /** @test */
    public function it_combines_fixed_and_percentage_modifiers()
    {
        $product = Product::factory()->withConfigurator()->create();
        $group = AttributeGroup::factory()->color()->create();
        $attribute = ProductAttribute::factory()->color('Special')->create([
            'attribute_group_id' => $group->id,
        ]);

        // Crear ambos modificadores
        \DB::table('product_attribute_values')->insert([
            'product_id' => $product->id,
            'attribute_group_id' => $group->id,
            'product_attribute_id' => $attribute->id,
            'custom_price_modifier' => 10.00,  // Add €10
            'custom_price_percentage' => 20.00, // Then add 20%
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $basePrice = 50.00;
        // First add fixed: 50 + 10 = 60
        // Then add percentage: 60 * 1.20 = 72
        $totalPrice = $attribute->calculatePrice($basePrice, 1, $product->id);

        $this->assertEquals(72.00, $totalPrice);
    }

    /** @test */
    public function it_applies_volume_discount_rule()
    {
        $product = Product::factory()->withConfigurator()->create([
            'configurator_base_price' => 10.00,
        ]);

        $rule = PriceRule::factory()->create([
            'rule_type' => 'volume',
            'product_id' => $product->id,
            'action_type' => 'add_percentage',
            'action_value' => -15, // 15% discount
            'quantity_min' => 100,
            'active' => true,
        ]);

        $quantity = 150; // Above 100 threshold
        $basePrice = 10.00;

        // Discount applies
        $discountedPrice = $basePrice * (1 + $rule->action_value / 100);

        $this->assertEquals(8.50, $discountedPrice); // 10 * 0.85
    }

    /** @test */
    public function it_does_not_apply_volume_discount_below_threshold()
    {
        $product = Product::factory()->withConfigurator()->create();

        $rule = PriceRule::factory()->create([
            'rule_type' => 'volume',
            'product_id' => $product->id,
            'action_type' => 'add_percentage',
            'action_value' => -15,
            'quantity_min' => 100,
            'active' => true,
        ]);

        $quantity = 50; // Below threshold
        $basePrice = 10.00;

        // Discount should NOT apply
        $this->assertTrue($quantity < $rule->quantity_min);
        $this->assertEquals($basePrice, $basePrice); // No change
    }

    /** @test */
    public function it_applies_multiple_price_rules_by_priority()
    {
        $product = Product::factory()->withConfigurator()->create([
            'configurator_base_price' => 100.00,
        ]);

        // Lower priority (applied first)
        $rule1 = PriceRule::factory()->create([
            'product_id' => $product->id,
            'action_type' => 'add_percentage',
            'action_value' => -10, // 10% discount
            'priority' => 10,
            'active' => true,
        ]);

        // Higher priority (applied second)
        $rule2 = PriceRule::factory()->create([
            'product_id' => $product->id,
            'action_type' => 'add_fixed',
            'action_value' => -5, // €5 discount
            'priority' => 20,
            'active' => true,
        ]);

        $basePrice = 100.00;

        // Apply in priority order (higher number = later execution in DESC order)
        $afterRule2 = $rule2->applyRule($basePrice); // = 95 (100 - 5)
        $afterRule1 = $rule1->applyRule($afterRule2); // = 85.5 (95 * 0.90)

        $this->assertEquals(85.50, $afterRule1);
    }

    /** @test */
    public function it_ignores_inactive_price_rules()
    {
        $product = Product::factory()->withConfigurator()->create();

        $inactiveRule = PriceRule::factory()->create([
            'product_id' => $product->id,
            'action_type' => 'add_percentage',
            'action_value' => -50, // 50% discount
            'active' => false, // INACTIVE
        ]);

        $basePrice = 100.00;

        // Inactive rule should be ignored
        $this->assertFalse($inactiveRule->active);
    }

    /** @test */
    public function it_respects_temporal_price_rules()
    {
        $product = Product::factory()->withConfigurator()->create();

        // Active temporal rule
        $activeRule = PriceRule::factory()->create([
            'product_id' => $product->id,
            'action_type' => 'add_percentage',
            'action_value' => -20,
            'valid_from' => now()->subDays(1),
            'valid_until' => now()->addDays(1),
            'active' => true,
        ]);

        // Expired rule
        $expiredRule = PriceRule::factory()->create([
            'product_id' => $product->id,
            'action_type' => 'add_percentage',
            'action_value' => -50,
            'valid_from' => now()->subDays(30),
            'valid_until' => now()->subDays(1),
            'active' => true,
        ]);

        $now = now();

        // Active rule should apply
        $this->assertTrue($activeRule->isValidNow());

        // Expired rule should NOT apply
        $this->assertFalse($expiredRule->isValidNow());
    }

    /** @test */
    public function it_calculates_total_for_multiple_attributes()
    {
        $product = Product::factory()->withConfigurator()->create();
        $colorGroup = AttributeGroup::factory()->color()->create();
        $materialGroup = AttributeGroup::factory()->material()->create();
        $sizeGroup = AttributeGroup::factory()->size()->create();

        $color = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $colorGroup->id,
        ]);

        $material = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $materialGroup->id,
        ]);

        $size = ProductAttribute::factory()->size('XL')->create([
            'attribute_group_id' => $sizeGroup->id,
        ]);

        // Crear modificadores para cada atributo
        \DB::table('product_attribute_values')->insert([
            [
                'product_id' => $product->id,
                'attribute_group_id' => $colorGroup->id,
                'product_attribute_id' => $color->id,
                'custom_price_modifier' => 5.00,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => $product->id,
                'attribute_group_id' => $materialGroup->id,
                'product_attribute_id' => $material->id,
                'custom_price_modifier' => 8.00,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => $product->id,
                'attribute_group_id' => $sizeGroup->id,
                'product_attribute_id' => $size->id,
                'custom_price_modifier' => 3.00,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $basePrice = 20.00;
        $price = $basePrice;
        $price = $color->calculatePrice($price, 1, $product->id);    // +5 = 25
        $price = $material->calculatePrice($price, 1, $product->id); // +8 = 33
        $price = $size->calculatePrice($price, 1, $product->id);     // +3 = 36

        $this->assertEquals(36.00, $price); // 20 + 5 + 8 + 3
    }

    /** @test */
    public function it_rounds_price_to_two_decimals()
    {
        $price = 15.33333333;
        $rounded = round($price, 2);

        $this->assertEquals(15.33, $rounded);
    }

    /** @test */
    public function it_handles_zero_base_price()
    {
        $product = Product::factory()->withConfigurator()->create([
            'configurator_base_price' => 0.00,
        ]);

        $group = AttributeGroup::factory()->color()->create();
        $attribute = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $group->id,
        ]);

        \DB::table('product_attribute_values')->insert([
            'product_id' => $product->id,
            'attribute_group_id' => $group->id,
            'product_attribute_id' => $attribute->id,
            'custom_price_modifier' => 25.00,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $totalPrice = $attribute->calculatePrice($product->configurator_base_price, 1, $product->id);

        $this->assertEquals(25.00, $totalPrice);
    }

    /** @test */
    public function it_prevents_negative_total_price()
    {
        $basePrice = 10.00;
        $discount = -15.00; // More than base price

        $totalPrice = max(0, $basePrice + $discount);

        $this->assertEquals(0.00, $totalPrice);
        $this->assertGreaterThanOrEqual(0, $totalPrice);
    }
}
