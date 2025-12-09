<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'hash',
        'from_lat',
        'from_lng',
        'to_lat',
        'to_lng',
        'provider',
        'profile',
        'distance_meters',
        'duration_seconds',
        'coordinates',
        'raw_response',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'raw_response' => 'array',
    ];
}

