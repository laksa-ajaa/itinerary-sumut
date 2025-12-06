@extends('layouts.itinerary')

@section('title', 'Pilih Preferensi Wisata | Itinerary Sumut')

@section('content')
    <div class="container mx-auto px-6 max-w-4xl">
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-green-700 mb-4">Pilih Preferensi Wisata Anda</h1>
            <p class="text-gray-600 text-lg">Pilih kategori wisata yang menarik minat Anda. Kami akan menyesuaikan
                rekomendasi berdasarkan pilihan Anda.</p>
        </div>

        {{-- Progress Indicator --}}
        <div class="mb-8">
            <div class="flex items-center justify-center gap-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">1
                    </div>
                    <span class="ml-2 text-green-600 font-semibold">Preferensi</span>
                </div>
                <div class="w-16 h-1 bg-gray-300"></div>
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-bold">
                        2</div>
                    <span class="ml-2 text-gray-400">Pilih Tempat</span>
                </div>
                <div class="w-16 h-1 bg-gray-300"></div>
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-bold">
                        3</div>
                    <span class="ml-2 text-gray-400">Generate</span>
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

        {{-- Form Setup Itinerary --}}
        <form action="{{ route('itinerary.places') }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
            @csrf

            {{-- Lokasi Start + Map --}}
            <div class="mb-6">
                <label for="start_location" class="block text-sm font-medium text-gray-700 mb-2">
                    Lokasi Mulai Perjalanan
                </label>
                <input type="text" name="start_location" id="start_location" readonly
                    value="{{ old('start_location', 'Medan, Sumatera Utara') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none mb-3 bg-gray-50 cursor-not-allowed"
                    placeholder="Klik peta atau gunakan GPS">
                <p class="text-xs text-gray-500 mb-3">Klik peta atau tombol GPS untuk mengatur titik koordinat mulai
                    perjalanan.</p>

                <div id="start-map" class="w-full h-64 rounded-lg border border-gray-200"></div>
                <div class="flex items-center gap-3 mt-3">
                    <button type="button" id="use-my-location"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                        Gunakan GPS Saya
                    </button>
                    <span id="location-status" class="text-xs text-gray-500"></span>
                </div>

                <input type="hidden" name="start_lat" id="start_lat" value="{{ old('start_lat', '3.595195') }}">
                <input type="hidden" name="start_lng" id="start_lng" value="{{ old('start_lng', '98.672223') }}">

                @error('start_location')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Perjalanan (Range Date Picker) --}}
            <div class="mb-6">
                <label for="travel_dates" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Perjalanan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="travel_dates" id="travel_dates" required readonly
                    value="{{ old('travel_dates') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none bg-white cursor-pointer"
                    placeholder="Pilih tanggal mulai dan selesai">

                {{-- Hidden inputs untuk start_date, end_date, dan duration_days --}}
                <input type="hidden" name="start_date" id="start_date" value="{{ old('start_date') }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ old('end_date') }}">
                <input type="hidden" name="duration_days" id="duration_days" value="{{ old('duration_days') }}">

                <div id="duration-display" class="mt-2 text-sm text-gray-600 hidden">
                    <span class="font-semibold text-green-600"></span>
                </div>

                <p class="text-sm text-gray-500 mt-2">Pilih tanggal mulai dan selesai perjalanan Anda.</p>
                @error('travel_dates')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('start_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('end_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('duration_days')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jam Berangkat Hari Pertama --}}
            <div class="mb-6">
                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                    Jam Berangkat Hari Pertama <span class="text-red-500">*</span>
                </label>
                <input type="time" name="start_time" id="start_time" value="{{ old('start_time', '08:00') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none">
                <p class="text-sm text-gray-500 mt-2">Tentukan jam berangkat untuk hari pertama perjalanan Anda.</p>
                @error('start_time')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category Selection (Minat Wisata) --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    Minat Wisata (pilih minimal 1):
                </label>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($categories as $category)
                        <label
                            class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 transition">
                            <input type="checkbox" name="category_slugs[]" value="{{ $category['slug'] }}"
                                class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                @if (isset($category['emoji']))
                                    <span class="mr-1">{{ $category['emoji'] }}</span>
                                @endif
                                {{ $category['name'] }}
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('category_slugs')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end gap-4 mt-8">
                <a href="{{ route('home') }}"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                    Lanjutkan →
                </button>
            </div>
        </form>

        {{-- Info Box --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 text-sm">
                <strong>Tips:</strong> Isi dulu lokasi start, durasi, jam berangkat, lalu pilih minat wisata.
                Sistem akan mencocokkan wisata berdasarkan preferensi dan rekomendasi AI.
            </p>
        </div>
    </div>
@endsection

{{-- Scripts --}}
@push('scripts')
    <script>
        (function() {
            // Initialize Leaflet Map
            let mapInstance = null;
            let markerInstance = null;

            function updateLatLng(latlng) {
                const latInput = document.getElementById('start_lat');
                const lngInput = document.getElementById('start_lng');
                const startInput = document.getElementById('start_location');
                if (!latInput || !lngInput) return;

                latInput.value = latlng.lat.toFixed(6);
                lngInput.value = latlng.lng.toFixed(6);
                if (startInput) {
                    startInput.value = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;
                }
                if (markerInstance) {
                    markerInstance.setLatLng(latlng);
                }
            }

            function initMap() {
                if (typeof L === 'undefined') {
                    console.log('Waiting for Leaflet...');
                    setTimeout(initMap, 100);
                    return;
                }

                const mapElement = document.getElementById('start-map');
                const latInput = document.getElementById('start_lat');
                const lngInput = document.getElementById('start_lng');

                if (!mapElement || !latInput || !lngInput) {
                    console.log('Map elements not found');
                    return;
                }

                if (mapElement._leaflet_id) {
                    console.log('Map already initialized');
                    return;
                }

                const lat = parseFloat(latInput.value || '3.595195');
                const lng = parseFloat(lngInput.value || '98.672223');

                console.log('Initializing map at:', lat, lng);

                try {
                    mapInstance = L.map('start-map').setView([lat, lng], 11);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(mapInstance);

                    markerInstance = L.marker([lat, lng], {
                        draggable: true
                    }).addTo(mapInstance);

                    markerInstance.on('dragend', function(e) {
                        updateLatLng(e.target.getLatLng());
                    });

                    mapInstance.on('click', function(e) {
                        updateLatLng(e.latlng);
                    });

                    console.log('Map initialized successfully');
                } catch (error) {
                    console.error('Error initializing map:', error);
                }
            }

            function useGeolocation() {
                const statusEl = document.getElementById('location-status');
                const buttonEl = document.getElementById('use-my-location');
                const startInput = document.getElementById('start_location');

                if (!statusEl || !buttonEl) return;

                if (!navigator.geolocation) {
                    statusEl.textContent = 'GPS tidak didukung di browser ini.';
                    return;
                }

                statusEl.textContent = 'Mengambil lokasi...';
                buttonEl.disabled = true;
                buttonEl.classList.add('opacity-70', 'cursor-not-allowed');

                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    console.log('Geolocation success:', lat, lng);

                    if (mapInstance) {
                        mapInstance.setView([lat, lng], 13);
                    }
                    updateLatLng({ lat, lng });
                    statusEl.textContent = 'Lokasi GPS berhasil diambil.';
                    buttonEl.disabled = false;
                    buttonEl.classList.remove('opacity-70', 'cursor-not-allowed');
                }, function(error) {
                    console.error('Geolocation error:', error);
                    statusEl.textContent = 'Gagal mendapatkan lokasi. Izinkan GPS atau coba lagi.';
                    buttonEl.disabled = false;
                    buttonEl.classList.remove('opacity-70', 'cursor-not-allowed');
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                });
            }

            function initLocationButton() {
                const buttonEl = document.getElementById('use-my-location');
                if (!buttonEl) return;
                buttonEl.addEventListener('click', useGeolocation);
            }

            // Initialize Flatpickr Date Range Picker
            function initDatePicker() {
                if (typeof flatpickr === 'undefined') {
                    console.log('Waiting for Flatpickr...');
                    setTimeout(initDatePicker, 100);
                    return;
                }

                const dateInput = document.getElementById('travel_dates');
                const startDateInput = document.getElementById('start_date');
                const endDateInput = document.getElementById('end_date');
                const durationInput = document.getElementById('duration_days');
                const durationDisplay = document.getElementById('duration-display');

                if (!dateInput) {
                    console.log('Date input not found');
                    return;
                }

                flatpickr(dateInput, {
                    mode: "range",
                    minDate: "today",
                    dateFormat: "Y-m-d",
                    locale: {
                        rangeSeparator: " sampai "
                    },
                    onChange: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length === 2) {
                            const startDate = selectedDates[0];
                            const endDate = selectedDates[1];

                            // Calculate duration in days (inclusive)
                            const timeDiff = endDate.getTime() - startDate.getTime();
                            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) +
                            1; // +1 to include both start and end day

                            // Format dates for hidden inputs (YYYY-MM-DD)
                            const formatDate = (date) => {
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate()).padStart(2, '0');
                                return `${year}-${month}-${day}`;
                            };

                            startDateInput.value = formatDate(startDate);
                            endDateInput.value = formatDate(endDate);
                            durationInput.value = daysDiff;

                            // Show duration
                            durationDisplay.classList.remove('hidden');
                            const nights = daysDiff - 1;
                            durationDisplay.querySelector('span').textContent =
                                `Durasi: ${daysDiff} Hari${nights > 0 ? ' (' + nights + ' Malam)' : ''}`;

                            console.log('Date range selected:', {
                                start: formatDate(startDate),
                                end: formatDate(endDate),
                                duration: daysDiff
                            });
                        } else {
                            // Clear if incomplete selection
                            startDateInput.value = '';
                            endDateInput.value = '';
                            durationInput.value = '';
                            durationDisplay.classList.add('hidden');
                        }
                    }
                });

                console.log('Flatpickr initialized successfully');
            }

            // Start initialization
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    initMap();
                    initLocationButton();
                    initDatePicker();
                });
            } else {
                initMap();
                initLocationButton();
                initDatePicker();
            }
        })();
    </script>
@endpush
