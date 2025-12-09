@extends('layouts.landing.app')

@section('title', 'Itinerary Sumut – Jelajahi Sumatera Utara')

@section('content')

    {{-- Hero Section --}}
    <section id="home" class="relative bg-cover bg-center h-[90vh]"
        style="background-image: url('{{ asset('assets/img/hero-banner.svg') }}')">
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

    {{-- Tentang --}}
    <section id="about" class="py-20 bg-gray-50">
        <div class="max-w-5xl mx-auto px-6 grid md:grid-cols-2 gap-10 items-center">
            <div>
                <h2 class="text-3xl font-bold mb-4 text-green-700">Tentang Itinerary Sumut</h2>
                <p class="text-gray-700 leading-relaxed">
                    Itinerary Sumut adalah platform perencanaan perjalanan untuk mengeksplor Sumatera Utara.
                    Kami memanfaatkan rekomendasi AI, peta interaktif, dan jadwal otomatis supaya kamu bisa
                    merancang perjalanan yang pas dengan waktu, budget, dan preferensi kamu.
                </p>
            </div>
            <div class="bg-white border rounded-xl p-6 shadow-sm">
                <ul class="space-y-4 text-gray-700">
                    <li class="flex items-start gap-3">
                        <span class="text-green-600 text-lg">•</span>
                        <div>
                            <p class="font-semibold">Fokus Sumatera Utara</p>
                            <p class="text-sm text-gray-600">Kurasi khusus destinasi wisata, kuliner, dan penginapan di
                                Sumut.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-green-600 text-lg">•</span>
                        <div>
                            <p class="font-semibold">Ramah waktu & budget</p>
                            <p class="text-sm text-gray-600">Jadwal otomatis menyesuaikan jam mulai, durasi, serta tingkat
                                aktivitas.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-green-600 text-lg">•</span>
                        <div>
                            <p class="font-semibold">Simpan & akses kembali</p>
                            <p class="text-sm text-gray-600">Login untuk menyimpan itinerary dan melanjutkan kapan saja.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- Benefit Sistem --}}
    <section id="benefits" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-3xl font-bold mb-10 text-green-700 text-center">Bagaimana sistem ini membantu?</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-6 border rounded-xl hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold mb-3">Pilih preferensi</h3>
                    <p class="text-gray-700">Atur kategori (wisata, kuliner, dll), tanggal mulai, titik keberangkatan, dan
                        durasi.</p>
                </div>
                <div class="p-6 border rounded-xl hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold mb-3">AI susun rute & jadwal</h3>
                    <p class="text-gray-700">Sistem menghitung jarak, menempatkan waktu makan siang, serta rekomendasi hotel
                        jika perlu.</p>
                </div>
                <div class="p-6 border rounded-xl hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold mb-3">Simpan & pakai di lapangan</h3>
                    <p class="text-gray-700">Masuk untuk menyimpan itinerary, lalu akses kembali saat perjalanan dengan peta
                        interaktif.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Kontak --}}
    <section id="contact" class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-4 text-green-700">Kontak</h2>
            <p class="text-gray-700 mb-6">Ada masukan atau butuh bantuan? Hubungi kami.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 text-gray-800">
                <a href="mailto:halo@itinerarysumut.com"
                    class="px-5 py-3 border border-gray-200 rounded-lg hover:border-green-500 hover:text-green-600 transition">
                    📧 halo@itinerarysumut.com
                </a>
                <a href="https://wa.me/6281234567890" target="_blank" rel="noopener"
                    class="px-5 py-3 border border-gray-200 rounded-lg hover:border-green-500 hover:text-green-600 transition">
                    💬 WhatsApp Admin
                </a>
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
