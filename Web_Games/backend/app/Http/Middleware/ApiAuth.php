<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Header tidak ada
        if (!$request->bearerToken()) {
            return response()->json([
                "status" => "unauthenticated",
                "message" => "Missing token"
            ], 401);
        }

        // 2. Cari token
        $token = PersonalAccessToken::findToken($request->bearerToken());

        if (!$token) {
            return response()->json([
                "status" => "unauthenticated",
                "message" => "Invalid token"
            ], 401);
        }

        $user = $token->tokenable;

        // 3. User diblokir
        if ($user->delete_reason !== null) {
            return response()->json([
                "status" => "blocked",
                "message" => "User blocked",
                "reason" => $user->delete_reason
            ], 403);
        }

        // inject user ke request
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
