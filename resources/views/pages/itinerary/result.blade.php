        @extends('layouts.itinerary')

        @section('title', 'Itinerary Hasil | Itinerary Sumut')

        @section('content')
            <div class="container mx-auto px-6 max-w-6xl">
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
                            <div
                                class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">
                                ✓</div>
                            <span class="ml-2 text-green-600 font-semibold">Preferensi</span>
                        </div>
                        <div class="w-16 h-1 bg-green-600"></div>
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">
                                ✓</div>
                            <span class="ml-2 text-green-600 font-semibold">Pilih Tempat</span>
                        </div>
                        <div class="w-16 h-1 bg-green-600"></div>
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">
                                ✓</div>
                            <span class="ml-2 text-green-600 font-semibold">Generate</span>
                        </div>
                        <div class="w-16 h-1 bg-green-600"></div>
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">
                                ✓</div>
                            <span class="ml-2 text-green-600 font-semibold">Hasil</span>
                        </div>
                    </div>
                </div>

                {{-- Itinerary Days --}}
                <div class="space-y-6">
                    @foreach ($itinerary['daily_plans'] as $day)
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
                                                <label for="start_time_day_{{ $day['day'] }}"
                                                    class="text-green-100 text-sm">
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
                                            <span
                                                class="px-3 py-1 bg-green-500 text-white rounded-full text-xs font-medium">
                                                {{ ucfirst($day['activity_level']) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Map Visualization --}}
                            @php
                                $placesForMap = array_filter($day['places'] ?? [], function ($p) {
                                    return ($p['type'] ?? '') === 'place' &&
                                        !empty($p['latitude']) &&
                                        !empty($p['longitude']);
                                });
                            @endphp
                            @if (count($placesForMap) > 0)
                                <div class="px-6 pt-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Rute Perjalanan</h3>
                                    <div id="map-day-{{ $day['day'] }}"
                                        class="w-full h-96 rounded-lg border border-gray-200 mb-4"></div>
                                    <div class="route-info flex gap-4 text-sm text-gray-600 mb-4">
                                        @if (!empty($day['stats']['total_distance']))
                                            <span>📏 Total Jarak:
                                                <strong>{{ number_format($day['stats']['total_distance'], 1) }}
                                                    km</strong></span>
                                        @endif
                                        @if (!empty($day['route_geometry']['duration_minutes']))
                                            <span>⏱️ Estimasi Waktu Tempuh:
                                                <strong>{{ number_format($day['route_geometry']['duration_minutes'], 0) }}
                                                    menit</strong></span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Activities --}}
                            <div class="p-6">
                                <div class="space-y-4">
                                    {{-- Show start location first if available --}}
                                    @if (!empty($day['start_point']))
                                        <div
                                            class="flex items-start gap-4 p-4 rounded-lg bg-gray-50 border-l-4 border-gray-400">
                                            <div class="flex-shrink-0">
                                                <div class="w-20 text-center">
                                                    <span class="text-lg font-bold text-gray-900">Start</span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 mb-1">📍 Lokasi Awal</h3>
                                                <p class="text-gray-600 text-sm">
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

                                            // Create unique ID for travel items to update from LRM
                                            $travelId = null;
                                            if (
                                                $type === 'travel' &&
                                                !empty($activity['from']) &&
                                                !empty($activity['to'])
                                            ) {
                                                // Use coordinates to create unique ID for matching with LRM segments
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
                                        <div class="flex items-start gap-4 p-4 rounded-lg 
                                            @if ($type == 'place' || $type == 'wisata') bg-green-50 border-l-4 border-green-500
                                            @elseif($type == 'meal') bg-yellow-50 border-l-4 border-yellow-500
                                            @elseif($type == 'hotel' || $type == 'lodging') bg-blue-50 border-l-4 border-blue-500
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
                                            @if ($type === 'place' || $type === 'meal' || $type === 'hotel' || $type === 'lodging') data-activity-start-time="{{ $activity['start_time'] ?? '' }}"
                                                data-activity-end-time="{{ $activity['end_time'] ?? '' }}"
                                                data-activity-duration="{{ $activity['duration_minutes'] ?? 0 }}" @endif>

                                            {{-- Time --}}
                                            <div class="flex-shrink-0">
                                                <div class="w-20 text-center">
                                                    <span
                                                        class="text-lg font-bold text-gray-900 time-display">{{ $timeLabel }}</span>
                                                    @if ($durationLabel)
                                                        <span
                                                            class="block text-xs text-gray-500 duration-display">{{ $durationLabel }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Activity Content --}}
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 mb-1">{{ $title }}</h3>
                                                @if ($location)
                                                    <p class="text-gray-600">{{ $location }}</p>
                                                @endif


                                                {{-- Restaurant info for lunch --}}
                                                @if ($type == 'meal' && $activity['meal_type'] == 'lunch' && !empty($activity['restaurant']))
                                                    <div class="mt-2 p-2 bg-yellow-100 rounded">
                                                        <p class="text-sm font-medium text-yellow-800">
                                                            🍽️ Rekomendasi: {{ $activity['restaurant']['name'] }}
                                                        </p>
                                                        @if (!empty($activity['restaurant']['rating_avg']))
                                                            <p class="text-xs text-yellow-700">
                                                                ⭐ Rating:
                                                                {{ number_format($activity['restaurant']['rating_avg'], 1) }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endif

                                                {{-- Icon based on type --}}
                                                <div class="mt-2 flex items-center gap-2">
                                                    @if ($type == 'place' || $type == 'wisata')
                                                        <span class="text-green-600">📍</span>
                                                        <span class="text-xs text-gray-500">Tempat Wisata</span>
                                                    @elseif($type == 'meal')
                                                        <span class="text-yellow-600">🍴</span>
                                                        <span class="text-xs text-gray-500">Makan</span>
                                                    @elseif($type == 'hotel' || $type == 'lodging')
                                                        <span class="text-blue-600">🏨</span>
                                                        <span class="text-xs text-gray-500">Penginapan</span>
                                                    @elseif($type == 'travel')
                                                        <span class="text-gray-600">🚗</span>
                                                        <span class="text-xs text-gray-500">
                                                            @if (!empty($activity['is_return']))
                                                                Pulang ke Rumah
                                                            @else
                                                                Perjalanan
                                                            @endif
                                                        </span>
                                                        @if (!empty($activity['from']) && !empty($activity['to']))
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                @if (!empty($activity['is_return']))
                                                                    🏠 Pulang dari {{ $activity['from']['name'] }} ke
                                                                    Lokasi Awal
                                                                @else
                                                                    Dari: {{ $activity['from']['name'] }} → Ke:
                                                                    {{ $activity['to']['name'] }}
                                                                @endif
                                                            </p>
                                                            <p class="text-xs text-blue-600 font-medium mt-1"
                                                                id="{{ $travelId }}-info">
                                                                ⏱️ <span
                                                                    class="travel-duration">{{ number_format($activity['duration_minutes'] ?? 0, 0) }}</span>
                                                                menit
                                                                @if (!empty($activity['distance_km']))
                                                                    • 📏 <span
                                                                        class="travel-distance">{{ number_format($activity['distance_km'], 1) }}</span>
                                                                    km
                                                                @endif
                                                                <span class="text-xs text-gray-400 ml-2">(estimasi)</span>
                                                            </p>
                                                        @endif
                                                    @elseif($type == 'departure')
                                                        <span class="text-gray-600">🚪</span>
                                                        <span class="text-xs text-gray-500">Berangkat</span>
                                                    @elseif($type == 'checkout')
                                                        <span class="text-gray-600">🚪</span>
                                                        <span class="text-xs text-gray-500">Check-out</span>
                                                    @elseif($type == 'return')
                                                        <span class="text-gray-600">🏠</span>
                                                        <span class="text-xs text-gray-500">Pulang</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
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
                }
            </style>

            @push('scripts')
                <script>
                    // Mapbox configuration
                    const MAPBOX_ACCESS_TOKEN = '{{ config('services.mapbox.access_token') }}';

                    // Validate Mapbox token
                    if (!MAPBOX_ACCESS_TOKEN || MAPBOX_ACCESS_TOKEN === '') {
                        console.error('⚠️ Mapbox Access Token tidak ditemukan! Pastikan MAPBOX_ACCESS_TOKEN sudah di-set di file .env');
                    } else {
                        console.log('✅ Mapbox Access Token loaded');
                    }

                    (function() {
                        // Initialize maps for each day using Leaflet Routing Machine with Mapbox routing
                        const dailyPlans = @json($itinerary['daily_plans'] ?? []);

                        function initMap(dayNumber, places, startPoint, returnTrip = null) {
                            if (typeof L === 'undefined' || typeof L.Routing === 'undefined') {
                                console.log('Waiting for Leaflet Routing Machine...');
                                setTimeout(() => initMap(dayNumber, places, startPoint), 100);
                                return;
                            }

                            const mapId = 'map-day-' + dayNumber;
                            const mapElement = document.getElementById(mapId);
                            if (!mapElement) return;

                            if (mapElement._leaflet_id) {
                                return; // Already initialized
                            }

                            // Build waypoints array
                            const waypoints = [];
                            const allCoords = [];

                            // Add start point if available
                            if (startPoint && startPoint.lat && startPoint.lng) {
                                waypoints.push(L.latLng(startPoint.lat, startPoint.lng));
                                allCoords.push([startPoint.lat, startPoint.lng]);
                            }

                            // Add places as waypoints
                            places.forEach(place => {
                                if (place.latitude && place.longitude) {
                                    waypoints.push(L.latLng(place.latitude, place.longitude));
                                    allCoords.push([place.latitude, place.longitude]);
                                }
                            });

                            // Add return trip waypoint if available
                            if (returnTrip && returnTrip.to && returnTrip.to.latitude && returnTrip.to.longitude) {
                                waypoints.push(L.latLng(returnTrip.to.latitude, returnTrip.to.longitude));
                                allCoords.push([returnTrip.to.latitude, returnTrip.to.longitude]);
                            }

                            if (waypoints.length === 0) return;

                            // Initialize map
                            const map = L.map(mapId).setView(allCoords[0], 11);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(map);

                            // Add start point marker
                            if (startPoint && startPoint.lat && startPoint.lng) {
                                L.marker([startPoint.lat, startPoint.lng], {
                                    icon: L.icon({
                                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                                        iconSize: [25, 41],
                                        iconAnchor: [12, 41],
                                    })
                                }).addTo(map).bindPopup('📍 Titik Mulai');
                            }

                            // Add place markers
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

                            // Add return point marker if available
                            if (returnTrip && returnTrip.to && returnTrip.to.latitude && returnTrip.to.longitude) {
                                L.marker([returnTrip.to.latitude, returnTrip.to.longitude], {
                                    icon: L.icon({
                                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                                        iconSize: [25, 41],
                                        iconAnchor: [12, 41],
                                    })
                                }).addTo(map).bindPopup('🏠 Lokasi Awal (Pulang)');
                            }

                            // Create routes with different colors for each segment
                            if (waypoints.length >= 2) {
                                // Colors for different segments
                                const segmentColors = [
                                    '#3b82f6', // blue
                                    '#10b981', // green
                                    '#f59e0b', // amber
                                    '#ef4444', // red
                                    '#8b5cf6', // purple
                                    '#ec4899', // pink
                                    '#06b6d4', // cyan
                                    '#84cc16', // lime
                                ];

                                let totalDistance = 0;
                                let totalDuration = 0;
                                let segmentsCompleted = 0;

                                // Store segment data for matching with travel items
                                const segmentData = new Map();

                                // Create routing for each segment separately
                                for (let i = 0; i < waypoints.length - 1; i++) {
                                    const segmentWaypoints = [waypoints[i], waypoints[i + 1]];
                                    const color = segmentColors[i % segmentColors.length];

                                    // Create key for matching with travel items
                                    const fromKey = `${waypoints[i].lat.toFixed(4)},${waypoints[i].lng.toFixed(4)}`;
                                    const toKey = `${waypoints[i + 1].lat.toFixed(4)},${waypoints[i + 1].lng.toFixed(4)}`;
                                    const segmentKey = fromKey + '|' + toKey;

                                    // Create routing control for this segment using Mapbox (hidden)
                                    // Check if Mapbox router is available, fallback to OSRM if not
                                    let router;
                                    if (MAPBOX_ACCESS_TOKEN && MAPBOX_ACCESS_TOKEN !== '' && L.Routing.mapbox) {
                                        router = L.Routing.mapbox(MAPBOX_ACCESS_TOKEN, {
                                            profile: 'mapbox/driving',
                                            language: 'id'
                                        });
                                    } else {
                                        console.warn(
                                            '⚠️ Mapbox router tidak tersedia, menggunakan OSRM demo (tidak disarankan untuk production)'
                                            );
                                        router = L.Routing.osrmv1({
                                            serviceUrl: 'https://router.project-osrm.org/route/v1',
                                            profile: 'driving'
                                        });
                                    }

                                    const segmentControl = L.Routing.control({
                                        waypoints: segmentWaypoints,
                                        routeWhileDragging: false,
                                        showAlternatives: false,
                                        addWaypoints: false,
                                        createMarker: function() {
                                            return false; // Don't create markers
                                        },
                                        router: router
                                    });

                                    segmentControl.addTo(map);

                                    // Hide the control panel immediately after adding
                                    setTimeout(() => {
                                        const container = segmentControl.getContainer();
                                        if (container) {
                                            const panel = container.querySelector('.leaflet-routing-container');
                                            if (panel) {
                                                panel.style.display = 'none';
                                            }
                                            // Also hide the control itself
                                            container.style.display = 'none';
                                        }
                                    }, 100);

                                    // Handle route found for this segment
                                    segmentControl.on('routesfound', function(e) {
                                        const routes = e.routes;
                                        if (routes && routes.length > 0) {
                                            const route = routes[0];

                                            // Get actual distance from LRM
                                            const segmentDistanceKm = (route.summary.totalDistance / 1000).toFixed(2);
                                            // Calculate duration from distance using 40 km/h speed
                                            const segmentDurationMin = Math.round((parseFloat(segmentDistanceKm) / 40) *
                                                60);

                                            // Remove default blue line
                                            setTimeout(() => {
                                                map.eachLayer(function(layer) {
                                                    if (layer instanceof L.Polyline &&
                                                        layer.options.color === '#3388ff' &&
                                                        layer._latlngs &&
                                                        layer._latlngs.length === route.coordinates.length
                                                    ) {
                                                        map.removeLayer(layer);
                                                    }
                                                });
                                            }, 100);

                                            // Draw segment with custom color
                                            if (route.coordinates && route.coordinates.length > 0) {
                                                L.polyline(route.coordinates, {
                                                    color: color,
                                                    weight: 5,
                                                    opacity: 0.8
                                                }).addTo(map);
                                            }

                                            // Store segment data
                                            segmentData.set(segmentKey, {
                                                distance: segmentDistanceKm,
                                                duration: segmentDurationMin
                                            });

                                            // Update travel item in list with LRM data
                                            // Find travel item by matching coordinates (with tolerance for floating point)
                                            const travelItems = document.querySelectorAll(
                                                `[data-day="${dayNumber}"][id^="travel-day-"]`);
                                            travelItems.forEach(item => {
                                                const fromLat = parseFloat(item.dataset.fromLat);
                                                const fromLng = parseFloat(item.dataset.fromLng);
                                                const toLat = parseFloat(item.dataset.toLat);
                                                const toLng = parseFloat(item.dataset.toLng);

                                                // Check if coordinates match (with small tolerance)
                                                const latMatch = Math.abs(fromLat - waypoints[i].lat) < 0.0001 &&
                                                    Math.abs(toLat - waypoints[i + 1].lat) < 0.0001;
                                                const lngMatch = Math.abs(fromLng - waypoints[i].lng) < 0.0001 &&
                                                    Math.abs(toLng - waypoints[i + 1].lng) < 0.0001;

                                                if (latMatch && lngMatch) {
                                                    const travelInfoEl = item.querySelector('[id$="-info"]');
                                                    if (travelInfoEl) {
                                                        const durationSpan = travelInfoEl.querySelector(
                                                            '.travel-duration');
                                                        const distanceSpan = travelInfoEl.querySelector(
                                                            '.travel-distance');
                                                        if (durationSpan) {
                                                            durationSpan.textContent = segmentDurationMin;
                                                            durationSpan.parentElement.classList.remove(
                                                                'text-blue-600');
                                                            durationSpan.parentElement.classList.add(
                                                                'text-green-600');
                                                        }
                                                        if (distanceSpan) {
                                                            distanceSpan.textContent = segmentDistanceKm;
                                                        }

                                                        // Remove "estimasi" label since we have real data from LRM
                                                        const estimasiLabel = travelInfoEl.querySelector(
                                                            '.text-gray-400');
                                                        if (estimasiLabel) estimasiLabel.remove();
                                                    }

                                                    // Update travel time in the itinerary list
                                                    // Use LRM distance and calculate time with 40 km/h
                                                    const timeDisplay = item.querySelector('.time-display');
                                                    if (timeDisplay && item.dataset.startTime) {
                                                        const startTime = item.dataset.startTime;
                                                        // Parse time string (format: "HH:MM")
                                                        const [startHours, startMinutes] = startTime.split(':').map(
                                                            Number);
                                                        const startDate = new Date(2000, 0, 1, startHours,
                                                            startMinutes, 0);

                                                        // Calculate end time using LRM distance and 40 km/h speed
                                                        // segmentDurationMin already calculated from LRM distance with 40 km/h
                                                        const endDate = new Date(startDate.getTime() +
                                                            segmentDurationMin * 60000);

                                                        // Format time as HH:MM
                                                        const formatTime = (date) => {
                                                            const h = date.getHours().toString().padStart(2,
                                                                '0');
                                                            const m = date.getMinutes().toString().padStart(2,
                                                                '0');
                                                            return `${h}:${m}`;
                                                        };

                                                        const startTimeStr = formatTime(startDate);
                                                        const endTimeStr = formatTime(endDate);

                                                        timeDisplay.textContent = `${startTimeStr} - ${endTimeStr}`;

                                                        // Update end time in data attribute
                                                        item.dataset.endTime = endTimeStr;

                                                        // Update next activity times based on new travel end time
                                                        updateNextActivityTimes(item, endTimeStr, dayNumber);
                                                    }
                                                }
                                            });

                                            totalDistance += route.summary.totalDistance;
                                            segmentsCompleted++;

                                            // Update info when all segments are done
                                            if (segmentsCompleted === waypoints.length - 1) {
                                                const distanceKm = (totalDistance / 1000).toFixed(2);
                                                // Calculate total duration from total distance using 40 km/h
                                                const durationMin = Math.round((parseFloat(distanceKm) / 40) * 60);

                                                const infoEl = document.querySelector(`#map-day-${dayNumber}`).parentElement
                                                    .querySelector('.route-info');
                                                if (infoEl) {
                                                    infoEl.innerHTML = `
                                                    <span>📏 Total Jarak: <strong>${distanceKm} km</strong></span>
                                                    <span>⏱️ Total Waktu perjalanan: <strong>${durationMin} menit</strong></span>
                                                `;
                                                }
                                            }
                                        }
                                    });

                                    // Handle routing error for this segment
                                    segmentControl.on('routingerror', function(e) {
                                        console.warn(`Segment ${i} routing error:`, e.error);
                                        // Draw straight line as fallback
                                        L.polyline([
                                            [waypoints[i].lat, waypoints[i].lng],
                                            [waypoints[i + 1].lat, waypoints[i + 1].lng]
                                        ], {
                                            color: color,
                                            weight: 5,
                                            opacity: 0.8,
                                            dashArray: '10, 5'
                                        }).addTo(map);

                                        segmentsCompleted++;
                                    });
                                }
                            } else {
                                // If only one waypoint, just center on it
                                map.setView(allCoords[0], 13);
                            }

                            // Fit bounds to show all waypoints
                            if (allCoords.length > 1) {
                                map.fitBounds(allCoords, {
                                    padding: [50, 50]
                                });
                            }
                        }

                        // Function to update next activity times after travel time changes
                        function updateNextActivityTimes(travelItem, newEndTime, dayNumber) {
                            // Find all items for this day
                            const allItems = Array.from(document.querySelectorAll(`[data-day="${dayNumber}"]`));
                            const travelIndex = allItems.indexOf(travelItem);

                            if (travelIndex === -1) return;

                            let currentTime = newEndTime;

                            // Update all items after the travel item
                            for (let i = travelIndex + 1; i < allItems.length; i++) {
                                const item = allItems[i];
                                const activityStartTime = item.dataset.activityStartTime;
                                const activityEndTime = item.dataset.activityEndTime;
                                const activityDuration = parseInt(item.dataset.activityDuration) || 0;

                                // Check if it's an activity (place, meal, hotel)
                                if (activityStartTime && activityEndTime && activityDuration > 0) {
                                    // Parse the current time (end time from previous item)
                                    const [hours, minutes] = currentTime.split(':').map(Number);
                                    const startDate = new Date(2000, 0, 1, hours, minutes, 0);

                                    // Calculate new end time based on activity duration
                                    const endDate = new Date(startDate.getTime() + activityDuration * 60000);

                                    // Format time as HH:MM
                                    const formatTime = (date) => {
                                        const h = date.getHours().toString().padStart(2, '0');
                                        const m = date.getMinutes().toString().padStart(2, '0');
                                        return `${h}:${m}`;
                                    };

                                    const startTimeStr = formatTime(startDate);
                                    const endTimeStr = formatTime(endDate);

                                    // Update display
                                    const timeDisplay = item.querySelector('.time-display');
                                    if (timeDisplay) {
                                        timeDisplay.textContent = `${startTimeStr} - ${endTimeStr}`;
                                    }

                                    // Update data attributes
                                    item.dataset.activityStartTime = startTimeStr;
                                    item.dataset.activityEndTime = endTimeStr;

                                    // Update current time for next iteration
                                    currentTime = endTimeStr;
                                }
                                // Check if it's another travel item
                                else if (item.dataset.startTime) {
                                    // For travel items, update start time
                                    // End time will be updated when LRM data arrives
                                    const [hours, minutes] = currentTime.split(':').map(Number);
                                    const startDate = new Date(`2000-01-01 ${hours}:${minutes}:00`);
                                    const startTimeStr = startDate.toTimeString().slice(0, 5);

                                    item.dataset.startTime = startTimeStr;

                                    // Update display with current end time (will be updated by LRM later)
                                    const timeDisplay = item.querySelector('.time-display');
                                    if (timeDisplay) {
                                        const currentEndTime = item.dataset.endTime || startTimeStr;
                                        timeDisplay.textContent = `${startTimeStr} - ${currentEndTime}`;
                                    }

                                    // Continue to next item, LRM will update end time when data arrives
                                    // Don't break here, continue updating subsequent items
                                }
                            }
                        }

                        // Initialize all maps
                        dailyPlans.forEach(day => {
                            const places = (day.places || []).filter(p => p.type === 'place' && p.latitude && p.longitude);
                            const startPoint = day.start_point || null;
                            // Check if there's a return trip
                            const returnTrip = (day.places || []).find(p => p.type === 'travel' && p.is_return);

                            if (places.length > 0) {
                                setTimeout(() => {
                                    initMap(day.day, places, startPoint, returnTrip);
                                }, 200 * day.day); // Stagger initialization to avoid API rate limits
                            }
                        });

                        // Function to update schedule when start time changes
                        function updateScheduleForDay(dayNumber, newStartTime) {
                            const allItems = Array.from(document.querySelectorAll(`[data-day="${dayNumber}"]`));
                            if (allItems.length === 0) return;

                            // Parse new start time
                            const [hours, minutes] = newStartTime.split(':').map(Number);
                            let currentTime = new Date(2000, 0, 1, hours, minutes, 0);

                            // Helper function to format time
                            const formatTime = (date) => {
                                const h = date.getHours().toString().padStart(2, '0');
                                const m = date.getMinutes().toString().padStart(2, '0');
                                return `${h}:${m}`;
                            };

                            // Update each item in order
                            allItems.forEach((item, index) => {
                                // Skip start location marker
                                const titleEl = item.querySelector('h3');
                                if (titleEl && titleEl.textContent.trim() === '📍 Lokasi Awal') {
                                    return;
                                }

                                // Check if it's a travel item (has travel-duration class or from/to data)
                                const travelDurationEl = item.querySelector('.travel-duration');
                                const hasTravelData = item.dataset.fromLat !== undefined || travelDurationEl !== null;

                                // Check if it's an activity item (has activity-duration data)
                                const activityDuration = parseInt(item.dataset.activityDuration) || 0;

                                if (hasTravelData) {
                                    // Handle travel items
                                    // Get duration from data attribute first (most reliable)
                                    let travelDuration = parseInt(item.dataset.travelDuration) || 0;

                                    // Fallback to element text if data attribute not available
                                    if (travelDuration === 0 && travelDurationEl) {
                                        travelDuration = parseInt(travelDurationEl.textContent) || 0;
                                    }

                                    // Last fallback: calculate from existing times
                                    if (travelDuration === 0 && item.dataset.startTime && item.dataset.endTime) {
                                        const [startH, startM] = item.dataset.startTime.split(':').map(Number);
                                        const [endH, endM] = item.dataset.endTime.split(':').map(Number);
                                        const start = new Date(2000, 0, 1, startH, startM);
                                        const end = new Date(2000, 0, 1, endH, endM);
                                        travelDuration = Math.round((end - start) / 60000);
                                    }

                                    if (travelDuration > 0) {
                                        const startTimeStr = formatTime(currentTime);
                                        const endTime = new Date(currentTime.getTime() + travelDuration * 60000);
                                        const endTimeStr = formatTime(endTime);

                                        // Update display
                                        const timeDisplay = item.querySelector('.time-display');
                                        if (timeDisplay) {
                                            timeDisplay.textContent = `${startTimeStr} - ${endTimeStr}`;
                                        }

                                        // Update data attributes
                                        item.dataset.startTime = startTimeStr;
                                        item.dataset.endTime = endTimeStr;

                                        currentTime = endTime;
                                    }
                                } else if (activityDuration > 0) {
                                    // Handle activity items (place, meal, hotel)
                                    const startTimeStr = formatTime(currentTime);
                                    const endTime = new Date(currentTime.getTime() + activityDuration * 60000);
                                    const endTimeStr = formatTime(endTime);

                                    // Update display
                                    const timeDisplay = item.querySelector('.time-display');
                                    if (timeDisplay) {
                                        timeDisplay.textContent = `${startTimeStr} - ${endTimeStr}`;
                                    }

                                    // Update duration display if exists
                                    const durationDisplay = item.querySelector('.duration-display');
                                    if (durationDisplay) {
                                        durationDisplay.textContent = `${activityDuration} menit`;
                                    }

                                    // Update data attributes
                                    item.dataset.activityStartTime = startTimeStr;
                                    item.dataset.activityEndTime = endTimeStr;

                                    currentTime = endTime;
                                }
                            });
                        }

                        // Add event listeners for start time inputs
                        document.querySelectorAll('input[type="time"][data-day]').forEach(input => {
                            input.addEventListener('change', function() {
                                const dayNumber = parseInt(this.dataset.day);
                                const newStartTime = this.value;

                                if (dayNumber && newStartTime) {
                                    updateScheduleForDay(dayNumber, newStartTime);
                                }
                            });
                        });
                    })();
                </script>
            @endpush
        @endsection
