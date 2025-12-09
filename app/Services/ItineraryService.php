<?php

namespace App\Services;

use App\Models\Place;
use App\Services\RouteService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ItineraryService
{
    protected $routeService;

    // Dynamic visit duration parameters (Approach A - Long Visit)
    private const MAX_VISIT = 300;  // 5 hours
    private const MIN_VISIT = 45;   // 45 minutes

    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    /**
     * Generate natural itinerary with routing optimization
     */
    public function generateItinerary(
        ?int $userId,
        array $placesByDay,
        array $activityLevels,
        int $durationDays,
        ?string $startDate,
        array $categoryNames,
        string $startTime = '08:00',
        ?string $startLocation = null,
        ?float $startLat = null,
        ?float $startLng = null
    ): array {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now();

        // Get all place IDs
        $allPlaceIds = [];
        foreach ($placesByDay as $dayPlaceIds) {
            $allPlaceIds = array_merge($allPlaceIds, $dayPlaceIds);
        }
        $allPlaceIds = array_unique($allPlaceIds);

        // Get all places
        $allPlaces = Place::whereIn('id', $allPlaceIds)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->filter(function ($place) {
                return is_numeric($place->latitude) && is_numeric($place->longitude);
            })
            ->keyBy('id');

        if ($allPlaces->isEmpty()) {
            return $this->emptyItinerary($startDate, $durationDays);
        }

        // Build start point
        $startPoint = null;
        if ($startLat && $startLng) {
            $startPoint = ['lat' => $startLat, 'lng' => $startLng];
        }

        // Parse start time
        [$startHour, $startMinute] = explode(':', $startTime);
        $startDateTime = $startDate->copy()->setTime((int)$startHour, (int)$startMinute);

        // Build daily itinerary
        $dailyPlans = $this->buildDailyItinerary(
            $placesByDay,
            $activityLevels,
            $allPlaces,
            $durationDays,
            $startPoint,
            $startDateTime
        );

        return [
            'metadata' => [
                'duration_days' => $durationDays,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $startDate->copy()->addDays($durationDays - 1)->format('Y-m-d'),
                'start_time' => $startTime,
                'total_places' => count($allPlaceIds),
                'activity_levels' => $activityLevels,
                'categories' => $categoryNames,
                'start_location' => $startLocation,
            ],
            'daily_plans' => $dailyPlans,
            'summary' => $this->generateSummary($dailyPlans),
        ];
    }

    /**
     * Get number of places per day based on activity level
     */
    private function getPlacesPerDay(string $activityLevel): array
    {
        return match ($activityLevel) {
            'santai' => ['min' => 2, 'max' => 3],
            'normal' => ['min' => 3, 'max' => 5],
            'padat' => ['min' => 5, 'max' => 7],
            default => ['min' => 3, 'max' => 5],
        };
    }

    /**
     * Build daily itinerary with routing optimization
     */
    private function buildDailyItinerary(
        array $placesByDay,
        array $activityLevels,
        Collection $allPlaces,
        int $durationDays,
        ?array $startPoint,
        Carbon $startDateTime
    ): array {
        $dailyPlans = [];

        for ($day = 1; $day <= $durationDays; $day++) {
            $dayKey = (string)$day;
            $dayPlaceIds = $placesByDay[$dayKey] ?? [];

            if (empty($dayPlaceIds)) {
                continue;
            }

            // Get places for this day
            $dailyPlaces = $allPlaces->whereIn('id', $dayPlaceIds);

            if ($dailyPlaces->isEmpty()) {
                continue;
            }

            // Determine date and start time for this day
            if ($day === 1) {
                $currentDate = $startDateTime->copy();
            } else {
                // Day 2+: start at 8:00 AM by default
                $currentDate = $startDateTime->copy()->addDays($day - 1)->setTime(8, 0);
            }

            // Determine starting point for this day
            $currentStartPoint = $this->getDayStartPoint($day, $startPoint, $dailyPlans);

            Log::info("Processing day {$day}", [
                'current_start_point' => $currentStartPoint,
                'places_count' => $dailyPlaces->count(),
                'activity_level' => $activityLevels[$dayKey] ?? 'normal',
            ]);

            // Optimize route for selected places
            $optimizedRoute = $this->optimizeRoute($dailyPlaces, $currentStartPoint);

            // Get route geometry (with fallback)
            $routeGeometry = $this->getRouteGeometry($optimizedRoute, $currentStartPoint);

            // Build schedule with time allocation
            // For day 1, use startDateTime; for other days, use 8:00 AM
            $isLastDay = ($day === $durationDays);

            $schedule = $this->buildDailySchedule(
                $optimizedRoute,
                $currentDate,
                $routeGeometry,
                $currentStartPoint,
                $startPoint, // Original start point (home)
                $isLastDay
            );

            $dailyPlans[] = [
                'day' => $day,
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->translatedFormat('l'),
                'start_time' => $currentDate->format('H:i'),
                'start_point' => $currentStartPoint,
                'places' => $schedule,
                'route_geometry' => $routeGeometry,
                'activity_level' => $activityLevels[$dayKey] ?? 'normal',
                'stats' => [
                    'total_places' => count(array_filter($schedule, fn($s) => $s['type'] === 'place')),
                    'total_distance' => $routeGeometry['distance_km'] ?? 0,
                    'estimated_cost' => $this->estimateDailyCost($schedule),
                ]
            ];
        }

        return $dailyPlans;
    }

    /**
     * Get starting point for a specific day
     * Always starts from initial start point
     */
    private function getDayStartPoint(int $day, ?array $initialStart, array $dailyPlans): ?array
    {
        // Always start from initial start point
        return $initialStart;
    }

    /**
     * Get route geometry (frontend will use Leaflet Routing Machine for visualization)
     */
    private function getRouteGeometry(Collection $places, ?array $startPoint): array
    {
        if ($places->isEmpty()) {
            return ['geometry' => null, 'distance_km' => 0, 'duration_minutes' => 0];
        }

        // Build coordinates array
        $coordinates = [];

        if ($startPoint) {
            $coordinates[] = $startPoint;
        }

        foreach ($places as $place) {
            $coordinates[] = [
                'lat' => $place->latitude,
                'lng' => $place->longitude
            ];
        }

        // Calculate route using Haversine (frontend will use Leaflet Routing Machine for visualization)
        return $this->calculateFallbackRoute($coordinates);
    }

    /**
     * Calculate fallback route using Haversine distance
     */
    private function calculateFallbackRoute(array $coordinates): array
    {
        $totalDistance = $this->routeService->calculateTotalDistance($coordinates);

        // Estimate duration: 30 km/h average speed
        $durationMinutes = ($totalDistance / 30) * 60;

        // Create simple LineString geometry for map (straight lines)
        // Frontend Leaflet Routing Machine will show actual route
        $lineCoordinates = array_map(fn($c) => [$c['lng'], $c['lat']], $coordinates);

        return [
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $lineCoordinates
            ],
            'distance_km' => round($totalDistance, 2),
            'duration_minutes' => round($durationMinutes, 1),
        ];
    }

    /**
     * Select places for a single day based on proximity
     */
    private function selectDailyPlaces(
        Collection $remainingPlaces,
        int $targetCount,
        ?array $startPoint
    ): Collection {
        if ($remainingPlaces->isEmpty()) {
            return collect();
        }

        $selected = collect();
        $currentPoint = $startPoint;

        // If no start point, use first place
        if (!$currentPoint && $remainingPlaces->isNotEmpty()) {
            $firstPlace = $remainingPlaces->first();
            $currentPoint = ['lat' => $firstPlace->latitude, 'lng' => $firstPlace->longitude];
        }

        for ($i = 0; $i < $targetCount && $remainingPlaces->isNotEmpty(); $i++) {
            $nearest = $this->findNearestPlace($remainingPlaces, $currentPoint);

            if ($nearest) {
                $selected->push($nearest);
                $remainingPlaces = $remainingPlaces->reject(fn($p) => $p->id === $nearest->id);
                $currentPoint = ['lat' => $nearest->latitude, 'lng' => $nearest->longitude];
            }
        }

        return $selected;
    }

    /**
     * Find nearest place to a point
     */
    private function findNearestPlace(Collection $places, ?array $point): ?Place
    {
        if (!$point || $places->isEmpty()) {
            return $places->first();
        }

        return $places->map(function ($place) use ($point) {
            $place->temp_distance = $this->calculateDistance(
                $point['lat'],
                $point['lng'],
                $place->latitude,
                $place->longitude
            );
            return $place;
        })->sortBy('temp_distance')->first();
    }

    /**
     * Optimize route using nearest neighbor algorithm
     */
    private function optimizeRoute(Collection $places, ?array $startPoint): Collection
    {
        if ($places->isEmpty()) {
            return $places;
        }

        if ($places->count() === 1) {
            return $places;
        }

        // Use RouteService to optimize route order
        try {
            $placesArray = $places->map(fn($p) => [
                'id' => $p->id,
                'latitude' => $p->latitude,
                'longitude' => $p->longitude,
                'name' => $p->name,
                'kind' => $p->kind,
                'price' => $p->price,
                'rating_avg' => $p->rating_avg,
                'description' => $p->description,
            ])->toArray();

            $optimized = $this->routeService->optimizeRouteOrder($placesArray, $startPoint);

            // Convert back to Place models
            return collect($optimized)->map(function ($data) use ($places) {
                return $places->firstWhere('id', $data['id']);
            })->filter();
        } catch (\Exception $e) {
            Log::warning('Route optimization failed, using simple nearest neighbor', [
                'error' => $e->getMessage()
            ]);

            // Fallback to simple nearest neighbor
            return $this->simpleNearestNeighbor($places, $startPoint);
        }
    }

    /**
     * Simple nearest neighbor optimization (fallback)
     */
    private function simpleNearestNeighbor(Collection $places, ?array $startPoint): Collection
    {
        $optimized = collect();
        $remaining = $places->values();
        $current = $startPoint;

        while ($remaining->isNotEmpty()) {
            $nearest = $this->findNearestPlace($remaining, $current);
            if ($nearest) {
                $optimized->push($nearest);
                $remaining = $remaining->reject(fn($p) => $p->id === $nearest->id);
                $current = ['lat' => $nearest->latitude, 'lng' => $nearest->longitude];
            } else {
                break;
            }
        }

        return $optimized;
    }

    /**
     * Calculate dynamic visit duration based on place count
     */
    private function calculateVisitDuration(int $placeCount): int
    {
        if ($placeCount <= 0) {
            return self::MIN_VISIT;
        }

        // Formula: MAX_VISIT * (1 / placeCount * 1.5)
        $visitDuration = self::MAX_VISIT * (1 / $placeCount * 1.5);

        // Clamp between MIN_VISIT and MAX_VISIT
        return max(self::MIN_VISIT, min((int)round($visitDuration), self::MAX_VISIT));
    }

    /**
     * Build daily schedule with time slots
     */
    private function buildDailySchedule(
        Collection $places,
        Carbon $date,
        array $routeGeometry,
        ?array $startPoint = null,
        ?array $originalStartPoint = null,
        bool $isLastDay = false
    ): array {
        $schedule = [];
        // Use the time from $date (already set with start time for day 1, or 8:00 for other days)
        $currentTime = $date->copy();

        // Calculate dynamic visit duration based on place count
        $placeCount = $places->count();
        $visitDuration = $this->calculateVisitDuration($placeCount);

        foreach ($places as $index => $place) {

            // Add travel time from previous location as separate item
            $travelDuration = null;
            if ($index > 0) {
                $prevPlace = $places[$index - 1];
                $travelInfo = $this->getTravelInfo($prevPlace, $place);
                $travelDuration = $travelInfo['duration_minutes'];

                // Add travel as separate schedule item
                $travelStartTime = $currentTime->copy();
                $travelEndTime = $currentTime->copy()->addMinutes($travelDuration);

                $schedule[] = [
                    'type' => 'travel',
                    'start_time' => $travelStartTime->format('H:i'),
                    'end_time' => $travelEndTime->format('H:i'),
                    'duration_minutes' => $travelDuration,
                    'distance_km' => $travelInfo['distance_km'],
                    'from' => [
                        'name' => $prevPlace->name,
                        'latitude' => $prevPlace->latitude,
                        'longitude' => $prevPlace->longitude,
                    ],
                    'to' => [
                        'name' => $place->name,
                        'latitude' => $place->latitude,
                        'longitude' => $place->longitude,
                    ],
                    'description' => "Perjalanan dari {$prevPlace->name} ke {$place->name}",
                ];

                $currentTime = $travelEndTime;
            } elseif ($index === 0 && $startPoint) {
                // Travel from start point to first place using RouteService
                $travelInfo = $this->routeService->getDistanceAndDuration(
                    $startPoint['lat'],
                    $startPoint['lng'],
                    $place->latitude,
                    $place->longitude
                );
                $travelDuration = $travelInfo['duration_minutes'];

                $travelStartTime = $currentTime->copy();
                $travelEndTime = $currentTime->copy()->addMinutes($travelDuration);

                $schedule[] = [
                    'type' => 'travel',
                    'start_time' => $travelStartTime->format('H:i'),
                    'end_time' => $travelEndTime->format('H:i'),
                    'duration_minutes' => $travelDuration,
                    'distance_km' => $travelInfo['distance_km'],
                    'from' => [
                        'name' => 'Lokasi Awal',
                        'latitude' => $startPoint['lat'],
                        'longitude' => $startPoint['lng'],
                    ],
                    'to' => [
                        'name' => $place->name,
                        'latitude' => $place->latitude,
                        'longitude' => $place->longitude,
                    ],
                    'description' => "Perjalanan dari lokasi awal ke {$place->name}",
                ];

                $currentTime = $travelEndTime;
            }

            $endTime = $currentTime->copy()->addMinutes($visitDuration);

            // No time limit - allow natural completion
            $schedule[] = [
                'type' => 'place',
                'place_id' => $place->id,
                'name' => $place->name,
                'kind' => $place->kind,
                'latitude' => $place->latitude,
                'longitude' => $place->longitude,
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'duration_minutes' => $visitDuration,
                'price' => $place->price ?? 0,
                'rating_avg' => $place->rating_avg ?? 0,
                'description' => $place->description,
            ];

            $currentTime = $endTime;
        }

        // Add return trip logic
        // Last day: ALWAYS return to home, regardless of distance
        // Other days: Always return to home
        if ($places->isNotEmpty() && $originalStartPoint) {
            $lastPlace = $places->last();

            // Calculate distance to home using RouteService
            $returnTravelInfo = $this->routeService->getDistanceAndDuration(
                $lastPlace->latitude,
                $lastPlace->longitude,
                $originalStartPoint['lat'],
                $originalStartPoint['lng']
            );

            // Get duration from RouteService
            $durationMinutes = $returnTravelInfo['duration_minutes'];
            $distanceKm = $returnTravelInfo['distance_km'];

            $returnStartTime = $currentTime->copy();
            $returnEndTime = $currentTime->copy()->addMinutes($durationMinutes);

            $schedule[] = [
                'type' => 'travel',
                'start_time' => $returnStartTime->format('H:i'),
                'end_time' => $returnEndTime->format('H:i'),
                'duration_minutes' => $durationMinutes,
                'distance_km' => round($distanceKm, 2),
                'from' => [
                    'name' => $lastPlace->name,
                    'latitude' => $lastPlace->latitude,
                    'longitude' => $lastPlace->longitude,
                ],
                'to' => [
                    'name' => 'Lokasi Awal',
                    'latitude' => $originalStartPoint['lat'],
                    'longitude' => $originalStartPoint['lng'],
                ],
                'description' => 'Pulang ke lokasi awal',
                'is_return' => true, // Flag to identify return trip
            ];
        }

        return $schedule;
    }

    /**
     * Get travel info between two places using RouteService
     */
    private function getTravelInfo(Place $from, Place $to): array
    {
        return $this->routeService->getDistanceAndDuration(
            $from->latitude,
            $from->longitude,
            $to->latitude,
            $to->longitude
        );
    }


    /**
     * Calculate distance between two points (Haversine formula)
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        // Validate numeric values
        if (
            !is_numeric($lat1) || !is_numeric($lng1) ||
            !is_numeric($lat2) || !is_numeric($lng2)
        ) {
            Log::warning("calculateDistance received non-numeric coordinates", [
                'lat1' => $lat1,
                'lng1' => $lng1,
                'lat2' => $lat2,
                'lng2' => $lng2,
            ]);
            return 0;
        }

        // Haversine formula
        $earthRadius = 6371; // KM

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }


    /**
     * Estimate daily cost
     */
    private function estimateDailyCost(array $schedule): int
    {
        $total = 0;

        foreach ($schedule as $item) {
            if ($item['type'] === 'place') {
                $total += $item['price'] ?? 0;
            }
        }

        return $total;
    }

    /**
     * Generate summary
     */
    private function generateSummary(array $dailyPlans): array
    {
        $totalPlaces = 0;
        $totalDistance = 0;
        $totalCost = 0;

        foreach ($dailyPlans as $plan) {
            $totalPlaces += $plan['stats']['total_places'];
            $totalDistance += $plan['stats']['total_distance'];
            $totalCost += $plan['stats']['estimated_cost'];
        }

        return [
            'total_places' => $totalPlaces,
            'total_distance_km' => round($totalDistance, 2),
            'total_estimated_cost' => $totalCost,
            'total_days' => count($dailyPlans),
        ];
    }

    /**
     * Return empty itinerary structure
     */
    private function emptyItinerary(Carbon $startDate, int $durationDays): array
    {
        return [
            'metadata' => [
                'duration_days' => $durationDays,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $startDate->copy()->addDays($durationDays - 1)->format('Y-m-d'),
                'total_places' => 0,
            ],
            'daily_plans' => [],
            'summary' => [
                'total_places' => 0,
                'total_distance_km' => 0,
                'total_estimated_cost' => 0,
                'total_days' => 0,
            ],
        ];
    }
}
