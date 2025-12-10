<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Place extends Model
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
        'description',
        'latitude',
        'longitude',
        'rating',
        'rating_avg',
        'rating_count',
        'city',
        'address',
        'tags',
        'website',
        'contact',
        'opening_hours',
        'facilities',
    ];

    protected $casts = [
        'tags' => 'array',
        'opening_hours' => 'array',
        'facilities' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'rating' => 'float',
        'rating_avg' => 'float',
        'rating_count' => 'integer',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'place_category', 'place_id', 'category_id');
    }

    public function ratings()
    {
        return $this->hasMany(UserRating::class, 'place_id');
    }
}
