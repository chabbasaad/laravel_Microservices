<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get user role and ID from headers
        $userRole = strtolower($request->header('X-User-Role'));
        $userId = $request->header('X-User-Id');

        // Log for debugging
        Log::info('Received user headers in User Service', [
            'role' => $userRole,
            'user_id' => $userId,
            'path' => $request->path(),
            'all_headers' => $request->headers->all()
        ]);

        // Validate role exists
        if (empty($userRole)) {
            Log::warning('Missing user role in request', [
                'headers' => $request->headers->all()
            ]);
            return response()->json(['message' => 'Unauthorized - Missing role'], 401);
        }

        // Validate role is valid
        if (!in_array($userRole, ['admin', 'event_creator', 'operator', 'user'])) {
            Log::warning('Invalid user role', [
                'role' => $userRole,
                'headers' => $request->headers->all()
            ]);
            return response()->json(['message' => 'Unauthorized - Invalid role'], 401);
        }

        // Add role and user ID to request for controllers
        $request->merge([
            'user_role' => $userRole,
            'authenticated_user_id' => $userId
        ]);

        // Log successful validation
        Log::info('User role validated', [
            'role' => $userRole,
            'user_id' => $userId,
            'path' => $request->path()
        ]);

        return $next($request);
    }
}
