<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remove the X-Frame-Options header and set a permissive
 * Content-Security-Policy frame-ancestors directive so the widget
 * page can be embedded inside an <iframe> on any origin.
 */
class AllowIframeEmbedding
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->remove('X-Frame-Options');
        $response->headers->set('Content-Security-Policy', "frame-ancestors *");

        return $response;
    }
}
