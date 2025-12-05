<nav class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="container mx-auto flex items-center justify-between px-6 py-3">
        <a href="/" class="text-2xl font-bold text-green-700">Itinerary<span
                class="text-emerald-500">Sumut</span></a>
        <ul class="hidden md:flex gap-6 font-medium text-gray-700">
            <li><a href="#home" class="hover:text-green-600">Beranda</a></li>
            <li><a href="#features" class="hover:text-green-600">Fitur</a></li>
            <li><a href="#destinations" class="hover:text-green-600">Destinasi</a></li>
            <li><a href="{{ route('itinerary.preferences') }}" class="hover:text-green-600">Buat Itinerary</a></li>
        </ul>
        <div class="flex items-center gap-3">
            <a href="{{ route('itinerary.preferences') }}"
                class="hidden md:block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                Buat Itinerary
            </a>
            @guest
                <a href="{{ route('login') }}"
                    class="hidden md:block border border-green-600 text-green-600 px-4 py-2 rounded-lg hover:bg-green-50 transition">
                    Masuk
                </a>
            @else
                <a href="{{ route('dashboard') }}"
                    class="hidden md:block border border-green-600 text-green-600 px-4 py-2 rounded-lg hover:bg-green-50 transition">
                    Dashboard
                </a>
            @endguest
        </div>
    </div>
</nav>
<div class="h-16"></div> {{-- spacer agar konten tidak tertutup navbar --}}
