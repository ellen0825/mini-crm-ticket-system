<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('api_user');

        $query = Ticket::with(['customer', 'assignedTo'])
            ->orderByDesc('created_at');

        // Operators only see tickets assigned to them
        if (! $user->isAdmin()) {
            $query->where('assigned_to', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function show(Request $request, Ticket $ticket)
    {
        $user = $request->attributes->get('api_user');

        if (! $user->isAdmin() && $ticket->assigned_to !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $ticket->load(['customer', 'assignedTo']);
        $ticket->attachments = $ticket->getMedia('attachments')->map(fn ($m) => [
            'id'       => $m->id,
            'name'     => $m->file_name,
            'url'      => $m->getUrl(),
            'mime'     => $m->mime_type,
            'size'     => $m->size,
        ]);

        return response()->json($ticket);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'subject'     => ['required', 'string', 'max:255'],
            'content'     => ['required', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'files'       => ['nullable', 'array'],
            'files.*'     => ['file', 'max:10240'], // 10 MB per file
        ]);

        $ticket = Ticket::create([
            'customer_id' => $validated['customer_id'],
            'subject'     => $validated['subject'],
            'content'     => $validated['content'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'status'      => 'new',
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $ticket->addMedia($file)->toMediaCollection('attachments');
            }
        }

        $ticket->load('customer');

        return response()->json($ticket, Response::HTTP_CREATED);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $user = $request->attributes->get('api_user');

        if (! $user->isAdmin() && $ticket->assigned_to !== $user->id) {
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

        // Track when admin first responds
        if (
            isset($validated['admin_response']) &&
            $validated['admin_response'] !== null &&
            $ticket->responded_at === null
        ) {
            $validated['responded_at'] = now();
        }

        $ticket->update($validated);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $ticket->addMedia($file)->toMediaCollection('attachments');
            }
        }

        return response()->json($ticket->fresh(['customer', 'assignedTo']));
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        $user = $request->attributes->get('api_user');

        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $ticket->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteAttachment(Request $request, Ticket $ticket, int $mediaId)
    {
        $user = $request->attributes->get('api_user');

        if (! $user->isAdmin() && $ticket->assigned_to !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $media = $ticket->getMedia('attachments')->firstWhere('id', $mediaId);

        if (! $media) {
            return response()->json(['message' => 'Attachment not found.'], Response::HTTP_NOT_FOUND);
        }

        $media->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
