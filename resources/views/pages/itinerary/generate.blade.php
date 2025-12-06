@extends('layouts.itinerary')

@section('title', 'Generate Itinerary | Itinerary Sumut')

@section('content')
    <div class="container mx-auto px-6 max-w-4xl">
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-green-700 mb-4">Generate Itinerary</h1>
            <p class="text-gray-600 text-lg">Tentukan durasi perjalanan dan tanggal mulai untuk menghasilkan itinerary Anda.
            </p>
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
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">3
                    </div>
                    <span class="ml-2 text-green-600 font-semibold">Generate</span>
                </div>
                <div class="w-16 h-1 bg-gray-300"></div>
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-bold">
                        4</div>
                    <span class="ml-2 text-gray-400">Hasil</span>
                </div>
            </div>
        </div>

        {{-- Summary Preferensi + Selected Places --}}
        <div class="mb-6 space-y-4">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">Ringkasan Preferensi</h2>
                <div class="flex flex-wrap gap-2">
                    @if (!empty($startLocation))
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                            📍 Start: {{ $startLocation }}
                        </span>
                    @endif
                    @if (!empty($startDate))
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                            📅 {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                            @if (!empty($endDate))
                                - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                            @endif
                        </span>
                    @endif
                    @if (!empty($durationDays))
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                            ⏱️ Durasi: {{ $durationDays }} hari
                        </span>
                    @endif
                    @if (!empty($startTime))
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                            ⏰ Jam Berangkat: {{ $startTime }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Lokasi Awal Map --}}
            @if (!empty($startLat) && !empty($startLng))
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">Lokasi Awal Perjalanan</h2>
                    @if (!empty($startLocation))
                        <p class="text-sm text-gray-600 mb-3">📍 {{ $startLocation }}</p>
                    @endif
                    <div id="start-location-map" class="w-full h-64 rounded-lg border border-gray-200"></div>
                </div>
            @endif

            {{-- Tempat yang Dipilih per Hari --}}
            @if (isset($placesByDay))
                @foreach ($placesByDay as $day => $dayPlaceIds)
                    @php
                        $dayPlaces = $places->whereIn('id', $dayPlaceIds);
                        $activityLevel = $activityLevels[$day] ?? 'normal';
                    @endphp
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">
                            Hari {{ $day }} - Aktivitas: {{ ucfirst($activityLevel) }}
                            <span class="text-sm font-normal text-gray-500">({{ $dayPlaces->count() }} tempat)</span>
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($dayPlaces as $place)
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900">{{ $place->name }}</h3>
                                        @if ($place->city)
                                            <p class="text-sm text-gray-500">{{ $place->city }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Form --}}
        <form action="{{ route('itinerary.generate.process') }}" method="POST" class="bg-white rounded-lg shadow-lg p-8"
            id="generateForm">
            @csrf

            {{-- Places by Day --}}
            @if (isset($placesByDay))
                @foreach ($placesByDay as $day => $dayPlaceIds)
                    @foreach ($dayPlaceIds as $placeId)
                        <input type="hidden" name="places_by_day[{{ $day }}][]" value="{{ $placeId }}">
                    @endforeach
                @endforeach
            @endif

            {{-- Activity Levels --}}
            @if (isset($activityLevels))
                @foreach ($activityLevels as $day => $activityLevel)
                    <input type="hidden" name="activity_levels[{{ $day }}]" value="{{ $activityLevel }}">
                @endforeach
            @endif

            @foreach ($categorySlugs as $categorySlug)
                <input type="hidden" name="category_slugs[]" value="{{ $categorySlug }}">
            @endforeach

            {{-- Bawa lagi preferensi dari Step 1 --}}
            @if (!empty($startLocation))
                <input type="hidden" name="start_location" value="{{ $startLocation }}">
            @endif
            @if (!empty($startLat) && !empty($startLng))
                <input type="hidden" name="start_lat" value="{{ $startLat }}">
                <input type="hidden" name="start_lng" value="{{ $startLng }}">
            @endif
            @if (!empty($durationDays))
                <input type="hidden" name="duration_days" value="{{ $durationDays }}">
            @endif
            @if (!empty($startTime))
                <input type="hidden" name="start_time" value="{{ $startTime }}">
            @endif

            {{-- Duration (Read-only dari preferences) --}}
            @if (!empty($durationDays))
                <input type="hidden" name="duration_days" value="{{ $durationDays }}">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Durasi Perjalanan
                    </label>
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                        {{ $durationDays }} Hari{{ $durationDays > 1 ? ' (' . ($durationDays - 1) . ' Malam)' : '' }}
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Durasi dari preferensi yang sudah dipilih sebelumnya.</p>
                </div>
            @else
                {{-- Fallback jika tidak ada duration_days --}}
                <input type="hidden" name="duration_days" value="1">
            @endif

            {{-- Start Date (dari preferences atau user pilih) --}}
            @if (!empty($startDate))
                {{-- Jika sudah ada dari preferences, gunakan itu (read-only) --}}
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Mulai Perjalanan
                    </label>
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                        📅 {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }}
                        @if (!empty($endDate))
                            <span class="text-gray-500"> - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Tanggal perjalanan dari preferensi yang sudah dipilih sebelumnya.
                    </p>
                </div>
            @else
                {{-- Jika belum ada, user harus pilih --}}
                <div class="mb-6">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Mulai Perjalanan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                        min="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none">
                    <p class="text-sm text-gray-500 mt-2">Pilih tanggal mulai perjalanan Anda. Durasi perjalanan sudah
                        ditentukan dari preferensi sebelumnya ({{ $durationDays ?? 1 }} hari).</p>
                    @error('start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Start Time (Read-only dari preferences) --}}
            @if (!empty($startTime))
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Berangkat Hari Pertama
                    </label>
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                        ⏰ {{ $startTime }}
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        Jam berangkat dari preferensi yang sudah dipilih sebelumnya.
                    </p>
                </div>
            @endif

            {{-- Start Location (Read-only dari preferences) --}}
            @if (!empty($startLocation))
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Lokasi Start
                    </label>
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                        📍 {{ $startLocation }}
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        Lokasi start dari preferensi yang sudah dipilih sebelumnya.
                    </p>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex justify-between gap-4 mt-8">
                <a href="{{ route('itinerary.places') }}"
                    onclick="event.preventDefault(); document.getElementById('backForm').submit();"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    ← Kembali
                </a>
                <form id="backForm" action="{{ route('itinerary.places') }}" method="POST" class="hidden">
                    @csrf
                    @foreach ($categorySlugs as $categorySlug)
                        <input type="hidden" name="category_slugs[]" value="{{ $categorySlug }}">
                    @endforeach
                </form>
                <button type="submit" id="generateBtn"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                    <span id="btnText">Generate Itinerary →</span>
                    <span id="btnLoading" class="hidden">Memproses...</span>
                </button>
            </div>
        </form>

        {{-- Info Box --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 text-sm">
                <strong>Informasi:</strong> Sistem akan secara otomatis mengatur jadwal perjalanan Anda,
                termasuk rekomendasi tempat makan dan penginapan berdasarkan tempat-tempat yang Anda pilih.
            </p>
        </div>
    </div>

    {{-- Leaflet Map CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-sA+4J8CkPC0Q5qLhG0uVHtVHCnQWmvMRG4EusDkq2nE=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-o9N1j7kGStpU1Qf0NenE2no0LYgR5p3pUVi6drwQP3s=" crossorigin=""></script>

    <script>
        // Show loading state on form submit
        document.getElementById('generateForm').addEventListener('submit', function() {
            const btn = document.getElementById('generateBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');

            btn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
        });

        // Initialize start location map
        @if (!empty($startLat) && !empty($startLng))
            document.addEventListener('DOMContentLoaded', function() {
                const startLat = {{ $startLat }};
                const startLng = {{ $startLng }};
                const startLocation = @json($startLocation ?? 'Lokasi Awal');

                const map = L.map('start-location-map').setView([startLat, startLng], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Add marker for start location
                L.marker([startLat, startLng], {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        })
                    }).addTo(map)
                    .bindPopup(`<strong>Lokasi Awal</strong><br>${startLocation}`)
                    .openPopup();
            });
        @endif
    </script>
@endsection
