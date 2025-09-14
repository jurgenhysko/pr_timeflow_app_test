<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_created()
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('John Doe', $customer->name);
        $this->assertEquals('john@example.com', $customer->email);
        $this->assertEquals('1234567890', $customer->phone);
        $this->assertEquals('123 Main St', $customer->address);
        $this->assertTrue($customer->is_active);
    }

    public function test_customer_has_orders_relationship()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Collection::class, $customer->orders);
        $this->assertCount(1, $customer->orders);
        $this->assertTrue($customer->orders->contains($order));
    }

    public function test_customer_get_products_method()
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

        $products = $customer->getProducts();

        $this->assertInstanceOf(Collection::class, $products);
        $this->assertCount(2, $products);
        $this->assertTrue($products->contains($product1));
        $this->assertTrue($products->contains($product2));
    }

    public function test_customer_favorite_product_method()
    {
        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $order1 = Order::factory()->create(['customer_id' => $customer->id]);
        $order2 = Order::factory()->create(['customer_id' => $customer->id]);
        
        OrderDetail::create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'quantity' => 3,
            'price' => 10.00
        ]);
        
        OrderDetail::create([
            'order_id' => $order2->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 10.00
        ]);
        
        OrderDetail::create([
            'order_id' => $order1->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 15.00
        ]);

        $favoriteProduct = $customer->favoriteProduct();

        $this->assertNotNull($favoriteProduct);
        $this->assertEquals($product1->id, $favoriteProduct->product_id);
        $this->assertEquals(5, $favoriteProduct->total_quantity);
    }

    public function test_customer_soft_deletes()
    {
        $customer = Customer::factory()->create();
        $customerId = $customer->id;

        $customer->delete();

        $this->assertSoftDeleted('customers', ['id' => $customerId]);
        $this->assertNull(Customer::find($customerId));
        $this->assertNotNull(Customer::withTrashed()->find($customerId));
    }

    public function test_customer_sortable_fields()
    {
        $expectedSortable = ['name', 'email', 'address', 'is_active', 'created_at', 'updated_at'];
        $this->assertEquals($expectedSortable, Customer::$sortable);
    }
}
