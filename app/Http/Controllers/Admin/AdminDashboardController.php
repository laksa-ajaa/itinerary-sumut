<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\Place;
use App\Models\User;
use App\Models\UserVisit;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $placesCount = Place::count();
        $restaurantsCount = Place::whereIn('kind', ['restaurant', 'restoran'])->count();
        $attractionsCount = Place::whereIn('kind', ['attraction', 'wisata'])->count();
        $lodgingCount = Place::whereIn('kind', ['lodging', 'penginapan'])->count();

        $userCount = User::count();
        $itineraryCount = Itinerary::count();
        $visitCount = UserVisit::count();

        $topCities = Place::selectRaw("COALESCE(city, 'Tidak diketahui') as city, COUNT(*) as total")
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topRatedPlaces = Place::orderByDesc('rating_avg')
            ->orderByDesc('rating_count')
            ->limit(6)
            ->get();

        $latestUsers = User::orderByDesc('created_at')->limit(5)->get();
        $latestItineraries = Itinerary::orderByDesc('created_at')->limit(5)->get();

        return view('pages.admin.dashboard', [
            'placesCount' => $placesCount,
            'restaurantsCount' => $restaurantsCount,
            'attractionsCount' => $attractionsCount,
            'lodgingCount' => $lodgingCount,
            'userCount' => $userCount,
            'itineraryCount' => $itineraryCount,
            'visitCount' => $visitCount,
            'topCities' => $topCities,
            'topRatedPlaces' => $topRatedPlaces,
            'latestUsers' => $latestUsers,
            'latestItineraries' => $latestItineraries,
        ]);
    }
}

