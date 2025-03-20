<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\EventController;
use App\Http\Middleware\TrustGatewayAuth;
use App\Http\Middleware\CheckRole;

// Health Check
Route::get('health', [HealthController::class, 'check']);

// Public Routes (no authentication required)
Route::get('/public', [EventController::class, 'publicEvents']);

// Protected Routes
Route::middleware(TrustGatewayAuth::class)->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Public event routes (require authentication but no specific role)
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{id}', [EventController::class, 'show']);

    // Protected event routes (require specific roles)
    Route::post('/', [EventController::class, 'store'])
        ->middleware(CheckRole::class . ':create');
        
    Route::match(['put', 'patch'], '/{id}', [EventController::class, 'update'])
        ->middleware(CheckRole::class . ':update');
        
    Route::delete('/{id}', [EventController::class, 'destroy'])
        ->middleware(CheckRole::class . ':delete');

    // Speaker management routes
    Route::post('/{id}/speakers', [EventController::class, 'addSpeaker'])
        ->middleware(CheckRole::class . ':update');
        
    Route::put('/{id}/speakers/{speakerId}', [EventController::class, 'updateSpeaker'])
        ->middleware(CheckRole::class . ':update');
        
    Route::delete('/{id}/speakers/{speakerId}', [EventController::class, 'removeSpeaker'])
        ->middleware(CheckRole::class . ':update');

    // Sponsor management routes
    Route::post('/{id}/sponsors', [EventController::class, 'addSponsor'])
        ->middleware(CheckRole::class . ':update');
        
    Route::put('/{id}/sponsors/{sponsorId}', [EventController::class, 'updateSponsor'])
        ->middleware(CheckRole::class . ':update');
        
    Route::delete('/{id}/sponsors/{sponsorId}', [EventController::class, 'removeSponsor'])
        ->middleware(CheckRole::class . ':update');
});
