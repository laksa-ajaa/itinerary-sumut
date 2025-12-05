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
                @if($durationDays)
                    <strong>{{ $durationDays }} Hari</strong>
                    @if($startDate)
                        ({{ \Carbon\Carbon::parse($startDate)->locale('id')->isoFormat('D MMM YYYY') }}
                        @if($endDate)
                            - {{ \Carbon\Carbon::parse($endDate)->locale('id')->isoFormat('D MMM YYYY') }}
                        @endif
                        )
                    @endif
                @endif
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                @if(!empty($meta['categories']))
                    <span class="text-sm text-gray-500">Minat:</span>
                    @foreach($meta['categories'] as $preference)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                            {{ $preference }}
                        </span>
                    @endforeach
                @endif

                @if(!empty($meta['budget_level']))
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">
                        Budget: {{ ucfirst($meta['budget_level']) }}
                    </span>
                @endif

                @if(!empty($meta['activity_level']))
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                        Aktivitas: {{ ucfirst($meta['activity_level']) }}
                    </span>
                @endif

                @if(!empty($meta['start_location']))
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
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">✓</div>
                    <span class="ml-2 text-green-600 font-semibold">Preferensi</span>
                </div>
                <div class="w-16 h-1 bg-green-600"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">✓</div>
                    <span class="ml-2 text-green-600 font-semibold">Pilih Tempat</span>
                </div>
                <div class="w-16 h-1 bg-green-600"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">✓</div>
                    <span class="ml-2 text-green-600 font-semibold">Generate</span>
                </div>
                <div class="w-16 h-1 bg-green-600"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">✓</div>
                    <span class="ml-2 text-green-600 font-semibold">Hasil</span>
                </div>
            </div>
        </div>

        {{-- Itinerary Days --}}
        <div class="space-y-6">
            @foreach($itinerary['daily_plans'] as $day)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    {{-- Day Header --}}
                    <div class="bg-green-600 text-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold">Hari {{ $day['day'] }}</h2>
                            <span class="text-green-100">
                                {{ \Carbon\Carbon::parse($day['date'])->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                            </span>
                        </div>
                    </div>

                    {{-- Activities --}}
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($day['places'] as $index => $activity)
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
                                @endphp
                                <div class="flex items-start gap-4 p-4 rounded-lg 
                                    @if($type == 'place' || $type == 'wisata') bg-green-50 border-l-4 border-green-500
                                    @elseif($type == 'meal') bg-yellow-50 border-l-4 border-yellow-500
                                    @elseif($type == 'hotel' || $type == 'lodging') bg-blue-50 border-l-4 border-blue-500
                                    @elseif($type == 'travel') bg-gray-50 border-l-4 border-gray-400
                                    @else bg-gray-100 border-l-4 border-gray-300
                                    @endif">
                                    
                                    {{-- Time --}}
                                    <div class="flex-shrink-0">
                                        <div class="w-20 text-center">
                                            <span class="text-lg font-bold text-gray-900">{{ $timeLabel }}</span>
                                            @if($durationLabel)
                                                <span class="block text-xs text-gray-500">{{ $durationLabel }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Activity Content --}}
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 mb-1">{{ $title }}</h3>
                                        @if($location)
                                            <p class="text-gray-600">{{ $location }}</p>
                                        @endif
                                        
                                        {{-- Icon based on type --}}
                                        <div class="mt-2 flex items-center gap-2">
                                            @if($type == 'place' || $type == 'wisata')
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
                                                <span class="text-xs text-gray-500">Perjalanan</span>
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
            nav, footer, .no-print {
                display: none;
            }
            body {
                background: white;
            }
        }
    </style>
@endsection

