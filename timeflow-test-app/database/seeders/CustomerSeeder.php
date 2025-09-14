<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1-555-0101',
                'address' => '123 Main Street, New York, NY 10001',
                'is_active' => true,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1-555-0102',
                'address' => '456 Oak Avenue, Los Angeles, CA 90210',
                'is_active' => true,
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '+1-555-0103',
                'address' => '789 Pine Road, Chicago, IL 60601',
                'is_active' => false,
            ],
            [
                'name' => 'Alice Brown',
                'email' => 'alice.brown@example.com',
                'phone' => '+1-555-0104',
                'address' => '321 Elm Street, Houston, TX 77001',
                'is_active' => true,
            ],
            [
                'name' => 'Charlie Wilson',
                'email' => 'charlie.wilson@example.com',
                'phone' => '+1-555-0105',
                'address' => '654 Maple Drive, Phoenix, AZ 85001',
                'is_active' => true,
            ],
            [
                'name' => 'Sarah Davis',
                'email' => 'sarah.davis@example.com',
                'phone' => '+1-555-0106',
                'address' => '987 Cedar Lane, Miami, FL 33101',
                'is_active' => true,
            ],
            [
                'name' => 'Michael Garcia',
                'email' => 'michael.garcia@example.com',
                'phone' => '+1-555-0107',
                'address' => '147 Birch Street, Seattle, WA 98101',
                'is_active' => true,
            ],
            [
                'name' => 'Emily Rodriguez',
                'email' => 'emily.rodriguez@example.com',
                'phone' => '+1-555-0108',
                'address' => '258 Spruce Avenue, Denver, CO 80201',
                'is_active' => false,
            ],
            [
                'name' => 'David Martinez',
                'email' => 'david.martinez@example.com',
                'phone' => '+1-555-0109',
                'address' => '369 Willow Road, Boston, MA 02101',
                'is_active' => true,
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@example.com',
                'phone' => '+1-555-0110',
                'address' => '741 Poplar Drive, San Francisco, CA 94101',
                'is_active' => true,
            ],
            [
                'name' => 'Robert Taylor',
                'email' => 'robert.taylor@example.com',
                'phone' => '+1-555-0111',
                'address' => '852 Ash Boulevard, Dallas, TX 75201',
                'is_active' => true,
            ],
            [
                'name' => 'Jennifer Thomas',
                'email' => 'jennifer.thomas@example.com',
                'phone' => '+1-555-0112',
                'address' => '963 Hickory Court, Atlanta, GA 30301',
                'is_active' => false,
            ],
            [
                'name' => 'William Jackson',
                'email' => 'william.jackson@example.com',
                'phone' => '+1-555-0113',
                'address' => '159 Sycamore Place, Las Vegas, NV 89101',
                'is_active' => true,
            ],
            [
                'name' => 'Amanda White',
                'email' => 'amanda.white@example.com',
                'phone' => '+1-555-0114',
                'address' => '357 Magnolia Street, Portland, OR 97201',
                'is_active' => true,
            ],
            [
                'name' => 'Christopher Harris',
                'email' => 'christopher.harris@example.com',
                'phone' => '+1-555-0115',
                'address' => '468 Dogwood Lane, Nashville, TN 37201',
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
