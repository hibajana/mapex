<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryPlace extends Model
{
    protected $fillable = [
        'itinerary_id', 'day', 'order', 'name', 'description',
        'reason_responsible', 'health_reason', 'effort_level',
        'accessible', 'lat', 'lng', 'image_query',
    ];

    protected $casts = [
        'accessible' => 'boolean',
        'lat'        => 'float',
        'lng'        => 'float',
    ];

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function getEffortBadgeColorAttribute(): string
    {
        return match ($this->effort_level) {
            'low'    => 'green',
            'medium' => 'amber',
            'high'   => 'red',
            default  => 'gray',
        };
    }
}
