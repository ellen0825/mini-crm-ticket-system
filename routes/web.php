<?php

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
