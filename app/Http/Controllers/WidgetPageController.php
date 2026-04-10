<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WidgetPageController extends Controller
{
    /**
     * The embeddable widget form — rendered inside an <iframe>.
     */
    public function show(): View
    {
        return view('widget');
    }

    /**
     * A standalone page that shows the copy-paste <iframe> embed snippet.
     */
    public function embed(): View
    {
        $widgetUrl = url('/widget');

        return view('widget-embed', compact('widgetUrl'));
    }
}
