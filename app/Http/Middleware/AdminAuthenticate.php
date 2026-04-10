<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects admin web routes using session-based authentication.
 * Redirects unauthenticated visitors to the admin login page.
 */
class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('admin_user_id')) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
