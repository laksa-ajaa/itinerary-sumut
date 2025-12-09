@extends('layouts.itinerary')

@section('title', 'Itinerary Saya | Itinerary Sumut')

@section('content')
    <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <p class="text-sm text-gray-500">Itinerary tersimpan</p>
                <h1 class="text-2xl font-bold text-gray-900">Daftar Itinerary Saya</h1>
            </div>
            <a href="{{ route('itinerary.preferences') }}"
                class="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                + Buat Itinerary Baru
            </a>
        </div>

        @if ($itineraries->isEmpty())
            <div class="bg-white border border-dashed border-gray-200 rounded-xl p-8 text-center text-gray-600">
                <p class="text-lg font-semibold mb-2">Belum ada itinerary</p>
                <p class="mb-4">Mulai rencanakan perjalananmu dan simpan itinerary di sini.</p>
                <a href="{{ route('itinerary.preferences') }}"
                    class="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    Buat Itinerary
                </a>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($itineraries as $itinerary)
                    @php
                        $start = $itinerary->start_date ? \Carbon\Carbon::parse($itinerary->start_date) : null;
                        $end = $start ? $start->copy()->addDays(max(0, ($itinerary->day_count ?? 1) - 1)) : null;
                        $payload = $itinerary->generated_payload ?? [];
                        $summary = $payload['summary'] ?? [];
                        $categories = $payload['metadata']['categories'] ?? [];
                    @endphp
                    <div class="bg-white border rounded-xl shadow-sm p-5 flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Itinerary</p>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $itinerary->title ?? 'Perjalanan' }}</h3>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full bg-green-50 text-green-700 border border-green-100">
                                {{ $itinerary->activity_level ?? 'normal' }}
                            </span>
                        </div>

                        <div class="text-sm text-gray-700 space-y-1">
                            <p>
                                <span class="font-medium">Tanggal:</span>
                                @if ($start && $end)
                                    {{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}
                                @elseif ($start)
                                    {{ $start->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </p>
                            <p>
                                <span class="font-medium">Durasi:</span>
                                {{ $itinerary->day_count ?? ($payload['metadata']['duration_days'] ?? 1) }} hari
                            </p>
                            @if (!empty($categories))
                                <p>
                                    <span class="font-medium">Kategori:</span>
                                    {{ implode(', ', $categories) }}
                                </p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm text-gray-700 bg-gray-50 border border-gray-100 rounded-lg p-3">
                            <div>
                                <p class="text-xs text-gray-500">Total Tempat</p>
                                <p class="font-semibold">{{ $summary['total_places'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Estimasi Biaya</p>
                                <p class="font-semibold">
                                    @if (isset($summary['total_estimated_cost']))
                                        Rp{{ number_format($summary['total_estimated_cost'], 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Jarak Total</p>
                                <p class="font-semibold">
                                    @if (isset($summary['total_distance_km']))
                                        {{ $summary['total_distance_km'] }} km
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Dibuat</p>
                                <p class="font-semibold">{{ $itinerary->created_at?->format('d M Y') }}</p>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-2">
                            <div class="text-xs text-gray-500">
                                Dibuat {{ $itinerary->created_at?->diffForHumans() }}
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('itinerary.show', $itinerary->id) }}"
                                    class="text-green-600 hover:text-green-700 font-medium text-sm">Lihat Detail</a>
                                <form action="{{ route('itinerary.destroy', $itinerary->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus itinerary ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700 text-sm">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $itineraries->links() }}
            </div>
        @endif
    </div>
@endsection



