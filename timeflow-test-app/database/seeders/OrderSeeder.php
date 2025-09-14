<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No customers or products found. Please run CustomerSeeder and ProductSeeder first.');
            return;
        }

        $orders = [];
        
        for ($i = 0; $i < 25; $i++) {
            $customer = $customers->random();
            $orderProducts = [];
            $productCount = rand(1, 4); 
            
            $selectedProducts = $products->random($productCount);
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $orderProducts[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ];
            }
            
            $statuses = ['processing', 'shipped', 'delivered', 'cancelled'];
            $statusWeights = [20, 25, 45, 10];
            $status = $this->weightedRandom($statuses, $statusWeights);
            
            $daysAgo = rand(1, 30);
            $orderDate = now()->subDays($daysAgo);
            
            $orders[] = [
                'customer_id' => $customer->id,
                'status' => $status,
                'products' => $orderProducts,
                'order_date' => $orderDate,
            ];
        }
        
        $highValueOrders = [
            [
                'customer_id' => $customers->random()->id,
                'status' => 'delivered',
                'products' => [
                    ['product_id' => $products->where('name', 'Laptop Pro 15"')->first()->id, 'quantity' => 1],
                    ['product_id' => $products->where('name', '4K Monitor 27"')->first()->id, 'quantity' => 1],
                    ['product_id' => $products->where('name', 'Mechanical Keyboard')->first()->id, 'quantity' => 1],
                    ['product_id' => $products->where('name', 'Wireless Mouse')->first()->id, 'quantity' => 1],
                ],
                'order_date' => now()->subDays(5),
            ],
            [
                'customer_id' => $customers->random()->id,
                'status' => 'shipped',
                'products' => [
                    ['product_id' => $products->where('name', 'Smartphone Pro Max')->first()->id, 'quantity' => 1],
                    ['product_id' => $products->where('name', 'Wireless Earbuds Pro')->first()->id, 'quantity' => 1],
                    ['product_id' => $products->where('name', 'Power Bank 20000mAh')->first()->id, 'quantity' => 1],
                ],
                'order_date' => now()->subDays(2),
            ],
            [
                'customer_id' => $customers->random()->id,
                'status' => 'processing',
                'products' => [
                    ['product_id' => $products->where('name', 'Gaming Chair')->first()->id, 'quantity' => 1],
                    ['product_id' => $products->where('name', 'Gaming Mouse Pad')->first()->id, 'quantity' => 1],
                    ['product_id' => $products->where('name', 'Bluetooth Speaker')->first()->id, 'quantity' => 1],
                ],
                'order_date' => now()->subHours(6),
            ],
        ];
        
        $orders = array_merge($orders, $highValueOrders);

        foreach ($orders as $orderData) {
            $orderProducts = [];
            $totalAmount = 0;

            foreach ($orderData['products'] as $productData) {
                $product = Product::find($productData['product_id']);
                if (!$product) continue; // Skip if product not found
                
                $quantity = $productData['quantity'];
                $price = $product->price;
                
                $totalAmount += $price * $quantity;
                $orderProducts[$productData['product_id']] = [
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            $order = Order::create([
                'customer_id' => $orderData['customer_id'],
                'total_amount' => $totalAmount,
                'status' => $orderData['status'],
                'order_date' => $orderData['order_date'] ?? now()->subDays(rand(1, 30)),
            ]);

            $order->products()->attach($orderProducts);
        }
    }
    
    private function weightedRandom($items, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = mt_rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($items as $index => $item) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $item;
            }
        }
        
        return $items[0];
    }
}
