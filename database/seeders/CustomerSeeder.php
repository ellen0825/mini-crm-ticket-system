<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // A handful of fixed customers for predictable test data
        $fixed = [
            ['name' => 'John Doe',      'phone' => '+12025550100', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith',    'phone' => '+447911123456', 'email' => 'jane@example.com'],
            ['name' => 'Carlos Rivera', 'phone' => '+34612345678',  'email' => 'carlos@example.com'],
            ['name' => 'Yuki Tanaka',   'phone' => '+819012345678', 'email' => 'yuki@example.com'],
        ];

        foreach ($fixed as $data) {
            Customer::create($data);
        }

        // Bulk random customers
        Customer::factory()->count(16)->create();
    }
}
