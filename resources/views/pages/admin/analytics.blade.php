@extends('layouts.admin')

@section('title', 'Analitik Aplikasi')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Sebaran Jenis Destinasi</h2>
            <span class="text-xs text-gray-500">kind</span>
        </div>
        <canvas id="chartKind" class="w-full h-64"></canvas>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Kota Terbanyak</h2>
            <span class="text-xs text-gray-500">Top 10</span>
        </div>
        <canvas id="chartCity" class="w-full h-64"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200 lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Tren Itinerary</h2>
            <span class="text-xs text-gray-500">30 hari terakhir</span>
        </div>
        <canvas id="chartItinerary" class="w-full h-60"></canvas>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Registrasi User</h2>
            <span class="text-xs text-gray-500">30 hari terakhir</span>
        </div>
        <canvas id="chartUser" class="w-full h-60"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Kunjungan Tempat</h2>
            <span class="text-xs text-gray-500">30 hari terakhir</span>
        </div>
        <canvas id="chartVisit" class="w-full h-56"></canvas>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Peringkat Destinasi</h2>
            <span class="text-xs text-gray-500">Top 10 rating</span>
        </div>
        <div class="space-y-2">
            @foreach ($ratingsLeaderboard as $place)
                <div class="flex items-center justify-between border border-slate-100 rounded-lg px-3 py-2">
                    <div>
                        <p class="font-medium">{{ $place->name }}</p>
                        <p class="text-xs text-gray-500">{{ $place->city ?? 'Kota tidak diketahui' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold">{{ number_format($place->rating_avg, 1) }}</p>
                        <p class="text-xs text-gray-500">{{ $place->rating_count }} ulasan</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const kindData = @json($placesByKind);
    const cityData = @json($placesByCity);
    const itineraryData = @json($itineraryTrends);
    const userData = @json($userSignupTrends);
    const visitData = @json($visitTrends);

    const palette = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#0ea5e9', '#22c55e', '#a855f7'];

    const ctxKind = document.getElementById('chartKind');
    if (ctxKind) {
        new Chart(ctxKind, {
            type: 'doughnut',
            data: {
                labels: kindData.map(k => k.kind || 'lainnya'),
                datasets: [{
                    data: kindData.map(k => k.total),
                    backgroundColor: palette,
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }

    const ctxCity = document.getElementById('chartCity');
    if (ctxCity) {
        new Chart(ctxCity, {
            type: 'bar',
            data: {
                labels: cityData.map(c => c.city || 'Tidak diketahui'),
                datasets: [{
                    label: 'Lokasi',
                    backgroundColor: palette[0],
                    data: cityData.map(c => c.total),
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    }

    const buildLineChart = (selector, data, label, colorIndex = 1) => {
        const el = document.getElementById(selector);
        if (!el) return;
        new Chart(el, {
            type: 'line',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label,
                    data: data.map(d => d.total),
                    tension: 0.3,
                    fill: false,
                    borderColor: palette[colorIndex % palette.length],
                }]
            },
            options: { scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
        });
    };

    buildLineChart('chartItinerary', itineraryData, 'Itinerary', 1);
    buildLineChart('chartUser', userData, 'User baru', 2);
    buildLineChart('chartVisit', visitData, 'Kunjungan', 3);
</script>
@endpush

