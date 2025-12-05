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

    {{-- Flatpickr CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- Additional CSS from pages --}}
    @stack('styles')
</head>

<body class="bg-gray-50 text-gray-900">

    {{-- Navbar --}}
    <nav class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="container mx-auto flex items-center justify-between px-6 py-3">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-green-700">Itinerary<span
                    class="text-emerald-500">Sumut</span></a>
            <ul class="hidden md:flex gap-6 font-medium text-gray-700">
                <li><a href="{{ route('home') }}" class="hover:text-green-600">Beranda</a></li>
                <li><a href="{{ route('itinerary.preferences') }}" class="hover:text-green-600">Buat Itinerary</a>
                </li>
                @auth
                    <li><a href="{{ route('dashboard') }}" class="hover:text-green-600">Dashboard</a></li>
                @endauth
            </ul>
            <div class="flex items-center gap-4">
                @guest
                    <a href="{{ route('login') }}"
                        class="hidden md:block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        Masuk
                    </a>
                @else
                    <span class="hidden md:block text-gray-700">{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="hidden md:block">
                        @csrf
                        <button type="submit"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                            Logout
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </nav>
    <div class="h-16"></div>

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

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Page-specific scripts --}}
    @stack('scripts')

    @vite('resources/js/app.js')
</body>

</html>
