<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
    }

    public function test_can_get_products_list()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->has('links')
                    ->has('meta')
            );
        
        $data = $response->json('data');
        $this->assertCount(3, $data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('description', $data[0]);
        $this->assertArrayHasKey('price', $data[0]);
        $this->assertArrayHasKey('stock_quantity', $data[0]);
        $this->assertArrayHasKey('sku', $data[0]);
    }

    public function test_can_search_products()
    {
        Product::factory()->create(['name' => 'Laptop Computer', 'sku' => 'LAP-001']);
        Product::factory()->create(['name' => 'Desktop Computer', 'sku' => 'DESK-001']);
        Product::factory()->create(['name' => 'Wireless Mouse', 'sku' => 'MOUSE-001']);

        $response = $this->getJson('/api/v1/products?qs=Computer');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_sort_products()
    {
        Product::factory()->create(['name' => 'Charlie Product', 'price' => 30.00]);
        Product::factory()->create(['name' => 'Alice Product', 'price' => 10.00]);
        Product::factory()->create(['name' => 'Bob Product', 'price' => 20.00]);

        $response = $this->getJson('/api/v1/products?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Alice Product', $data[0]['name']);
        $this->assertEquals('Bob Product', $data[1]['name']);
        $this->assertEquals('Charlie Product', $data[2]['name']);
    }

    public function test_can_create_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'A test product description',
            'price' => 29.99,
            'stock_quantity' => 100,
            'sku' => 'TEST-001',
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);
        $data = $response->json('data');
        $this->assertEquals('Test Product', $data['name']);
        $this->assertEquals('A test product description', $data['description']);
        $this->assertEquals(29.99, $data['price']);
        $this->assertEquals(100, $data['stock_quantity']);
        $this->assertEquals('TEST-001', $data['sku']);
        $this->assertArrayHasKey('id', $data);

        $this->assertDatabaseHas('products', $productData);
    }

    public function test_cannot_create_product_with_duplicate_sku()
    {
        Product::factory()->create(['sku' => 'DUPLICATE-001']);

        $productData = [
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 29.99,
            'stock_quantity' => 100,
            'sku' => 'DUPLICATE-001',
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_cannot_create_product_without_required_fields()
    {
        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'stock_quantity', 'sku']);
    }

    public function test_can_get_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->where('id', $product->id)
                        ->where('name', $product->name)
                        ->where('description', $product->description)
                        ->where('price', $product->price)
                        ->where('stock_quantity', $product->stock_quantity)
                        ->where('sku', $product->sku)
                )
            );
    }

    public function test_cannot_get_nonexistent_product()
    {
        $response = $this->getJson('/api/v1/products/999');

        $response->assertStatus(400);
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create();

        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 39.99,
            'stock_quantity' => 50,
            'sku' => 'UPDATED-001',
        ];

        $response = $this->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Updated Product', $data['name']);
        $this->assertEquals('Updated description', $data['description']);
        $this->assertEquals(39.99, $data['price']);
        $this->assertEquals(50, $data['stock_quantity']);
        $this->assertEquals('UPDATED-001', $data['sku']);

        $this->assertDatabaseHas('products', array_merge(['id' => $product->id], $updateData));
    }

    public function test_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_pagination()
    {
        Product::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/products?per_page=10');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->has('links')
                    ->has('meta')
            );
        
        $meta = $response->json('meta');
        $this->assertEquals(10, $meta['per_page']);
        $this->assertEquals(25, $meta['total']);
        $this->assertEquals(3, $meta['last_page']);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_can_search_products_by_price()
    {
        Product::factory()->create(['price' => 10.00]);
        Product::factory()->create(['price' => 20.00]);
        Product::factory()->create(['price' => 30.00]);

        $response = $this->getJson('/api/v1/products?qs=20');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_can_search_products_by_sku()
    {
        Product::factory()->create(['sku' => 'ABC-123']);
        Product::factory()->create(['sku' => 'DEF-456']);
        Product::factory()->create(['sku' => 'GHI-789']);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/v1/products?qs=ABC');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}
