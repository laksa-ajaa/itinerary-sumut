<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index()
    {
        return Place::with(['categories:id,name', 'facilities:id,name'])->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'open_time' => 'nullable',
            'close_time' => 'nullable',
            'entry_price' => 'nullable|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'provinces' => 'nullable|string',
            'city' => 'nullable|string',
            'subdistrict' => 'nullable|string',
            'street_name' => 'nullable|string',
            'postal_code' => 'nullable|string',
        ]);
        $place = Place::create($data);
        return response()->json($place, 201);
    }

    public function show(Place $place)
    {
        return $place->load(['categories:id,name', 'facilities:id,name']);
    }

    public function update(Request $request, Place $place)
    {
        $place->update($request->all());
        return $place;
    }

    public function destroy(Place $place)
    {
        $place->delete();
        return response()->noContent();
    }
}


