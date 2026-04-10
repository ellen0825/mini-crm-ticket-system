<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WidgetController extends Controller
{
    /**
     * Public REST endpoint consumed by the /widget Blade page via AJAX.
     * No authentication required — resolves or creates the customer record,
     * then opens a new ticket and attaches any uploaded files.
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'regex:/^\+[1-9]\d{1,14}$/'],
            'email'   => ['nullable', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'files'   => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip'],
        ]);

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
            'reference' => 'TKT-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT),
        ], Response::HTTP_CREATED);
    }

    private function resolveCustomer(array $data): Customer
    {
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
