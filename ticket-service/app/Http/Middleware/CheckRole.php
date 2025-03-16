<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    private array $rolePermissions = [
        'admin' => ['view', 'purchase', 'validate', 'cancel'],
        'operator' => ['view', 'validate'],
        'user' => ['view', 'purchase', 'cancel']
    ];

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->get('user');
        
        if (!$user || !isset($user['role'])) {
            Log::warning('Missing user data in request', [
                'user' => $user
            ]);
            return response()->json([
                'message' => 'Unauthorized - Invalid user data'
            ], 401);
        }

        $userRole = $user['role'];

        // Check if role exists and has required permission
        if (!isset($this->rolePermissions[$userRole]) || 
            !in_array($permission, $this->rolePermissions[$userRole])) {
            Log::warning('Insufficient permissions', [
                'role' => $userRole,
                'required_permission' => $permission
            ]);
            return response()->json([
                'message' => 'Forbidden - Insufficient permissions'
            ], 403);
        }

        return $next($request);
    }
}
