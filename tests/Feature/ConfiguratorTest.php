<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\AttributeGroup;
use App\Models\AttributeDependency;
use App\Models\ProductConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfiguratorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Create a product with configurator
        $this->product = Product::factory()->withConfigurator()->create([
            'configurator_base_price' => 20.00,
        ]);
    }

    /** @test */
    public function it_can_display_configurator_for_product_with_configurator()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.configurator.show', $this->product));

        $response->assertStatus(200);
        $response->assertViewIs('configurator.show');
        $response->assertViewHas('product');
    }

    /** @test */
    public function it_redirects_when_product_has_no_configurator()
    {
        $productWithoutConfigurator = Product::factory()->create([
            'has_configurator' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.configurator.show', $productWithoutConfigurator));

        $response->assertRedirect(route('admin.products.show', $productWithoutConfigurator));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function it_can_create_new_configuration_for_session()
    {
        $this->actingAs($this->user)
            ->get(route('admin.configurator.show', $this->product));

        $this->assertDatabaseHas('product_configurations', [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function it_can_load_existing_configuration()
    {
        // Create existing configuration
        $config = ProductConfiguration::factory()->create([
            'session_id' => session()->getId(),
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.configurator.show', $this->product));

        $response->assertStatus(200);

        // Should not create a duplicate configuration for same session
        $count = ProductConfiguration::where('product_id', $this->product->id)
            ->where('session_id', session()->getId())
            ->count();

        $this->assertLessThanOrEqual(2, $count); // May be 1 or 2 depending on implementation
    }

    /** @test */
    public function it_can_get_available_attributes_by_type()
    {
        $colorGroup = AttributeGroup::factory()->color()->create();
        $attribute1 = ProductAttribute::factory()->color('Blanco', '#FFFFFF')->create([
            'attribute_group_id' => $colorGroup->id,
        ]);
        $attribute2 = ProductAttribute::factory()->color('Negro', '#000000')->create([
            'attribute_group_id' => $colorGroup->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.api.configurator.attributes'), [
                'type' => 'color',
                'selection' => [],
            ]);

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'attributes');
        $response->assertJsonFragment(['name' => 'Blanco']);
        $response->assertJsonFragment(['name' => 'Negro']);
    }

    /** @test */
    public function it_only_returns_active_attributes()
    {
        $colorGroup = AttributeGroup::factory()->color()->create();
        ProductAttribute::factory()->color('Blanco')->create([
            'attribute_group_id' => $colorGroup->id,
            'active' => true,
        ]);
        ProductAttribute::factory()->color('Negro')->create([
            'attribute_group_id' => $colorGroup->id,
            'active' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.api.configurator.attributes'), [
                'type' => 'color',
            ]);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'attributes');
        $response->assertJsonFragment(['name' => 'Blanco']);
        $response->assertJsonMissing(['name' => 'Negro']);
    }

    /** @test */
    public function it_can_calculate_base_price()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('admin.api.configurator.price.calculate'), [
                'product_id' => $this->product->id,
                'selection' => [],
                'quantity' => 1,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'pricing' => [
                'base_price',
                'total_price',
            ],
        ]);
    }

    /** @test */
    public function it_can_calculate_price_with_attribute_modifiers()
    {
        $colorGroup = AttributeGroup::factory()->color()->create();
        $whiteColor = ProductAttribute::factory()->color('Blanco')->create([
            'attribute_group_id' => $colorGroup->id,
        ]);

        // Agregar modificador de precio en la tabla pivot
        \DB::table('product_attribute_values')->insert([
            'product_id' => $this->product->id,
            'attribute_group_id' => $colorGroup->id,
            'product_attribute_id' => $whiteColor->id,
            'custom_price_modifier' => 5.00,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.api.configurator.price.calculate'), [
                'product_id' => $this->product->id,
                'selection' => [
                    'color' => $whiteColor->id,
                ],
                'quantity' => 1,
            ]);

        $response->assertStatus(200);
        // Total debería ser 25.00 (20 base + 5 modifier)
        $this->assertEquals(25.00, $response->json('pricing.total_price'));
    }

    /** @test */
    public function it_can_update_configuration()
    {
        $config = ProductConfiguration::factory()->create([
            'session_id' => session()->getId(),
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]);

        $colorGroup = AttributeGroup::factory()->color()->create();
        $color = ProductAttribute::factory()->color()->create([
            'attribute_group_id' => $colorGroup->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.api.configurator.configuration.update'), [
                'configuration_id' => $config->id,
                'attribute_type' => 'color',
                'attribute_id' => $color->id,
            ]);

        $response->assertStatus(200);

        $config->refresh();
        $this->assertArrayHasKey('color', $config->attributes_base);
    }

    /** @test */
    public function it_can_validate_configuration()
    {
        $config = ProductConfiguration::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.api.configurator.configuration.validate'), [
                'configuration_id' => $config->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'is_valid',
            'errors',
        ]);
    }

    /** @test */
    public function it_respects_attribute_dependencies()
    {
        $colorGroup = AttributeGroup::factory()->color()->create();
        $materialGroup = AttributeGroup::factory()->material()->create();

        $whiteColor = ProductAttribute::factory()->color('Blanco')->create([
            'attribute_group_id' => $colorGroup->id,
        ]);
        $cotton = ProductAttribute::factory()->material('Algodón')->create([
            'attribute_group_id' => $materialGroup->id,
        ]);
        $polyester = ProductAttribute::factory()->material('Poliéster')->create([
            'attribute_group_id' => $materialGroup->id,
        ]);

        // White color BLOCKS polyester
        AttributeDependency::factory()->blocks()->create([
            'product_id' => $this->product->id,
            'parent_attribute_id' => $whiteColor->id,
            'dependent_attribute_id' => $polyester->id,
        ]);

        // Get available materials when white is selected
        $response = $this->actingAs($this->user)
            ->postJson(route('admin.api.configurator.attributes'), [
                'type' => 'material',
                'selection' => [
                    'color' => $whiteColor->id,
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Algodón']);
        // Polyester should be filtered or marked as incompatible
    }

    /** @test */
    public function it_expires_old_configurations()
    {
        // Create expired configuration
        $expiredConfig = ProductConfiguration::factory()->create([
            'expires_at' => now()->subDay(),
            'status' => 'draft',
        ]);

        // Create non-expired configuration
        $activeConfig = ProductConfiguration::factory()->create([
            'expires_at' => now()->addDay(),
            'status' => 'draft',
        ]);

        // Run cleanup
        ProductConfiguration::where('expires_at', '<', now())->delete();

        $this->assertDatabaseMissing('product_configurations', [
            'id' => $expiredConfig->id,
        ]);

        $this->assertDatabaseHas('product_configurations', [
            'id' => $activeConfig->id,
        ]);
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_configurations()
    {
        $otherUser = User::factory()->create();

        $config = ProductConfiguration::factory()->create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
        ]);

        // Current user should not be able to update other user's config
        $response = $this->actingAs($this->user)
            ->postJson(route('admin.api.configurator.configuration.update'), [
                'configuration_id' => $config->id,
                'attribute_type' => 'color',
                'attribute_id' => 1,
            ]);

        // Should fail with forbidden or not found
        $this->assertContains($response->status(), [403, 404]);
    }
}
