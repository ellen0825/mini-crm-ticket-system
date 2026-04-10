<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->attributes->get('api_user');
        $query = Ticket::with(['customer', 'assignedTo'])->orderByDesc('created_at');

        // Operators only see tickets assigned to them
        if (! $user->hasRole('admin')) {
            $query->where('assigned_to', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

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

    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        if (! $request->attributes->get('api_user')->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $ticket->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteAttachment(Request $request, Ticket $ticket, int $mediaId): JsonResponse
    {
        $user = $request->attributes->get('api_user');

        if (! $user->hasRole('admin') && $ticket->assigned_to !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $media = $ticket->getMedia('attachments')->firstWhere('id', $mediaId);

        if (! $media) {
            return response()->json(['message' => 'Attachment not found.'], Response::HTTP_NOT_FOUND);
        }

        $media->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

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
