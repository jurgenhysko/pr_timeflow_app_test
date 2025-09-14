<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
    }

    public function test_can_get_customers_list()
    {
        Customer::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->has('links')
                    ->has('meta')
            );
        
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('email', $data[0]);
    }

    public function test_can_get_only_active_customers_by_default()
    {
        Customer::factory()->active()->count(2)->create();
        Customer::factory()->inactive()->count(2)->create();

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_get_all_customers_including_inactive()
    {
        Customer::factory()->active()->count(2)->create();
        Customer::factory()->inactive()->count(2)->create();

        $response = $this->getJson('/api/v1/customers?with_inactive=1');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
    }

    public function test_can_search_customers()
    {
        Customer::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        Customer::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        Customer::factory()->create(['name' => 'Bob Johnson', 'email' => 'bob@example.com']);

        $response = $this->getJson('/api/v1/customers?qs=John');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    public function test_can_sort_customers()
    {
        Customer::factory()->create(['name' => 'Charlie Brown']);
        Customer::factory()->create(['name' => 'Alice Wonder']);
        Customer::factory()->create(['name' => 'Bob Builder']);

        $response = $this->getJson('/api/v1/customers?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $names = array_column($data, 'name');
        $this->assertIsArray($data);
    }

    public function test_can_create_customer()
    {
        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/customers', $customerData);

        $response->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->where('name', 'John Doe')
                        ->where('email', 'john@example.com')
                        ->where('phone', '1234567890')
                        ->where('address', '123 Main St')
                        ->where('is_active', true)
                        ->has('id')
                        ->has('created_at')
                        ->has('updated_at')
                )
            );

        $this->assertDatabaseHas('customers', $customerData);
    }

    public function test_cannot_create_customer_with_duplicate_email()
    {
        Customer::factory()->create(['email' => 'john@example.com']);

        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
        ];

        $response = $this->postJson('/api/v1/customers', $customerData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_create_customer_without_required_fields()
    {
        $response = $this->postJson('/api/v1/customers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_can_get_single_customer()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $response = $this->getJson("/api/v1/customers/{$customer->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($customer->id, $data['id']);
        $this->assertEquals($customer->name, $data['name']);
        $this->assertEquals($customer->email, $data['email']);
    }

    public function test_cannot_get_nonexistent_customer()
    {
        $response = $this->getJson('/api/v1/customers/999');

        $response->assertStatus(400);
    }

    public function test_can_update_customer()
    {
        $customer = Customer::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '9876543210',
            'address' => '456 Updated St',
            'is_active' => false,
        ];

        $response = $this->putJson("/api/v1/customers/{$customer->id}", $updateData);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Updated Name', $data['name']);
        $this->assertEquals('updated@example.com', $data['email']);
        $this->assertEquals('9876543210', $data['phone']);
        $this->assertEquals('456 Updated St', $data['address']);
        $this->assertFalse($data['is_active']);

        $this->assertDatabaseHas('customers', array_merge(['id' => $customer->id], $updateData));
    }

    public function test_can_delete_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/v1/customers/{$customer->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_can_get_customer_profile()
    {
        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 10.00
        ]);
        
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 15.00
        ]);

        $response = $this->getJson("/api/v1/customers/{$customer->id}/profile");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($customer->id, $data['id']);
        $this->assertArrayHasKey('orders', $data);
        $this->assertArrayHasKey('products', $data);
        $this->assertArrayHasKey('favorite_product', $data);
    }

    public function test_customer_pagination()
    {
        Customer::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/customers?per_page=10');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->has('links')
                    ->has('meta')
            );
        
        $meta = $response->json('meta');
        $this->assertEquals(10, $meta['per_page']);
        $this->assertGreaterThanOrEqual(15, $meta['total']);
        $this->assertGreaterThanOrEqual(2, $meta['last_page']);

        $this->assertCount(10, $response->json('data'));
    }
}
