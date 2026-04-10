<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TicketController extends Controller
{
    // ── List ─────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user  = $request->attributes->get('api_user');
        $query = Ticket::with(['customer', 'assignedTo'])->orderByDesc('created_at');

        if (! $user->hasRole('admin')) {
            $query->where('assigned_to', $user->id);
        }

        if ($request->filled('status')) {
            $query->ofStatus($request->status);
        }

        return response()->json($query->paginate(20));
    }

    // ── Statistics (admin only) ───────────────────────────────────────────────

    public function statistics(): JsonResponse
    {
        $statuses = ['new', 'in_progress', 'completed'];

        $breakdown = function (string $scope) use ($statuses): array {
            $base = Ticket::{$scope}();
            $row  = ['total' => (clone $base)->count()];

            foreach ($statuses as $s) {
                $row[$s] = (clone $base)->ofStatus($s)->count();
            }

            return $row;
        };

        return response()->json([
            'daily'   => $breakdown('daily'),
            'weekly'  => $breakdown('weekly'),
            'monthly' => $breakdown('monthly'),
            'all_time' => [
                'total'       => Ticket::count(),
                'new'         => Ticket::ofStatus('new')->count(),
                'in_progress' => Ticket::ofStatus('in_progress')->count(),
                'completed'   => Ticket::ofStatus('completed')->count(),
            ],
        ]);
    }

    // ── Show ─────────────────────────────────────────────────────────────────

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->attributes->get('api_user');

        if (! $user->hasRole('admin') && $ticket->assigned_to !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $ticket->load(['customer', 'assignedTo']);
        $ticket->attachments = $ticket->getMedia('attachments')->map(fn ($m) => [
            'id'   => $m->id,
            'name' => $m->file_name,
            'url'  => $m->getUrl(),
            'mime' => $m->mime_type,
            'size' => $m->size,
        ]);

        return response()->json($ticket);
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'subject'     => ['required', 'string', 'max:255'],
            'content'     => ['required', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'files'       => ['nullable', 'array'],
            'files.*'     => ['file', 'max:10240'],
        ]);

        $ticket = Ticket::create([
            'customer_id' => $validated['customer_id'],
            'subject'     => $validated['subject'],
            'content'     => $validated['content'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'status'      => 'new',
        ]);

        $this->attachFiles($request, $ticket);

        return response()->json($ticket->load('customer'), Response::HTTP_CREATED);
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->attributes->get('api_user');

        if (! $user->hasRole('admin') && $ticket->assigned_to !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'subject'        => ['sometimes', 'string', 'max:255'],
            'content'        => ['sometimes', 'string'],
            'status'         => ['sometimes', 'in:new,in_progress,completed'],
            'admin_response' => ['nullable', 'string'],
            'assigned_to'    => ['nullable', 'exists:users,id'],
            'files'          => ['nullable', 'array'],
            'files.*'        => ['file', 'max:10240'],
        ]);

        if (! empty($validated['admin_response']) && $ticket->responded_at === null) {
            $validated['responded_at'] = now();
        }

        $ticket->update($validated);
        $this->attachFiles($request, $ticket);

        return response()->json($ticket->fresh(['customer', 'assignedTo']));
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        if (! $request->attributes->get('api_user')->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $ticket->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    // ── Delete attachment ─────────────────────────────────────────────────────

    public function deleteAttachment(Request $request, Ticket $ticket, int $mediaId): JsonResponse
    {
        $user = $request->attributes->get('api_user');

        if (! $user->hasRole('admin') && $ticket->assigned_to !== $user->id) {
            return response()->json(['message' => 'Attachment not found.'], Response::HTTP_NOT_FOUND);
        }

        $media = $ticket->getMedia('attachments')->firstWhere('id', $mediaId);

        if (! $media) {
            return response()->json(['message' => 'Attachment not found.'], Response::HTTP_NOT_FOUND);
        }

        $media->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function attachFiles(Request $request, Ticket $ticket): void
    {
        if (! $request->hasFile('files')) {
            return;
        }

        foreach ($request->file('files') as $file) {
            $ticket->addMedia($file)->toMediaCollection('attachments');
        }
    }
}
