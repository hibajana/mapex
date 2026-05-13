<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Itinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trip_title',
        'mode',
        'eco_score',
        'comfort_score',
        'duration_days',
        'user_preferences_snapshot',
        'health_snapshot',
        'status',
    ];

    protected $casts = [
        'user_preferences_snapshot' => 'array',
        'health_snapshot'            => 'array',
        'reduced_mobility'           => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function places(): HasMany
    {
        return $this->hasMany(ItineraryPlace::class)->orderBy('day')->orderBy('order');
    }

    public function placesByDay(): \Illuminate\Support\Collection
    {
        return $this->places->groupBy('day');
    }

    protected function ecoScoreLabel(): Attribute
    {
        return Attribute::make(get: function () {
            return match (true) {
                $this->eco_score >= 80 => 'Excellent',
                $this->eco_score >= 60 => 'Bon',
                $this->eco_score >= 40 => 'Moyen',
                default                => 'Faible',
            };
        });
    }

    protected function ecoScoreColor(): Attribute
    {
        return Attribute::make(get: function () {
            return match (true) {
                $this->eco_score >= 80 => 'green',
                $this->eco_score >= 60 => 'teal',
                $this->eco_score >= 40 => 'amber',
                default                => 'red',
            };
        });
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)->latest();
    }
}
