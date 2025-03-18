<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'location',
        'max_tickets',
        'available_tickets',
        'price',
        'creator_id',
        'status',
        'speakers',
        'sponsors',
        'image'
    ];

    protected $casts = [
        'date' => 'datetime',
        'price' => 'decimal:2',
        'max_tickets' => 'integer',
        'available_tickets' => 'integer',
        'creator_id' => 'integer'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
    }

    /**
     * Get speakers as array
     */
    public function getSpeakersArray(): array
    {
        return array_map('trim', explode(',', $this->speakers ?? ''));
    }

    /**
     * Get sponsors as array
     */
    public function getSponsorsArray(): array
    {
        return array_map('trim', explode(',', $this->sponsors ?? ''));
    }

    /**
     * Set speakers from array
     */
    public function setSpeakersAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['speakers'] = implode(', ', $value);
        } else {
            $this->attributes['speakers'] = $value;
        }
    }

    /**
     * Set sponsors from array
     */
    public function setSponsorsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['sponsors'] = implode(', ', $value);
        } else {
            $this->attributes['sponsors'] = $value;
        }
    }

    /**
     * Check if a user can manage this event
     *
     * @param ?User $user
     * @return bool
     */
    public function canBeManageBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->role === 'admin' || 
               ($user->role === 'event_creator' && $this->creator_id == $user->id);
    }

    /**
     * Check if a user can view this event
     *
     * @param ?User $user
     * @return bool
     */
    public function canBeViewedBy(?User $user): bool
    {
        // Published events can be viewed by any authenticated user
        if ($this->status === 'published' && $user) {
            return true;
        }

        // Draft events can only be viewed by admins and their creators
        if (!$user) {
            return false;
        }

        return $user->role === 'admin' || 
               ($user->role === 'event_creator' && $this->creator_id == $user->id);
    }
}
