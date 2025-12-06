<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Services\ItineraryService;
use App\Helpers\PlaceCategoryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ItineraryController extends Controller
{
    protected $itineraryService;
    public function __construct(ItineraryService $itineraryService)
    {
        $this->itineraryService = $itineraryService;
    }

    /**
     * Backward compatibility: alias to preferences()
     */
    public function showPreferences()
    {
        return $this->preferences();
    }

    /**
     * Backward compatibility: previously used to select places.
     */
    public function selectPlaces(Request $request)
    {
        $validated = $request->validate([
            'category_slugs' => 'required|array|min:1',
            'category_slugs.*' => 'string',
            'duration_days' => 'required|integer|min:1|max:30',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'required|string',
            'start_location' => 'nullable|string|max:255',
            'start_lat' => 'nullable|numeric',
            'start_lng' => 'nullable|numeric',
        ]);

        $categorySlugs = $validated['category_slugs'];
        $durationDays = $validated['duration_days'];
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $startTime = $validated['start_time'] ?? '08:00';
        $startLocation = $validated['start_location'] ?? null;
        $startLat = $validated['start_lat'] ?? null;
        $startLng = $validated['start_lng'] ?? null;

        // Ambil daftar tempat berdasarkan kategori yang dipilih
        $placesQuery = Place::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if (!empty($categorySlugs)) {
            $placesQuery->whereIn('kind', $categorySlugs);
        }

        $places = $placesQuery
            ->orderByDesc('rating_avg')
            ->orderByDesc('rating_count')
            ->get();

        // Rekomendasi sederhana: ambil yang teratas dari hasil filter
        $recommendedPlaces = $places->take(6)->values();
        $recommendedPlaceIds = $recommendedPlaces->pluck('id')->toArray();
        $nonRecommendedPlaces = $places->whereNotIn('id', $recommendedPlaceIds)->values();

        $categories = collect(PlaceCategoryHelper::getCategories())
            ->whereIn('slug', $categorySlugs)
            ->values();

        return view('pages.itinerary.places', [
            'places' => $nonRecommendedPlaces,
            'recommendedPlaces' => $recommendedPlaces,
            'categories' => $categories,
            'categorySlugs' => $categorySlugs,
            'durationDays' => $durationDays,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'startTime' => $startTime,
            'startLocation' => $startLocation,
            'startLat' => $startLat,
            'startLng' => $startLng,
        ]);
    }
    /**
     * Show itinerary preferences form
     */
    public function preferences()
    {
        $categories = PlaceCategoryHelper::getCategories();

        return view('pages.itinerary.preferences', compact('categories'));
    }
    /**
     * Step 3: ringkasan + konfirmasi sebelum generate
     */
    public function showGenerate(Request $request)
    {
        // Ambil data dari request atau old input (agar bisa kembali dengan error)
        $input = $request->all();
        if (empty($input)) {
            $input = $request->session()->get('_old_input', []);
        }

        if (empty($input)) {
            return redirect()->route('itinerary.preferences')
                ->with('warning', 'Silakan pilih tempat dan preferensi terlebih dahulu.');
        }

        $validator = Validator::make($input, [
            'places_by_day' => 'required|array',
            'places_by_day.*' => 'required|array|min:1',
            'places_by_day.*.*' => 'exists:places,id',
            'activity_levels' => 'required|array',
            'activity_levels.*' => 'required|string|in:santai,normal,padat',
            'category_slugs' => 'nullable|array',
            'category_slugs.*' => 'string',
            'duration_days' => 'required|integer|min:1|max:30',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'required|string',
            'start_location' => 'nullable|string|max:255',
            'start_lat' => 'nullable|numeric',
            'start_lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        // Collect all place IDs from all days
        $allPlaceIds = [];
        $placesByDay = [];
        foreach ($validated['places_by_day'] as $day => $dayPlaceIds) {
            $allPlaceIds = array_merge($allPlaceIds, $dayPlaceIds);
            $placesByDay[$day] = $dayPlaceIds;
        }
        $allPlaceIds = array_unique($allPlaceIds);

        $places = Place::whereIn('id', $allPlaceIds)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($places->isEmpty()) {
            return redirect()->route('itinerary.preferences')
                ->with('error', 'Tempat yang dipilih tidak ditemukan. Silakan mulai ulang.');
        }

        $categorySlugs = $validated['category_slugs'] ?? [];
        $categories = collect(PlaceCategoryHelper::getCategories())
            ->whereIn('slug', $categorySlugs)
            ->values();

        return view('pages.itinerary.generate', [
            'places' => $places,
            'placesByDay' => $placesByDay,
            'activityLevels' => $validated['activity_levels'],
            'categories' => $categories,
            'categorySlugs' => $categorySlugs,
            'durationDays' => $validated['duration_days'],
            'startDate' => $validated['start_date'] ?? null,
            'endDate' => $validated['end_date'] ?? null,
            'startTime' => $validated['start_time'],
            'startLocation' => $validated['start_location'] ?? null,
            'startLat' => $validated['start_lat'] ?? null,
            'startLng' => $validated['start_lng'] ?? null,
        ]);
    }
    /**
     * Generate itinerary based on user preferences
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'places_by_day' => 'required|array',
            'places_by_day.*' => 'required|array|min:1',
            'places_by_day.*.*' => 'exists:places,id',
            'activity_levels' => 'required|array',
            'activity_levels.*' => 'required|string|in:santai,normal,padat',
            'category_slugs' => 'nullable|array',
            'category_slugs.*' => 'string',
            'duration_days' => 'required|integer|min:1|max:30',
            'start_date' => 'nullable|date|after_or_equal:today',
            'start_time' => 'required|string',
            'start_location' => 'nullable|string|max:255',
            'start_lat' => 'nullable|numeric',
            'start_lng' => 'nullable|numeric',
        ]);

        // Collect places by day
        $placesByDay = [];
        $allPlaceIds = [];
        foreach ($validated['places_by_day'] as $day => $dayPlaceIds) {
            $placesByDay[$day] = $dayPlaceIds;
            $allPlaceIds = array_merge($allPlaceIds, $dayPlaceIds);
        }
        $allPlaceIds = array_unique($allPlaceIds);

        $categorySlugs = $validated['category_slugs'] ?? [];
        $selectedPlaces = Place::whereIn('id', $allPlaceIds)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($selectedPlaces->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tidak ada tempat yang sesuai dengan filter kategori yang dipilih.');
        }

        $userId = Auth::id();
        $categories = collect(PlaceCategoryHelper::getCategories())
            ->whereIn('slug', $categorySlugs)
            ->values();
        $categoryNames = $categories->pluck('name')->toArray();

        try {
            $itinerary = $this->itineraryService->generateItinerary(
                $userId,
                $placesByDay,
                $validated['activity_levels'],
                $validated['duration_days'],
                $validated['start_date'] ?? null,
                $categoryNames,
                $validated['start_time'],
                $validated['start_location'] ?? null,
                $validated['start_lat'] ?? null,
                $validated['start_lng'] ?? null
            );
            // Save itinerary to database if user is authenticated
            if ($userId) {
                $this->saveItineraryToDatabase($userId, $itinerary, $validated);
            }
            return view('pages.itinerary.result', compact('itinerary'));
        } catch (\Exception $e) {
            Log::error('Itinerary generation failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat itinerary. Silakan coba lagi.');
        }
    }
    /**
     * Generate itinerary via API (JSON)
     */
    public function generateApi(Request $request)
    {
        $validated = $request->validate([
            'place_ids' => 'required|array|min:1',
            'place_ids.*' => 'exists:places,id',
            'category_slugs' => 'nullable|array',
            'category_slugs.*' => 'string',
            'duration_days' => 'required|integer|min:1|max:30',
            'start_date' => 'nullable|date|after_or_equal:today',
            'activity_level' => 'nullable|string|in:santai,normal,padat',
            'start_location' => 'nullable|string|max:255',
            'start_lat' => 'nullable|numeric',
            'start_lng' => 'nullable|numeric',
        ]);

        $categorySlugs = $validated['category_slugs'] ?? [];
        $selectedPlaces = Place::whereIn('id', $validated['place_ids'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->filter(function ($place) use ($categorySlugs) {
                if (!$place || !$place->id) {
                    return false;
                }
                if (!empty($categorySlugs)) {
                    $placeCategories = PlaceCategoryHelper::extractCategoriesFromKind($place->kind);
                    return !empty(array_intersect($placeCategories, $categorySlugs));
                }
                return true;
            });

        if ($selectedPlaces->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada tempat yang sesuai dengan filter kategori yang dipilih.',
            ], 422);
        }

        $activityLevel = $validated['activity_level'] ?? 'normal';
        $minPlacesNeeded = $this->calculateMinPlacesNeeded(
            $validated['duration_days'],
            $activityLevel
        );

        if ($selectedPlaces->count() < $minPlacesNeeded) {
            return response()->json([
                'message' => "Untuk {$validated['duration_days']} hari dengan aktivitas {$activityLevel}, minimal dibutuhkan {$minPlacesNeeded} tempat. Saat ini hanya tersedia {$selectedPlaces->count()} tempat."
            ], 422);
        }

        $maxPlacesAllowed = $this->calculateMaxPlacesAllowed(
            $validated['duration_days'],
            $activityLevel
        );

        if ($selectedPlaces->count() > $maxPlacesAllowed) {
            return response()->json([
                'message' => "Untuk {$validated['duration_days']} hari dengan aktivitas {$activityLevel}, maksimal {$maxPlacesAllowed} tempat. Anda memilih {$selectedPlaces->count()} tempat."
            ], 422);
        }

        $categories = collect(PlaceCategoryHelper::getCategories())
            ->whereIn('slug', $categorySlugs)
            ->values();
        $categoryNames = $categories->pluck('name')->toArray();

        // For API, convert to new format (backward compatibility)
        // If places_by_day is provided, use it; otherwise, distribute evenly
        $placesByDay = [];
        $activityLevels = [];
        if ($request->has('places_by_day') && is_array($request->input('places_by_day'))) {
            $placesByDay = $request->input('places_by_day');
            $activityLevelsInput = $request->input('activity_levels', []);
            $activityLevels = is_array($activityLevelsInput) ? $activityLevelsInput : [];
        } else {
            // Distribute places evenly across days
            $placeIds = $selectedPlaces->pluck('id')->toArray();
            $placesPerDay = ceil(count($placeIds) / $validated['duration_days']);
            for ($day = 1; $day <= $validated['duration_days']; $day++) {
                $dayKey = (string)$day;
                $placesByDay[$dayKey] = array_slice($placeIds, ($day - 1) * $placesPerDay, $placesPerDay);
                $activityLevels[$dayKey] = $activityLevel;
            }
        }

        try {
            $itinerary = $this->itineraryService->generateItinerary(
                Auth::id(),
                $placesByDay,
                $activityLevels,
                $validated['duration_days'],
                $validated['start_date'] ?? null,
                $categoryNames,
                $validated['start_time'] ?? '08:00',
                $validated['start_location'] ?? null,
                $validated['start_lat'] ?? null,
                $validated['start_lng'] ?? null
            );

            return response()->json($itinerary);
        } catch (\Exception $e) {
            Log::error('Itinerary API generation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat itinerary. Silakan coba lagi.'
            ], 500);
        }
    }
    /**
     * Calculate minimum places needed based on duration and activity level
     */
    /**
     * Calculate minimum places needed for entire trip based on activity level
     * Activity level determines total places for all days, not per day
     */
    private function calculateMinPlacesNeeded(int $durationDays, string $activityLevel): int
    {
        $totalPlaces = $this->getTotalPlacesForTrip($durationDays, $activityLevel);
        return $totalPlaces['min'];
    }

    /**
     * Calculate maximum places allowed for entire trip based on activity level
     * Activity level determines total places for all days, not per day
     */
    private function calculateMaxPlacesAllowed(int $durationDays, string $activityLevel): int
    {
        $totalPlaces = $this->getTotalPlacesForTrip($durationDays, $activityLevel);
        return $totalPlaces['max'];
    }

    /**
     * Get total places for entire trip based on activity level and duration
     * Activity level determines total places for all days, not per day
     */
    private function getTotalPlacesForTrip(int $durationDays, string $activityLevel): array
    {
        // Base total places for entire trip based on activity level
        $basePlaces = match ($activityLevel) {
            'santai' => ['min' => 2, 'max' => 3],      // 2-3 places total for entire trip
            'normal' => ['min' => 3, 'max' => 5],      // 3-5 places total for entire trip
            'padat' => ['min' => 5, 'max' => 7],       // 5-7 places total for entire trip
            default => ['min' => 3, 'max' => 5],
        };

        // Scale based on duration days
        // For multi-day trips, multiply base by duration (but keep it reasonable)
        if ($durationDays > 1) {
            return [
                'min' => $basePlaces['min'] * $durationDays,
                'max' => $basePlaces['max'] * $durationDays,
            ];
        }

        return $basePlaces;
    }

    /**
     * Get places per day (for backward compatibility, but now calculated from total)
     * This is now used for display/calculation purposes only
     */
    private function getPlacesPerDay(string $activityLevel): int
    {
        // This is now just for display, actual calculation uses getTotalPlacesForTrip
        return match ($activityLevel) {
            'santai' => 2,
            'normal' => 3,
            'padat' => 5,
            default => 3,
        };
    }
    /**
     * Save generated itinerary to database
     */
    private function saveItineraryToDatabase(int $userId, array $itinerary, array $validated): void
    {
        // You can implement this based on your database schema
        // Example:
        /*
        $savedItinerary = Itinerary::create([
            'user_id' => $userId,
            'title' => 'Trip ' . $itinerary['metadata']['start_date'],
            'duration_days' => $itinerary['metadata']['duration_days'],
            'start_date' => $itinerary['metadata']['start_date'],
            'end_date' => $itinerary['metadata']['end_date'],
            'activity_level' => $itinerary['metadata']['activity_level'],
            'total_cost' => $itinerary['summary']['total_estimated_cost'],
            'data' => json_encode($itinerary),
        ]);
        */
    }
    /**
     * Show saved itineraries
     */
    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Silakan login untuk melihat itinerary Anda.');
        }
        // Load user's saved itineraries
        // $itineraries = Itinerary::where('user_id', $userId)
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(10);
        // return view('pages.itinerary.index', compact('itineraries'));

        return view('pages.itinerary.index');
    }
    /**
     * Show single itinerary detail
     */
    public function show($id)
    {
        $userId = Auth::id();

        // Load specific itinerary
        // $itinerary = Itinerary::where('id', $id)
        //     ->where('user_id', $userId)
        //     ->firstOrFail();
        // $itineraryData = json_decode($itinerary->data, true);
        // return view('pages.itinerary.result', compact('itineraryData'));

        return redirect()->route('itinerary.preferences');
    }
    /**
     * Delete itinerary
     */
    public function destroy($id)
    {
        $userId = Auth::id();

        // $itinerary = Itinerary::where('id', $id)
        //     ->where('user_id', $userId)
        //     ->firstOrFail();

        // $itinerary->delete();
        return redirect()->route('itinerary.index')
            ->with('success', 'Itinerary berhasil dihapus.');
    }
}
