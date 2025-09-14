<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop Pro 15"',
                'description' => 'High-performance laptop with 16GB RAM and 512GB SSD',
                'price' => 1299.99,
                'stock_quantity' => 50,
                'sku' => 'LAPTOP-PRO-15',
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with USB receiver',
                'price' => 29.99,
                'stock_quantity' => 200,
                'sku' => 'MOUSE-WIRELESS-001',
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB backlit mechanical keyboard with blue switches',
                'price' => 89.99,
                'stock_quantity' => 75,
                'sku' => 'KEYBOARD-MECH-RGB',
            ],
            [
                'name' => '4K Monitor 27"',
                'description' => 'Ultra HD 4K monitor with HDR support',
                'price' => 399.99,
                'stock_quantity' => 30,
                'sku' => 'MONITOR-4K-27',
            ],
            [
                'name' => 'USB-C Hub',
                'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, and SD card reader',
                'price' => 49.99,
                'stock_quantity' => 100,
                'sku' => 'HUB-USBC-7IN1',
            ],
            [
                'name' => 'Bluetooth Headphones',
                'description' => 'Noise-cancelling wireless headphones with 30h battery',
                'price' => 199.99,
                'stock_quantity' => 60,
                'sku' => 'HEADPHONES-BT-NC',
            ],
            [
                'name' => 'Webcam HD 1080p',
                'description' => 'Full HD webcam with built-in microphone',
                'price' => 79.99,
                'stock_quantity' => 40,
                'sku' => 'WEBCAM-HD-1080P',
            ],
            [
                'name' => 'Gaming Chair',
                'description' => 'Ergonomic gaming chair with lumbar support',
                'price' => 299.99,
                'stock_quantity' => 25,
                'sku' => 'CHAIR-GAMING-ERG',
            ],
            [
                'name' => 'Smartphone Pro Max',
                'description' => 'Latest flagship smartphone with 256GB storage',
                'price' => 999.99,
                'stock_quantity' => 40,
                'sku' => 'PHONE-PRO-MAX-256',
            ],
            [
                'name' => 'Tablet Air 10"',
                'description' => 'Lightweight tablet with 128GB storage and stylus support',
                'price' => 599.99,
                'stock_quantity' => 35,
                'sku' => 'TABLET-AIR-10-128',
            ],
            [
                'name' => 'Smart Watch Series 8',
                'description' => 'Advanced smartwatch with health monitoring and GPS',
                'price' => 399.99,
                'stock_quantity' => 50,
                'sku' => 'WATCH-SERIES-8-GPS',
            ],
            [
                'name' => 'Power Bank 20000mAh',
                'description' => 'High-capacity portable charger with fast charging',
                'price' => 49.99,
                'stock_quantity' => 80,
                'sku' => 'POWERBANK-20K-FAST',
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'description' => 'Premium wireless earbuds with active noise cancellation',
                'price' => 249.99,
                'stock_quantity' => 45,
                'sku' => 'EARBUDS-PRO-ANC',
            ],
            [
                'name' => 'External SSD 1TB',
                'description' => 'Ultra-fast external SSD with USB-C connectivity',
                'price' => 179.99,
                'stock_quantity' => 30,
                'sku' => 'SSD-EXT-1TB-USBC',
            ],
            [
                'name' => 'LED Desk Lamp',
                'description' => 'Adjustable LED desk lamp with USB charging port',
                'price' => 39.99,
                'stock_quantity' => 60,
                'sku' => 'LAMP-LED-DESK-USB',
            ],
            [
                'name' => 'Laptop Stand Adjustable',
                'description' => 'Ergonomic aluminum laptop stand with height adjustment',
                'price' => 79.99,
                'stock_quantity' => 40,
                'sku' => 'STAND-LAPTOP-ADJ',
            ],
            [
                'name' => 'Cable Management Kit',
                'description' => 'Complete cable management solution with clips and ties',
                'price' => 19.99,
                'stock_quantity' => 100,
                'sku' => 'CABLE-MGMT-KIT',
            ],
            [
                'name' => 'Bluetooth Speaker',
                'description' => 'Portable Bluetooth speaker with 360-degree sound',
                'price' => 89.99,
                'stock_quantity' => 55,
                'sku' => 'SPEAKER-BT-360',
            ],
            [
                'name' => 'Gaming Mouse Pad',
                'description' => 'Large gaming mouse pad with RGB lighting',
                'price' => 34.99,
                'stock_quantity' => 70,
                'sku' => 'MOUSEPAD-GAMING-RGB',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
