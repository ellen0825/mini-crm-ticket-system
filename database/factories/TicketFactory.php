<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    private static array $subjects = [
        'Cannot log in to my account',
        'Payment not processed',
        'Refund request',
        'Product not delivered',
        'Wrong item received',
        'Account suspended without reason',
        'Subscription cancellation request',
        'Technical issue with the app',
        'Password reset not working',
        'Billing discrepancy',
        'Feature request',
        'General inquiry',
        'Slow response times',
        'Data export request',
        'Integration not working',
    ];

    public function definition(): array
    {
        $status = fake()->randomElement(['new', 'in_progress', 'completed']);

        $adminResponse = null;
        $respondedAt   = null;

        if (in_array($status, ['in_progress', 'completed'])) {
            $adminResponse = fake()->paragraph();
            $respondedAt   = fake()->dateTimeBetween('-30 days', 'now');
        }

        return [
            'customer_id'    => Customer::factory(),
            'assigned_to'    => null,
            'subject'        => fake()->randomElement(self::$subjects),
            'content'        => fake()->paragraphs(2, true),
            'status'         => $status,
            'admin_response' => $adminResponse,
            'responded_at'   => $respondedAt,
        ];
    }

    public function statusNew(): static
    {
        return $this->state([
            'status'         => 'new',
            'admin_response' => null,
            'responded_at'   => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state([
            'status'         => 'in_progress',
            'admin_response' => fake()->paragraph(),
            'responded_at'   => now()->subHours(fake()->numberBetween(1, 72)),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status'         => 'completed',
            'admin_response' => fake()->paragraph(),
            'responded_at'   => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(['assigned_to' => $user->id]);
    }
}
