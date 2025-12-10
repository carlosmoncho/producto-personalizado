<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\AttributeGroup;
use App\Models\AttributeDependency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactorySchemaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_attribute_group_with_factory()
    {
        $group = AttributeGroup::factory()->color()->create();

        $this->assertDatabaseHas('attribute_groups', [
            'id' => $group->id,
            'type' => 'color',
        ]);

        // Slug should start with 'colores-' followed by unique number
        $this->assertStringStartsWith('colores-', $group->slug);
    }

    /** @test */
    public function it_can_create_product_attribute_with_factory()
    {
        $group = AttributeGroup::factory()->color()->create();
        $attribute = ProductAttribute::factory()->color('Test Color')->create([
            'attribute_group_id' => $group->id,
        ]);

        $this->assertDatabaseHas('product_attributes', [
            'id' => $attribute->id,
            'type' => 'color',
            'name' => 'Test Color',
        ]);

        // Verify price columns don't exist
        $this->assertFalse(property_exists($attribute, 'price_modifier'));
        $this->assertFalse(property_exists($attribute, 'price_percentage'));
    }

    /** @test */
    public function it_can_create_product_with_configurator()
    {
        $product = Product::factory()->withConfigurator()->create();

        $this->assertTrue($product->has_configurator);
        $this->assertNotNull($product->configurator_base_price);
    }

    /** @test */
    public function it_can_create_attribute_dependency()
    {
        $product = Product::factory()->withConfigurator()->create();
        $colorGroup = AttributeGroup::factory()->color()->create();
        $materialGroup = AttributeGroup::factory()->material()->create();

        $color = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $colorGroup->id,
        ]);

        $material = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $materialGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->allows()->create([
            'product_id' => $product->id,
            'parent_attribute_id' => $color->id,
            'dependent_attribute_id' => $material->id,
        ]);

        $this->assertEquals('allows', $dependency->condition_type);
        $this->assertEquals($product->id, $dependency->product_id);
    }
}
