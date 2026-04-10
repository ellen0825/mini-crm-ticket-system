<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WidgetPageController extends Controller
{
    public function show(): View
    {
        return view('widget');
    }
}
