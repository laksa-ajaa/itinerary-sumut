<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Itinerary Sumut')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-50 text-gray-900">

    {{-- Navbar --}}
    @include('partials.navbar')

    {{-- Main Content --}}
    <main class="min-h-screen">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('partials.landing.footer')


    @if (session('success'))
        <div class="fixed bottom-5 right-5 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @vite('resources/js/app.js')
</body>

</html>
