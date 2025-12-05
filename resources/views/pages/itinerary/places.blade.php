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

                @if (!empty($budgetLevel))
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">
                        Budget: {{ ucfirst($budgetLevel) }}
                    </span>
                @endif

                @if (!empty($activityLevel))
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                        Aktivitas: {{ ucfirst($activityLevel) }}
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
            @if (!empty($budgetLevel))
                <input type="hidden" name="budget_level" value="{{ $budgetLevel }}">
            @endif
            @if (!empty($activityLevel))
                <input type="hidden" name="activity_level" value="{{ $activityLevel }}">
            @endif
            @if (!empty($startDate))
                <input type="hidden" name="start_date" value="{{ $startDate }}">
            @endif
            @if (!empty($endDate))
                <input type="hidden" name="end_date" value="{{ $endDate }}">
            @endif

            @if (!empty($placeSelectionLimit))
                <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <div class="text-sm text-gray-600">
                        Batas sesuai aktivitas: maksimal {{ $placeSelectionLimit }} tempat
                        ({{ $placesPerDay ?? 0 }} / hari).
                    </div>
                    <div id="selection-limit-info" class="text-sm font-semibold text-green-700"></div>
                </div>
            @endif

            {{-- Action Buttons (Top) --}}
            <div class="flex justify-between gap-4 mb-6">
                <a href="{{ route('itinerary.preferences') }}"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    ← Kembali
                </a>
                @if ($places->count() > 0 || (isset($recommendedPlaces) && $recommendedPlaces->count() > 0))
                    <button type="submit" form="placesForm"
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                        Lanjutkan →
                    </button>
                @endif
            </div>

            {{-- Recommended Places (Hybrid) --}}
            @if (isset($recommendedPlaces) && $recommendedPlaces->count() > 0)
                <div class="mb-6 bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Rekomendasi Utama
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Dipilih otomatis berdasarkan preferensi dan data pengguna lain yang mirip.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($recommendedPlaces as $place)
                            <label
                                class="flex flex-col p-4 border-2 border-green-400 bg-green-50 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-100 transition">
                                <div class="flex items-start">
                                    <input type="checkbox" name="place_ids[]" value="{{ $place->id }}"
                                        class="mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                        checked>
                                    <div class="ml-3 flex-1">
                                        <h3 class="font-semibold text-gray-900 mb-1">{{ $place->name }}</h3>
                                        <span
                                            class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs rounded mb-2">
                                            🔮 Rekomendasi Sistem
                                        </span>
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
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif


            {{-- Place Selection (Lengkap) --}}
            <div class="mb-6 bg-white rounded-lg shadow-lg p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Tambahkan atau kurangi tempat wisata dari daftar lengkap berikut:
                    </label>
                    <div class="relative w-full md:w-64">
                        <input type="text" id="search-places" placeholder="Cari tempat wisata..."
                            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                @if ($places->count() > 0)
                    <div id="places-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($places as $index => $place)
                            <label data-place-name="{{ strtolower($place->name) }}"
                                data-place-city="{{ strtolower($place->city ?? '') }}"
                                data-place-description="{{ strtolower($place->description ?? '') }}"
                                class="place-item flex flex-col p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 transition"
                                style="{{ $index >= 10 ? 'display: none;' : '' }}">
                                <div class="flex items-start">
                                    <input type="checkbox" name="place_ids[]" value="{{ $place->id }}"
                                        class="mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    <div class="ml-3 flex-1">
                                        <h3 class="font-semibold text-gray-900 mb-1">{{ $place->name }}</h3>
                                        {{-- Badge untuk menunjukkan ini adalah objek wisata --}}
                                        <span
                                            class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs rounded mb-2">📍
                                            Objek Wisata</span>
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

                    <div id="no-results-message" class="text-center py-12 hidden">
                        <p class="text-gray-500 text-lg mb-4">Tidak ada tempat wisata yang sesuai dengan pencarian Anda.
                        </p>
                    </div>

                    @if ($places->count() > 10)
                        <div class="text-center mt-6">
                            <button type="button" id="load-more-btn"
                                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                                Muat Lebih Banyak
                            </button>
                            <p id="load-more-info" class="text-sm text-gray-500 mt-2"></p>
                        </div>
                    @endif
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

                @error('place_ids')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

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

        {{-- Info Box --}}
        @if ($places->count() > 0)
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-800 text-sm">
                    <strong>Tips:</strong> Pilih beberapa tempat wisata yang menarik minat Anda.
                    Anda akan bisa menentukan durasi perjalanan dan tanggal di langkah berikutnya.
                </p>
            </div>
        @endif
    </div>

    <script>
        (function() {
            const form = document.getElementById('placesForm');
            const checkboxes = Array.from(document.querySelectorAll('input[name="place_ids[]"]'));
            const maxSelection = {{ $placeSelectionLimit ?? 'null' }};
            const infoEl = document.getElementById('selection-limit-info');
            const searchInput = document.getElementById('search-places');
            const placesGrid = document.getElementById('places-grid');
            const loadMoreBtn = document.getElementById('load-more-btn');
            const loadMoreInfo = document.getElementById('load-more-info');
            const noResultsMessage = document.getElementById('no-results-message');

            let currentDisplayCount = 10;
            const itemsPerPage = 10;
            let allPlaceItems = Array.from(document.querySelectorAll('.place-item'));
            let filteredPlaceItems = [...allPlaceItems];

            function updateInfo() {
                if (!maxSelection || !infoEl) return;
                const count = document.querySelectorAll('input[name="place_ids[]"]:checked').length;
                infoEl.textContent = `Terpilih ${count} / ${maxSelection}`;
            }

            function trimInitial() {
                if (!maxSelection) return;
                const checked = checkboxes.filter(cb => cb.checked);
                if (checked.length > maxSelection) {
                    checked.slice(maxSelection).forEach(cb => (cb.checked = false));
                }
                updateInfo();
            }

            function filterPlaces(searchTerm) {
                const term = searchTerm.toLowerCase().trim();
                if (term === '') {
                    filteredPlaceItems = [...allPlaceItems];
                } else {
                    filteredPlaceItems = allPlaceItems.filter(item => {
                        const name = item.dataset.placeName || '';
                        const city = item.dataset.placeCity || '';
                        const description = item.dataset.placeDescription || '';
                        return name.includes(term) || city.includes(term) || description.includes(term);
                    });
                }

                currentDisplayCount = 10;
                displayPlaces();
            }

            function displayPlaces() {
                // Sembunyikan semua dulu
                allPlaceItems.forEach(item => {
                    item.style.display = 'none';
                });

                // Tampilkan pesan jika tidak ada hasil
                if (noResultsMessage) {
                    if (filteredPlaceItems.length === 0) {
                        noResultsMessage.classList.remove('hidden');
                        if (loadMoreBtn) loadMoreBtn.style.display = 'none';
                        if (loadMoreInfo) loadMoreInfo.textContent = '';
                        return;
                    } else {
                        noResultsMessage.classList.add('hidden');
                    }
                }

                // Tampilkan yang sesuai filter dan dalam batas currentDisplayCount
                const toShow = filteredPlaceItems.slice(0, currentDisplayCount);
                toShow.forEach(item => {
                    item.style.display = 'flex';
                });

                // Update tombol Load More
                if (loadMoreBtn) {
                    if (currentDisplayCount >= filteredPlaceItems.length) {
                        loadMoreBtn.style.display = 'none';
                        if (loadMoreInfo) {
                            loadMoreInfo.textContent = `Menampilkan semua ${filteredPlaceItems.length} tempat`;
                        }
                    } else {
                        loadMoreBtn.style.display = 'inline-block';
                        if (loadMoreInfo) {
                            loadMoreInfo.textContent =
                                `Menampilkan ${currentDisplayCount} dari ${filteredPlaceItems.length} tempat`;
                        }
                    }
                }
            }

            // Search functionality
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    filterPlaces(e.target.value);
                });
            }

            // Load More functionality
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    currentDisplayCount += itemsPerPage;
                    displayPlaces();
                });
            }

            // Initialize display
            if (allPlaceItems.length > 0) {
                displayPlaces();
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function(e) {
                    if (!maxSelection) {
                        updateInfo();
                        return;
                    }
                    const checkedCount = document.querySelectorAll('input[name="place_ids[]"]:checked')
                        .length;
                    if (checkedCount > maxSelection) {
                        e.target.checked = false;
                        alert(`Maksimal ${maxSelection} tempat sesuai tingkat aktivitas Anda.`);
                        return;
                    }
                    updateInfo();
                });
            });

            form.addEventListener('submit', function(e) {
                const selected = document.querySelectorAll('input[name="place_ids[]"]:checked');
                if (selected.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih minimal 1 tempat wisata.');
                    return;
                }
                if (maxSelection && selected.length > maxSelection) {
                    e.preventDefault();
                    alert(`Maksimal ${maxSelection} tempat sesuai tingkat aktivitas Anda.`);
                }
            });

            trimInitial();
            updateInfo();
        })();
    </script>
@endsection
