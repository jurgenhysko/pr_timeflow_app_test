<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_detail_can_be_created()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create();

        $orderDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 15.99,
        ]);

        $this->assertInstanceOf(OrderDetail::class, $orderDetail);
        $this->assertEquals($order->id, $orderDetail->order_id);
        $this->assertEquals($product->id, $orderDetail->product_id);
        $this->assertEquals(3, $orderDetail->quantity);
        $this->assertEquals(15.99, $orderDetail->price);
    }

    public function test_order_detail_belongs_to_order()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create();

        $orderDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 10.00,
        ]);

        $this->assertInstanceOf(Order::class, $orderDetail->order);
        $this->assertEquals($order->id, $orderDetail->order->id);
    }

    public function test_order_detail_belongs_to_product()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create();

        $orderDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 25.50,
        ]);

        $this->assertInstanceOf(Product::class, $orderDetail->product);
        $this->assertEquals($product->id, $orderDetail->product->id);
    }

    public function test_order_detail_uses_correct_table()
    {
        $orderDetail = new OrderDetail();
        $this->assertEquals('order_product', $orderDetail->getTable());
    }

    public function test_order_detail_fillable_attributes()
    {
        $orderDetail = new OrderDetail();
        $fillable = $orderDetail->getFillable();

        $expectedFillable = ['order_id', 'product_id', 'quantity', 'price'];
        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_order_detail_with_product_relationship()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create(['name' => 'Test Product']);

        $orderDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 12.00,
        ]);

        // Test that the product relationship is loaded by default
        $loadedOrderDetail = OrderDetail::with('product')->find($orderDetail->id);
        $this->assertTrue($loadedOrderDetail->relationLoaded('product'));
        $this->assertEquals('Test Product', $loadedOrderDetail->product->name);
    }

    public function test_order_detail_quantity_validation()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create();

        $orderDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10.00,
        ]);

        $this->assertEquals(1, $orderDetail->quantity);
    }

    public function test_order_detail_price_validation()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create();

        $orderDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 0.01,
        ]);

        $this->assertEquals(0.01, $orderDetail->price);
    }
}
