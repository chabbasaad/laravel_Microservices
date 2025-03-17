<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Jobs\TestEmailJob;
use Illuminate\Support\Facades\Log;

// Health and monitoring endpoints (no auth required)
Route::get('/health', [NotificationController::class, 'queueHealth']);
Route::get('/test-queue', [NotificationController::class, 'testQueue']);

// Notification routes (no auth required as they are called internally)
Route::post('/purchase', [NotificationController::class, 'sendPurchaseNotification']);
Route::post('/cancellation', [NotificationController::class, 'sendCancellationNotification']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
