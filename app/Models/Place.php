<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'kind', 'description', 'open_time', 'close_time', 'entry_price', 'latitude', 'longitude', 'visit_count', 'provinces', 'city', 'subdistrict', 'street_name', 'postal_code', 'google_place_id', 'rating_avg', 'rating_count'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'place_category');
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'place_facility');
    }
}


