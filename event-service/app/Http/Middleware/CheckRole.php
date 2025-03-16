<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    protected $rolePermissions = [
        'admin' => ['view', 'create', 'update', 'delete'],
        'event_creator' => ['view', 'create', 'update', 'delete'],
        'operator' => ['view'],
        'user' => ['view']
    ];

    public function handle(Request $request, Closure $next, string $action)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userRole = strtolower($user->role);
        
        // If viewing events, allow any authenticated user
        if ($action === 'view') {
            return $next($request);
        }
        
        // For other actions, check specific role permissions
        if (!isset($this->rolePermissions[$userRole])) {
            return response()->json(['message' => 'Invalid role'], 403);
        }

        if (!in_array($action, $this->rolePermissions[$userRole])) {
            return response()->json([
                'message' => "User with role '{$userRole}' is not authorized to {$action} events"
            ], 403);
        }

        return $next($request);
    }
}
