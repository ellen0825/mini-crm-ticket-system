<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $user  = $token ? User::where('api_token', $token)->first() : null;

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        // Make the authenticated user available to the rest of the pipeline
        // and to Laravel's auth() helper via the request user resolver.
        $request->attributes->set('api_user', $user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
