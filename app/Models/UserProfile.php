<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 'preferred_language', 'reduced_mobility', 'needs_shade',
        'physical_endurance', 'budget', 'comfort_level', 'allergies',
        'tourism_preferences',
    ];

    protected $casts = [
        'reduced_mobility'     => 'boolean',
        'needs_shade'          => 'boolean',
        'allergies'            => 'array',
        'tourism_preferences'  => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
