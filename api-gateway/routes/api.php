<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ServiceRouter;

// API Gateway routes
Route::prefix('v1')->middleware('api')->group(function () {
    // Health check for API Gateway itself
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'service' => 'api-gateway',
            'timestamp' => now()
        ]);
    });

    // Route all other requests through the ServiceRouter middleware
    Route::any('{service}/{path?}', function ($service, $path = '') {
        // This won't be reached as the ServiceRouter middleware will handle the response
        return response()->json(['error' => 'Invalid request'], 400);
    })->where('path', '.*')->middleware(ServiceRouter::class);
});
