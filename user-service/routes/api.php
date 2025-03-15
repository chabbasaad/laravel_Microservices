<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckUserRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint
Route::get('health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'user',
        'timestamp' => now(),
        'port' => env('PORT', 8002)
    ]);
});

// User management routes
Route::middleware(CheckUserRole::class)->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::get('/by-email/{email}', [UserController::class, 'findByEmail']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});
