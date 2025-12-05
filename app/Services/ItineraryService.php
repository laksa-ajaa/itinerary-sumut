<?php

namespace App\Services;

use App\Models\Place;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ItineraryService
{
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

    /**
     * Generate natural itinerary with routing and hotel recommendations
     */
    public function generateItinerary(
        ?int $userId,
        array $placeIds,
        int $durationDays,
        ?string $startDate,
        array $categoryNames,
        string $budgetLevel = 'sedang',
        string $activityLevel = 'normal',
        ?string $startLocation = null
    ): array {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now();
        
        // Get places to visit
        $places = Place::whereIn('id', $placeIds)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($places->isEmpty()) {
            return $this->emptyItinerary($startDate, $durationDays);
        }

        // Determine places per day based on activity level
        $placesPerDay = $this->getPlacesPerDay($activityLevel);
        
        // Get hotel recommendations if multi-day trip
        $hotels = $durationDays > 1 
            ? $this->getHotelRecommendations($places, $budgetLevel, $durationDays - 1)
            : collect();

        // Build daily itinerary
        $dailyPlans = $this->buildDailyItinerary(
            $places,
            $hotels,
            $durationDays,
            $placesPerDay,
            $startLocation,
            $startDate
        );

        return [
            'metadata' => [
                'duration_days' => $durationDays,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $startDate->copy()->addDays($durationDays - 1)->format('Y-m-d'),
                'total_places' => $places->count(),
                'total_hotels' => $hotels->count(),
                'budget_level' => $budgetLevel,
                'activity_level' => $activityLevel,
                'categories' => $categoryNames,
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
        return match($activityLevel) {
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
        string $budgetLevel,
        int $nightsNeeded
    ): Collection {
        // Calculate centroid of all places for each day
        $centerLat = $places->avg('latitude');
        $centerLng = $places->avg('longitude');

        // Find hotels near the center (price not used because column not available)
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
        Collection $places,
        Collection $hotels,
        int $durationDays,
        array $placesPerDay,
        ?string $startLocation,
        Carbon $startDate
    ): array {
        $dailyPlans = [];
        $remainingPlaces = $places->shuffle(); // Start with shuffled for variety
        
        for ($day = 1; $day <= $durationDays; $day++) {
            $currentDate = $startDate->copy()->addDays($day - 1);
            
            // Determine starting point
            if ($day === 1 && $startLocation) {
                $startPoint = $this->parseStartLocation($startLocation);
            } elseif ($day > 1 && isset($dailyPlans[$day - 2]['hotel'])) {
                // Start from previous day's hotel
                $prevHotel = $dailyPlans[$day - 2]['hotel'];
                $startPoint = [
                    'lat' => $prevHotel['latitude'],
                    'lng' => $prevHotel['longitude']
                ];
            } else {
                // Start from first available place
                $startPoint = $remainingPlaces->first() 
                    ? ['lat' => $remainingPlaces->first()->latitude, 'lng' => $remainingPlaces->first()->longitude]
                    : null;
            }

            // Select places for this day
            $targetPlaces = rand($placesPerDay['min'], $placesPerDay['max']);
            $dailyPlaces = $this->selectDailyPlaces(
                $remainingPlaces,
                $targetPlaces,
                $startPoint
            );

            // Remove selected places from remaining
            $remainingPlaces = $remainingPlaces->reject(function ($place) use ($dailyPlaces) {
                return $dailyPlaces->contains('id', $place->id);
            });

            // Optimize route for selected places
            $optimizedRoute = $this->optimizeRoute($dailyPlaces, $startPoint);

            // Build schedule with time allocation
            $schedule = $this->buildDailySchedule($optimizedRoute, $currentDate);

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
                'places' => $schedule,
                'hotel' => $hotel ? $this->formatHotel($hotel) : null,
                'stats' => [
                    'total_places' => count($schedule),
                    'total_distance' => $this->calculateTotalDistance($optimizedRoute),
                    'estimated_cost' => $this->estimateDailyCost($schedule, $hotel),
                ]
            ];

            // Break if no more places
            if ($remainingPlaces->isEmpty()) {
                break;
            }
        }

        return $dailyPlans;
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

        for ($i = 0; $i < $targetCount && $remainingPlaces->isNotEmpty(); $i++) {
            // Find nearest place to current point
            $nearest = $currentPoint
                ? $this->findNearestPlace($remainingPlaces, $currentPoint)
                : $remainingPlaces->first();

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
        if (!$point) {
            // fallback: just return the first item if no start point is provided
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

        $optimized = collect();
        $remaining = $places->values();
        $current = $startPoint;

        while ($remaining->isNotEmpty()) {
            $nearest = $this->findNearestPlace($remaining, $current);
            $optimized->push($nearest);
            $remaining = $remaining->reject(fn($p) => $p->id === $nearest->id);
            $current = ['lat' => $nearest->latitude, 'lng' => $nearest->longitude];
        }

        return $optimized;
    }

    /**
     * Build daily schedule with time slots
     */
    private function buildDailySchedule(Collection $places, Carbon $date): array
    {
        $schedule = [];
        $currentTime = $date->copy()->setTime(self::START_TIME, 0);
        $isLunchAdded = false;

        foreach ($places as $index => $place) {
            $visitDuration = self::VISIT_DURATIONS[$place->kind] ?? self::VISIT_DURATIONS['default'];
            
            // Check if need lunch break
            if (!$isLunchAdded && $currentTime->hour >= self::LUNCH_START) {
                $schedule[] = [
                    'type' => 'meal',
                    'meal_type' => 'lunch',
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $currentTime->copy()->addHour()->format('H:i'),
                    'description' => 'Istirahat makan siang',
                ];
                $currentTime->addHour();
                $isLunchAdded = true;
            }

            // Add travel time (estimate 30 min between places)
            if ($index > 0) {
                $prevPlace = $places[$index - 1];
                $travelTime = $this->estimateTravelTime(
                    $prevPlace->latitude,
                    $prevPlace->longitude,
                    $place->latitude,
                    $place->longitude
                );
                $currentTime->addMinutes($travelTime);
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
                'rating_avg' => $place->rating_avg ?? 0,
                'description' => $place->description,
            ];

            $currentTime = $endTime;

            // Add dinner if approaching evening
            if ($currentTime->hour >= self::DINNER_START && !isset($schedule[count($schedule) - 1]['meal_type'])) {
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

        return $schedule;
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
     * Calculate total distance of route
     */
    private function calculateTotalDistance(Collection $places): float
    {
        $total = 0;
        for ($i = 1; $i < $places->count(); $i++) {
            $prev = $places[$i - 1];
            $curr = $places[$i];
            $total += $this->calculateDistance(
                $prev->latitude,
                $prev->longitude,
                $curr->latitude,
                $curr->longitude
            );
        }
        return round($total, 2);
    }

    /**
     * Estimate travel time between two points (minutes)
     */
    private function estimateTravelTime(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): int {
        $distance = $this->calculateDistance($lat1, $lng1, $lat2, $lng2);
        // Assume average speed of 30 km/h in city
        $hours = $distance / 30;
        return max(15, (int)($hours * 60)); // minimum 15 minutes
    }

    /**
     * Estimate daily cost
     */
    private function estimateDailyCost(array $schedule, ?array $hotel): int
    {
        $total = 0;
        
        // Price data not available in schema; keep only meal estimate.
        $total += 150000; // ~50k per meal x3

        return $total;
    }

    /**
     * Parse start location string
     */
    private function parseStartLocation(?string $location): ?array
    {
        // This is a simple parser, you might want to geocode the location
        // For now, return null to start from first place
        return null;
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
