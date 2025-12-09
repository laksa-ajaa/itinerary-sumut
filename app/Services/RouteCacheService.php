<?php

namespace App\Services;

use App\Models\RouteCache;

class RouteCacheService
{
    /**
     * Retrieve cached route by waypoints and profile.
     *
     * @param array $waypoints Array of [['lat' => float, 'lng' => float], ...] (2 points expected)
     * @param string $profile
     * @return RouteCache|null
     */
    public function getCachedRoute(array $waypoints, string $profile = 'mapbox/driving'): ?RouteCache
    {
        if (count($waypoints) < 2) {
            return null;
        }

        $hash = $this->buildHash($waypoints, $profile);

        return RouteCache::where('hash', $hash)->first();
    }

    /**
     * Store a new cached route (idempotent by hash).
     *
     * @param array $waypoints [['lat' => float, 'lng' => float], ...]
     * @param array $payload ['distance_meters', 'duration_seconds', 'coordinates', 'provider', 'profile', 'raw_response']
     * @return RouteCache
     */
    public function store(array $waypoints, array $payload): RouteCache
    {
        $profile = $payload['profile'] ?? 'mapbox/driving';
        $hash = $this->buildHash($waypoints, $profile);

        return RouteCache::updateOrCreate(
            ['hash' => $hash],
            [
                'from_lat' => $this->roundCoord($waypoints[0]['lat']),
                'from_lng' => $this->roundCoord($waypoints[0]['lng']),
                'to_lat' => $this->roundCoord($waypoints[1]['lat']),
                'to_lng' => $this->roundCoord($waypoints[1]['lng']),
                'provider' => $payload['provider'] ?? 'mapbox',
                'profile' => $profile,
                'distance_meters' => $payload['distance_meters'],
                'duration_seconds' => $payload['duration_seconds'] ?? null,
                'coordinates' => $payload['coordinates'],
                'raw_response' => $payload['raw_response'] ?? null,
            ]
        );
    }

    /**
     * Build deterministic hash for a route.
     */
    private function buildHash(array $waypoints, string $profile): string
    {
        $coords = collect($waypoints)
            ->map(function ($point) {
                return [
                    'lat' => $this->roundCoord($point['lat']),
                    'lng' => $this->roundCoord($point['lng']),
                ];
            })
            ->values()
            ->toArray();

        return md5(json_encode([
            'profile' => $profile,
            'coords' => $coords,
        ]));
    }

    private function roundCoord(float $value): float
    {
        return round($value, 5); // ~1 meter precision
    }
}

