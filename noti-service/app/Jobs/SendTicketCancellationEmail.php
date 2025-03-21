<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketCancellationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userEmail;
    protected $ticketDetails;

    /**
     * Create a new job instance.
     */
    public function __construct($userEmail, $ticketDetails)
    {
        $this->userEmail = $userEmail;
        $this->ticketDetails = $ticketDetails;
        $this->onConnection('database');
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Preparing to send ticket cancellation email', [
                'email' => $this->userEmail,
                'ticket_details' => [
                    'ticket_number' => $this->ticketDetails['ticket_number'] ?? null,
                    'event_title' => $this->ticketDetails['event']['title'] ?? null,
                    'price' => $this->ticketDetails['price'] ?? null,
                    'cancelled_at' => $this->ticketDetails['cancelled_at'] ?? null
                ]
            ]);

            // Validate required fields
            if (!$this->validateTicketDetails()) {
                throw new \Exception('Missing required ticket details for cancellation email');
            }

            Mail::send('emails.ticket-cancellation', ['ticket' => $this->ticketDetails], function ($message) {
                $message->to($this->userEmail)
                        ->subject('Your Ticket Cancellation Confirmation - ' . ($this->ticketDetails['event']['title'] ?? 'Event'));
            });

            Log::info('Ticket cancellation email sent successfully', [
                'email' => $this->userEmail,
                'ticket_number' => $this->ticketDetails['ticket_number']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ticket cancellation email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $this->userEmail,
                'ticket_details' => $this->ticketDetails
            ]);

            // Retry the job with exponential backoff
            if ($this->attempts() < 3) {
                $this->release(pow(2, $this->attempts()) * 60); // 1 min, 2 min, 4 min
                return;
            }

            throw $e;
        }
    }

    /**
     * Validate ticket details
     */
    private function validateTicketDetails(): bool
    {
        $required = [
            'ticket_number',
            'event.title',
            'price',
            'cancelled_at'
        ];

        foreach ($required as $field) {
            if (str_contains($field, '.')) {
                [$parent, $child] = explode('.', $field);
                if (!isset($this->ticketDetails[$parent][$child])) {
                    Log::error('Missing required field for cancellation email', [
                        'field' => $field,
                        'ticket_details' => $this->ticketDetails
                    ]);
                    return false;
                }
            } else if (!isset($this->ticketDetails[$field])) {
                Log::error('Missing required field for cancellation email', [
                    'field' => $field,
                    'ticket_details' => $this->ticketDetails
                ]);
                return false;
            }
        }

        return true;
    }
}
