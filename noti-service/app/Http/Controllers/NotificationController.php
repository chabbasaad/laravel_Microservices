<?php

namespace App\Http\Controllers;

use App\Jobs\SendTicketPurchaseEmail;
use App\Jobs\SendTicketCancellationEmail;
use App\Jobs\TestEmailJob; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Queue a ticket purchase notification email
     */
    public function sendPurchaseNotification(Request $request)
    {
        Log::info('Purchase notification request received', [
            'data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'ticket_number' => 'required|string',
            'event.title' => 'required|string',
            'event.date' => 'required|string',
            'event.location' => 'required|string',
            'price' => 'required|numeric',
            'purchase_date' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Invalid notification request data', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all(),
                'validation_rules' => $validator->getRules()
            ]);
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Monitor queue before dispatch
            $queueSize = \DB::table('jobs')->where('queue', 'default')->count();
            $failedJobs = \DB::table('failed_jobs')->count();
            
            Log::info('Current queue status before dispatch', [
                'queue' => 'default',
                'size' => $queueSize,
                'failed_jobs' => $failedJobs,
                'notification_type' => 'purchase'
            ]);
            
            // Create and dispatch the job with required parameters
            SendTicketPurchaseEmail::dispatch($request->email, $request->all())
                ->onConnection('database')
                ->onQueue('default');

            Log::info('Purchase notification queued successfully', [
                'email' => $request->email,
                'ticket_number' => $request->ticket_number,
                'queue_metrics' => [
                    'size' => $queueSize + 1,
                    'failed_jobs' => $failedJobs
                ]
            ]);

            return response()->json([
                'message' => 'Purchase notification queued successfully',
                'queue_status' => [
                    'size' => $queueSize + 1,
                    'failed_jobs' => $failedJobs
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue purchase notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'queue' => 'default'
            ]);

            return response()->json([
                'error' => 'Failed to queue notification',
                'details' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Queue a ticket cancellation notification email
     */
    public function sendCancellationNotification(Request $request)
    {
        Log::info('Cancellation notification request received', [
            'data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'ticket_number' => 'required|string',
            'event.title' => 'required|string',
            'price' => 'required|numeric',
            'purchase_date' => 'required|string',
            'cancelled_at' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Invalid cancellation notification request data', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all(),
                'validation_rules' => $validator->getRules()
            ]);
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Monitor queue before dispatch
            $queueSize = \DB::table('jobs')->where('queue', 'default')->count();
            $failedJobs = \DB::table('failed_jobs')->count();
            
            Log::info('Current queue status before dispatch', [
                'queue' => 'default',
                'size' => $queueSize,
                'failed_jobs' => $failedJobs,
                'notification_type' => 'cancellation'
            ]);
            
            // Create and dispatch the job with required parameters
            SendTicketCancellationEmail::dispatch($request->email, $request->all())
                ->onConnection('database')
                ->onQueue('default');

            Log::info('Cancellation notification queued successfully', [
                'email' => $request->email,
                'ticket_number' => $request->ticket_number,
                'queue_metrics' => [
                    'size' => $queueSize + 1,
                    'failed_jobs' => $failedJobs
                ]
            ]);

            return response()->json([
                'message' => 'Cancellation notification queued successfully',
                'queue_status' => [
                    'size' => $queueSize + 1,
                    'failed_jobs' => $failedJobs
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue cancellation notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'queue' => 'default'
            ]);

            return response()->json([
                'error' => 'Failed to queue notification',
                'details' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Test the queue system with a simple job
     */
    public function testQueue()
    {
        try {
            $job = new TestEmailJob();
            $job->onConnection('database')->onQueue('default');
            
            // Monitor queue before dispatch
            $queueSize = \DB::table('jobs')->where('queue', 'default')->count();
            Log::info('Current queue status before test job', [
                'queue' => 'default',
                'size' => $queueSize,
                'type' => 'test'
            ]);
            
            $job->dispatch();

            Log::info('Test job queued successfully', [
                'queue_size' => $queueSize + 1
            ]);

            return response()->json([
                'message' => 'Test job dispatched to database queue',
                'queue_status' => [
                    'size' => $queueSize + 1
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue test job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to queue test job',
                'details' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get queue health metrics
     */
    public function queueHealth()
    {
        try {
            $metrics = [
                'queue_size' => \DB::table('jobs')->where('queue', 'default')->count(),
                'failed_jobs' => \DB::table('failed_jobs')->count(),
                'recent_jobs' => \DB::table('jobs')
                    ->where('created_at', '>=', now()->subHours(1))
                    ->count(),
                'recent_failed_jobs' => \DB::table('failed_jobs')
                    ->where('failed_at', '>=', now()->subHours(1))
                    ->count(),
                'queue_latency' => $this->getQueueLatency(),
                'timestamp' => now()->toIso8601String()
            ];

            return response()->json([
                'status' => 'healthy',
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get queue health metrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'unhealthy',
                'error' => 'Failed to get queue metrics'
            ], 500);
        }
    }

    /**
     * Calculate queue latency by checking the oldest job
     */
    private function getQueueLatency()
    {
        $oldestJob = \DB::table('jobs')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$oldestJob) {
            return 0;
        }

        return now()->diffInSeconds(\Carbon\Carbon::parse($oldestJob->created_at));
    }
}
