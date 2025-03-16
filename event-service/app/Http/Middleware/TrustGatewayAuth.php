<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TrustGatewayAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized - No token provided'], 401);
        }

        try {
            // Validate token with auth service
            $authResponse = Http::withToken($token)
                ->get(config('services.auth.url') . '/api/user');

            if (!$authResponse->successful()) {
                return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
            }

            $userData = $authResponse->json();
            
            // Find or create user based on auth service data
            $user = User::firstOrCreate(
                ['external_user_id' => $userData['id']],
                [
                    'name' => $userData['name'] ?? 'User ' . $userData['id'],
                    'email' => $userData['email'] ?? $userData['id'] . '@temp.com',
                    'password' => bcrypt(uniqid()),
                    'role' => strtolower($userData['role'])
                ]
            );

            Auth::login($user);
            
            return $next($request);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Authentication failed'], 500);
        }
    }
}
