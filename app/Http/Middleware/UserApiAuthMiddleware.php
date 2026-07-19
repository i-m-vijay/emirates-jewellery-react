<?php

namespace App\Http\Middleware;

use App\Services\UserAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserApiAuthMiddleware
{
    public function __construct(private readonly UserAuthService $authService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please login.',
            ], 401);
        }

        $user = $this->authService->findByToken($token);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Attach the user to the request so controllers can access it
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
