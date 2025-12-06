<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Itinerary extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'title',
        'start_date',
        'day_count',
        'activity_level',
        'preferences',
        'generated_payload'
    ];

    protected $casts = [
        'preferences' => 'array',
        'generated_payload' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(ItineraryItem::class, 'itinerary_id');
    }
}
