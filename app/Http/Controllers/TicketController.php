<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class TicketController extends Controller
{
    #[OA\Get(
        path: '/tickets',
        tags: ['Tickets'],
        summary: 'List tickets (paginated). Admins see all; operators see only assigned.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false,
                schema: new OA\Schema(type: 'string', enum: ['new', 'in_progress', 'completed'])
            ),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
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

    #[OA\Get(
        path: '/tickets/statistics',
        tags: ['Tickets'],
        summary: 'Ticket statistics — daily, weekly, monthly, all-time',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Statistics breakdown'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
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
            'daily'    => $breakdown('daily'),
            'weekly'   => $breakdown('weekly'),
            'monthly'  => $breakdown('monthly'),
            'all_time' => [
                'total'       => Ticket::count(),
                'new'         => Ticket::ofStatus('new')->count(),
                'in_progress' => Ticket::ofStatus('in_progress')->count(),
                'completed'   => Ticket::ofStatus('completed')->count(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/tickets/{id}',
        tags: ['Tickets'],
        summary: 'Get a single ticket with attachments',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Ticket detail'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
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

    #[OA\Post(
        path: '/tickets',
        tags: ['Tickets'],
        summary: 'Create a new ticket',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['customer_id', 'subject', 'content'],
                properties: [
                    new OA\Property(property: 'customer_id', type: 'integer'),
                    new OA\Property(property: 'subject',     type: 'string'),
                    new OA\Property(property: 'content',     type: 'string'),
                    new OA\Property(property: 'assigned_to', type: 'integer'),
                ]
            )
        )),
        responses: [
            new OA\Response(response: 201, description: 'Ticket created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

    #[OA\Post(
        path: '/tickets/{id}',
        tags: ['Tickets'],
        summary: 'Update a ticket (POST used to support multipart file uploads)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Updated ticket'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
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

    #[OA\Delete(
        path: '/tickets/{id}',
        tags: ['Tickets'],
        summary: 'Delete a ticket (admin only)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        if (! $request->attributes->get('api_user')->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $ticket->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    #[OA\Delete(
        path: '/tickets/{id}/attachments/{mediaId}',
        tags: ['Tickets'],
        summary: 'Delete a single attachment',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id',      in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'mediaId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Deleted'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
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
