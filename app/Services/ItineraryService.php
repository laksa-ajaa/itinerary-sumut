<?php

namespace App\Services;

use App\Models\Place;
use App\Services\RouteService;
use App\Services\OsrmService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ItineraryService
{
    protected $routeService;
    protected $osrmService;

    // Typical visit duration per place type (in minutes)
    private const VISIT_DURATIONS = [
        'wisata' => 120,      // 2 hours
        'kuliner' => 90,      // 1.5 hours
        'hotel' => 0,         // overnight stay
        'default' => 90,
    ];

    // Daily schedule constraints
    private const START_TIME = 8;  // 8 AM
    private const END_TIME = 20;   // 8 PM
    private const LUNCH_START = 12; // 12 PM
    private const LUNCH_END = 13;   // 1 PM
    private const DINNER_START = 18; // 6 PM
    private const AVAILABLE_HOURS = 10; // realistic touring hours per day

    public function __construct(RouteService $routeService, OsrmService $osrmService)
    {
        $this->routeService = $routeService;
        $this->osrmService = $osrmService;
    }

    /**
     * Generate natural itinerary with routing and hotel recommendations
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
            ->keyBy('id');

        if ($allPlaces->isEmpty()) {
            return $this->emptyItinerary($startDate, $durationDays);
        }

        // Get hotel recommendations if multi-day trip
        $hotels = $durationDays > 1
            ? $this->getHotelRecommendations($allPlaces, $durationDays - 1)
            : collect();

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
            $hotels,
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
                'total_hotels' => $hotels->count(),
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
     * Get hotel recommendations near places
     */
    private function getHotelRecommendations(
        Collection $places,
        int $nightsNeeded
    ): Collection {
        // Calculate centroid of all places for each day
        $centerLat = $places->avg('latitude');
        $centerLng = $places->avg('longitude');

        // Find hotels near the center
        $hotels = Place::where('kind', 'hotel')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($hotel) use ($centerLat, $centerLng) {
                $hotel->distance_from_center = $this->calculateDistance(
                    $centerLat,
                    $centerLng,
                    $hotel->latitude,
                    $hotel->longitude
                );
                return $hotel;
            })
            ->sortBy('distance_from_center')
            ->take($nightsNeeded);

        return $hotels;
    }


    /**
     * Build daily itinerary with routing optimization
     */
    private function buildDailyItinerary(
        array $placesByDay,
        array $activityLevels,
        Collection $allPlaces,
        Collection $hotels,
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
            // Always pass startPoint for return trip (every day should return to start)
            $schedule = $this->buildDailySchedule(
                $optimizedRoute,
                $currentDate,
                $routeGeometry,
                $currentStartPoint,
                $startPoint // Always return to original start point
            );

            // Get lunch restaurant recommendation
            $lunchRestaurant = $this->getLunchRestaurant($optimizedRoute);

            // Add hotel for overnight stay (except last day)
            $hotel = null;
            if ($day < $durationDays && $hotels->isNotEmpty()) {
                $hotel = $this->selectNearestHotel(
                    $hotels,
                    $optimizedRoute->last()
                );
            }

            $dailyPlans[] = [
                'day' => $day,
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->translatedFormat('l'),
                'start_time' => $currentDate->format('H:i'),
                'start_point' => $currentStartPoint,
                'places' => $schedule,
                'hotel' => $hotel ? $this->formatHotel($hotel) : null,
                'route_geometry' => $routeGeometry,
                'activity_level' => $activityLevels[$dayKey] ?? 'normal',
                'stats' => [
                    'total_places' => count(array_filter($schedule, fn($s) => $s['type'] === 'place')),
                    'total_distance' => $routeGeometry['distance_km'] ?? 0,
                    'estimated_cost' => $this->estimateDailyCost($schedule, $hotel),
                ]
            ];
        }

        return $dailyPlans;
    }

    /**
     * Get starting point for a specific day
     * Day 1: starts from initial start point
     * Day 2+: starts from hotel if available, otherwise from initial start point
     */
    private function getDayStartPoint(int $day, ?array $initialStart, array $dailyPlans): ?array
    {
        // Day 1 always starts from initial start point
        if ($day === 1) {
            return $initialStart;
        }

        // Day 2+: check if previous day has hotel
        if ($day > 1 && isset($dailyPlans[$day - 2]['hotel'])) {
            $prevHotel = $dailyPlans[$day - 2]['hotel'];
            return [
                'lat' => $prevHotel['latitude'],
                'lng' => $prevHotel['longitude']
            ];
        }

        // If no hotel, return to initial start point
        return $initialStart;
    }

    /**
     * Get route geometry using OSRM (with fallback to straight lines)
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
     * Build daily schedule with time slots
     */
    private function buildDailySchedule(Collection $places, Carbon $date, array $routeGeometry, ?array $startPoint = null, ?array $originalStartPoint = null): array
    {
        $schedule = [];
        // Use the time from $date (already set with start time for day 1, or 8:00 for other days)
        $currentTime = $date->copy();
        $isLunchAdded = false;
        $lunchRestaurant = null;

        foreach ($places as $index => $place) {
            $visitDuration = self::VISIT_DURATIONS[$place->kind] ?? self::VISIT_DURATIONS['default'];

            // Add travel time from previous location as separate item
            if ($index > 0) {
                $prevPlace = $places[$index - 1];
                $travelInfo = $this->getTravelInfo($prevPlace, $place);

                // Add travel as separate schedule item
                $travelStartTime = $currentTime->copy();
                $travelEndTime = $currentTime->copy()->addMinutes($travelInfo['duration_minutes']);

                $schedule[] = [
                    'type' => 'travel',
                    'start_time' => $travelStartTime->format('H:i'),
                    'end_time' => $travelEndTime->format('H:i'),
                    'duration_minutes' => $travelInfo['duration_minutes'],
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
                // Travel from start point to first place
                $travelInfo = $this->routeService->getDistanceAndDuration(
                    $startPoint['lat'],
                    $startPoint['lng'],
                    $place->latitude,
                    $place->longitude
                );

                $travelStartTime = $currentTime->copy();
                $travelEndTime = $currentTime->copy()->addMinutes($travelInfo['duration_minutes']);

                $schedule[] = [
                    'type' => 'travel',
                    'start_time' => $travelStartTime->format('H:i'),
                    'end_time' => $travelEndTime->format('H:i'),
                    'duration_minutes' => $travelInfo['duration_minutes'],
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

            // Check if need lunch break
            if (!$isLunchAdded && $currentTime->hour >= self::LUNCH_START && $currentTime->hour < self::LUNCH_END) {
                // Find nearby restaurant for lunch
                if (!$lunchRestaurant) {
                    $lunchRestaurant = $this->getNearbyRestaurant($place);
                }

                $schedule[] = [
                    'type' => 'meal',
                    'meal_type' => 'lunch',
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $currentTime->copy()->addHour()->format('H:i'),
                    'description' => 'Istirahat makan siang',
                    'restaurant' => $lunchRestaurant ? [
                        'id' => $lunchRestaurant->id,
                        'name' => $lunchRestaurant->name,
                        'rating_avg' => $lunchRestaurant->rating_avg ?? 0,
                        'price' => $lunchRestaurant->price ?? 0,
                    ] : null,
                ];
                $currentTime->addHour();
                $isLunchAdded = true;
            }

            $endTime = $currentTime->copy()->addMinutes($visitDuration);

            // Check if exceeds daily time limit
            if ($endTime->hour >= self::END_TIME) {
                break;
            }

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

            // Add dinner if approaching evening
            if ($currentTime->hour >= self::DINNER_START && $index === $places->count() - 1) {
                $schedule[] = [
                    'type' => 'meal',
                    'meal_type' => 'dinner',
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $currentTime->copy()->addMinutes(90)->format('H:i'),
                    'description' => 'Makan malam',
                ];
                $currentTime->addMinutes(90);
            }
        }

        // Add return trip to home at the end of every day
        // Use original start point (home) for return trip, not the day's starting point
        $returnPoint = $originalStartPoint;
        if ($places->isNotEmpty() && $returnPoint) {
            $lastPlace = $places->last();

            // Try to get distance from OSRM (similar to LRM) for more accurate distance
            // If OSRM fails, fallback to RouteService
            $distanceKm = null;
            try {
                $osrmRoute = $this->osrmService->getRoute([
                    ['lat' => $lastPlace->latitude, 'lng' => $lastPlace->longitude],
                    ['lat' => $returnPoint['lat'], 'lng' => $returnPoint['lng']],
                ]);

                if ($osrmRoute && isset($osrmRoute['distance_km'])) {
                    $distanceKm = $osrmRoute['distance_km'];
                }
            } catch (\Exception $e) {
                Log::debug('OSRM failed for return trip, using RouteService', ['error' => $e->getMessage()]);
            }

            // Fallback to RouteService if OSRM didn't provide distance
            if ($distanceKm === null) {
                $returnTravelInfo = $this->routeService->getDistanceAndDuration(
                    $lastPlace->latitude,
                    $lastPlace->longitude,
                    $returnPoint['lat'],
                    $returnPoint['lng']
                );
                $distanceKm = $returnTravelInfo['distance_km'];
            }

            // Calculate duration based on distance with 30 km/h speed
            // Formula: (distance_km / 30) * 60 minutes
            $durationMinutes = max(5, (int)ceil(($distanceKm / 30) * 60));

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
                    'latitude' => $returnPoint['lat'],
                    'longitude' => $returnPoint['lng'],
                ],
                'description' => 'Pulang ke lokasi awal',
                'is_return' => true, // Flag to identify return trip
            ];
        }

        return $schedule;
    }

    /**
     * Get travel info between two places
     */
    private function getTravelInfo(Place $from, Place $to): array
    {
        try {
            return $this->routeService->getDistanceAndDuration(
                $from->latitude,
                $from->longitude,
                $to->latitude,
                $to->longitude
            );
        } catch (\Exception $e) {
            // Fallback calculation
            $distance = $this->calculateDistance(
                $from->latitude,
                $from->longitude,
                $to->latitude,
                $to->longitude
            );

            return [
                'distance_km' => round($distance, 2),
                'duration_minutes' => max(15, (int)ceil(($distance / 30) * 60))
            ];
        }
    }

    /**
     * Get nearby restaurant for lunch
     */
    private function getNearbyRestaurant(Place $nearPlace): ?Place
    {
        return Place::where('kind', 'kuliner')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($restaurant) use ($nearPlace) {
                $restaurant->temp_distance = $this->calculateDistance(
                    $nearPlace->latitude,
                    $nearPlace->longitude,
                    $restaurant->latitude,
                    $restaurant->longitude
                );
                return $restaurant;
            })
            ->sortBy('temp_distance')
            ->first();
    }

    /**
     * Get lunch restaurant recommendation
     */
    private function getLunchRestaurant(Collection $places): ?Place
    {
        if ($places->isEmpty()) {
            return null;
        }

        $centerPlace = $places->skip($places->count() / 2)->first();
        return $this->getNearbyRestaurant($centerPlace);
    }

    /**
     * Select nearest hotel to last place of the day
     */
    private function selectNearestHotel(Collection $hotels, Place $lastPlace): ?Place
    {
        return $hotels->map(function ($hotel) use ($lastPlace) {
            $hotel->temp_distance = $this->calculateDistance(
                $lastPlace->latitude,
                $lastPlace->longitude,
                $hotel->latitude,
                $hotel->longitude
            );
            return $hotel;
        })->sortBy('temp_distance')->first();
    }

    /**
     * Format hotel data
     */
    private function formatHotel(Place $hotel): array
    {
        return [
            'id' => $hotel->id,
            'name' => $hotel->name,
            'latitude' => $hotel->latitude,
            'longitude' => $hotel->longitude,
            'price' => $hotel->price ?? 0,
            'rating_avg' => $hotel->rating_avg ?? 0,
            'description' => $hotel->description,
        ];
    }

    /**
     * Calculate distance between two points (Haversine formula)
     */
    private function calculateDistance(
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
     * Estimate daily cost
     */
    private function estimateDailyCost(array $schedule, ?array $hotel): int
    {
        $total = 0;

        foreach ($schedule as $item) {
            if ($item['type'] === 'place') {
                $total += $item['price'] ?? 0;
            }
        }

        if ($hotel) {
            $total += $hotel['price'] ?? 0;
        }

        // Add estimated meal costs
        $total += 150000; // ~50k per meal x 3

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
                'total_hotels' => 0,
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
