<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Event::query();
            $user = Auth::user();
            
            // If user is not admin or event_creator, only show published events
            if (!$user || !in_array($user->role, ['admin', 'event_creator'])) {
                $query->where('status', 'published');
            }
            
            $events = $query->orderBy('date', 'asc')->paginate(10);
            return response()->json($events);
        } catch (\Exception $e) {
            Log::error('Failed to fetch events: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch events'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::info('Creating event with data:', [
                'request_data' => $request->all(),
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'date' => 'required|date|after:now',
                'location' => 'required|string|max:255',
                'max_tickets' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'status' => 'in:draft,published',
                'speakers' => 'nullable|string',
                'sponsors' => 'nullable|string',
                'image' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                Log::warning('Event validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $event = Event::create([
                'title' => $request->title,
                'description' => $request->description,
                'date' => $request->date,
                'location' => $request->location,
                'max_tickets' => $request->max_tickets,
                'available_tickets' => $request->max_tickets,
                'price' => $request->price,
                'creator_id' => $user->id,
                'status' => $request->status ?? 'draft',
                'speakers' => $request->speakers,
                'sponsors' => $request->sponsors,
                'image' => $request->image
            ]);

            Log::info('Event created successfully', [
                'event_id' => $event->id,
                'creator_id' => $user->id,
                'creator_role' => $user->role
            ]);
            
            return response()->json($event, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create event', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to create event'], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);
            $user = Auth::user();
            
            if (!$event->canBeViewedBy($user)) {
                return response()->json(['error' => 'Event not found'], 404);
            }
            
            return response()->json($event);
        } catch (\Exception $e) {
            Log::error('Failed to fetch event: ' . $e->getMessage());
            return response()->json(['error' => 'Event not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);
            $user = Auth::user();
            
            if (!$event->canBeManageBy($user)) {
                return response()->json(['error' => 'Unauthorized to update this event'], 403);
            }

            // Special handling for available_tickets update
            if ($request->has('available_tickets')) {
                $newAvailableTickets = $request->available_tickets;
                
                // Validate available tickets
                if ($newAvailableTickets < 0 || $newAvailableTickets > $event->max_tickets) {
                    return response()->json([
                        'error' => 'Invalid available tickets count',
                        'max_allowed' => $event->max_tickets,
                        'requested' => $newAvailableTickets
                    ], 422);
                }

                $event->available_tickets = $newAvailableTickets;
                $event->save();

                Log::info('Event tickets updated', [
                    'event_id' => $event->id,
                    'previous_available' => $event->available_tickets,
                    'new_available' => $newAvailableTickets,
                    'updater_id' => $user->id
                ]);

                return response()->json($event);
            }

            // Regular update validation for other fields
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'description' => 'string',
                'date' => 'date|after:now',
                'location' => 'string|max:255',
                'max_tickets' => 'integer|min:' . $event->max_tickets - ($event->max_tickets - $event->available_tickets),
                'price' => 'numeric|min:0',
                'status' => 'in:draft,published,cancelled',
                'speakers' => 'nullable|string',
                'sponsors' => 'nullable|string',
                'image' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $event->update($request->all());
            
            Log::info('Event updated successfully', [
                'event_id' => $event->id,
                'updater_id' => $user->id,
                'updater_role' => $user->role
            ]);
            
            return response()->json($event);
        } catch (\Exception $e) {
            Log::error('Failed to update event: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update event'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            // First check if the user is authenticated
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            // Try to find the event
            $event = Event::find($id);
            if (!$event) {
                return response()->json(['error' => 'Event not found', 'event_id' => $id], 404);
            }
            
            // Check authorization
            if (!$event->canBeManageBy($user)) {
                return response()->json(['error' => 'Unauthorized to delete this event'], 403);
            }
            
            // Check if event has sold tickets
            if ($event->max_tickets !== $event->available_tickets) {
                return response()->json(['error' => 'Cannot delete event with sold tickets'], 422);
            }

            // Store event details before deletion
            $eventDetails = [
                'id' => $event->id,
                'title' => $event->title
            ];

            // Delete the event
            $deleted = $event->delete();
            
            if (!$deleted) {
                throw new \Exception('Failed to delete event from database');
            }
            
            Log::info('Event deleted successfully', [
                'event_id' => $id,
                'event_title' => $eventDetails['title'],
                'deleter_id' => $user->id,
                'deleter_role' => $user->role
            ]);
            
            return response()->json([
                'message' => 'Event deleted successfully',
                'event' => $eventDetails
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete event', [
                'error_message' => $e->getMessage(),
                'event_id' => $id,
                'user_id' => $user->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to delete event',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addSpeaker(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);
            $user = Auth::user();
            
            if (!$event->canBeManageBy($user)) {
                return response()->json(['error' => 'Unauthorized to update this event'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'bio' => 'required|string',
                'photo_url' => 'required|url',
                'company' => 'required|string',
                'position' => 'required|string',
                'topic' => 'required|string',
                'speaking_time' => 'required|date_format:H:i'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $speaker = $event->addSpeaker($request->all());
            return response()->json($speaker, 201);
        } catch (\Exception $e) {
            Log::error('Failed to add speaker: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add speaker'], 500);
        }
    }

    public function updateSpeaker(Request $request, $id, $speakerId)
    {
        try {
            $event = Event::findOrFail($id);
            $user = Auth::user();
            
            if (!$event->canBeManageBy($user)) {
                return response()->json(['error' => 'Unauthorized to update this event'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'string',
                'bio' => 'string',
                'photo_url' => 'url',
                'company' => 'string',
                'position' => 'string',
                'topic' => 'string',
                'speaking_time' => 'date_format:H:i'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $event->updateSpeaker($speakerId, $request->all());
            return response()->json(['message' => 'Speaker updated successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to update speaker: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update speaker'], 500);
        }
    }

    public function removeSpeaker(Request $request, $id, $speakerId)
    {
        try {
            $event = Event::findOrFail($id);
            $user = Auth::user();
            
            if (!$event->canBeManageBy($user)) {
                return response()->json(['error' => 'Unauthorized to update this event'], 403);
            }

            $event->removeSpeaker($speakerId);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Failed to remove speaker: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to remove speaker'], 500);
        }
    }

    public function addSponsor(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);
            $user = Auth::user();
            
            if (!$event->canBeManageBy($user)) {
                return response()->json(['error' => 'Unauthorized to update this event'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'logo_url' => 'required|url',
                'website_url' => 'required|url',
                'tier' => 'required|in:platinum,gold,silver,bronze',
                'type' => 'required|in:main,regular'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $sponsor = $event->addSponsor($request->all());
            return response()->json($sponsor, 201);
        } catch (\Exception $e) {
            Log::error('Failed to add sponsor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add sponsor'], 500);
        }
    }

    public function updateSponsor(Request $request, $id, $sponsorId)
    {
        try {
            $event = Event::findOrFail($id);
            $user = Auth::user();
            
            if (!$event->canBeManageBy($user)) {
                return response()->json(['error' => 'Unauthorized to update this event'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'string',
                'logo_url' => 'url',
                'website_url' => 'url',
                'tier' => 'in:platinum,gold,silver,bronze',
                'type' => 'in:main,regular'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $event->updateSponsor($sponsorId, $request->all());
            return response()->json(['message' => 'Sponsor updated successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to update sponsor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update sponsor'], 500);
        }
    }

    public function removeSponsor(Request $request, $id, $sponsorId)
    {
        try {
            $event = Event::findOrFail($id);
            $user = Auth::user();
            
            if (!$event->canBeManageBy($user)) {
                return response()->json(['error' => 'Unauthorized to update this event'], 403);
            }

            $event->removeSponsor($sponsorId);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Failed to remove sponsor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to remove sponsor'], 500);
        }
    }
}
