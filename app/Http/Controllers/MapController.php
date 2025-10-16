<?php

namespace App\Http\Controllers;

use App\Models\Place;

class MapController extends Controller
{
    public function geojson()
    {
        $places = Place::with('categories:id,name')->limit(1000)->get();
        return [
            'type' => 'FeatureCollection',
            'features' => $places->map(function ($p) {
                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [(float)$p->longitude, (float)$p->latitude],
                    ],
                    'properties' => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'category' => $p->categories->pluck('name'),
                    ],
                ];
            }),
        ];
    }
}


