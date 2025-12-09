<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use App\Models\Place;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\HomeController;


Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

// Logout Route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Route
Route::get('/dashboard', function () {
    $placesCount = Place::count();
    $restaurantsCount = Place::where('kind', 'restaurant')->count();
    $attractionsCount = Place::where('kind', 'attraction')->count();
    $lodgingCount = Place::where('kind', 'lodging')->count();

    $recentPlaces = Place::with(['categories:id,name'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    $topRatedPlaces = Place::with(['categories:id,name'])
        ->where('rating_count', '>', 0)
        ->orderBy('rating_avg', 'desc')
        ->limit(10)
        ->get();

    // Get recommendations if user is logged in
    $recommendations = [];
    try {
        $resp = Http::post(config('services.reco.url') . '/recommend/mixed', [
            'user_id' => Auth::id(),
            'top_k' => 10,
        ]);
        $ids = $resp->json('place_ids') ?? [];
        if (!empty($ids)) {
            $recommendations = Place::with(['categories:id,name'])
                ->whereIn('id', $ids)
                ->get();
        }
    } catch (\Exception $e) {
        // Service might not be available
    }

    return view('dashboard', [
        'placesCount' => $placesCount,
        'restaurantsCount' => $restaurantsCount,
        'attractionsCount' => $attractionsCount,
        'lodgingCount' => $lodgingCount,
        'recentPlaces' => $recentPlaces,
        'topRatedPlaces' => $topRatedPlaces,
        'recommendations' => $recommendations,
    ]);
})->name('dashboard')->middleware('auth');

// API ringan untuk demo (sementara), sebaiknya pindah ke routes/api.php saat diaktifkan Sanctum/Breeze
Route::get('/api/places', [PlaceController::class, 'index']);
// Route statis 'map' harus didefinisikan sebelum parameter {place}
Route::get('/api/places/map', [MapController::class, 'geojson']);
Route::get('/api/places/{place}', [PlaceController::class, 'show'])->whereNumber('place');
Route::post('/api/places', [PlaceController::class, 'store']);
Route::put('/api/places/{place}', [PlaceController::class, 'update']);
Route::delete('/api/places/{place}', [PlaceController::class, 'destroy']);

Route::post('/api/recommend', function (Request $request) {
    $userId = Auth::id() ?? $request->integer('user_id');
    $topK = $request->integer('top_k', 20);
    $resp = Http::post(config('services.reco.url') . '/recommend/mixed', [
        'user_id' => $userId,
        'top_k' => $topK,
    ]);
    $ids = $resp->json('place_ids') ?? [];
    return Place::whereIn('id', $ids)->get();
});

// Itinerary Routes (accessible to all users and guests)
Route::get('/itinerary', [ItineraryController::class, 'showPreferences'])->name('itinerary.preferences');
Route::post('/itinerary/places', [ItineraryController::class, 'selectPlaces'])->name('itinerary.places');
Route::match(['get', 'post'], '/itinerary/generate', [ItineraryController::class, 'showGenerate'])->name('itinerary.generate');
Route::post('/itinerary/generate/process', [ItineraryController::class, 'generate'])->name('itinerary.generate.process');
Route::get('/itineraries', [ItineraryController::class, 'index'])->middleware('auth')->name('itinerary.index');
Route::get('/itineraries/{id}', [ItineraryController::class, 'show'])->middleware('auth')->name('itinerary.show');
Route::delete('/itineraries/{id}', [ItineraryController::class, 'destroy'])->middleware('auth')->name('itinerary.destroy');

// API Routes
Route::post('/api/itinerary/generate', [ItineraryController::class, 'generateApi']);
