@extends('layouts.landing.app')

@section('title', 'Itinerary Sumut – Jelajahi Sumatera Utara')

@section('content')

    {{-- Hero Section --}}
    <section id="home" class="relative bg-cover bg-center h-[90vh]"
        style="background-image: url('{{ asset('images/hero-sumut.jpg') }}')">
        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="text-center text-white px-6">
                <h1 class="text-5xl md:text-6xl font-bold mb-4">Temukan Keindahan Sumatera Utara</h1>
                <p class="text-lg md:text-xl mb-8">Bangun itinerary impianmu dengan rekomendasi AI cerdas dan peta
                    interaktif.</p>

                {{-- Kondisi login --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('itinerary.preferences') }}"
                        class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-lg text-white font-semibold transition w-48 text-center">
                        Buat Itinerary
                    </a>
                    @guest
                        <a href="{{ route('register') }}"
                            class="bg-white text-green-700 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition w-48 text-center">
                            Daftar Sekarang
                        </a>
                        <a href="{{ route('login') }}"
                            class="bg-white text-green-700 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition w-48 text-center">
                            Masuk
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}"
                            class="bg-white text-green-700 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition w-48 text-center">
                            Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-20 bg-white text-center">
        <h2 class="text-3xl font-bold mb-10 text-green-700">Kenapa Itinerary Sumut?</h2>
        <div class="grid md:grid-cols-3 gap-10 max-w-6xl mx-auto">
            <div class="p-6 border rounded-xl hover:shadow-lg transition">
                <h3 class="text-xl font-semibold mb-3">Rekomendasi AI</h3>
                <p>Temukan destinasi terbaik berdasarkan minat, waktu, dan preferensimu secara otomatis.</p>
            </div>
            <div class="p-6 border rounded-xl hover:shadow-lg transition">
                <h3 class="text-xl font-semibold mb-3">Peta Interaktif</h3>
                <p>Jelajahi wisata, kuliner, dan penginapan langsung melalui peta interaktif Sumatera Utara.</p>
            </div>
            <div class="p-6 border rounded-xl hover:shadow-lg transition">
                <h3 class="text-xl font-semibold mb-3">Jadwal Otomatis</h3>
                <p>AI membantu mengatur waktu perjalananmu agar efisien dan menyenangkan setiap harinya.</p>
            </div>
        </div>
    </section>

    {{-- Popular Destinations --}}
    <section id="destinations" class="py-20 bg-gray-50 text-center">
        <h2 class="text-3xl font-bold mb-10 text-green-700">Destinasi Populer</h2>
        <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <div class="rounded-xl overflow-hidden shadow hover:scale-105 transition">
                <img src="{{ asset('images/danau-toba.jpg') }}" alt="Danau Toba" class="w-full h-60 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold">Danau Toba</h3>
                    <p class="text-sm text-gray-600">Keajaiban alam terbesar di Sumatera Utara.</p>
                </div>
            </div>
            <div class="rounded-xl overflow-hidden shadow hover:scale-105 transition">
                <img src="{{ asset('images/berastagi.jpg') }}" alt="Berastagi" class="w-full h-60 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold">Berastagi</h3>
                    <p class="text-sm text-gray-600">Kota sejuk di kaki Gunung Sibayak yang penuh pesona.</p>
                </div>
            </div>
            <div class="rounded-xl overflow-hidden shadow hover:scale-105 transition">
                <img src="{{ asset('images/medan.jpg') }}" alt="Medan" class="w-full h-60 object-cover">
                <div class="p-5">
                    <h3 class="text-lg font-semibold">Medan</h3>
                    <p class="text-sm text-gray-600">Pusat kuliner dan budaya dengan tempat menarik.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Call to Action --}}
    <section id="cta" class="py-20 bg-green-700 text-white text-center">
        <h2 class="text-3xl font-bold mb-6">Siap menjelajahi Sumut?</h2>
        <p class="mb-8 text-lg">Buat itinerary pertamamu sekarang dan temukan destinasi tersembunyi favoritmu!</p>

        <a href="{{ route('itinerary.preferences') }}"
            class="bg-white text-green-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
            Buat Itinerary Sekarang
        </a>
    </section>

@endsection
