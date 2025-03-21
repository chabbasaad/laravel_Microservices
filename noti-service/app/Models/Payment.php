<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'transaction_id',
        'amount',
        'status',
        'payment_method',
        'payment_details',
        'error_message',
        'paid_at',
        'refunded_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime'
    ];

    protected $hidden = [
        'payment_details',
        'deleted_at'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate unique transaction ID before creating
        static::creating(function ($payment) {
            $payment->transaction_id = $payment->generateTransactionId();
        });
    }

    /**
     * Get the ticket associated with the payment.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Generate a unique transaction ID.
     */
    protected function generateTransactionId(): string
    {
        $prefix = 'TRX';
        $random = strtoupper(Str::random(8));
        $timestamp = now()->format('ymdHis');
        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Process a payment.
     */
    public function process(array $paymentDetails): bool
    {
        try {
            // Mask card number for storage
            $maskedDetails = $this->maskCardDetails($paymentDetails);
            $this->payment_details = $maskedDetails;

            // Simulate payment processing
            // In a real application, this would integrate with a payment gateway
            $success = rand(0, 10) > 2; // 80% success rate for simulation

            if ($success) {
                $this->status = 'completed';
                $this->paid_at = now();
                $this->save();

                // Confirm the ticket
                $this->ticket->confirm();
                return true;
            } else {
                $this->status = 'failed';
                $this->error_message = 'Payment processing failed';
                $this->save();
                return false;
            }
        } catch (\Exception $e) {
            $this->status = 'failed';
            $this->error_message = $e->getMessage();
            $this->save();
            return false;
        }
    }

    /**
     * Process a refund.
     */
    public function refund(): bool
    {
        if ($this->status !== 'completed' || $this->refunded_at) {
            throw new \Exception('Payment cannot be refunded');
        }

        try {
            // Simulate refund processing
            // In a real application, this would integrate with a payment gateway
            $success = rand(0, 10) > 2; // 80% success rate for simulation

            if ($success) {
                $this->status = 'refunded';
                $this->refunded_at = now();
                $this->save();

                // Cancel the ticket
                $this->ticket->cancel();
                return true;
            } else {
                $this->error_message = 'Refund processing failed';
                $this->save();
                return false;
            }
        } catch (\Exception $e) {
            $this->error_message = $e->getMessage();
            $this->save();
            return false;
        }
    }

    /**
     * Mask sensitive card details for storage.
     */
    protected function maskCardDetails(array $details): array
    {
        if (isset($details['card_number'])) {
            $details['card_number'] = '****' . substr($details['card_number'], -4);
        }
        if (isset($details['cvv'])) {
            $details['cvv'] = '***';
        }
        return $details;
    }

    /**
     * Get payment details for display.
     */
    public function getDisplayDetails(): array
    {
        return [
            'transaction_id' => $this->transaction_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at,
            'refunded_at' => $this->refunded_at,
            'error_message' => $this->error_message,
            'card_number' => $this->payment_details['card_number'] ?? null,
        ];
    }
}
