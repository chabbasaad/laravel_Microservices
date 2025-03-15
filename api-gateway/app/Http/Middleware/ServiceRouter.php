<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ServiceRegistry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ServiceRouter
{
    protected ServiceRegistry $serviceRegistry;

    public function __construct(ServiceRegistry $serviceRegistry)
    {
        $this->serviceRegistry = $serviceRegistry;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $service = $this->determineService($path);
        
        Log::info('Handling request', [
            'path' => $path,
            'service' => $service
        ]);

        if (!$service || !$this->serviceRegistry->isValidService($service)) {
            Log::warning('Invalid service requested', ['path' => $path]);
            return response()->json(['error' => 'Service not found'], 404);
        }

        $serviceUrl = $this->serviceRegistry->getServiceUrl($service);
        $targetPath = $this->getTargetPath($path, $service);
        $fullUrl = rtrim($serviceUrl, '/') . '/api/' . ltrim($targetPath, '/');

        // For auth service requests, just forward them as is
        if ($service === 'auth') {
            return $this->forwardRequest($request, $fullUrl);
        }

        // For other services, validate token with auth service first
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Unauthorized - No token provided'], 401);
        }

        try {
            // Call auth service to validate token and get user info
            $authResponse = Http::withToken($token)
                ->get($this->serviceRegistry->getServiceUrl('auth') . '/api/user');

            if (!$authResponse->successful()) {
                return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
            }

            $userData = $authResponse->json();
            
            // Add user role and ID headers for downstream services
            $request->headers->set('X-User-Role', strtolower($userData['role'] ?? ''));
            $request->headers->set('X-User-Id', (string)($userData['id'] ?? ''));

            Log::info('User authenticated', [
                'role' => $userData['role'] ?? null,
                'user_id' => $userData['id'] ?? null,
                'service' => $service
            ]);

            return $this->forwardRequest($request, $fullUrl);

        } catch (\Exception $e) {
            Log::error('Auth validation failed', [
                'error' => $e->getMessage(),
                'service' => $service
            ]);
            return response()->json(['message' => 'Authentication failed'], 401);
        }
    }

    protected function forwardRequest(Request $request, string $url): Response
    {
        try {
            $headers = $this->getForwardableHeaders($request);
            
            Log::info('Forwarding request', [
                'from' => $request->fullUrl(),
                'to' => $url,
                'method' => $request->method(),
                'headers' => $headers
            ]);

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->withToken($request->bearerToken())
                ->send(
                    $request->method(),
                    $url,
                    [
                        'query' => $request->query(),
                        'json' => $request->json()->all(),
                    ]
                );

            Log::info('Service response received', [
                'status' => $response->status(),
                'url' => $url
            ]);

            return response($response->body(), $response->status())
                ->withHeaders($this->getForwardableResponseHeaders($response));

        } catch (\Exception $e) {
            Log::error('Service request failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Service unavailable',
                'message' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 503);
        }
    }

    protected function determineService(string $path): ?string
    {
        $parts = explode('/', $path);
        // api/v1/service-name/...
        return count($parts) >= 3 ? $parts[2] : null;
    }

    protected function getTargetPath(string $path, string $service): string
    {
        $parts = explode('/', $path);
        // Skip api/v1/service-name
        return implode('/', array_slice($parts, 3));
    }

    protected function getForwardableHeaders(Request $request): array
    {
        $headers = [];
        foreach ($this->getForwardableHeaderNames() as $header) {
            if ($request->header($header)) {
                $headers[$header] = $request->header($header);
            }
        }

        // Always forward role and user ID if present
        if ($request->header('X-User-Role')) {
            $headers['X-User-Role'] = $request->header('X-User-Role');
        }
        if ($request->header('X-User-Id')) {
            $headers['X-User-Id'] = $request->header('X-User-Id');
        }

        return $headers;
    }

    protected function getForwardableResponseHeaders($response): array
    {
        $headers = [];
        foreach ($this->getForwardableHeaderNames() as $header) {
            if ($response->header($header)) {
                $headers[$header] = $response->header($header);
            }
        }
        return $headers;
    }

    protected function getForwardableHeaderNames(): array
    {
        return [
            'accept',
            'content-type',
            'authorization',
            'x-requested-with',
            'x-correlation-id',
            'x-user-role',
            'x-user-id'
        ];
    }
}
