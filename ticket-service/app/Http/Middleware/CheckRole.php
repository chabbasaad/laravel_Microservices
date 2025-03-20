<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    private array $rolePermissions = [
        'admin' => ['view', 'purchase', 'validate', 'cancel', 'admin', 'event_creator'],
        'operator' => ['view', 'validate'],
        'user' => ['view', 'purchase', 'cancel'],
        'event_creator' => ['view', 'validate', 'event_creator']
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

        $userRole = strtolower($user['role']);

        // For comma-separated permissions, check if user has ANY of the permissions
        $requiredPermissions = explode(',', $permission);
        $hasPermission = false;

        foreach ($requiredPermissions as $reqPermission) {
            $reqPermission = trim($reqPermission);
            // If the user's role directly matches the required permission
            if ($userRole === $reqPermission) {
                $hasPermission = true;
                break;
            }
            // Or if the user's role has the required permission
            if (isset($this->rolePermissions[$userRole]) && 
                in_array($reqPermission, $this->rolePermissions[$userRole])) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            Log::warning('Insufficient permissions', [
                'role' => $userRole,
                'required_permissions' => $requiredPermissions
            ]);
            return response()->json([
                'message' => 'Forbidden - Insufficient permissions'
            ], 403);
        }

        return $next($request);
    }
}
