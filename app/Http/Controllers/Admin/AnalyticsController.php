<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\Place;
use App\Models\User;
use App\Models\UserVisit;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $placesByKind = Place::selectRaw("COALESCE(kind, 'lainnya') as kind, COUNT(*) as total")
            ->groupBy('kind')
            ->orderByDesc('total')
            ->get();

        $placesByCity = Place::selectRaw("COALESCE(city, 'Tidak diketahui') as city, COUNT(*) as total")
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $itineraryTrends = Itinerary::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->limit(30)
            ->get();

        $userSignupTrends = User::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->limit(30)
            ->get();

        $visitTrends = UserVisit::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->limit(30)
            ->get();

        $ratingsLeaderboard = Place::whereNotNull('rating_avg')
            ->orderByDesc('rating_avg')
            ->orderByDesc('rating_count')
            ->limit(10)
            ->get(['name', 'city', 'rating_avg', 'rating_count', 'kind']);

        return view('pages.admin.analytics', [
            'placesByKind' => $placesByKind,
            'placesByCity' => $placesByCity,
            'itineraryTrends' => $itineraryTrends,
            'userSignupTrends' => $userSignupTrends,
            'visitTrends' => $visitTrends,
            'ratingsLeaderboard' => $ratingsLeaderboard,
        ]);
    }
}

