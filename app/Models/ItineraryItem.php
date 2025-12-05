<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ItineraryItem extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'itinerary_id',
        'day',
        'item_id',
        'item_type',
        'start_time',
        'end_time',
        'order_index'
    ];

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class, 'itinerary_id');
    }

    public function item()
    {
        // Semua item itinerary sekarang direferensikan ke tabel places,
        // dengan variasi jenis dibedakan lewat kolom "kind" di Place.
        return $this->belongsTo(Place::class, 'item_id');
    }
}
