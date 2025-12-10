@extends('layouts.itinerary')

@section('title', 'Itinerary Hasil | Itinerary Sumut')

@section('content')
    <div class="container mx-auto px-6 max-w-7xl">
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-green-700 mb-4">Itinerary Perjalanan Anda</h1>
            @php
                $meta = $itinerary['metadata'] ?? [];
                $durationDays = $meta['duration_days'] ?? null;
                $startDate = $meta['start_date'] ?? null;
                $endDate = $meta['end_date'] ?? null;
            @endphp
            <p class="text-gray-600 text-lg mb-2">
                @if ($durationDays)
                    <strong>{{ $durationDays }} Hari</strong>
                    @if ($startDate)
                        ({{ \Carbon\Carbon::parse($startDate)->locale('id')->isoFormat('D MMM YYYY') }}
                        @if ($endDate)
                            - {{ \Carbon\Carbon::parse($endDate)->locale('id')->isoFormat('D MMM YYYY') }}
                        @endif
                        )
                    @endif
                @endif
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                @if (!empty($meta['categories']))
                    <span class="text-sm text-gray-500">Minat:</span>
                    @foreach ($meta['categories'] as $preference)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                            {{ $preference }}
                        </span>
                    @endforeach
                @endif

                @if (!empty($meta['start_time']))
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                        ⏰ Jam Berangkat: {{ $meta['start_time'] }}
                    </span>
                @endif

                @if (!empty($meta['start_location']))
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                        Start: {{ $meta['start_location'] }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Progress Indicator --}}
        <div class="mb-8">
            <div class="flex items-center justify-center gap-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">✓
                    </div>
                    <span class="ml-2 text-green-600 font-semibold">Preferensi</span>
                </div>
                <div class="w-16 h-1 bg-green-600"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">✓
                    </div>
                    <span class="ml-2 text-green-600 font-semibold">Pilih Tempat</span>
                </div>
                <div class="w-16 h-1 bg-green-600"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">✓
                    </div>
                    <span class="ml-2 text-green-600 font-semibold">Generate</span>
                </div>
                <div class="w-16 h-1 bg-green-600"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">✓
                    </div>
                    <span class="ml-2 text-green-600 font-semibold">Hasil</span>
                </div>
            </div>
        </div>

        @php
            $dailyPlans = $itinerary['daily_plans'] ?? [];
        @endphp

        {{-- Itinerary Days --}}
        <div class="space-y-6">
            @if (empty($dailyPlans))
                <div class="bg-white rounded-lg border border-gray-200 p-6 text-center text-gray-600">
                    Itinerary tidak memiliki jadwal harian untuk ditampilkan.
                </div>
            @else
                @foreach ($dailyPlans as $day)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        {{-- Day Header --}}
                        <div class="bg-green-600 text-white px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold">Hari {{ $day['day'] }}</h2>
                                    <span class="text-green-100 text-sm">
                                        {{ \Carbon\Carbon::parse($day['date'])->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4">
                                    @if ($day['day'] > 1)
                                        <div class="flex items-center gap-2">
                                            <label for="start_time_day_{{ $day['day'] }}" class="text-green-100 text-sm">
                                                Jam Mulai:
                                            </label>
                                            <input type="time" id="start_time_day_{{ $day['day'] }}"
                                                name="start_times[{{ $day['day'] }}]"
                                                value="{{ $day['start_time'] ?? '08:00' }}"
                                                class="px-2 py-1 rounded text-gray-900 text-sm bg-white"
                                                data-day="{{ $day['day'] }}">
                                        </div>
                                    @else
                                        <span class="text-green-100 text-sm">
                                            ⏰ {{ $day['start_time'] ?? ($meta['start_time'] ?? '08:00') }}
                                        </span>
                                    @endif
                                    @if (!empty($day['activity_level']))
                                        <span class="px-3 py-1 bg-green-500 text-white rounded-full text-xs font-medium">
                                            {{ ucfirst($day['activity_level']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Two Column Layout --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                            {{-- Left Column: Activities List --}}
                            <div class="p-6 border-r border-gray-200 overflow-y-auto" style="max-height: 600px;">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Daftar Aktivitas</h3>
                                <div class="space-y-3">
                                    {{-- Show start location first if available --}}
                                    @if (!empty($day['start_point']))
                                        @php
                                            $startPoint = $day['start_point'];
                                            $isHotel = !empty($startPoint['is_hotel']) && $startPoint['is_hotel'];
                                            $startName = $startPoint['name'] ?? 'Lokasi Awal';
                                            $displayName = $isHotel ? "Lokasi Awal ({$startName})" : $startName;
                                        @endphp
                                        <div
                                            class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 border-l-4 border-gray-400">
                                            <div class="flex-shrink-0">
                                                <div class="w-16 text-center">
                                                    <span class="text-sm font-bold text-gray-900">Start</span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-gray-900 text-sm mb-1">📍 {{ $displayName }}
                                                </h4>
                                                <p class="text-gray-600 text-xs">
                                                    {{ $day['start_point']['lat'] }}, {{ $day['start_point']['lng'] }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    @foreach ($day['places'] as $index => $activity)
                                        @php
                                            $type = $activity['type'] ?? ($activity['kind'] ?? 'place');
                                            $durationLabel = null;
                                            if (!empty($activity['duration_minutes'])) {
                                                $durationLabel = $activity['duration_minutes'] . ' menit';
                                            } elseif (!empty($activity['duration'])) {
                                                $durationLabel = $activity['duration'];
                                            }
                                            $timeLabel = '';
                                            if (!empty($activity['start_time']) && !empty($activity['end_time'])) {
                                                $timeLabel = $activity['start_time'] . ' - ' . $activity['end_time'];
                                            } elseif (!empty($activity['start_time'])) {
                                                $timeLabel = $activity['start_time'];
                                            }
                                            $title = $activity['name'] ?? ($activity['activity'] ?? '-');
                                            $location = $activity['description'] ?? ($activity['location'] ?? '');

                                            $travelId = null;
                                            if (
                                                $type === 'travel' &&
                                                !empty($activity['from']) &&
                                                !empty($activity['to'])
                                            ) {
                                                $fromLat = round($activity['from']['latitude'], 4);
                                                $fromLng = round($activity['from']['longitude'], 4);
                                                $toLat = round($activity['to']['latitude'], 4);
                                                $toLng = round($activity['to']['longitude'], 4);
                                                $travelId =
                                                    'travel-day-' .
                                                    $day['day'] .
                                                    '-' .
                                                    md5($fromLat . $fromLng . $toLat . $toLng);
                                            }
                                        @endphp
                                        <div class="flex items-start gap-3 p-3 rounded-lg text-sm
                                        @if ($type == 'place' || $type == 'wisata') bg-green-50 border-l-4 border-green-500
                                        @elseif($type == 'travel') bg-gray-50 border-l-4 border-gray-400
                                        @else bg-gray-100 border-l-4 border-gray-300 @endif"
                                            data-day="{{ $day['day'] }}"
                                            @if ($travelId) id="{{ $travelId }}" @endif
                                            @if ($type === 'travel') data-from-lat="{{ $activity['from']['latitude'] ?? '' }}"
                                                data-from-lng="{{ $activity['from']['longitude'] ?? '' }}"
                                                data-to-lat="{{ $activity['to']['latitude'] ?? '' }}"
                                                data-to-lng="{{ $activity['to']['longitude'] ?? '' }}"
                                                data-start-time="{{ $activity['start_time'] ?? '' }}"
                                                data-end-time="{{ $activity['end_time'] ?? '' }}"
                                                data-travel-duration="{{ $activity['duration_minutes'] ?? 0 }}" @endif
                                            @if ($type === 'place') data-activity-start-time="{{ $activity['start_time'] ?? '' }}"
                                                data-activity-end-time="{{ $activity['end_time'] ?? '' }}"
                                                data-activity-duration="{{ $activity['duration_minutes'] ?? 0 }}" @endif>

                                            {{-- Time --}}
                                            <div class="flex-shrink-0">
                                                <div class="w-16 text-center">
                                                    <span
                                                        class="text-sm font-bold text-gray-900 time-display">{{ $timeLabel }}</span>
                                                    @if ($durationLabel)
                                                        <span
                                                            class="block text-xs text-gray-500 duration-display">{{ $durationLabel }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Activity Content --}}
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-semibold text-gray-900 mb-1 text-sm truncate">
                                                    {{ $title }}</h4>
                                                @if ($location)
                                                    <p class="text-gray-600 text-xs line-clamp-2">{{ $location }}</p>
                                                @endif

                                                {{-- Icon based on type --}}
                                                <div class="mt-2 flex items-center gap-2 text-xs">
                                                    @if ($type == 'place' || $type == 'wisata')
                                                        <span class="text-green-600">📍</span>
                                                        <span class="text-gray-500">Tempat Wisata</span>
                                                    @elseif($type == 'travel')
                                                        <span class="text-gray-600">🚗</span>
                                                        <span class="text-gray-500">
                                                            @if (!empty($activity['is_return']))
                                                                Pulang ke Rumah
                                                            @else
                                                                Perjalanan
                                                            @endif
                                                        </span>
                                                        @if (!empty($activity['from']) && !empty($activity['to']))
                                                            <p class="text-blue-600 font-medium"
                                                                id="{{ $travelId }}-info">
                                                                ⏱️ <span
                                                                    class="travel-duration">{{ number_format($activity['duration_minutes'] ?? 0, 0) }}</span>
                                                                menit
                                                                @if (!empty($activity['distance_km']))
                                                                    • 📏 <span
                                                                        class="travel-distance">{{ number_format($activity['distance_km'], 1) }}</span>
                                                                    km
                                                                @endif
                                                            </p>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Right Column: Map --}}
                            @php
                                $placesForMap = array_filter($day['places'] ?? [], function ($p) {
                                    return ($p['type'] ?? '') === 'place' &&
                                        !empty($p['latitude']) &&
                                        !empty($p['longitude']);
                                });
                            @endphp
                            <div class="p-6 bg-gray-50">
                                @if (count($placesForMap) > 0)
                                    <div class="sticky top-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-3">🗺️ Peta Rute</h3>
                                        <div id="map-day-{{ $day['day'] }}"
                                            class="w-full h-96 rounded-lg border border-gray-300 mb-3"></div>
                                        <div
                                            class="route-info flex flex-col gap-2 text-sm text-gray-600 bg-white p-3 rounded-lg border border-gray-200">
                                            @if (!empty($day['stats']['total_distance']))
                                                <div class="flex items-center gap-2">
                                                    <span class="text-lg">📏</span>
                                                    <span>Total Jarak:
                                                        <strong>{{ number_format($day['stats']['total_distance'], 1) }}
                                                            km</strong></span>
                                                </div>
                                            @endif
                                            @if (!empty($day['route_geometry']['duration_minutes']))
                                                <div class="flex items-center gap-2">
                                                    <span class="text-lg">⏱️</span>
                                                    <span>Waktu Tempuh:
                                                        <strong>{{ number_format($day['route_geometry']['duration_minutes'], 0) }}
                                                            menit</strong></span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-400">
                                        <div class="text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                            </svg>
                                            <p class="mt-2 text-sm">Tidak ada data lokasi untuk ditampilkan</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Action Buttons --}}
        <div class="mt-8 flex justify-center gap-4">
            <a href="{{ route('itinerary.preferences') }}"
                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                Buat Itinerary Baru
            </a>
            <button onclick="window.print()"
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
                Print Itinerary
            </button>
        </div>

        {{-- Info Box --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 text-sm">
                <strong>Catatan:</strong> Itinerary ini dibuat berdasarkan preferensi dan tempat yang Anda pilih.
                Waktu perjalanan dan durasi kunjungan adalah estimasi. Silakan sesuaikan sesuai kebutuhan Anda.
            </p>
            @if (!auth()->check())
                <p class="text-blue-800 text-sm mt-2">
                    <strong>💡 Info:</strong> Itinerary ini tidak akan tersimpan.
                    <a href="{{ route('login') }}" class="underline font-semibold">Login</a> atau
                    <a href="{{ route('register') }}" class="underline font-semibold">Daftar</a>
                    untuk menyimpan itinerary Anda.
                </p>
            @endif
        </div>
    </div>

    <style>
        @media print {

            nav,
            footer,
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .grid-cols-2 {
                grid-template-columns: 1fr !important;
            }
        }

        /* Custom scrollbar for activity list */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    @push('scripts')
        <script>
            // [Script tetap sama seperti sebelumnya - tidak ada perubahan pada JavaScript]
            const MAPBOX_ACCESS_TOKEN = '{{ config('services.mapbox.access_token') }}';

            if (!MAPBOX_ACCESS_TOKEN || MAPBOX_ACCESS_TOKEN === '') {
                console.error('⚠️ Mapbox Access Token tidak ditemukan!');
            } else {
                console.log('✅ Mapbox Access Token loaded');
            }

            (function() {
                const dailyPlans = @json($itinerary['daily_plans'] ?? []);
                const ROUTE_CACHE_ENDPOINT = '/api/routes/cache';

                async function fetchCachedRoute(start, end) {
                    try {
                        const params = new URLSearchParams({
                            from_lat: start.lat,
                            from_lng: start.lng,
                            to_lat: end.lat,
                            to_lng: end.lng,
                        });
                        const resp = await fetch(`${ROUTE_CACHE_ENDPOINT}?${params.toString()}`);
                        if (!resp.ok) return null;
                        return await resp.json();
                    } catch (err) {
                        console.warn('Failed to fetch cached route', err);
                        return null;
                    }
                }

                async function storeRouteCache(start, end, route) {
                    try {
                        await fetch(ROUTE_CACHE_ENDPOINT, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                from_lat: start.lat,
                                from_lng: start.lng,
                                to_lat: end.lat,
                                to_lng: end.lng,
                                distance_meters: route.summary.totalDistance,
                                duration_seconds: route.summary.totalTime ?? null,
                                coordinates: route.coordinates.map(c => [c.lat, c.lng]),
                                provider: 'mapbox',
                                profile: 'mapbox/driving',
                                raw_response: route
                            })
                        });
                    } catch (err) {
                        console.warn('Failed to store route cache', err);
                    }
                }

                function drawPolyline(map, coordinates, color) {
                    if (!coordinates || coordinates.length === 0) return;

                    L.polyline(coordinates.map(c => L.latLng(c[0], c[1])), {
                        color: color,
                        weight: 5,
                        opacity: 0.8
                    }).addTo(map);
                }

                function hideRoutingControl(segmentControl, map) {
                    setTimeout(() => {
                        const container = segmentControl.getContainer();
                        if (container) {
                            const panel = container.querySelector('.leaflet-routing-container');
                            if (panel) panel.style.display = 'none';
                            container.style.display = 'none';
                        }

                        // Remove default blue polyline drawn by LRM
                        map.eachLayer(function(layer) {
                            if (layer instanceof L.Polyline && layer.options.color === '#3388ff') {
                                map.removeLayer(layer);
                            }
                        });
                    }, 100);
                }

                async function buildSegment(map, segmentWaypoints, color) {
                    const start = segmentWaypoints[0];
                    const end = segmentWaypoints[1];

                    // 1) Try cache first
                    const cached = await fetchCachedRoute(start, end);
                    if (cached && cached.coordinates?.length) {
                        drawPolyline(map, cached.coordinates, color);
                        return {
                            distance: cached.distance_meters ?? 0
                        };
                    }

                    // 2) Fallback to live routing (Mapbox -> OSRM)
                    let router;
                    if (MAPBOX_ACCESS_TOKEN && MAPBOX_ACCESS_TOKEN !== '' && L.Routing.mapbox) {
                        router = L.Routing.mapbox(MAPBOX_ACCESS_TOKEN, {
                            profile: 'mapbox/driving',
                            language: 'id'
                        });
                    } else {
                        router = L.Routing.osrmv1({
                            serviceUrl: 'https://router.project-osrm.org/route/v1',
                            profile: 'driving'
                        });
                    }

                    return await new Promise(resolve => {
                        const segmentControl = L.Routing.control({
                            waypoints: segmentWaypoints,
                            routeWhileDragging: false,
                            showAlternatives: false,
                            addWaypoints: false,
                            createMarker: function() {
                                return false;
                            },
                            router: router
                        });

                        segmentControl.addTo(map);
                        hideRoutingControl(segmentControl, map);

                        segmentControl.on('routesfound', async function(e) {
                            const routes = e.routes;
                            if (!routes || routes.length === 0) {
                                resolve(null);
                                return;
                            }

                            const route = routes[0];
                            if (route.coordinates && route.coordinates.length > 0) {
                                L.polyline(route.coordinates, {
                                    color: color,
                                    weight: 5,
                                    opacity: 0.8
                                }).addTo(map);
                            }

                            // Persist to cache for future requests
                            if (route.summary && route.summary.totalDistance) {
                                await storeRouteCache(start, end, route);
                            }

                            resolve({
                                distance: route.summary.totalDistance ?? 0
                            });
                        });

                        segmentControl.on('routingerror', function() {
                            resolve(null);
                        });
                    });
                }

                async function initMap(dayNumber, places, startPoint, returnTrip = null) {
                    if (typeof L === 'undefined' || typeof L.Routing === 'undefined') {
                        console.log('Waiting for Leaflet Routing Machine...');
                        setTimeout(() => initMap(dayNumber, places, startPoint), 100);
                        return;
                    }

                    const mapId = 'map-day-' + dayNumber;
                    const mapElement = document.getElementById(mapId);
                    if (!mapElement) return;
                    if (mapElement._leaflet_id) return;

                    const waypoints = [];
                    const allCoords = [];

                    if (startPoint && startPoint.lat && startPoint.lng) {
                        waypoints.push(L.latLng(startPoint.lat, startPoint.lng));
                        allCoords.push([startPoint.lat, startPoint.lng]);
                    }

                    places.forEach(place => {
                        if (place.latitude && place.longitude) {
                            waypoints.push(L.latLng(place.latitude, place.longitude));
                            allCoords.push([place.latitude, place.longitude]);
                        }
                    });

                    if (returnTrip && returnTrip.to && returnTrip.to.latitude && returnTrip.to.longitude) {
                        waypoints.push(L.latLng(returnTrip.to.latitude, returnTrip.to.longitude));
                        allCoords.push([returnTrip.to.latitude, returnTrip.to.longitude]);
                    }

                    if (waypoints.length === 0) return;

                    const map = L.map(mapId).setView(allCoords[0], 11);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    if (startPoint && startPoint.lat && startPoint.lng) {
                        L.marker([startPoint.lat, startPoint.lng], {
                            icon: L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                            })
                        }).addTo(map).bindPopup('📍 Titik Mulai');
                    }

                    places.forEach((place, index) => {
                        if (place.latitude && place.longitude) {
                            L.marker([place.latitude, place.longitude], {
                                icon: L.icon({
                                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                                    iconSize: [25, 41],
                                    iconAnchor: [12, 41],
                                })
                            }).addTo(map).bindPopup(`${index + 1}. ${place.name || 'Tempat Wisata'}`);
                        }
                    });

                    if (returnTrip && returnTrip.to && returnTrip.to.latitude && returnTrip.to.longitude) {
                        L.marker([returnTrip.to.latitude, returnTrip.to.longitude], {
                            icon: L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                            })
                        }).addTo(map).bindPopup('🏠 Lokasi Awal (Pulang)');
                    }

                    if (waypoints.length >= 2) {
                        const segmentColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899',
                            '#06b6d4',
                            '#84cc16'
                        ];
                        let totalDistance = 0;

                        for (let i = 0; i < waypoints.length - 1; i++) {
                            const segmentWaypoints = [waypoints[i], waypoints[i + 1]];
                            const color = segmentColors[i % segmentColors.length];

                            const result = await buildSegment(map, segmentWaypoints, color);
                            if (result && result.distance) {
                                totalDistance += result.distance;
                            }
                        }

                        const distanceKm = (totalDistance / 1000).toFixed(2);
                        const durationMin = Math.round((parseFloat(distanceKm) / 40) * 60);

                        const infoEl = document.querySelector(`#map-day-${dayNumber}`).parentElement
                            .querySelector('.route-info');
                        if (infoEl) {
                            infoEl.innerHTML = `
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📏</span>
                                    <span>Total Jarak: <strong>${distanceKm} km</strong></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">⏱️</span>
                                    <span>Waktu Tempuh: <strong>${durationMin} menit</strong></span>
                                </div>
                            `;
                        }
                    }

                    if (allCoords.length > 1) {
                        map.fitBounds(allCoords, {
                            padding: [50, 50]
                        });
                    }
                }

                dailyPlans.forEach(day => {
                    const places = (day.places || []).filter(p => p.type === 'place' && p.latitude && p.longitude);
                    const startPoint = day.start_point || null;
                    const returnTrip = (day.places || []).find(p => p.type === 'travel' && p.is_return);

                    if (places.length > 0) {
                        setTimeout(() => {
                            initMap(day.day, places, startPoint, returnTrip);
                        }, 200 * day.day);
                    }
                });
            })();
        </script>
    @endpush
@endsection
