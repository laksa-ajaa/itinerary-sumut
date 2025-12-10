<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Itinerary Sumut')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body class="bg-slate-50 text-gray-900 min-h-screen">
    <div class="flex">
        <aside id="adminSidebar" class="w-72 bg-white border-r border-slate-200 min-h-screen hidden lg:block">
            <div class="px-6 py-5 border-b border-slate-100">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-green-700">
                    Itinerary<span class="text-emerald-500">Sumut</span>
                </a>
                <p class="text-sm text-gray-500 mt-1">Panel Admin</p>
            </div>
            <nav class="px-4 py-6 space-y-1">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-slate-100' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.analytics') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.analytics') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-slate-100' }}">
                    <span>Analitik</span>
                </a>
                <a href="{{ route('admin.wisata.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.wisata.*') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-slate-100' }}">
                    <span>Wisata (GeoJSON)</span>
                </a>
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-slate-100' }}">
                    <span>User Management</span>
                </a>
            </nav>
        </aside>

        <div class="flex-1 min-h-screen">
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg border border-slate-200">
                        ☰
                    </button>
                    <div>
                        <p class="text-sm text-gray-500">Panel Admin</p>
                        <h1 class="text-xl font-semibold">@yield('title', 'Dashboard')</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="font-semibold">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="px-3 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <main class="p-6">
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @vite('resources/js/app.js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('adminSidebar');
            if (!toggle || !sidebar) return;

            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('hidden');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>

