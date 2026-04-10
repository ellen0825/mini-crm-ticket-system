<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WidgetController extends Controller
{
    /**
     * Public endpoint — no authentication required.
     * Accepts a ticket submission from an external website widget.
     * Finds or creates the customer by email/phone, then creates the ticket.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'regex:/^\+[1-9]\d{1,14}$/'],
            'email'   => ['nullable', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'files'   => ['nullable', 'array'],
            'files.*' => ['file', 'max:10240'],
        ]);

        // Resolve or create the customer record
        $customer = $this->resolveCustomer($validated);

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject'     => $validated['subject'],
            'content'     => $validated['content'],
            'status'      => 'new',
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $ticket->addMedia($file)->toMediaCollection('attachments');
            }
        }

        return response()->json([
            'message'   => 'Your request has been submitted. We will get back to you shortly.',
            'ticket_id' => $ticket->id,
        ], Response::HTTP_CREATED);
    }

    private function resolveCustomer(array $data): Customer
    {
        // Try to match by email first, then phone
        if (! empty($data['email'])) {
            $customer = Customer::where('email', $data['email'])->first();
            if ($customer) {
                return $customer;
            }
        }

        if (! empty($data['phone'])) {
            $customer = Customer::where('phone', $data['phone'])->first();
            if ($customer) {
                return $customer;
            }
        }

        return Customer::create([
            'name'  => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }
}
