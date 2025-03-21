<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketPurchaseEmail implements ShouldQueue
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
            Log::info('Sending ticket purchase confirmation email', [
                'email' => $this->userEmail,
                'ticket_number' => $this->ticketDetails['ticket_number'] ?? null
            ]);

            Mail::send('emails.ticket-purchase', ['ticket' => $this->ticketDetails], function ($message) {
                $message->to($this->userEmail)
                        ->subject('Your Ticket Purchase Confirmation');
            });

            Log::info('Ticket purchase confirmation email sent successfully', [
                'email' => $this->userEmail,
                'ticket_number' => $this->ticketDetails['ticket_number'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ticket purchase confirmation email', [
                'email' => $this->userEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retry the job with exponential backoff
            if ($this->attempts() < 3) {
                $this->release(pow(2, $this->attempts()) * 60);
            }
        }
    }
}
