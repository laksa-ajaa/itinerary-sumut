@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <p class="text-sm text-gray-500">Total Destinasi</p>
        <p class="text-3xl font-bold">{{ number_format($placesCount) }}</p>
        <p class="text-xs text-gray-400 mt-1">Termasuk wisata, restoran, penginapan</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <p class="text-sm text-gray-500">Wisata</p>
        <p class="text-3xl font-bold">{{ number_format($attractionsCount) }}</p>
        <p class="text-xs text-gray-400 mt-1">Kategori kind: wisata/attraction</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <p class="text-sm text-gray-500">Restoran</p>
        <p class="text-3xl font-bold">{{ number_format($restaurantsCount) }}</p>
        <p class="text-xs text-gray-400 mt-1">Dari data GeoJSON / manual</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <p class="text-sm text-gray-500">Penginapan</p>
        <p class="text-3xl font-bold">{{ number_format($lodgingCount) }}</p>
        <p class="text-xs text-gray-400 mt-1">Hotel, homestay, guest house</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <p class="text-sm text-gray-500">Total User</p>
        <p class="text-3xl font-bold">{{ number_format($userCount) }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <p class="text-sm text-gray-500">Itinerary Dibuat</p>
        <p class="text-3xl font-bold">{{ number_format($itineraryCount) }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <p class="text-sm text-gray-500">Catatan Kunjungan</p>
        <p class="text-3xl font-bold">{{ number_format($visitCount) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200 lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Top Destinasi</h2>
            <span class="text-xs text-gray-500">Berdasar rating</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @forelse ($topRatedPlaces as $place)
                <div class="p-3 border border-slate-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold">{{ $place->name }}</p>
                            <p class="text-xs text-gray-500">{{ $place->city ?? 'Kota tidak diketahui' }}</p>
                        </div>
                        @if ($place->rating_avg)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">
                                {{ number_format($place->rating_avg, 1) }} ({{ $place->rating_count }})
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1 capitalize">Kind: {{ $place->kind ?? 'n/a' }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada data.</p>
            @endforelse
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <h2 class="text-lg font-semibold mb-3">Kota Terbanyak</h2>
        <ul class="space-y-2">
            @foreach ($topCities as $city)
                <li class="flex items-center justify-between">
                    <span>{{ $city->city ?? 'Tidak diketahui' }}</span>
                    <span class="text-sm text-gray-600">{{ $city->total }} lokasi</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">User Terbaru</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($latestUsers as $user)
                <div class="py-2 flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ $user->created_at?->format('d M Y') }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500 py-2">Belum ada user.</p>
            @endforelse
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Itinerary Terbaru</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($latestItineraries as $item)
                <div class="py-2 flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ $item->title }}</p>
                        <p class="text-xs text-gray-500">User: {{ $item->user_id }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ $item->created_at?->format('d M Y') }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500 py-2">Belum ada itinerary.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

