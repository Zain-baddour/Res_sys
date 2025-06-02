<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockedUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_blocked) {
            return response()->json([
                'message' => 'Your account has been blocked. Contact support for assistance.'
            ], 403); // Forbidden
        }

        return $next($request);
    }
}
