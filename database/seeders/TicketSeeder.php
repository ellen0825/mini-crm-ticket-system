<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $operators = User::role('operator')->get();
        $customers = Customer::all();

        // Guarantee one ticket per status so the admin always has something to see
        Ticket::factory()->statusNew()->create([
            'customer_id' => $customers->random()->id,
            'assigned_to' => $operators->random()->id,
            'subject'     => 'Cannot log in to my account',
            'content'     => 'I have been trying to log in for the past hour but keep getting an error.',
        ]);

        Ticket::factory()->inProgress()->create([
            'customer_id'    => $customers->random()->id,
            'assigned_to'    => $operators->random()->id,
            'subject'        => 'Billing discrepancy',
            'content'        => 'I was charged twice this month. Please investigate.',
            'admin_response' => 'We are looking into this. Please allow 24 hours.',
        ]);

        Ticket::factory()->completed()->create([
            'customer_id'    => $customers->random()->id,
            'subject'        => 'Feature request: dark mode',
            'content'        => 'Would love to see a dark mode option in the app.',
            'admin_response' => 'Dark mode is now available in settings.',
        ]);

        // Bulk random tickets distributed across all customers
        foreach ($customers as $customer) {
            Ticket::factory()
                ->count(rand(1, 4))
                ->create([
                    'customer_id' => $customer->id,
                    'assigned_to' => rand(0, 1) ? $operators->random()->id : null,
                ]);
        }
    }
}
