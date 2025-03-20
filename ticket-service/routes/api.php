<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\TrustGatewayAuth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health Check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'ticket-service',
        'timestamp' => now()
    ]);
});


// Protected routes with role-based access
Route::middleware('trust.gateway')->group(function () {
    // Admin and Event Creator: View tickets
    Route::get('/', [TicketController::class, 'getTickets'])
        ->middleware('check.role:admin,event_creator');

    // Ticket purchase
    Route::post('/purchase', [TicketController::class, 'purchase'])
        ->middleware('check.role:purchase');

    // Get user's tickets
    Route::get('/user/{userId}', [TicketController::class, 'getUserTickets'])
        ->middleware('check.role:view');

    // Get specific ticket
    Route::get('/{ticketId}', [TicketController::class, 'show'])
        ->middleware('check.role:view');

    // Validate ticket (anyone can validate)
    Route::post('/{ticketId}/validate', [TicketController::class, 'validate']);

    // Cancel ticket and process refund
    Route::post('/{ticketId}/cancel', [TicketController::class, 'cancel'])
        ->middleware('check.role:cancel');
});
