<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'A test product description',
            'price' => 29.99,
            'stock_quantity' => 100,
            'sku' => 'TEST-001',
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('A test product description', $product->description);
        $this->assertEquals(29.99, $product->price);
        $this->assertEquals(100, $product->stock_quantity);
        $this->assertEquals('TEST-001', $product->sku);
    }

    public function test_product_soft_deletes()
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $productId]);
        $this->assertNull(Product::find($productId));
        $this->assertNotNull(Product::withTrashed()->find($productId));
    }

    public function test_product_sortable_fields()
    {
        $expectedSortable = ['name', 'description', 'price', 'stock_quantity', 'sku', 'created_at', 'updated_at'];
        $this->assertEquals($expectedSortable, Product::$sortable);
    }

    public function test_product_sku_uniqueness()
    {
        Product::create([
            'name' => 'Test Product 1',
            'description' => 'A test product',
            'price' => 10.00,
            'stock_quantity' => 10,
            'sku' => 'UNIQUE-001',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Product::create([
            'name' => 'Test Product 2',
            'description' => 'Another test product',
            'price' => 20.00,
            'stock_quantity' => 5,
            'sku' => 'UNIQUE-001',
        ]);
    }
}
