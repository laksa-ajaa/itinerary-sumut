@extends('layouts.landing.app')

@section('title', 'Itinerary Sumut – Jelajahi Sumatera Utara')

@section('content')

    {{-- Hero Section --}}
    <section id="home" class="relative bg-cover bg-center h-[90vh]"
        style="background-image: url('{{ asset('assets/img/hero.png') }}')">
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/60"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-white px-6 max-w-4xl">
                <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">
                    Temukan Keindahan Sumatera Utara
                </h1>
                <p class="text-base md:text-xl mb-8 drop-shadow-md max-w-2xl mx-auto">
                    Bangun itinerary impianmu dengan rekomendasi AI cerdas dan peta interaktif.
                </p>

                {{-- Tombol lebih sederhana --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('itinerary.preferences') }}"
                        class="bg-green-600 hover:bg-green-700 px-8 py-4 rounded-lg text-white font-semibold transition shadow-lg hover:shadow-xl w-full sm:w-auto text-center">
                        Mulai Buat Itinerary
                    </a>
                    @guest
                        <a href="{{ route('register') }}"
                            class="bg-white text-green-700 hover:bg-gray-100 px-8 py-4 rounded-lg font-semibold transition shadow-lg hover:shadow-xl w-full sm:w-auto text-center">
                            Daftar Gratis
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}"
                            class="bg-white/90 text-green-700 hover:bg-white px-8 py-4 rounded-lg font-semibold transition shadow-lg hover:shadow-xl w-full sm:w-auto text-center">
                            Ke Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-green-700 text-center">
                Kenapa Itinerary Sumut?
            </h2>
            <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">
                Platform perencanaan perjalanan terlengkap untuk mengeksplor Sumatera Utara
            </p>

            <div class="grid md:grid-cols-3 gap-8">
                <div
                    class="p-8 border border-gray-200 rounded-2xl hover:shadow-xl hover:border-green-500 transition-all duration-300 group">
                    <div
                        class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 group-hover:bg-green-600 transition-colors">
                        <svg class="w-8 h-8 text-green-600 group-hover:text-white" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">Rekomendasi AI</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Temukan destinasi terbaik berdasarkan minat, waktu, dan preferensimu secara otomatis.
                    </p>
                </div>

                <div
                    class="p-8 border border-gray-200 rounded-2xl hover:shadow-xl hover:border-green-500 transition-all duration-300 group">
                    <div
                        class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 group-hover:bg-green-600 transition-colors">
                        <svg class="w-8 h-8 text-green-600 group-hover:text-white" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">Peta Interaktif</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Jelajahi wisata langsung melalui peta interaktif Sumatera Utara.
                    </p>
                </div>

                <div
                    class="p-8 border border-gray-200 rounded-2xl hover:shadow-xl hover:border-green-500 transition-all duration-300 group">
                    <div
                        class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 group-hover:bg-green-600 transition-colors">
                        <svg class="w-8 h-8 text-green-600 group-hover:text-white" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">Jadwal Otomatis</h3>
                    <p class="text-gray-600 leading-relaxed">
                        AI membantu mengatur waktu perjalananmu agar efisien dan menyenangkan setiap harinya.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Tentang --}}
    <section id="about" class="py-20 bg-gradient-to-br from-gray-50 to-green-50">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="text-green-600 font-semibold text-sm uppercase tracking-wide">Tentang Kami</span>
                    <h2 class="text-3xl md:text-4xl font-bold mt-2 mb-6 text-gray-800">
                        Itinerary Sumut
                    </h2>
                    <p class="text-gray-700 leading-relaxed mb-6 text-lg">
                        Platform perencanaan perjalanan untuk mengeksplor Sumatera Utara dengan teknologi AI.
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        Kami memanfaatkan rekomendasi AI, peta interaktif, dan jadwal otomatis supaya kamu bisa
                        merancang perjalanan yang pas dengan waktu, budget, dan preferensi kamu.
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
                    <h3 class="text-xl font-bold mb-6 text-gray-800">Keunggulan Platform</h3>
                    <ul class="space-y-5">
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Fokus Sumatera Utara</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    Kurasi khusus destinasi wisata di Sumut.
                                </p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Ramah Waktu & Budget</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    Jadwal otomatis menyesuaikan jam mulai, durasi, serta tingkat aktivitas.
                                </p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Simpan & Akses Kembali</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    Login untuk menyimpan itinerary dan melanjutkan kapan saja.
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="cta" class="relative py-24 bg-gradient-to-br from-green-600 to-green-800 text-white overflow-hidden">
        {{-- Decorative elements --}}
        <div class="absolute top-0 left-0 w-64 h-64 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>

        <div class="relative max-w-4xl mx-auto text-center px-6">
            <h2 class="text-3xl md:text-5xl font-bold mb-6">
                Siap Menjelajahi Sumut?
            </h2>
            <p class="mb-10 text-lg md:text-xl text-green-50 max-w-2xl mx-auto">
                Buat itinerary pertamamu sekarang dan temukan destinasi tersembunyi favoritmu!
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ route('itinerary.preferences') }}"
                    class="bg-white text-green-700 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition shadow-xl hover:shadow-2xl hover:scale-105 transform duration-200 w-full sm:w-auto text-center">
                    Buat Itinerary Sekarang
                </a>
                <a href="#features"
                    class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-green-700 transition shadow-xl w-full sm:w-auto text-center">
                    Pelajari Lebih Lanjut
                </a>
            </div>
        </div>
    </section>

    {{-- Kontak --}}
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-800">Hubungi Kami</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Ada masukan atau butuh bantuan? Tim kami siap membantu kamu.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 gap-6 max-w-3xl mx-auto">
                <a href="mailto:halo@itinerarysumut.com"
                    class="group p-6 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-600 transition-colors">
                            <svg class="w-6 h-6 text-green-600 group-hover:text-white" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800 group-hover:text-green-600">Email</p>
                            <p class="text-sm text-gray-600">halo@itinerarysumut.com</p>
                        </div>
                    </div>
                </a>

                <a href="https://wa.me/6281234567890" target="_blank" rel="noopener"
                    class="group p-6 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-600 transition-colors">
                            <svg class="w-6 h-6 text-green-600 group-hover:text-white" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z">
                                </path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800 group-hover:text-green-600">WhatsApp</p>
                            <p class="text-sm text-gray-600">Chat dengan Admin</p>
                        </div>
                    </div>
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
