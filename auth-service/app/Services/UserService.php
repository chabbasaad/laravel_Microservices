<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.user.base_url', 'http://localhost:8001');
        Log::info('UserService initialized with base URL: ' . $this->baseUrl);
    }

    /**
     * Create a new user in the User Service
     */
    public function createUser(array $userData): Response
    {
        $url = "{$this->baseUrl}/api/users";
        Log::info('Attempting to create user in User Service', [
            'url' => $url,
            'userData' => array_merge($userData, ['password' => '***REDACTED***'])
        ]);

        try {
            $response = Http::timeout(10)->post($url, $userData);

            if (!$response->successful()) {
                Log::error('User Service returned error response', [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                    'url' => $url
                ]);
            } else {
                Log::info('Successfully created user in User Service', [
                    'status' => $response->status(),
                    'userId' => $response->json()['id'] ?? null
                ]);
            }

            return $response;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('User Service is not accessible', [
                'error' => $e->getMessage(),
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);
            return new \Illuminate\Http\Client\Response(new \GuzzleHttp\Psr7\Response(
                503,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'status' => 'error',
                    'message' => 'User Service is currently unavailable. Please try again later.',
                    'error_code' => 'USER_SERVICE_UNAVAILABLE'
                ])
            ));
        } catch (\Exception $e) {
            Log::error('Failed to create user in User Service', [
                'error' => $e->getMessage(),
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get user details from User Service
     */
    public function getUserById(string $userId): Response
    {
        $url = "{$this->baseUrl}/api/users/{$userId}";
        Log::info('Fetching user from User Service', ['url' => $url, 'userId' => $userId]);

        try {
            $response = Http::get($url);

            if (!$response->successful()) {
                Log::error('Failed to fetch user from User Service', [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                    'userId' => $userId
                ]);
            }

            return $response;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('User Service is not accessible', [
                'error' => $e->getMessage(),
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);
            return new \Illuminate\Http\Client\Response(new \GuzzleHttp\Psr7\Response(
                503,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'status' => 'error',
                    'message' => 'User Service is currently unavailable. Please try again later.',
                    'error_code' => 'USER_SERVICE_UNAVAILABLE'
                ])
            ));
        } catch (\Exception $e) {
            Log::error('Error fetching user from User Service', [
                'error' => $e->getMessage(),
                'userId' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get user by email from User Service
     */
    public function getUserByEmail(string $email): Response
    {
        $url = "{$this->baseUrl}/api/users/by-email/{$email}";
        Log::info('Fetching user by email from User Service', ['url' => $url, 'email' => $email]);

        try {
            $response = Http::get($url);

            if (!$response->successful()) {
                Log::error('Failed to fetch user by email from User Service', [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                    'email' => $email
                ]);
            }

            return $response;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('User Service is not accessible', [
                'error' => $e->getMessage(),
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);
            return new \Illuminate\Http\Client\Response(new \GuzzleHttp\Psr7\Response(
                503,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'status' => 'error',
                    'message' => 'User Service is currently unavailable. Please try again later.',
                    'error_code' => 'USER_SERVICE_UNAVAILABLE'
                ])
            ));
        } catch (\Exception $e) {
            Log::error('Error fetching user by email from User Service', [
                'error' => $e->getMessage(),
                'email' => $email,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update user in User Service
     */
    public function updateUser(string $userId, array $userData): Response
    {
        $url = "{$this->baseUrl}/api/users/{$userId}";
        Log::info('Updating user in User Service', [
            'url' => $url,
            'userId' => $userId,
            'userData' => array_merge($userData, ['password' => '***REDACTED***'])
        ]);

        try {
            $response = Http::timeout(10)->put($url, $userData);

            if (!$response->successful()) {
                Log::error('Failed to update user in User Service', [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                    'userId' => $userId
                ]);
            }

            return $response;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('User Service is not accessible', [
                'error' => $e->getMessage(),
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);
            return new \Illuminate\Http\Client\Response(new \GuzzleHttp\Psr7\Response(
                503,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'status' => 'error',
                    'message' => 'User Service is currently unavailable. Please try again later.',
                    'error_code' => 'USER_SERVICE_UNAVAILABLE'
                ])
            ));
        } catch (\Exception $e) {
            Log::error('Error updating user in User Service', [
                'error' => $e->getMessage(),
                'userId' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
