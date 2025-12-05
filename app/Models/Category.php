<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['slug', 'name', 'emoji'];
    public $timestamps = true;

    public function places()
    {
        return $this->belongsToMany(Place::class, 'place_category', 'category_id', 'place_id');
    }
}
