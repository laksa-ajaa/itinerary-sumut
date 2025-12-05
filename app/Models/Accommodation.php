<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Accommodation extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'osm_id',
        'osm_type',
        'source',
        'name',
        'kind',
        'latitude',
        'longitude',
        'rating',
        'rating_avg',
        'rating_count',
        'city',
        'address',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];
}


