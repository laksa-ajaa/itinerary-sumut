<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OsrmService
{
    // OSRM public instance (you can change this to your own instance)
    private const OSRM_BASE_URL = 'https://router.project-osrm.org';

    // Alternative: use local instance if available
    // private const OSRM_BASE_URL = 'http://localhost:5000';

    // Timeout and retry settings
    private const TIMEOUT_SECONDS = 3; // Reduced from 5 to 3
    private const MAX_RETRIES = 2;
    private const CACHE_TTL = 3600; // 1 hour cache

    /**
     * Get route between multiple waypoints with caching and retry
     * 
     * @param array $coordinates Array of [lat, lng] pairs
     * @return array|null Route data with geometry, distance, duration
     */
    public function getRoute(array $coordinates): ?array
    {
        if (count($coordinates) < 2) {
            return null;
        }

        // Create cache key from coordinates
        $cacheKey = 'osrm_route_' . md5(json_encode($coordinates));

        // Try to get from cache first
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            Log::info('OSRM route retrieved from cache');
            return $cached;
        }

        // Try OSRM with retries
        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $route = $this->fetchRouteFromOsrm($coordinates);

                if ($route) {
                    // Cache successful result
                    Cache::put($cacheKey, $route, self::CACHE_TTL);
                    Log::info('OSRM route fetched successfully', ['attempt' => $attempt]);
                    return $route;
                }
            } catch (\Exception $e) {
                Log::warning("OSRM attempt {$attempt} failed", [
                    'error' => $e->getMessage(),
                    'coordinates_count' => count($coordinates)
                ]);

                if ($attempt < self::MAX_RETRIES) {
                    // Wait before retry (exponential backoff)
                    usleep(500000 * $attempt); // 0.5s, 1s
                }
            }
        }

        // All retries failed, return null to trigger fallback
        Log::error('OSRM all retries failed, using fallback', [
            'coordinates_count' => count($coordinates)
        ]);

        return null;
    }

    /**
     * Fetch route from OSRM API
     */
    private function fetchRouteFromOsrm(array $coordinates): ?array
    {
        // Format coordinates for OSRM: lng,lat (note: OSRM uses lng,lat order)
        $coordsString = implode(';', array_map(function ($coord) {
            return $coord['lng'] . ',' . $coord['lat'];
        }, $coordinates));

        $url = self::OSRM_BASE_URL . '/route/v1/driving/' . $coordsString;

        // Make request with short timeout
        $response = Http::timeout(self::TIMEOUT_SECONDS)
            ->retry(1, 100) // Internal retry with 100ms delay
            ->get($url, [
                'overview' => 'simplified',
                'geometries' => 'geojson',
                'steps' => 'false',
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['code']) && $data['code'] === 'Ok' && !empty($data['routes'])) {
                $route = $data['routes'][0];

                return [
                    'geometry' => $route['geometry'] ?? null,
                    'distance' => $route['distance'] ?? 0,
                    'duration' => $route['duration'] ?? 0,
                    'distance_km' => round(($route['distance'] ?? 0) / 1000, 2),
                    'duration_minutes' => round(($route['duration'] ?? 0) / 60, 1),
                    'waypoints' => $data['waypoints'] ?? [],
                ];
            }
        }

        return null;
    }

    /**
     * Get distance and duration between two points
     * Always returns result (uses fallback if OSRM fails)
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return array ['distance_km', 'duration_minutes']
     */
    public function getDistanceAndDuration(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): array {
        // Try OSRM first (with cache)
        $route = $this->getRoute([
            ['lat' => $lat1, 'lng' => $lng1],
            ['lat' => $lat2, 'lng' => $lng2],
        ]);

        if ($route) {
            return [
                'distance_km' => $route['distance_km'],
                'duration_minutes' => $route['duration_minutes'],
            ];
        }

        // Fallback to Haversine calculation
        $distanceKm = $this->calculateHaversineDistance($lat1, $lng1, $lat2, $lng2);

        // Estimate duration based on distance
        // Use realistic speeds: 30 km/h in city, 60 km/h on highway
        // For simplicity, use 40 km/h average
        $durationMinutes = max(5, (int)ceil(($distanceKm / 40) * 60));

        return [
            'distance_km' => round($distanceKm, 2),
            'duration_minutes' => $durationMinutes,
        ];
    }

    /**
     * Optimize route order using nearest neighbor
     * This doesn't use OSRM's optimization endpoint to avoid timeout issues
     * 
     * @param array $places Array of places with lat/lng
     * @param array|null $startPoint ['lat' => float, 'lng' => float]
     * @return array Optimized order of places
     */
    public function optimizeRouteOrder(array $places, ?array $startPoint = null): array
    {
        if (empty($places)) {
            return [];
        }

        if (count($places) === 1) {
            return $places;
        }

        $optimized = [];
        $remaining = $places;
        $current = $startPoint ?? [
            'lat' => $places[0]['latitude'] ?? $places[0]['lat'],
            'lng' => $places[0]['longitude'] ?? $places[0]['lng'],
        ];

        while (!empty($remaining)) {
            $nearest = null;
            $minDistance = PHP_FLOAT_MAX;
            $nearestIndex = null;

            foreach ($remaining as $index => $place) {
                $placeLat = $place['latitude'] ?? $place['lat'];
                $placeLng = $place['longitude'] ?? $place['lng'];

                // Use Haversine for optimization (faster than OSRM API calls)
                $distance = $this->calculateHaversineDistance(
                    $current['lat'],
                    $current['lng'],
                    $placeLat,
                    $placeLng
                );

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearest = $place;
                    $nearestIndex = $index;
                }
            }

            if ($nearest) {
                $optimized[] = $nearest;
                $current = [
                    'lat' => $nearest['latitude'] ?? $nearest['lat'],
                    'lng' => $nearest['longitude'] ?? $nearest['lng'],
                ];
                unset($remaining[$nearestIndex]);
                $remaining = array_values($remaining);
            } else {
                break;
            }
        }

        return $optimized;
    }

    /**
     * Calculate Haversine distance (fallback method)
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Distance in kilometers
     */
    private function calculateHaversineDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Clear OSRM cache (useful for debugging)
     */
    public function clearCache(): void
    {
        Cache::flush();
        Log::info('OSRM cache cleared');
    }

    /**
     * Check if OSRM service is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get(self::OSRM_BASE_URL . '/route/v1/driving/98.6,3.5;98.7,3.6', [
                'overview' => 'false',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
