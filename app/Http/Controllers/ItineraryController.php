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
            'budget_level' => 'nullable|string|in:hemat,sedang,premium',
            'activity_level' => 'nullable|string|in:santai,normal,padat',
            'start_location' => 'nullable|string|max:255',
            'start_lat' => 'nullable|numeric',
            'start_lng' => 'nullable|numeric',
        ]);

        $categorySlugs = $validated['category_slugs'];
        $durationDays = $validated['duration_days'];
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $budgetLevel = $validated['budget_level'] ?? 'sedang';
        $activityLevel = $validated['activity_level'] ?? 'normal';
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
        $recommendedPlaces = $places->sortByDesc('rating_avg')->take(6);

        $categories = collect(PlaceCategoryHelper::getCategories())
            ->whereIn('slug', $categorySlugs)
            ->values();

        return view('pages.itinerary.places', [
            'places' => $places,
            'recommendedPlaces' => $recommendedPlaces,
            'categories' => $categories,
            'categorySlugs' => $categorySlugs,
            'durationDays' => $durationDays,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'budgetLevel' => $budgetLevel,
            'activityLevel' => $activityLevel,
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
            'place_ids' => 'required|array|min:1',
            'place_ids.*' => 'exists:places,id',
            'category_slugs' => 'nullable|array',
            'category_slugs.*' => 'string',
            'duration_days' => 'required|integer|min:1|max:30',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget_level' => 'nullable|string|in:hemat,sedang,premium',
            'activity_level' => 'nullable|string|in:santai,normal,padat',
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

        $placeIds = $validated['place_ids'];
        $places = Place::whereIn('id', $placeIds)
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
            'placeIds' => $placeIds,
            'categories' => $categories,
            'categorySlugs' => $categorySlugs,
            'durationDays' => $validated['duration_days'],
            'startDate' => $validated['start_date'] ?? null,
            'endDate' => $validated['end_date'] ?? null,
            'budgetLevel' => $validated['budget_level'] ?? 'sedang',
            'activityLevel' => $validated['activity_level'] ?? 'normal',
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
            'place_ids' => 'required|array|min:1',
            'place_ids.*' => 'exists:places,id',
            'category_slugs' => 'nullable|array',
            'category_slugs.*' => 'string',
            'duration_days' => 'required|integer|min:1|max:30',
            'start_date' => 'nullable|date|after_or_equal:today',
            'budget_level' => 'nullable|string|in:hemat,sedang,premium',
            'activity_level' => 'nullable|string|in:santai,normal,padat',
            'start_location' => 'nullable|string|max:255',
            'start_lat' => 'nullable|numeric',
            'start_lng' => 'nullable|numeric',
        ]);
        // Ambil places berdasarkan ID yang dipilih
        $categorySlugs = $validated['category_slugs'] ?? [];

        $selectedPlaces = Place::whereIn('id', $validated['place_ids'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->filter(function ($place) use ($categorySlugs) {
                if (!$place || !$place->id) {
                    return false;
                }
                // Filter berdasarkan kategori jika ada
                if (!empty($categorySlugs)) {
                    $placeCategories = PlaceCategoryHelper::extractCategoriesFromKind($place->kind);
                    return !empty(array_intersect($placeCategories, $categorySlugs));
                }
                return true;
            });
        // Validasi: minimal ada tempat yang sesuai
        if ($selectedPlaces->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tidak ada tempat yang sesuai dengan filter kategori yang dipilih.');
        }
        // Validasi: cek apakah jumlah tempat cukup untuk durasi
        $activityLevel = $validated['activity_level'] ?? 'normal';
        $minPlacesNeeded = $this->calculateMinPlacesNeeded(
            $validated['duration_days'],
            $activityLevel
        );
        if ($selectedPlaces->count() < $minPlacesNeeded) {
            return redirect()->back()
                ->withInput()
                ->with('warning', "Untuk {$validated['duration_days']} hari dengan aktivitas {$activityLevel}, minimal dibutuhkan {$minPlacesNeeded} tempat. Saat ini hanya tersedia {$selectedPlaces->count()} tempat.");
        }
        $userId = Auth::id();
        $categories = collect(PlaceCategoryHelper::getCategories())
            ->whereIn('slug', $categorySlugs)
            ->values();
        $categoryNames = $categories->pluck('name')->toArray();
        // Generate itinerary menggunakan service
        $validPlaceIds = $selectedPlaces->pluck('id')->toArray();

        try {
            $itinerary = $this->itineraryService->generateItinerary(
                $userId,
                $validPlaceIds,
                $validated['duration_days'],
                $validated['start_date'] ?? null,
                $categoryNames,
                $validated['budget_level'] ?? 'sedang',
                $validated['activity_level'] ?? 'normal',
                $validated['start_location'] ?? null
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
            'budget_level' => 'nullable|string|in:hemat,sedang,premium',
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

        $categories = collect(PlaceCategoryHelper::getCategories())
            ->whereIn('slug', $categorySlugs)
            ->values();
        $categoryNames = $categories->pluck('name')->toArray();

        try {
            $itinerary = $this->itineraryService->generateItinerary(
                Auth::id(),
                $selectedPlaces->pluck('id')->toArray(),
                $validated['duration_days'],
                $validated['start_date'] ?? null,
                $categoryNames,
                $validated['budget_level'] ?? 'sedang',
                $activityLevel,
                $validated['start_location'] ?? null
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
    private function calculateMinPlacesNeeded(int $durationDays, string $activityLevel): int
    {
        $placesPerDay = match ($activityLevel) {
            'santai' => 2,
            'normal' => 3,
            'padat' => 5,
            default => 3,
        };
        return $durationDays * $placesPerDay;
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
            'budget_level' => $itinerary['metadata']['budget_level'],
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
