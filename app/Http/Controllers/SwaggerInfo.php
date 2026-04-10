<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Mini CRM API',
    version: '1.0.0',
    description: 'REST API for the Mini CRM ticket system. Authenticated endpoints require a Bearer token from POST /api/auth/login.',
    contact: new OA\Contact(email: 'admin@example.com')
)]
#[OA\Server(url: '/api', description: 'API base path')]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer')]
#[OA\Tag(name: 'Auth',      description: 'Authentication')]
#[OA\Tag(name: 'Tickets',   description: 'Ticket management')]
#[OA\Tag(name: 'Customers', description: 'Customer management')]
#[OA\Tag(name: 'Users',     description: 'User management (admin only)')]
#[OA\Tag(name: 'Widget',    description: 'Public widget submission')]
#[OA\Schema(
    schema: 'Ticket',
    properties: [
        new OA\Property(property: 'id',             type: 'integer'),
        new OA\Property(property: 'customer_id',    type: 'integer'),
        new OA\Property(property: 'assigned_to',    type: 'integer',  nullable: true),
        new OA\Property(property: 'subject',        type: 'string'),
        new OA\Property(property: 'content',        type: 'string'),
        new OA\Property(property: 'status',         type: 'string',   enum: ['new', 'in_progress', 'completed']),
        new OA\Property(property: 'admin_response', type: 'string',   nullable: true),
        new OA\Property(property: 'responded_at',   type: 'string',   format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at',     type: 'string',   format: 'date-time'),
        new OA\Property(property: 'updated_at',     type: 'string',   format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Customer',
    properties: [
        new OA\Property(property: 'id',    type: 'integer'),
        new OA\Property(property: 'name',  type: 'string'),
        new OA\Property(property: 'email', type: 'string', nullable: true),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id',    type: 'integer'),
        new OA\Property(property: 'name',  type: 'string'),
        new OA\Property(property: 'email', type: 'string'),
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
    ]
)]
class SwaggerInfo {}
