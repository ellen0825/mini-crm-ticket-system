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
     * @OA\Post(
     *     path="/widget/submit",
     *     tags={"Widget"},
     *     summary="Submit a support request from the public widget (no auth required)",
     *     description="Limited to one submission per unique email or phone number per calendar day.",
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","subject","content"},
     *                 @OA\Property(property="name",    type="string",  example="John Doe"),
     *                 @OA\Property(property="email",   type="string",  example="john@example.com"),
     *                 @OA\Property(property="phone",   type="string",  example="+12025550100"),
     *                 @OA\Property(property="subject", type="string",  example="Login issue"),
     *                 @OA\Property(property="content", type="string",  example="I cannot log in."),
     *                 @OA\Property(property="files[]", type="array",   @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Ticket created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message",   type="string"),
     *             @OA\Property(property="ticket_id", type="integer"),
     *             @OA\Property(property="reference", type="string", example="TKT-00042")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=429, description="Rate limit exceeded — one submission per day")
     * )
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
            RateLimiter::hit($key, now()->secondsUntilEndOfDay());
        }
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
