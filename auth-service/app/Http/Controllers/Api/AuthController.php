<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function health()
    {
        return response()->json([
            'status' => 'healthy',
            'service' => 'auth',
            'timestamp' => now(),
            'port' => env('APP_PORT', 8001)
        ]);
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Password::defaults()],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:100'],
                'country' => ['nullable', 'string', 'max:100'],
                'role' => ['sometimes', 'string', 'in:admin,event_creator,operator,user'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // First, create user in User Service
            $userServiceResponse = $this->userService->createUser([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
            ]);

            if (!$userServiceResponse->successful()) {
                Log::error('Failed to create user in User Service', [
                    'response' => $userServiceResponse->json(),
                    'status' => $userServiceResponse->status()
                ]);

                // Check if it's a service unavailability error
                $responseData = $userServiceResponse->json();
                if ($userServiceResponse->status() === 503 && 
                    isset($responseData['error_code']) && 
                    $responseData['error_code'] === 'USER_SERVICE_UNAVAILABLE') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Registration is temporarily unavailable. Please try again later.',
                        'error_code' => 'REGISTRATION_UNAVAILABLE'
                    ], 503);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create user account',
                    'errors' => $userServiceResponse->json()['errors'] ?? ['general' => ['Failed to create user']]
                ], $userServiceResponse->status());
            }

            $userServiceData = $userServiceResponse->json();

            // Check if we have the required data from User Service
            if (!isset($userServiceData['data']['id'])) {
                Log::error('Invalid response from User Service', [
                    'response' => $userServiceData
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid response from User Service'
                ], 500);
            }

            // Create auth user record - only store authentication-related fields
            $user = User::create([
                'external_user_id' => $userServiceData['data']['id'],
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
                'is_active' => true
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'external_user_id' => $user->external_user_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => array_merge(
                        $userServiceData['data'],
                        ['role' => $user->role]
                    ),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. Please try again later.',
                'error_code' => 'REGISTRATION_ERROR'
            ], 500);
        }
    }

    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = User::where('email', $request->email)->firstOrFail();

            // Get user details from User Service
            $userServiceResponse = $this->userService->getUserById($user->external_user_id);

            if (!$userServiceResponse->successful()) {
                Log::error('Failed to fetch user details from User Service', [
                    'user_id' => $user->external_user_id,
                    'status' => $userServiceResponse->status()
                ]);
            }

            // Update last login timestamp
            $user->updateLastLogin();

            $token = $user->createToken('auth_token')->plainTextToken;

            // Log successful login with role for debugging
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'external_user_id' => $user->external_user_id,
                'role' => $user->role
            ]);

            return response()->json([
                'message' => 'User logged in successfully',
                'user' => [
                    'id' => $user->external_user_id,
                    'role' => $user->role
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            Log::error('Login failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Login failed'
            ], 500);
        }
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Logout failed'
            ], 500);
        }
    }

    /**
     * Get authenticated user info
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->external_user_id,
            'role' => $user->role,
            'email' => $user->email
        ]);
    }
}
