<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WidgetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Universal website widget — no authentication required
Route::post('/widget/submit', [WidgetController::class, 'submit']);

/*
|--------------------------------------------------------------------------
| Authenticated routes (any role)
|--------------------------------------------------------------------------
*/

Route::middleware('auth.api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Tickets
    Route::get('/tickets',                                      [TicketController::class, 'index']);
    Route::post('/tickets',                                     [TicketController::class, 'store']);
    Route::get('/tickets/{ticket}',                             [TicketController::class, 'show']);
    Route::post('/tickets/{ticket}',                            [TicketController::class, 'update']); // POST for multipart/form-data
    Route::delete('/tickets/{ticket}',                          [TicketController::class, 'destroy']);
    Route::delete('/tickets/{ticket}/attachments/{mediaId}',    [TicketController::class, 'deleteAttachment']);

    // Customers
    Route::get('/customers',              [CustomerController::class, 'index']);
    Route::post('/customers',             [CustomerController::class, 'store']);
    Route::get('/customers/{customer}',   [CustomerController::class, 'show']);
    Route::put('/customers/{customer}',   [CustomerController::class, 'update']);
    Route::delete('/customers/{customer}',[CustomerController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Admin-only routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.api', 'role:admin'])->group(function () {
    // User management
    Route::get('/users',          [UserController::class, 'index']);
    Route::get('/users/{user}',   [UserController::class, 'show']);
    Route::put('/users/{user}',   [UserController::class, 'update']);
    Route::delete('/users/{user}',[UserController::class, 'destroy']);

    // Role assignment
    Route::put('/users/{user}/roles', [RoleController::class, 'sync']);
});
