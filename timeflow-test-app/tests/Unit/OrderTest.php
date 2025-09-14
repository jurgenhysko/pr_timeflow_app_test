<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created()
    {
        $customer = Customer::factory()->create();
        
        $order = Order::create([
            'customer_id' => $customer->id,
            'total_amount' => 100.50,
            'status' => 'processing',
            'order_date' => now(),
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($customer->id, $order->customer_id);
        $this->assertEquals(100.50, $order->total_amount);
        $this->assertEquals('processing', $order->status);
        $this->assertNotNull($order->order_date);
    }

    public function test_order_soft_deletes()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $orderId = $order->id;

        $order->delete();

        $this->assertSoftDeleted('orders', ['id' => $orderId]);
        $this->assertNull(Order::find($orderId));
        $this->assertNotNull(Order::withTrashed()->find($orderId));
    }

    public function test_order_sortable_fields()
    {
        $expectedSortable = [
            'id', 
            'status', 
            'order_date', 
            'total_amount', 
            'created_at', 
            'orders.id', 
            'orders.status', 
            'orders.order_date', 
            'orders.total_amount', 
            'orders.created_at',
            'customers.name',
            'customers.email',
            'customers.phone',
            'customers.address'
        ];
        $this->assertEquals($expectedSortable, Order::$sortable);
    }
}
