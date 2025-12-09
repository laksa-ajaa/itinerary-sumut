<?php

namespace App\Http\Controllers;

use App\Services\RouteCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RouteCacheController extends Controller
{
    public function __construct(private RouteCacheService $routeCacheService)
    {
    }

    /**
     * GET /api/routes/cache?from_lat=&from_lng=&to_lat=&to_lng=&profile=
     */
    public function show(Request $request)
    {
        $validated = $request->validate([
            'from_lat' => 'required|numeric',
            'from_lng' => 'required|numeric',
            'to_lat' => 'required|numeric',
            'to_lng' => 'required|numeric',
            'profile' => 'nullable|string',
        ]);

        $profile = $validated['profile'] ?? 'mapbox/driving';
        $waypoints = [
            ['lat' => (float) $validated['from_lat'], 'lng' => (float) $validated['from_lng']],
            ['lat' => (float) $validated['to_lat'], 'lng' => (float) $validated['to_lng']],
        ];

        $cached = $this->routeCacheService->getCachedRoute($waypoints, $profile);
        if (!$cached) {
            return response()->json(['message' => 'Route not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'distance_meters' => $cached->distance_meters,
            'duration_seconds' => $cached->duration_seconds,
            'coordinates' => $cached->coordinates,
            'provider' => $cached->provider,
            'profile' => $cached->profile,
        ]);
    }

    /**
     * POST /api/routes/cache
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_lat' => 'required|numeric',
            'from_lng' => 'required|numeric',
            'to_lat' => 'required|numeric',
            'to_lng' => 'required|numeric',
            'distance_meters' => 'required|numeric',
            'duration_seconds' => 'nullable|numeric',
            'coordinates' => 'required|array|min:2',
            'coordinates.*' => 'array|size:2',
            'coordinates.*.0' => 'numeric',
            'coordinates.*.1' => 'numeric',
            'provider' => 'nullable|string',
            'profile' => 'nullable|string',
            'raw_response' => 'nullable|array',
        ]);

        $waypoints = [
            ['lat' => (float) $validated['from_lat'], 'lng' => (float) $validated['from_lng']],
            ['lat' => (float) $validated['to_lat'], 'lng' => (float) $validated['to_lng']],
        ];

        $cache = $this->routeCacheService->store($waypoints, [
            'distance_meters' => $validated['distance_meters'],
            'duration_seconds' => $validated['duration_seconds'] ?? null,
            'coordinates' => $validated['coordinates'],
            'provider' => $validated['provider'] ?? 'mapbox',
            'profile' => $validated['profile'] ?? 'mapbox/driving',
            'raw_response' => $validated['raw_response'] ?? null,
        ]);

        return response()->json([
            'distance_meters' => $cache->distance_meters,
            'duration_seconds' => $cache->duration_seconds,
            'coordinates' => $cache->coordinates,
            'provider' => $cache->provider,
            'profile' => $cache->profile,
        ]);
    }
}

