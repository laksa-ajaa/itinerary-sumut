<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Itinerary | Itinerary Sumut')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    
    {{-- Leaflet Routing Machine CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

    {{-- Flatpickr CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- Additional CSS from pages --}}
    @stack('styles')
</head>

<body class="bg-gray-50 text-gray-900">

    {{-- Navbar --}}
    @include('partials.navbar')

    {{-- Main Content --}}
    <main class="min-h-screen py-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-300 mt-20">
        <div class="container mx-auto px-6 py-10">
            <div class="text-center">
                <h2 class="text-xl font-bold text-white mb-3">ItinerarySumut</h2>
                <p>Platform rekomendasi perjalanan cerdas untuk menjelajahi Sumatera Utara.</p>
            </div>
            <div class="border-t border-gray-700 text-center py-4 text-sm text-gray-400 mt-8">
                © {{ date('Y') }} Itinerary Sumut. All rights reserved.
            </div>
        </div>
    </footer>

    @if (session('success'))
        <div class="fixed bottom-5 right-5 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="fixed bottom-5 right-5 bg-red-600 text-white px-4 py-3 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    
    {{-- Leaflet Routing Machine JS --}}
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Page-specific scripts --}}
    @stack('scripts')

    @vite('resources/js/app.js')
</body>

</html>
