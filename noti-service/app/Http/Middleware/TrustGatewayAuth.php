<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class TrustGatewayAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            Log::warning('Missing JWT token');
            return response()->json([
                'message' => 'Unauthorized - No token provided'
            ], 401);
        }

        try {
            $authUrl = config('services.auth.base_url') . config('services.auth.routes.validate');
            
            Log::info('Validating token with auth service', [
                'token' => substr($token, 0, 10) . '...',
                'auth_url' => $authUrl
            ]);

            // Validate token with auth service
            $response = Http::withOptions([
                'timeout' => 10,
                'verify' => false
            ])->withToken($token)
              ->get($authUrl);

            Log::info('Auth service response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if (!$response->successful()) {
                Log::warning('Token validation failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return response()->json([
                    'message' => 'Unauthorized - Invalid token'
                ], 401);
            }

            // Store user data in request
            $userData = $response->json();
            Log::info('User data from auth service', [
                'user' => $userData
            ]);
            
            $request->merge(['user' => $userData]);
            
            return $next($request);
            
        } catch (\Exception $e) {
            Log::error('Auth service error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Authentication service unavailable'
            ], 503);
        }
    }
}
