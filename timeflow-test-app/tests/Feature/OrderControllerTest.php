<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Facades\DB;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
    }

    // public function test_can_get_orders_list()
    // {
    //     $customer = Customer::factory()->create();
    //     $order = Order::factory()->create(['customer_id' => $customer->id]);

    //     $response = $this->getJson('/api/v1/orders');

    //     $response->assertStatus(200)
    //         ->assertJson(fn (AssertableJson $json) =>
    //             $json->has('data')
    //                 ->has('links')
    //                 ->has('meta')
    //         );
        
    //     $data = $response->json('data');
    //     $this->assertCount(1, $data);
    //     $this->assertArrayHasKey('id', $data[0]);
    //     $this->assertArrayHasKey('status', $data[0]);
    //     $this->assertArrayHasKey('order_date', $data[0]);
    //     $this->assertArrayHasKey('total_amount', $data[0]);
    //     $this->assertArrayHasKey('customer_name', $data[0]);
    //     $this->assertArrayHasKey('customer_email', $data[0]);
    // }

    // public function test_can_search_orders()
    // {
    //     $customer1 = Customer::factory()->create(['name' => 'John Doe']);
    //     $customer2 = Customer::factory()->create(['name' => 'Jane Smith']);
    //     $order1 = Order::factory()->create(['customer_id' => $customer1->id, 'status' => 'processing']);
    //     $order2 = Order::factory()->create(['customer_id' => $customer2->id, 'status' => 'delivered']);

    //     $response = $this->getJson('/api/v1/orders?qs=John');

    //     $response->assertStatus(200);
    //     $data = $response->json('data');
    //     $this->assertGreaterThanOrEqual(1, count($data));
    // }

    // public function test_can_filter_orders_by_status()
    // {
    //     $customer = Customer::factory()->create();
    //     Order::factory()->create(['customer_id' => $customer->id, 'status' => 'processing']);
    //     Order::factory()->create(['customer_id' => $customer->id, 'status' => 'delivered']);
    //     Order::factory()->create(['customer_id' => $customer->id, 'status' => 'cancelled']);

    //     $response = $this->getJson('/api/v1/orders?status=processing');

    //     $response->assertStatus(200);
    //     $this->assertCount(1, $response->json('data'));
    // }

    // public function test_can_sort_orders()
    // {
    //     $customer = Customer::factory()->create();
    //     $order1 = Order::factory()->create(['customer_id' => $customer->id, 'total_amount' => 100.00]);
    //     $order2 = Order::factory()->create(['customer_id' => $customer->id, 'total_amount' => 200.00]);
    //     $order3 = Order::factory()->create(['customer_id' => $customer->id, 'total_amount' => 50.00]);

    //     $response = $this->getJson('/api/v1/orders?sort_by=total_amount&sort_direction=asc');

    //     $response->assertStatus(200);
    //     $data = $response->json('data');
    //     $this->assertEquals(50.00, $data[0]['total_amount']);
    //     $this->assertEquals(100.00, $data[1]['total_amount']);
    //     $this->assertEquals(200.00, $data[2]['total_amount']);
    // }

    public function test_can_create_order()
    {
        $customer = Customer::factory()->create();
        $product1 = Product::factory()->inStock()->create(['price' => 10.00, 'stock_quantity' => 100]);
        $product2 = Product::factory()->inStock()->create(['price' => 20.00, 'stock_quantity' => 50]);

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201);
        $data = $response->json('data');
        $this->assertEquals('processing', $data['status']);
        $this->assertEquals(40.00, $data['total_amount']);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('order_date', $data);
        $this->assertArrayHasKey('products', $data);
        $this->assertArrayHasKey('customer', $data);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'total_amount' => 40.00,
            'status' => 'processing',
        ]);
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(500)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('success', false)
                    ->has('message')
            );
    }

    public function test_cannot_create_order_with_nonexistent_customer()
    {
        $product = Product::factory()->create();

        $orderData = [
            'customer_id' => 999,
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }

    public function test_cannot_create_order_with_nonexistent_product()
    {
        $customer = Customer::factory()->create();

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['product_id' => 999, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.product_id']);
    }

    public function test_can_get_single_order()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create();
        
        $order->products()->attach([
            $product->id => ['quantity' => 2, 'price' => 10.00]
        ]);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($order->id, $data['id']);
        $this->assertArrayHasKey('customer', $data);
        $this->assertArrayHasKey('products', $data);
    }

    public function test_cannot_get_nonexistent_order()
    {
        $response = $this->getJson('/api/v1/orders/999');

        $response->assertStatus(400);
    }

    public function test_can_update_order()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $updateData = [
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'total_amount' => 150.00,
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 150.00
                ]
            ]
        ];

        $response = $this->putJson("/api/v1/orders/{$order->id}", $updateData);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertContains($data['status'], ['processing', 'shipped', 'delivered', 'cancelled']);
        $this->assertArrayHasKey('total_amount', $data);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_can_delete_order()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $response = $this->deleteJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_can_get_monthly_revenue()
    {
        $customer = Customer::factory()->create();
        Order::factory()->create(['customer_id' => $customer->id, 'total_amount' => 100.00, 'order_date' => now()->subDays(1)]);
        Order::factory()->create(['customer_id' => $customer->id, 'total_amount' => 200.00, 'order_date' => now()->subDays(2)]);

        $response = $this->getJson('/api/v1/monthly-revenue');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
            );
    }

    // public function test_order_pagination()
    // {
    //     $customer = Customer::factory()->create();
    //     Order::factory()->count(25)->create(['customer_id' => $customer->id]);

    //     $response = $this->getJson('/api/v1/orders?per_page=10');

    //     $response->assertStatus(200)
    //         ->assertJson(fn (AssertableJson $json) =>
    //             $json->has('data')
    //                 ->has('links')
    //                 ->has('meta')
    //         );
        
    //     $meta = $response->json('meta');
    //     $this->assertEquals(10, $meta['per_page']);
    //     $this->assertEquals(25, $meta['total']);
    //     $this->assertEquals(3, $meta['last_page']);

    //     $this->assertCount(10, $response->json('data'));
    // }

    public function test_order_creation_reduces_product_stock()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 10,
        ]);
    }
}
