<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

class WidgetController extends Controller
{
    /**
     * Public REST endpoint consumed by the /widget Blade page via AJAX.
     * Limited to one submission per unique email/phone per calendar day.
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

        if ($this->isRateLimited($validated)) {
            return response()->json([
                'message' => 'You have already submitted a request today. Please try again tomorrow.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

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

        $this->hitRateLimiter($validated);

        return response()->json([
            'message'   => 'Your request has been submitted. We will get back to you shortly.',
            'ticket_id' => $ticket->id,
            'reference' => 'TKT-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT),
        ], Response::HTTP_CREATED);
    }

    // ── Rate limiting ─────────────────────────────────────────────────────────

    /**
     * Build a deterministic daily rate-limit key from the contact identifiers.
     * At least one of email or phone must be present for a key to be generated.
     */
    private function rateLimitKey(array $data): ?string
    {
        $identifier = null;

        if (! empty($data['email'])) {
            $identifier = 'email:' . strtolower(trim($data['email']));
        } elseif (! empty($data['phone'])) {
            $identifier = 'phone:' . trim($data['phone']);
        }

        if ($identifier === null) {
            return null;
        }

        // Rotate key daily so the limit resets at midnight
        return 'widget_submit:' . $identifier . ':' . now()->toDateString();
    }

    private function isRateLimited(array $data): bool
    {
        $key = $this->rateLimitKey($data);

        return $key !== null && RateLimiter::tooManyAttempts($key, 1);
    }

    private function hitRateLimiter(array $data): void
    {
        $key = $this->rateLimitKey($data);

        if ($key !== null) {
            // Decay until end of current calendar day
            $secondsUntilMidnight = now()->secondsUntilEndOfDay();
            RateLimiter::hit($key, $secondsUntilMidnight);
        }
    }

    // ── Customer resolution ───────────────────────────────────────────────────

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
