<nav class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="container mx-auto flex items-center justify-between px-6 py-3">
        <a href="{{ route('home') }}" class="text-2xl font-bold text-green-700">
            Itinerary<span class="text-emerald-500">Sumut</span>
        </a>

        <div class="flex items-center gap-6 text-gray-700 font-medium">
            <a href="{{ route('home') }}" class="hover:text-green-600">Beranda</a>
            <a href="{{ route('itinerary.preferences') }}" class="hover:text-green-600">Buat Itinerary</a>
        </div>

        <div class="flex items-center gap-3 relative">
            @guest
                <a href="{{ route('login') }}"
                    class="hidden sm:block border border-green-600 text-green-600 px-4 py-2 rounded-lg hover:bg-green-50 transition">
                    Masuk
                </a>
                <a href="{{ route('register') }}"
                    class="hidden sm:block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    Daftar
                </a>
            @else
                <button id="userMenuButton" type="button"
                    class="hidden sm:flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                    <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-semibold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span class="text-gray-800 font-medium truncate max-w-[140px]">{{ Auth::user()->name }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div id="userMenu"
                    class="hidden absolute right-0 top-12 w-56 bg-white border border-gray-200 rounded-lg shadow-lg py-2 z-50">
                    @if(Auth::user()->is_admin ?? false)
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50">
                        <span>Admin Dashboard</span>
                    </a>
                    @endif
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50">
                        <span>Profil</span>
                    </a>
                    <a href="{{ route('itinerary.index') }}"
                        class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50">
                        <span>Daftar Itinerary Saya</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full text-left flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50">
                            Logout
                        </button>
                    </form>
                </div>
            @endguest
        </div>
    </div>
</nav>
<div class="h-16"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('userMenuButton');
        const menu = document.getElementById('userMenu');
        if (!btn || !menu) return;

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    });
</script>

