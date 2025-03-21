<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class TicketController extends Controller
{
    /**
     * Purchase tickets for an event
     */
    public function purchase(Request $request): JsonResponse
    {
        try {
            $user = $request->get('user');
            
            Log::info('Processing ticket purchase request', [
                'user_id' => $user['id'],
                'event_id' => $request->event_id,
                'quantity' => $request->quantity
            ]);

            // Validate request
            $validator = Validator::make($request->all(), [
                'event_id' => 'required|integer',
                'quantity' => 'required|integer|min:1',
                'payment' => 'required|array',
                'payment.card_number' => 'required|string|size:16',
                'payment.expiry' => ['required', 'string', 'size:5', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
                'payment.cvv' => 'required|string|size:3'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            try {
                // Get event details
                $eventUrl = rtrim(config('services.events.base_url'), '/') . config('services.events.routes.show');
                $eventUrl = str_replace('{id}', $request->event_id, $eventUrl);
                
                Log::info('Fetching event details', [
                    'url' => $eventUrl,
                    'user_id' => $user['id'],
                    'user_role' => $user['role']
                ]);

                // Use Laravel's HTTP client with explicit timeout and no verify
                $eventResponse = Http::withOptions([
                    'timeout' => 30,
                    'verify' => false
                ])->withHeaders([
                    'X-User-Id' => (string)$user['id'],
                    'X-User-Role' => strtolower($user['role'])
                ])->withToken($request->bearerToken())
                  ->get($eventUrl);

                Log::info('Event Service Response', [
                    'url' => $eventUrl,
                    'status' => $eventResponse->status(),
                    'body' => $eventResponse->body()
                ]);

                if ($eventResponse->status() === 401 || $eventResponse->status() === 403) {
                    Log::error('Unauthorized access to event details', [
                        'event_id' => $request->event_id,
                        'user_id' => $user['id']
                    ]);
                    return response()->json(['error' => 'Unauthorized to view event details'], $eventResponse->status());
                }

                if (!$eventResponse->successful()) {
                    Log::error('Failed to fetch event details', [
                        'event_id' => $request->event_id,
                        'response' => $eventResponse->body(),
                        'status' => $eventResponse->status()
                    ]);
                    return response()->json(['error' => 'Event not found'], 404);
                }

                $event = $eventResponse->json();

                // Validate event data structure based on our schema
                if (!isset($event['id'], $event['price'], $event['available_tickets'])) {
                    Log::error('Invalid event data structure', [
                        'event' => $event
                    ]);
                    return response()->json(['error' => 'Invalid event data'], 500);
                }

                // Check if enough tickets are available
                if ($event['available_tickets'] < $request->quantity) {
                    return response()->json([
                        'error' => 'Not enough tickets available',
                        'available' => $event['available_tickets']
                    ], 422);
                }

                // Calculate total price
                $totalPrice = $event['price'] * $request->quantity;

                // Create tickets in a transaction
                \DB::beginTransaction();

                $tickets = [];
                for ($i = 0; $i < $request->quantity; $i++) {
                    $ticket = Ticket::createForEvent(
                        $event['id'],
                        $user['id'],
                        $event['price']
                    );
                    $tickets[] = $ticket;
                }

                // Create payment record
                $payment = Payment::create([
                    'amount' => $event['price'] * $request->quantity,
                    'status' => 'completed',
                    'payment_date' => now(),
                    'payment_method' => 'card',
                    'last_four' => substr($request->payment['card_number'], -4),
                    'user_id' => $user['id']
                ]);

                // Associate payment with tickets
                foreach ($tickets as $ticket) {
                    $ticket->payment()->associate($payment);
                    $ticket->save();
                }

                \DB::commit();

                // Update event available tickets
                $updateUrl = rtrim(config('services.events.base_url'), '/') . config('services.events.routes.update');
                $updateUrl = str_replace('{id}', $request->event_id, $updateUrl);
                
                Log::info('Updating event tickets', [
                    'url' => $updateUrl,
                    'tickets_purchased' => $request->quantity
                ]);

                // Simplified update payload - only send what needs to change
                $updateResponse = Http::withHeaders([
                    'X-User-Id' => (string)$user['id'],
                    'X-User-Role' => strtolower($user['role'])
                ])->withToken($request->bearerToken())
                  ->patch($updateUrl, [
                    'available_tickets' => $event['available_tickets'] - $request->quantity
                ]);

                if (!$updateResponse->successful()) {
                    Log::error('Failed to update event tickets', [
                        'event_id' => $request->event_id,
                        'response' => $updateResponse->body(),
                        'status' => $updateResponse->status()
                    ]);
                }

                return response()->json([
                    'message' => 'Tickets purchased successfully',
                    'tickets' => $tickets
                ], 201);

            } catch (ConnectException $e) {
                Log::error('Connection error to Event Service', [
                    'url' => $eventUrl ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['error' => 'Could not connect to Event Service'], 503);
            } catch (RequestException $e) {
                Log::error('Request error to Event Service', [
                    'url' => $eventUrl ?? 'unknown',
                    'error' => $e->getMessage(),
                    'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
                ]);
                return response()->json(['error' => 'Error fetching event details'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Ticket purchase failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to process ticket purchase'], 500);
        }
    }

    /**
     * Get tickets for a specific user
     */
    public function getUserTickets(Request $request, $userId): JsonResponse
    {
        try {
            $user = $request->get('user');
            
            // Check if user is requesting their own tickets or has admin role
            if ($user['id'] != $userId && strtolower($user['role']) !== 'admin') {
                return response()->json(['error' => 'Unauthorized to view these tickets'], 403);
            }

            $tickets = Ticket::where('user_id', $userId)
                           ->with('payment')
                           ->orderBy('created_at', 'desc')
                           ->get();

            if ($tickets->isEmpty()) {
                return response()->json(['message' => 'No tickets found for this user'], 404);
            }

            // Fetch event details for each ticket
            foreach ($tickets as $ticket) {
                try {
                    $eventUrl = rtrim(config('services.events.base_url'), '/') . config('services.events.routes.show');
                    $eventUrl = str_replace('{id}', $ticket->event_id, $eventUrl);
                    
                    Log::info('Fetching event details', [
                        'url' => $eventUrl,
                        'user_id' => $user['id'],
                        'user_role' => $user['role']
                    ]);

                    $eventResponse = Http::withHeaders([
                        'X-User-Id' => (string)$user['id'],
                        'X-User-Role' => strtolower($user['role'])
                    ])->withToken($request->bearerToken())
                      ->get($eventUrl);

                    if ($eventResponse->successful()) {
                        $event = $eventResponse->json();
                        
                        // Handle speakers and sponsors data
                        $speakers = is_array($event['speakers'] ?? null) ? $event['speakers'] : json_decode($event['speakers'] ?? '[]', true);
                        $sponsors = is_array($event['sponsors'] ?? null) ? $event['sponsors'] : json_decode($event['sponsors'] ?? '[]', true);
                        
                        $ticket->event = array_merge($event, [
                            'speakers' => $speakers,
                            'sponsors' => $sponsors
                        ]);
                    } else {
                        Log::warning('Failed to fetch event details for ticket', [
                            'ticket_id' => $ticket->id,
                            'event_id' => $ticket->event_id,
                            'status' => $eventResponse->status()
                        ]);
                        $ticket->event = ['error' => 'Event details not available'];
                    }
                } catch (\Exception $e) {
                    Log::error('Error fetching event details', [
                        'ticket_id' => $ticket->id,
                        'event_id' => $ticket->event_id,
                        'error' => $e->getMessage()
                    ]);
                    $ticket->event = ['error' => 'Event details not available'];
                }
            }

            return response()->json($tickets);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user tickets', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch tickets'], 500);
        }
    }

      /**
     * Show a specific ticket
     */
    public function show(Request $request, $ticketId): JsonResponse
    {
        try {
            $user = $request->get('user');
            
            Log::info('Fetching ticket details', [
                'ticket_id' => $ticketId,
                'user_id' => $user['id'],
                'user_role' => $user['role']
            ]);

            // First check if the ticket exists without considering soft deletes
            $ticket = Ticket::where('id', $ticketId)->first();

            // If not found, then check with soft deletes included
            if (!$ticket) {
                $ticket = Ticket::withTrashed()
                              ->where('id', $ticketId)
                              ->first();

                if (!$ticket) {
                    Log::warning('Ticket not found', [
                        'ticket_id' => $ticketId,
                        'requested_by_user' => $user['id']
                    ]);
                    return response()->json([
                        'error' => 'Ticket not found',
                        'message' => 'The requested ticket does not exist'
                    ], 404);
                }

                if ($ticket->trashed()) {
                    Log::info('Attempted to access deleted ticket', [
                        'ticket_id' => $ticketId,
                        'deleted_at' => $ticket->deleted_at,
                        'requested_by_user' => $user['id']
                    ]);
                    return response()->json([
                        'error' => 'Ticket not available',
                        'message' => 'This ticket has been deleted',
                        'deleted_at' => $ticket->deleted_at
                    ], 410); // HTTP 410 Gone
                }
            }

            // Load payment relationship
            $ticket->load('payment');

            // Check if user owns the ticket or has admin role
            if ($ticket->user_id != $user['id'] && strtolower($user['role']) !== 'admin') {
                Log::warning('Unauthorized ticket access attempt', [
                    'ticket_id' => $ticketId,
                    'ticket_owner' => $ticket->user_id,
                    'requested_by_user' => $user['id'],
                    'user_role' => $user['role']
                ]);
                return response()->json([
                    'error' => 'Unauthorized to view this ticket',
                    'message' => 'You do not have permission to view this ticket'
                ], 403);
            }

            try {
                // Get event details
                $eventUrl = rtrim(config('services.events.base_url'), '/') . config('services.events.routes.show');
                $eventUrl = str_replace('{id}', $ticket->event_id, $eventUrl);
                
                Log::info('Fetching event details for ticket', [
                    'url' => $eventUrl,
                    'ticket_id' => $ticketId,
                    'event_id' => $ticket->event_id,
                    'user_id' => $user['id'],
                    'user_role' => $user['role']
                ]);

                $eventResponse = Http::withOptions([
                    'timeout' => 30,
                    'verify' => false
                ])->withHeaders([
                    'X-User-Id' => (string)$user['id'],
                    'X-User-Role' => strtolower($user['role'])
                ])->withToken($request->bearerToken())
                  ->get($eventUrl);

                if ($eventResponse->successful()) {
                    $event = $eventResponse->json();
                    
                    // Handle speakers and sponsors data
                    $speakers = is_array($event['speakers'] ?? null) ? $event['speakers'] : json_decode($event['speakers'] ?? '[]', true);
                    $sponsors = is_array($event['sponsors'] ?? null) ? $event['sponsors'] : json_decode($event['sponsors'] ?? '[]', true);
                    
                    $ticket->event = array_merge($event, [
                        'speakers' => $speakers,
                        'sponsors' => $sponsors
                    ]);

                    Log::info('Successfully fetched event details', [
                        'ticket_id' => $ticketId,
                        'event_id' => $ticket->event_id,
                        'event_title' => $event['title'] ?? 'Unknown'
                    ]);
                } else {
                    Log::warning('Failed to fetch event details for ticket', [
                        'ticket_id' => $ticketId,
                        'event_id' => $ticket->event_id,
                        'status' => $eventResponse->status(),
                        'response' => $eventResponse->body()
                    ]);
                    $ticket->event = [
                        'error' => 'Event details not available',
                        'message' => 'Unable to fetch event information at this time'
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error fetching event details for ticket', [
                    'ticket_id' => $ticketId,
                    'event_id' => $ticket->event_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $ticket->event = [
                    'error' => 'Event details not available',
                    'message' => 'An error occurred while fetching event information'
                ];
            }

            // Get full ticket details including payment and event info
            $response = $ticket->getFullDetails();

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Failed to fetch ticket details', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to fetch ticket details',
                'message' => 'An unexpected error occurred while retrieving the ticket'
            ], 500);
        }
    }

    /**
     * Validate a ticket
     */
    public function validate($ticketId): JsonResponse
    {
        try {
            $ticket = Ticket::where('id', $ticketId)
                          ->where('status', 'confirmed')
                          ->whereNull('used_at')
                          ->whereNull('cancelled_at')
                          ->first();

            if (!$ticket) {
                return response()->json([
                    'error' => 'Invalid or already used ticket'
                ], 404);
            }

            $ticket->update([
                'used_at' => now()
            ]);

            Log::info('Ticket validated successfully', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'event_id' => $ticket->event_id
            ]);

            return response()->json([
                'message' => 'Ticket validated successfully',
                'ticket' => $ticket
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to validate ticket', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Failed to validate ticket'
            ], 500);
        }
    }

  /**
     * Cancel a ticket and process refund
     */
    public function cancel(Request $request, string $ticketId): JsonResponse
    {
        try {
            $user = $request->get('user');
            
            Log::info('Attempting to cancel ticket', [
                'ticket_id' => $ticketId,
                'user_id' => $user['id'],
                'user_role' => $user['role']
            ]);

            $ticket = Ticket::with('payment')->findOrFail($ticketId);

            // Check if user is authorized to cancel this ticket
            if ($ticket->user_id != $user['id'] && strtolower($user['role']) !== 'admin') {
                Log::warning('Unauthorized ticket cancellation attempt', [
                    'ticket_id' => $ticketId,
                    'ticket_owner' => $ticket->user_id,
                    'requested_by_user' => $user['id'],
                    'user_role' => $user['role']
                ]);
                return response()->json([
                    'error' => 'Unauthorized to cancel this ticket',
                    'message' => 'You do not have permission to cancel this ticket'
                ], 403);
            }

            if (!$ticket->canBeCancelled()) {
                Log::warning('Ticket cannot be cancelled', [
                    'ticket_id' => $ticketId,
                    'status' => $ticket->status,
                    'used_at' => $ticket->used_at,
                    'cancelled_at' => $ticket->cancelled_at
                ]);
                return response()->json([
                    'error' => 'Ticket cannot be cancelled',
                    'message' => 'This ticket is not eligible for cancellation',
                    'reason' => $this->getCancellationBlockReason($ticket)
                ], 422);
            }

            // Process cancellation
            try {
                // Begin transaction
                \DB::beginTransaction();
                
                $ticket->cancel();

                // Get event details to update available tickets
                $eventUrl = rtrim(config('services.events.base_url'), '/') . config('services.events.routes.show');
                $eventUrl = str_replace('{id}', $ticket->event_id, $eventUrl);
                
                $eventResponse = Http::withHeaders([
                    'X-User-Id' => (string)$user['id'],
                    'X-User-Role' => strtolower($user['role'])
                ])->withToken($request->bearerToken())
                  ->get($eventUrl);

                if (!$eventResponse->successful()) {
                    throw new \Exception('Failed to fetch event details for ticket update');
                }

                $event = $eventResponse->json();

                // Update available tickets in Event Service
                $updateUrl = rtrim(config('services.events.base_url'), '/') . config('services.events.routes.update');
                $updateUrl = str_replace('{id}', $ticket->event_id, $updateUrl);
                
                Log::info('Updating event available tickets after cancellation', [
                    'event_id' => $ticket->event_id,
                    'current_available' => $event['available_tickets'],
                    'adding_back' => 1
                ]);

                $updateResponse = Http::withHeaders([
                    'X-User-Id' => (string)$user['id'],
                    'X-User-Role' => strtolower($user['role'])
                ])->withToken($request->bearerToken())
                  ->patch($updateUrl, [
                    'available_tickets' => $event['available_tickets'] + 1
                ]);

                if (!$updateResponse->successful()) {
                    throw new \Exception('Failed to update event available tickets');
                }

                // Send test notification first
                // try {
                //     $testUrl = rtrim(config('services.notifications.base_url'), '/') . config('services.notifications.routes.test');
                    
                //     Log::info('Sending test notification for cancellation', [
                //         'url' => $testUrl
                //     ]);

                //     $testResponse = Http::withHeaders([
                //         'Accept' => 'application/json',
                //         'Content-Type' => 'application/json'
                //     ])->get($testUrl);

                //     Log::info('Test notification response for cancellation', [
                //         'status' => $testResponse->status(),
                //         'body' => $testResponse->body()
                //     ]);

                //     if (!$testResponse->successful()) {
                //         Log::error('Test notification failed for cancellation', [
                //             'status' => $testResponse->status(),
                //             'body' => $testResponse->body()
                //         ]);
                //     }
                // } catch (\Exception $e) {
                //     Log::error('Error sending test notification for cancellation', [
                //         'error' => $e->getMessage(),
                //         'trace' => $e->getTraceAsString()
                //     ]);
                // }

                // Send cancellation notification
                try {
                    $notificationUrl = rtrim(config('services.notifications.base_url'), '/') . config('services.notifications.routes.cancellation');
                    
                    // Get ticket without relationship since we already have event data
                    $ticket = Ticket::find($ticketId);
                    if (!$ticket) {
                        throw new \Exception('Ticket not found');
                    }

                    // We now get email directly from auth service response
                    if (!isset($user['email'])) {
                        Log::error('User email not found in auth response', [
                            'user' => $user,
                            'ticket_id' => $ticketId
                        ]);
                        throw new \Exception('User email not found in auth response');
                    }

                    $notificationData = [
                        'email' => $user['email'],
                        'ticket_number' => (string)$ticket->id,  // Convert to string
                        'event' => [
                            'title' => $event['title']
                        ],
                        'price' => $ticket->price,
                        'purchase_date' => $ticket->purchase_date,
                        'cancelled_at' => now()->format('Y-m-d H:i:s')
                    ];

                    Log::info('Sending cancellation notification', [
                        'url' => $notificationUrl,
                        'data' => $notificationData
                    ]);

                    $notificationResponse = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    ])->post($notificationUrl, $notificationData);

                    Log::info('Cancellation notification response', [
                        'status' => $notificationResponse->status(),
                        'body' => $notificationResponse->body()
                    ]);

                    if (!$notificationResponse->successful()) {
                        Log::error('Failed to send cancellation notification', [
                            'ticket_id' => $ticket->id,
                            'response' => $notificationResponse->body(),
                            'status' => $notificationResponse->status()
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending cancellation notification', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'ticket_id' => $ticketId
                    ]);
                }

                \DB::commit();
                
                Log::info('Ticket cancelled successfully and event updated', [
                    'ticket_id' => $ticketId,
                    'user_id' => $user['id'],
                    'cancelled_at' => $ticket->cancelled_at,
                    'event_id' => $ticket->event_id
                ]);

                return response()->json([
                    'message' => 'Ticket cancelled successfully',
                    'ticket' => $ticket->getFullDetails()
                ]);

            } catch (\Exception $e) {
                \DB::rollBack();
                Log::error('Failed to cancel ticket or update event', [
                    'ticket_id' => $ticketId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Failed to cancel ticket',
                    'message' => 'An error occurred while cancelling the ticket'
                ], 500);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Ticket not found for cancellation', [
                'ticket_id' => $ticketId
            ]);
            return response()->json([
                'error' => 'Ticket not found',
                'message' => 'The requested ticket does not exist'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Unexpected error during ticket cancellation', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to process ticket cancellation',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get the reason why a ticket cannot be cancelled
     */
    private function getCancellationBlockReason(Ticket $ticket): string
    {
        if ($ticket->status !== Ticket::STATUS_CONFIRMED) {
            return "Ticket status must be 'confirmed' to be cancelled";
        }
        if ($ticket->used_at !== null) {
            return "Ticket has already been used";
        }
        if ($ticket->cancelled_at !== null) {
            return "Ticket has already been cancelled";
        }
        return "Unknown reason";
    }

    /**
     * Get tickets based on user role
     * - Admin: Can see all tickets
     * - Event Creator: Can see tickets for their events
     */
    public function getTickets(Request $request): JsonResponse
    {
        try {
            $user = $request->get('user');
            $query = Ticket::query();
            
            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // If event creator, get event IDs they created first
            if ($user['role'] === 'event_creator') {
                $eventUrl = rtrim(config('services.events.base_url'), '/') . '/api/creator/' . $user['id'] . '/events';
                
                $eventResponse = Http::withOptions([
                    'timeout' => 30,
                    'verify' => false
                ])->withHeaders([
                    'X-User-Id' => (string)$user['id'],
                    'X-User-Role' => 'event_creator'
                ])->withToken($request->bearerToken())
                  ->get($eventUrl);

                if (!$eventResponse->successful()) {
                    return response()->json(['error' => 'Failed to fetch creator events'], 500);
                }

                $creatorEvents = collect($eventResponse->json()['data'] ?? [])->pluck('id');
                $query->whereIn('event_id', $creatorEvents);
            }
            // For admin, no filtering needed as they can see all tickets
            
            $tickets = $query->with(['payment'])
                           ->orderBy('created_at', 'desc')
                           ->paginate(20);

            return response()->json([
                'status' => 'success',
                'data' => $tickets,
                'message' => 'Tickets retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch tickets: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch tickets'
            ], 500);
        }
    }
}
