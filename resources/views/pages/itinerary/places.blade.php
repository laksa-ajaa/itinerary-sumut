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

            {{-- Recommended Places (Hybrid) --}}
            @if (isset($recommendedPlaces) && $recommendedPlaces->count() > 0)
                <div class="mb-6 bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Rekomendasi Utama (Hybrid CB + CF)
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Dipilih otomatis dari kombinasi konten tempat dan perilaku pengguna lain yang mirip.
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
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    Tambahkan atau kurangi tempat wisata dari daftar lengkap berikut:
                </label>

                @if ($places->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($places as $place)
                            <label
                                class="flex flex-col p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 transition">
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
                @if ($places->count() > 0)
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
        // Validate at least one place is selected
        document.getElementById('placesForm').addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('input[name="place_ids[]"]:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 tempat wisata.');
            }
        });
    </script>
@endsection
