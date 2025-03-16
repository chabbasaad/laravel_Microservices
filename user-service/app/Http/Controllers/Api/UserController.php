<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): JsonResponse
    {
        try {
            $userRole = request()->user_role;
            $userId = request()->authenticated_user_id;

            // Different access levels based on role
            switch ($userRole) {
                case 'admin':
                    // Admin can see everything
                    $users = User::paginate(20);
                    break;

                case 'event_creator':
                case 'operator':
                    // Event creators and operators can only see basic user information
                    $users = User::select('id', 'name', 'email', 'phone_number', 'city', 'country')
                        ->paginate(20);
                    break;

                default:
                    // Regular users can only see their own profile
                    Log::info('User attempted to access all users without permission', [
                        'user_id' => $userId,
                        'role' => $userRole
                    ]);
                    return response()->json([
                        'message' => 'You do not have permission to view all users'
                    ], 403);
            }

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Failed to fetch users', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch users'], 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('Received user creation request', [
            'data' => $request->except(['password', 'password_confirmation'])
        ]);
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:100'],
                'country' => ['nullable', 'string', 'max:100'],
                'bio' => ['nullable', 'string'],
                'date_of_birth' => ['nullable', 'date'],
                'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                'language' => ['nullable', 'string', 'max:10'],
                'preferences' => ['nullable', 'array'],
            ]);
            if ($validator->fails()) {
                Log::warning('User creation validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            $user = User::create($request->all());
            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $userRole = request()->user_role;
            $authenticatedUserId = request()->authenticated_user_id;

            $user = User::findOrFail($id);

            switch ($userRole) {
                case 'admin':
                    // Admin can see everything
                    return response()->json($user);

                case 'event_creator':
                case 'operator':
                    // Event creators and operators see basic info
                    return response()->json([
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'city' => $user->city,
                        'country' => $user->country,
                        'is_active' => $user->is_active
                    ]);

                default:
                    // Regular users can only see their own profile
                    if ($id === $authenticatedUserId) {
                        return response()->json($user);
                    }

                    Log::warning('Unauthorized attempt to view user profile', [
                        'authenticated_user_id' => $authenticatedUserId,
                        'requested_user_id' => $id,
                        'role' => $userRole
                    ]);
                    return response()->json([
                        'message' => 'You do not have permission to view this user'
                    ], 403);
            }

        } catch (\Exception $e) {
            Log::error('Failed to fetch user', ['error' => $e->getMessage(), 'id' => $id]);
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    /**
     * Get user by email.
     */
    public function findByEmail(string $email): JsonResponse
    {
        try {
            $user = User::where('email', $email)->firstOrFail();
            return response()->json($user);
        } catch (\Exception $e) {
            Log::error('Failed to fetch user by email', ['error' => $e->getMessage(), 'email' => $email]);
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $userRole = request()->user_role;
            $authenticatedUserId = request()->authenticated_user_id;

            // Only admin and the user themselves can update profiles
            if ($userRole !== 'admin' && $id !== $authenticatedUserId) {
                Log::warning('Unauthorized attempt to update user profile', [
                    'authenticated_user_id' => $authenticatedUserId,
                    'target_user_id' => $id,
                    'role' => $userRole
                ]);
                return response()->json([
                    'message' => 'You do not have permission to update this user'
                ], 403);
            }

            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:100'],
                'country' => ['nullable', 'string', 'max:100'],
                'bio' => ['nullable', 'string'],
                'date_of_birth' => ['nullable', 'date'],
                'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                'language' => ['nullable', 'string', 'max:10'],
                'preferences' => ['nullable', 'array'],
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user->update($request->all());

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user', ['error' => $e->getMessage(), 'id' => $id]);
            return response()->json(['message' => 'Failed to update user'], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $id): JsonResponse
    {
        Log::info('Delete user request received', [
            'target_user_id' => $id,
            'request_method' => request()->method(),
            'request_path' => request()->path(),
            'request_headers' => [
                'x-user-role' => request()->header('X-User-Role'),
                'x-user-id' => request()->header('X-User-Id'),
                'authorization' => request()->header('Authorization') ? 'present' : 'missing'
            ]
        ]);

        try {
            $userRole = request()->user_role;
            $authenticatedUserId = request()->authenticated_user_id;

            Log::info('Processing delete user request', [
                'authenticated_user_id' => $authenticatedUserId,
                'target_user_id' => $id,
                'user_role' => $userRole,
                'is_self_delete' => $id === $authenticatedUserId,
                'is_admin' => $userRole === 'admin'
            ]);

            // Only admin and the user themselves can delete profiles
            if ($userRole !== 'admin' && $id !== $authenticatedUserId) {
                Log::warning('Unauthorized attempt to delete user', [
                    'authenticated_user_id' => $authenticatedUserId,
                    'target_user_id' => $id,
                    'role' => $userRole,
                    'request_headers' => request()->headers->all()
                ]);
                return response()->json([
                    'message' => 'You do not have permission to delete this user'
                ], 403);
            }

            // Try to find the user first
            try {
                $user = User::findOrFail($id);
                Log::info('Found user to delete', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_name' => $user->name
                ]);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::error('User not found for deletion', [
                    'target_user_id' => $id,
                    'error' => $e->getMessage()
                ]);
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            // Attempt to delete the user
            try {
                $user->delete();
                Log::info('User deleted successfully', [
                    'deleted_user_id' => $id,
                    'deleted_user_email' => $user->email,
                    'deleted_by_user_id' => $authenticatedUserId,
                    'role' => $userRole
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to delete user in database', [
                    'user_id' => $id,
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'target_user_id' => $id,
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to delete user'], 500);
        }
    }
}
