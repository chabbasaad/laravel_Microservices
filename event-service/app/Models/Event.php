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
        'sponsors'
    ];

    protected $casts = [
        'date' => 'datetime',
        'price' => 'decimal:2',
        'max_tickets' => 'integer',
        'available_tickets' => 'integer',
        'creator_id' => 'integer',
        'speakers' => 'array',
        'sponsors' => 'array'
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

        static::creating(function ($event) {
            // Add IDs to speakers if present
            if (!empty($event->speakers)) {
                $speakerId = 1;
                $speakers = $event->speakers;
                foreach ($speakers as &$speaker) {
                    $speaker['id'] = $speakerId++;
                }
                $event->speakers = $speakers;
            }

            // Add IDs to sponsors if present
            if (!empty($event->sponsors)) {
                $sponsorId = 1;
                $sponsors = $event->sponsors;
                foreach ($sponsors as &$sponsor) {
                    $sponsor['id'] = $sponsorId++;
                }
                $event->sponsors = $sponsors;
            }
        });
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

    /**
     * Get main sponsors of the event
     */
    public function getMainSponsors(): array
    {
        return array_filter($this->sponsors ?? [], function($sponsor) {
            return ($sponsor['type'] ?? '') === 'main';
        });
    }

    /**
     * Get regular sponsors of the event
     */
    public function getRegularSponsors(): array
    {
        return array_filter($this->sponsors ?? [], function($sponsor) {
            return ($sponsor['type'] ?? '') === 'regular';
        });
    }

    /**
     * Get platinum tier sponsors
     */
    public function getPlatinumSponsors(): array
    {
        return array_filter($this->sponsors ?? [], function($sponsor) {
            return ($sponsor['tier'] ?? '') === 'platinum';
        });
    }

    /**
     * Add a speaker to the event
     */
    public function addSpeaker(array $speaker)
    {
        $speakers = $this->speakers ?? [];
        $maxId = 0;
        
        // Find the highest existing ID
        foreach ($speakers as $existingSpeaker) {
            if (isset($existingSpeaker['id']) && $existingSpeaker['id'] > $maxId) {
                $maxId = $existingSpeaker['id'];
            }
        }
        
        // Add auto-incrementing ID
        $speaker['id'] = $maxId + 1;
        $speakers[] = $speaker;
        
        $this->speakers = $speakers;
        $this->save();
        
        return $speaker;
    }

    /**
     * Update speaker information
     */
    public function updateSpeaker($speakerId, array $data)
    {
        $speakers = $this->speakers ?? [];
        $updated = false;

        foreach ($speakers as &$speaker) {
            if ($speaker['id'] == $speakerId) {
                $speaker = array_merge($speaker, $data);
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            throw new \Exception('Speaker not found');
        }

        $this->speakers = $speakers;
        $this->save();
    }

    /**
     * Remove a speaker from the event
     */
    public function removeSpeaker($speakerId)
    {
        $speakers = $this->speakers ?? [];
        $filtered = array_filter($speakers, function($speaker) use ($speakerId) {
            return $speaker['id'] != $speakerId;
        });

        if (count($filtered) === count($speakers)) {
            throw new \Exception('Speaker not found');
        }

        $this->speakers = array_values($filtered);
        $this->save();
    }

    /**
     * Add a sponsor to the event
     */
    public function addSponsor(array $sponsor)
    {
        $sponsors = $this->sponsors ?? [];
        $maxId = 0;
        
        foreach ($sponsors as $existingSponsor) {
            if (isset($existingSponsor['id']) && $existingSponsor['id'] > $maxId) {
                $maxId = $existingSponsor['id'];
            }
        }
        
        $sponsor['id'] = $maxId + 1;
        $sponsors[] = $sponsor;
        
        $this->sponsors = $sponsors;
        $this->save();
        
        return $sponsor;
    }

    /**
     * Update sponsor information
     */
    public function updateSponsor($sponsorId, array $data)
    {
        $sponsors = $this->sponsors ?? [];
        $updated = false;

        foreach ($sponsors as &$sponsor) {
            if ($sponsor['id'] == $sponsorId) {
                $sponsor = array_merge($sponsor, $data);
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            throw new \Exception('Sponsor not found');
        }

        $this->sponsors = $sponsors;
        $this->save();
    }

    /**
     * Remove a sponsor from the event
     */
    public function removeSponsor($sponsorId)
    {
        $sponsors = $this->sponsors ?? [];
        $filtered = array_filter($sponsors, function($sponsor) use ($sponsorId) {
            return $sponsor['id'] != $sponsorId;
        });

        if (count($filtered) === count($sponsors)) {
            throw new \Exception('Sponsor not found');
        }

        $this->sponsors = array_values($filtered);
        $this->save();
    }
}
