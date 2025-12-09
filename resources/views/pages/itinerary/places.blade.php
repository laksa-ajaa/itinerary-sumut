@extends('layouts.itinerary')

@section('title', 'Pilih Tempat Wisata | Itinerary Sumut')

@section('content')
    <div class="container mx-auto px-6 max-w-6xl">
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-green-700 mb-4">Pilih Tempat Wisata</h1>
            <p class="text-gray-600 text-lg">
                Sistem sudah menyiapkan rekomendasi awal berdasarkan preferensi dan data pengguna lain.
                Anda tetap bisa menambah / mengurangi tempat sesuai keinginan.
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                @if ($categories && $categories->count() > 0)
                    <span class="text-sm text-gray-500">Minat:</span>
                    @foreach ($categories as $category)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                            @if (isset($category['emoji']))
                                <span class="mr-1">{{ $category['emoji'] }}</span>
                            @endif
                            {{ $category['name'] }}
                        </span>
                    @endforeach
                @endif

                @if (!empty($startLocation))
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                        Start: {{ $startLocation }}
                    </span>
                @endif

                @if (!empty($durationDays))
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                        Durasi: {{ $durationDays }} hari
                    </span>
                @endif

                @if (!empty($startTime))
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                        Jam Berangkat: {{ $startTime }}
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
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">2
                    </div>
                    <span class="ml-2 text-green-600 font-semibold">Pilih Tempat</span>
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

        {{-- Form --}}
        <form action="{{ route('itinerary.generate') }}" method="POST" id="placesForm">
            @csrf
            @foreach ($categorySlugs as $categorySlug)
                <input type="hidden" name="category_slugs[]" value="{{ $categorySlug }}">
            @endforeach

            {{-- Persist setup values from Step 1 --}}
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
            @if (!empty($startDate))
                <input type="hidden" name="start_date" value="{{ $startDate }}">
            @endif
            @if (!empty($endDate))
                <input type="hidden" name="end_date" value="{{ $endDate }}">
            @endif

            {{-- Action Buttons (Top) --}}
            <div class="flex justify-between gap-4 mb-6">
                <a href="{{ route('itinerary.preferences') }}"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    ← Kembali
                </a>
            </div>

            {{-- Info Box --}}
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-800 text-sm">
                    <strong>Petunjuk:</strong> Pilih tempat wisata untuk setiap hari perjalanan Anda. Tingkat aktivitas akan otomatis ditentukan berdasarkan jumlah wisata yang dipilih.
                    <br>
                    <strong>Santai:</strong> 1-3 wisata | <strong>Normal:</strong> 4-5 wisata | <strong>Padat:</strong> 5+
                    wisata
                </p>
            </div>

            {{-- Per Hari Selection --}}
            @if ($places->count() > 0 || (isset($recommendedPlaces) && $recommendedPlaces->count() > 0))
                @php
                    $allPlaces = collect();
                    if (isset($recommendedPlaces) && $recommendedPlaces->count() > 0) {
                        $allPlaces = $allPlaces->merge($recommendedPlaces);
                    }
                    if ($places->count() > 0) {
                        $allPlaces = $allPlaces->merge($places);
                    }
                    $allPlaces = $allPlaces->unique('id');
                @endphp

                @for ($day = 1; $day <= $durationDays; $day++)
                    <div class="mb-6 bg-white rounded-lg shadow-lg p-8 day-section" data-day="{{ $day }}">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-green-700">Hari {{ $day }}</h2>
                            <div class="flex items-center gap-4">
                                {{-- Activity Level per Hari (Auto) --}}
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-700">
                                        Tingkat Aktivitas:
                                    </span>
                                    <span id="activity-level-display-day-{{ $day }}"
                                        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium">
                                        Belum dipilih
                                    </span>
                                    {{-- Hidden input untuk activity level --}}
                                    <input type="hidden" name="activity_levels[{{ $day }}]"
                                        id="activity_level_day_{{ $day }}" value="">
                                </div>
                                <div class="text-sm text-gray-600">
                                    <span
                                        class="selection-count-day-{{ $day }} font-semibold text-green-700">0</span>
                                    wisata dipilih
                                </div>
                            </div>
                        </div>

                        {{-- Search untuk hari ini --}}
                        <div class="mb-4">
                            <div class="relative">
                                <input type="text" id="search-places-day-{{ $day }}"
                                    placeholder="Cari tempat wisata untuk hari {{ $day }}..."
                                    class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none">
                                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        {{-- Place Selection untuk hari ini --}}
                        <div
                            class="places-container-day-{{ $day }} grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($allPlaces as $index => $place)
                                <label data-place-name="{{ strtolower($place->name) }}"
                                    data-place-city="{{ strtolower($place->city ?? '') }}"
                                    data-place-description="{{ strtolower($place->description ?? '') }}"
                                    class="place-item-day-{{ $day }} flex flex-col p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 transition"
                                    style="{{ $index >= 12 ? 'display: none;' : '' }}">
                                    <div class="flex items-start">
                                        <input type="checkbox" name="places_by_day[{{ $day }}][]"
                                            value="{{ $place->id }}"
                                            class="place-checkbox-day-{{ $day }} mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                            data-day="{{ $day }}" data-name="{{ $place->name }}">
                                        <div class="ml-3 flex-1">
                                            <h3 class="font-semibold text-gray-900 mb-1">{{ $place->name }}</h3>
                                            @if (isset($recommendedPlaces) && $recommendedPlaces->contains('id', $place->id))
                                                <span
                                                    class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs rounded mb-2">
                                                    🔮 Rekomendasi
                                                </span>
                                            @else
                                                <span
                                                    class="inline-block px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded mb-2">
                                                    📍 Objek Wisata
                                                </span>
                                            @endif
                                            @if ($place->description)
                                                <p class="text-sm text-gray-600 mb-2 line-clamp-2">
                                                    {{ $place->description }}</p>
                                            @endif
                                            <div class="flex flex-wrap gap-1 mb-2">
                                                @php
                                                    $placeCategories = \App\Helpers\PlaceCategoryHelper::extractCategoriesFromKind(
                                                        $place->kind,
                                                    );
                                                    $categoryInfo = collect(
                                                        \App\Helpers\PlaceCategoryHelper::getCategories(),
                                                    )
                                                        ->whereIn('slug', $placeCategories)
                                                        ->take(3);
                                                @endphp
                                                @foreach ($categoryInfo as $cat)
                                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">
                                                        {{ $cat['name'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                                @if ($place->city)
                                                    <span>📍 {{ $place->city }}</span>
                                                @endif
                                                @if ($place->rating_avg > 0)
                                                    <span class="flex items-center">
                                                        ⭐ {{ number_format($place->rating_avg, 1) }}
                                                        @if ($place->rating_count > 0)
                                                            <span class="ml-1">({{ $place->rating_count }})</span>
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($place->entry_price)
                                                <p class="text-sm text-green-600 font-medium mt-1">
                                                    Rp {{ number_format($place->entry_price, 0, ',', '.') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div id="no-results-day-{{ $day }}" class="text-center py-8 hidden">
                            <p class="text-gray-500">Tidak ada tempat wisata yang sesuai dengan pencarian.</p>
                        </div>

                        {{-- Selected Places List --}}
                        <div class="selected-places-day-{{ $day }} mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Tempat yang Dipilih:</h3>
                            <ul class="list-disc pl-5 space-y-1 text-gray-700"></ul>
                        </div>
                    </div>
                @endfor
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg mb-4">Tidak ada tempat wisata yang ditemukan untuk kategori yang
                        dipilih.</p>
                    <a href="{{ route('itinerary.preferences') }}"
                        class="text-green-600 hover:text-green-700 font-semibold">
                        ← Kembali pilih preferensi
                    </a>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex justify-between gap-4 mt-8">
                <a href="{{ route('itinerary.preferences') }}"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    ← Kembali
                </a>
                @if ($places->count() > 0 || (isset($recommendedPlaces) && $recommendedPlaces->count() > 0))
                    <button type="submit"
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                        Lanjutkan →
                    </button>
                @endif
            </div>
        </form>
    </div>

    <script>
        (function() {
            const form = document.getElementById('placesForm');
            const durationDays = {{ $durationDays ?? 1 }};

            // Initialize each day
            for (let day = 1; day <= durationDays; day++) {
                initDay(day);
            }

            function initDay(day) {
                const activityInput = document.getElementById(`activity_level_day_${day}`);
                const activityDisplay = document.getElementById(`activity-level-display-day-${day}`);
                const checkboxes = Array.from(document.querySelectorAll(`.place-checkbox-day-${day}`));
                const searchInput = document.getElementById(`search-places-day-${day}`);
                const countEl = document.querySelector(`.selection-count-day-${day}`);
                const placesContainer = document.querySelector(`.places-container-day-${day}`);
                const noResultsEl = document.getElementById(`no-results-day-${day}`);
                const allPlaceItems = Array.from(document.querySelectorAll(`.place-item-day-${day}`));
                const selectedUl = document.querySelector(`.selected-places-day-${day} ul`);

                // Determine activity level based on count
                function getActivityLevel(count) {
                    if (count === 0) {
                        return { level: '', label: 'Belum dipilih', color: 'bg-gray-100 text-gray-700' };
                    } else if (count >= 1 && count <= 3) {
                        return { level: 'santai', label: 'Santai (1-3 wisata)', color: 'bg-blue-100 text-blue-700' };
                    } else if (count >= 4 && count <= 5) {
                        return { level: 'normal', label: 'Normal (4-5 wisata)', color: 'bg-green-100 text-green-700' };
                    } else {
                        return { level: 'padat', label: 'Padat (5+ wisata)', color: 'bg-orange-100 text-orange-700' };
                    }
                }

                // Update activity level display and hidden input
                function updateActivityLevel() {
                    const checked = checkboxes.filter(cb => cb.checked).length;
                    const activity = getActivityLevel(checked);
                    
                    if (activityInput) {
                        activityInput.value = activity.level;
                    }
                    
                    if (activityDisplay) {
                        activityDisplay.textContent = activity.label;
                        activityDisplay.className = `px-3 py-2 ${activity.color} rounded-lg text-sm font-medium`;
                    }
                }

                // Update count display
                function updateCount() {
                    const checked = checkboxes.filter(cb => cb.checked).length;
                    if (countEl) {
                        countEl.textContent = checked;
                    }
                }

                // Update selected list
                function updateSelectedList() {
                    selectedUl.innerHTML = ''; // Clear list
                    checkboxes
                        .filter(cb => cb.checked)
                        .forEach(cb => {
                            const li = document.createElement('li');
                            li.textContent = cb.dataset.name;
                            li.dataset.placeId = cb.value;
                            selectedUl.appendChild(li);
                        });
                }

                // Checkbox change
                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        updateCount();
                        updateSelectedList();
                        updateActivityLevel();
                    });
                });

                // Search functionality per day
                if (searchInput) {
                    searchInput.addEventListener('input', function(e) {
                        const term = e.target.value.toLowerCase().trim();
                        let visibleCount = 0;

                        allPlaceItems.forEach(item => {
                            const name = item.dataset.placeName || '';
                            const city = item.dataset.placeCity || '';
                            const description = item.dataset.placeDescription || '';
                            const matches = term === '' || name.includes(term) || city.includes(term) ||
                                description.includes(term);

                            if (matches) {
                                item.style.display = 'flex';
                                visibleCount++;
                            } else {
                                item.style.display = 'none';
                            }
                        });

                        if (noResultsEl) {
                            if (visibleCount === 0 && term !== '') {
                                noResultsEl.classList.remove('hidden');
                            } else {
                                noResultsEl.classList.add('hidden');
                            }
                        }
                    });
                }

                // Initial count, list, and activity level
                updateCount();
                updateSelectedList();
                updateActivityLevel();
            }

            // Form validation
            form.addEventListener('submit', function(e) {
                // Update activity levels before submit
                for (let day = 1; day <= durationDays; day++) {
                    const checkboxes = Array.from(document.querySelectorAll(
                        `.place-checkbox-day-${day}:checked`));
                    const activityInput = document.getElementById(`activity_level_day_${day}`);
                    const count = checkboxes.length;
                    
                    // Determine activity level
                    let activityLevel = '';
                    if (count >= 1 && count <= 3) {
                        activityLevel = 'santai';
                    } else if (count >= 4 && count <= 5) {
                        activityLevel = 'normal';
                    } else if (count > 5) {
                        activityLevel = 'padat';
                    }
                    
                    if (activityInput) {
                        activityInput.value = activityLevel;
                    }
                }

                // Check if at least one day has places
                let totalSelected = 0;
                for (let day = 1; day <= durationDays; day++) {
                    const checkboxes = Array.from(document.querySelectorAll(
                        `.place-checkbox-day-${day}:checked`));
                    totalSelected += checkboxes.length;
                }

                if (totalSelected === 0) {
                    e.preventDefault();
                    alert('Silakan pilih minimal 1 tempat wisata untuk setidaknya satu hari.');
                    return false;
                }
            });
        })();
    </script>
@endsection
