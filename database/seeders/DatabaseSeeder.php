<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'name'      => 'Admin',
            'email'     => 'admin@example.com',
            'password'  => bcrypt('password'),
            'role'      => 'admin',
            'api_token' => Str::random(80),
        ]);

        // Operator user
        $operator = User::create([
            'name'      => 'Operator',
            'email'     => 'operator@example.com',
            'password'  => bcrypt('password'),
            'role'      => 'operator',
            'api_token' => Str::random(80),
        ]);

        // Demo customers
        $customer1 = Customer::create([
            'name'  => 'John Doe',
            'phone' => '+12025550100',
            'email' => 'john@example.com',
        ]);

        $customer2 = Customer::create([
            'name'  => 'Jane Smith',
            'phone' => '+447911123456',
            'email' => 'jane@example.com',
        ]);

        // Demo tickets
        Ticket::create([
            'customer_id' => $customer1->id,
            'assigned_to' => $operator->id,
            'subject'     => 'Cannot log in to my account',
            'content'     => 'I have been trying to log in for the past hour but keep getting an error.',
            'status'      => 'new',
        ]);

        Ticket::create([
            'customer_id'    => $customer2->id,
            'assigned_to'    => $operator->id,
            'subject'        => 'Billing question',
            'content'        => 'I was charged twice this month. Please help.',
            'status'         => 'in_progress',
            'admin_response' => 'We are looking into this. Please allow 24 hours.',
            'responded_at'   => now(),
        ]);

        Ticket::create([
            'customer_id'    => $customer1->id,
            'subject'        => 'Feature request: dark mode',
            'content'        => 'Would love to see a dark mode option in the app.',
            'status'         => 'completed',
            'admin_response' => 'Dark mode is now available in settings.',
            'responded_at'   => now()->subDays(2),
        ]);
    }
}
