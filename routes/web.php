<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\WidgetPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// The widget itself — allow embedding in any <iframe>
Route::get('/widget', [WidgetPageController::class, 'show'])
    ->middleware('allow.iframe')
    ->name('widget');

// Embed snippet page — shows the copy-paste <iframe> code
Route::get('/widget/embed', [WidgetPageController::class, 'embed'])
    ->name('widget.embed');

// ── Admin auth ────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');

    // Protected admin routes
    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/tickets',                          [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}',                 [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::patch('/tickets/{ticket}/status',        [AdminTicketController::class, 'updateStatus'])->name('tickets.status');
    });
});
