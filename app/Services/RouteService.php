<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Route Service - calculates distances and durations using Haversine formula
 * Frontend uses Leaflet Routing Machine for route visualization
 */
class RouteService
{
    /**
     * Get distance and duration between two points
     * Uses Haversine formula for distance calculation
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
     * Optimize route order using nearest neighbor algorithm
     * Uses Haversine distance for optimization
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

                // Use Haversine for optimization
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
     * Calculate Haversine distance between two points
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Distance in kilometers
     */
    public function calculateHaversineDistance(
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
     * Calculate total distance for a route
     * 
     * @param array $coordinates Array of ['lat' => float, 'lng' => float]
     * @return float Total distance in kilometers
     */
    public function calculateTotalDistance(array $coordinates): float
    {
        if (count($coordinates) < 2) {
            return 0;
        }

        $total = 0;
        for ($i = 1; $i < count($coordinates); $i++) {
            $total += $this->calculateHaversineDistance(
                $coordinates[$i - 1]['lat'],
                $coordinates[$i - 1]['lng'],
                $coordinates[$i]['lat'],
                $coordinates[$i]['lng']
            );
        }

        return round($total, 2);
    }
}

