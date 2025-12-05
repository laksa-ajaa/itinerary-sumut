@extends('layouts.app')

@section('title', 'Dashboard - Itinerary Sumut')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-2">
            Selamat Datang, {{ Auth::user()->name }}! 👋
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Jelajahi dan rencanakan perjalanan Anda di Sumatera Utara
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Destinasi</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($placesCount) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Restoran</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($restaurantsCount) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Atraksi Wisata</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($attractionsCount) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Penginapan</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($lodgingCount) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations Section -->
    @if(count($recommendations) > 0)
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Rekomendasi untuk Anda</h2>
            <span class="px-3 py-1 bg-gradient-to-r from-blue-600 to-green-600 text-white text-sm font-semibold rounded-full">
                AI Powered
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($recommendations as $place)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $place->name }}</h3>
                        @if($place->rating_avg > 0)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($place->rating_avg, 1) }}</span>
                        </div>
                        @endif
                    </div>
                    @if($place->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ $place->description }}</p>
                    @endif
                    @if($place->categories->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($place->categories->take(3) as $category)
                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs rounded-full">
                            {{ $category->name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        @if($place->kind)
                        <span class="capitalize">{{ $place->kind }}</span>
                        @endif
                        @if($place->city)
                        <span>•</span>
                        <span>{{ $place->city }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Top Rated Places -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Destinasi Terpopuler</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($topRatedPlaces as $place)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $place->name }}</h3>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($place->rating_avg, 1) }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ $place->rating_count }})</span>
                        </div>
                    </div>
                    @if($place->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ $place->description }}</p>
                    @endif
                    @if($place->categories->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($place->categories->take(3) as $category)
                        <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs rounded-full">
                            {{ $category->name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        @if($place->kind)
                        <span class="capitalize">{{ $place->kind }}</span>
                        @endif
                        @if($place->city)
                        <span>•</span>
                        <span>{{ $place->city }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">Belum ada destinasi tersedia</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Places -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Destinasi Terbaru</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($recentPlaces as $place)
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $place->name }}</h3>
                        @if($place->rating_avg > 0)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($place->rating_avg, 1) }}</span>
                        </div>
                        @endif
                    </div>
                    @if($place->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ $place->description }}</p>
                    @endif
                    @if($place->categories->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($place->categories->take(3) as $category)
                        <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs rounded-full">
                            {{ $category->name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        @if($place->kind)
                        <span class="capitalize">{{ $place->kind }}</span>
                        @endif
                        @if($place->city)
                        <span>•</span>
                        <span>{{ $place->city }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">Belum ada destinasi tersedia</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gradient-to-r from-blue-600 to-green-600 rounded-2xl p-8 text-center text-white">
        <h3 class="text-2xl font-bold mb-4">Siap Memulai Petualangan?</h3>
        <p class="text-blue-100 mb-6">Jelajahi lebih banyak destinasi dan buat itinerary Anda</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/api/places" class="px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-100 transition">
                Lihat Semua Destinasi
            </a>
            <a href="/api/places/map" class="px-6 py-3 bg-white/20 text-white border-2 border-white rounded-lg font-semibold hover:bg-white/30 transition">
                Lihat di Peta
            </a>
        </div>
    </div>
</div>
@endsection

