<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\AttributeGroup;
use App\Models\AttributeDependency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeDependencyTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected AttributeGroup $colorGroup;
    protected AttributeGroup $materialGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->withConfigurator()->create();
        $this->colorGroup = AttributeGroup::factory()->color()->create();
        $this->materialGroup = AttributeGroup::factory()->material()->create();
    }

    /** @test */
    public function it_creates_an_allows_dependency()
    {
        $whiteColor = ProductAttribute::factory()->color('Blanco')->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $cotton = ProductAttribute::factory()->material('Algodón')->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->allows()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $whiteColor->id,
            'dependent_attribute_id' => $cotton->id,
        ]);

        $this->assertEquals('allows', $dependency->condition_type);
        $this->assertTrue($dependency->active);
    }

    /** @test */
    public function it_creates_a_blocks_dependency()
    {
        $whiteColor = ProductAttribute::factory()->color('Blanco')->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $polyester = ProductAttribute::factory()->material('Poliéster')->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->blocks()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $whiteColor->id,
            'dependent_attribute_id' => $polyester->id,
        ]);

        $this->assertEquals('blocks', $dependency->condition_type);
    }

    /** @test */
    public function it_creates_a_requires_dependency()
    {
        $silkMaterial = ProductAttribute::factory()->material('Seda')->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $premiumColor = ProductAttribute::factory()->color('Premium')->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->requires()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $silkMaterial->id,
            'dependent_attribute_id' => $premiumColor->id,
        ]);

        $this->assertEquals('requires', $dependency->condition_type);
    }

    /** @test */
    public function it_respects_dependency_priority()
    {
        $parent = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $highPriority = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
            'priority' => 100,
        ]);

        $lowPriority = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
            'priority' => 10,
        ]);

        // Higher priority number = executed last
        $this->assertGreaterThan($lowPriority->priority, $highPriority->priority);
    }

    /** @test */
    public function it_applies_price_impact_from_dependency()
    {
        $parent = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
            'price_impact' => 12.50,
        ]);

        $this->assertEquals(12.50, $dependency->price_impact);
    }

    /** @test */
    public function it_supports_auto_select_flag()
    {
        $parent = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
            'auto_select' => true,
        ]);

        $this->assertTrue($dependency->auto_select);
    }

    /** @test */
    public function it_supports_reset_dependents_flag()
    {
        $parent = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
            'reset_dependents' => true,
        ]);

        $this->assertTrue($dependency->reset_dependents);
    }

    /** @test */
    public function it_can_be_inactive()
    {
        $parent = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
            'active' => false,
        ]);

        $this->assertFalse($dependency->active);
    }

    /** @test */
    public function it_belongs_to_a_product()
    {
        $parent = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
        ]);

        $this->assertInstanceOf(Product::class, $dependency->product);
        $this->assertEquals($this->product->id, $dependency->product_id);
    }

    /** @test */
    public function it_has_parent_and_dependent_attributes()
    {
        $parent = ProductAttribute::factory()->color('Parent')->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material('Dependent')->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $dependency = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
        ]);

        $this->assertInstanceOf(ProductAttribute::class, $dependency->parentAttribute);
        $this->assertInstanceOf(ProductAttribute::class, $dependency->dependentAttribute);
        $this->assertEquals('Parent', $dependency->parentAttribute->name);
        $this->assertEquals('Dependent', $dependency->dependentAttribute->name);
    }

    /** @test */
    public function it_can_store_custom_conditions_as_json()
    {
        $parent = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $conditions = [
            'min_quantity' => 100,
            'max_quantity' => 500,
            'requires_certification' => true,
        ];

        $dependency = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
            'conditions' => $conditions,
        ]);

        $this->assertIsArray($dependency->conditions);
        $this->assertEquals(100, $dependency->conditions['min_quantity']);
        $this->assertTrue($dependency->conditions['requires_certification']);
    }

    /** @test */
    public function it_queries_dependencies_by_product()
    {
        $otherProduct = Product::factory()->withConfigurator()->create();

        $parent = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        // Create dependencies for both products
        $dep1 = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
        ]);

        $dep2 = AttributeDependency::factory()->create([
            'product_id' => $otherProduct->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
        ]);

        $dependencies = AttributeDependency::where('product_id', $this->product->id)->get();

        $this->assertCount(1, $dependencies);
        $this->assertEquals($dep1->id, $dependencies->first()->id);
    }

    /** @test */
    public function it_queries_dependencies_by_parent_attribute()
    {
        $parent1 = ProductAttribute::factory()->color('Parent1')->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $parent2 = ProductAttribute::factory()->color('Parent2')->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent1->id,
            'dependent_attribute_id' => $dependent->id,
        ]);

        AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent2->id,
            'dependent_attribute_id' => $dependent->id,
        ]);

        $dependencies = AttributeDependency::where('parent_attribute_id', $parent1->id)->get();

        $this->assertCount(1, $dependencies);
    }

    /** @test */
    public function it_orders_dependencies_by_priority()
    {
        $parent = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $this->colorGroup->id,
        ]);

        $dependent = ProductAttribute::factory()->material()->create([
            'attribute_group_id' => $this->materialGroup->id,
        ]);

        $low = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
            'priority' => 10,
        ]);

        $high = AttributeDependency::factory()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $parent->id,
            'dependent_attribute_id' => $dependent->id,
            'priority' => 100,
        ]);

        $dependencies = AttributeDependency::where('product_id', $this->product->id)
            ->orderBy('priority', 'asc')
            ->get();

        $this->assertEquals($low->id, $dependencies->first()->id);
        $this->assertEquals($high->id, $dependencies->last()->id);
    }
}
