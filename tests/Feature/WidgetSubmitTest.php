<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class WidgetSubmitTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name'    => 'John Doe',
        'email'   => 'john@example.com',
        'phone'   => '+12025550100',
        'subject' => 'Test subject',
        'content' => 'Test message content.',
    ];

    public function test_guest_can_submit_widget_form(): void
    {
        $response = $this->postJson('/api/widget/submit', $this->payload);

        $response->assertCreated()
                 ->assertJsonStructure(['message', 'ticket_id', 'reference']);

        $this->assertDatabaseHas('tickets', ['subject' => 'Test subject', 'status' => 'new']);
        $this->assertDatabaseHas('customers', ['email' => 'john@example.com']);
    }

    public function test_submit_creates_customer_if_not_exists(): void
    {
        $this->assertDatabaseCount('customers', 0);

        $this->postJson('/api/widget/submit', $this->payload)->assertCreated();

        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('customers', ['name' => 'John Doe']);
    }

    public function test_submit_reuses_existing_customer_by_email(): void
    {
        Customer::factory()->create(['email' => 'john@example.com', 'name' => 'Old Name']);

        $this->postJson('/api/widget/submit', $this->payload)->assertCreated();

        // No new customer should be created
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_submit_requires_name_subject_and_content(): void
    {
        $this->postJson('/api/widget/submit', [])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['name', 'subject', 'content']);
    }

    public function test_phone_must_be_e164_format(): void
    {
        $this->postJson('/api/widget/submit', array_merge($this->payload, ['phone' => '0123456789']))
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['phone']);
    }

    public function test_second_submission_same_day_is_rejected(): void
    {
        RateLimiter::clear('widget_submit:email:john@example.com:' . now()->toDateString());

        $this->postJson('/api/widget/submit', $this->payload)->assertCreated();
        $this->postJson('/api/widget/submit', $this->payload)->assertStatus(429);
    }

    public function test_reference_format_is_correct(): void
    {
        $response = $this->postJson('/api/widget/submit', $this->payload)->assertCreated();

        $ticketId  = $response->json('ticket_id');
        $reference = $response->json('reference');

        $this->assertEquals('TKT-' . str_pad($ticketId, 5, '0', STR_PAD_LEFT), $reference);
    }
}
