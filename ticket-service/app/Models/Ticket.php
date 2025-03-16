<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'event_id',
        'user_id',
        'price',
        'status',
        'purchase_date',
        'used_at',
        'cancelled_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_date' => 'datetime',
        'used_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate unique ticket number before creating
        static::creating(function ($ticket) {
            $ticket->ticket_number = $ticket->generateTicketNumber();
        });
    }

    /**
     * Get the payment associated with the ticket.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Generate a unique ticket number.
     */
    public function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $random = strtoupper(Str::random(6));
        $timestamp = now()->format('ymd');
        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Check if the ticket can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'confirmed' && 
               !$this->used_at && 
               !$this->cancelled_at;
    }

    /**
     * Check if the ticket can be used.
     */
    public function canBeUsed(): bool
    {
        return $this->status === 'confirmed' && 
               !$this->used_at && 
               !$this->cancelled_at;
    }

    /**
     * Mark ticket as used.
     */
    public function markAsUsed(): void
    {
        if (!$this->canBeUsed()) {
            throw new \Exception('Ticket cannot be used');
        }

        $this->used_at = now();
        $this->status = 'used';
        $this->save();
    }

    /**
     * Cancel the ticket.
     */
    public function cancel(): void
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('Ticket cannot be cancelled');
        }

        $this->cancelled_at = now();
        $this->status = 'cancelled';
        $this->save();
    }

    /**
     * Confirm the ticket after successful payment.
     */
    public function confirm(): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Ticket is not in pending status');
        }

        $this->status = 'confirmed';
        $this->purchase_date = now();
        $this->save();
    }

    /**
     * Get ticket details with payment information.
     */
    public function getFullDetails(): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'price' => $this->price,
            'status' => $this->status,
            'purchase_date' => $this->purchase_date,
            'used_at' => $this->used_at,
            'cancelled_at' => $this->cancelled_at,
            'payment' => $this->payment ? [
                'transaction_id' => $this->payment->transaction_id,
                'status' => $this->payment->status,
                'payment_method' => $this->payment->payment_method,
                'paid_at' => $this->payment->paid_at,
                'refunded_at' => $this->payment->refunded_at,
            ] : null
        ];
    }
}
