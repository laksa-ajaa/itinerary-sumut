<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Place;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\MapController;

Route::get('/', function () {
    return view('welcome');
});

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


