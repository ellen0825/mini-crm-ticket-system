<?php

use App\Http\Controllers\WidgetPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/widget', [WidgetPageController::class, 'show'])->name('widget');
