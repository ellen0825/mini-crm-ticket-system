<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    private function adminHeaders(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return ['Authorization' => 'Bearer ' . $admin->api_token];
    }

    private function operatorHeaders(User &$operator = null): array
    {
        $operator = User::factory()->create();
        $operator->assignRole('operator');

        return ['Authorization' => 'Bearer ' . $operator->api_token];
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/tickets')->assertUnauthorized();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_admin_can_create_ticket(): void
    {
        $customer = Customer::factory()->create();

        $this->postJson('/api/tickets', [
            'customer_id' => $customer->id,
            'subject'     => 'New ticket',
            'content'     => 'Some content here.',
        ], $this->adminHeaders())
        ->assertCreated()
        ->assertJsonPath('subject', 'New ticket')
        ->assertJsonPath('status', 'new');
    }

    public function test_create_ticket_requires_customer_id_subject_content(): void
    {
        $this->postJson('/api/tickets', [], $this->adminHeaders())
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['customer_id', 'subject', 'content']);
    }

    // ── List ──────────────────────────────────────────────────────────────────

    public function test_admin_sees_all_tickets(): void
    {
        Ticket::factory()->count(3)->create();

        $this->getJson('/api/tickets', $this->adminHeaders())
             ->assertOk()
             ->assertJsonPath('total', 3);
    }

    public function test_operator_sees_only_assigned_tickets(): void
    {
        $operator = null;
        $headers  = $this->operatorHeaders($operator);

        Ticket::factory()->count(2)->create(['assigned_to' => $operator->id]);
        Ticket::factory()->count(3)->create(['assigned_to' => null]);

        $this->getJson('/api/tickets', $headers)
             ->assertOk()
             ->assertJsonPath('total', 2);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_admin_can_view_any_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->getJson("/api/tickets/{$ticket->id}", $this->adminHeaders())
             ->assertOk()
             ->assertJsonPath('id', $ticket->id);
    }

    public function test_operator_cannot_view_unassigned_ticket(): void
    {
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        $this->getJson("/api/tickets/{$ticket->id}", $this->operatorHeaders())
             ->assertForbidden();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_admin_can_update_ticket_status(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'new']);

        $this->postJson("/api/tickets/{$ticket->id}", [
            'status' => 'in_progress',
        ], $this->adminHeaders())
        ->assertOk()
        ->assertJsonPath('status', 'in_progress');
    }

    public function test_responded_at_is_set_on_first_admin_response(): void
    {
        $ticket = Ticket::factory()->create(['admin_response' => null, 'responded_at' => null]);

        $this->postJson("/api/tickets/{$ticket->id}", [
            'admin_response' => 'We are looking into this.',
        ], $this->adminHeaders())->assertOk();

        $this->assertNotNull($ticket->fresh()->responded_at);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_admin_can_delete_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->deleteJson("/api/tickets/{$ticket->id}", [], $this->adminHeaders())
             ->assertNoContent();

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_operator_cannot_delete_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->deleteJson("/api/tickets/{$ticket->id}", [], $this->operatorHeaders())
             ->assertForbidden();
    }

    // ── Statistics ────────────────────────────────────────────────────────────

    public function test_statistics_returns_correct_structure(): void
    {
        Ticket::factory()->statusNew()->count(2)->create();
        Ticket::factory()->inProgress()->count(1)->create();

        $this->getJson('/api/tickets/statistics', $this->adminHeaders())
             ->assertOk()
             ->assertJsonStructure([
                 'daily'    => ['total', 'new', 'in_progress', 'completed'],
                 'weekly'   => ['total', 'new', 'in_progress', 'completed'],
                 'monthly'  => ['total', 'new', 'in_progress', 'completed'],
                 'all_time' => ['total', 'new', 'in_progress', 'completed'],
             ]);
    }

    public function test_statistics_counts_are_accurate(): void
    {
        Ticket::factory()->statusNew()->count(3)->create();
        Ticket::factory()->completed()->count(2)->create();

        $response = $this->getJson('/api/tickets/statistics', $this->adminHeaders())
                         ->assertOk();

        $this->assertEquals(5, $response->json('all_time.total'));
        $this->assertEquals(3, $response->json('all_time.new'));
        $this->assertEquals(2, $response->json('all_time.completed'));
    }
}
