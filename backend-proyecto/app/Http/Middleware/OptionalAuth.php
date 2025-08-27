<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class OptionalAuth
{
    public function handle(Request $request, Closure $next)
    {
       if ($token = $request->bearerToken()) {
            try {
                $accessToken = PersonalAccessToken::findToken($token);
                if ($accessToken && $accessToken->tokenable) {
                    $request->setUserResolver(fn() => $accessToken->tokenable);
                }
            } catch (\Exception $e) {
            }
        }

        return $next($request);
    }
}