<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login | Itinerary Sumut')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-50 font-sans text-gray-900">

    <div class="flex min-h-screen">
        {{-- Bagian kiri (gambar atau ilustrasi) --}}
        <div class="hidden md:flex w-1/2 bg-cover bg-center relative"
            style="background-image: url('{{ asset('images/login-bg.jpg') }}')">
            <div class="absolute inset-0 bg-green-900 bg-opacity-50 flex items-center justify-center">
                <div class="text-center text-white p-8">
                    <h1 class="text-4xl font-bold mb-4">Selamat Datang Kembali!</h1>
                    <p class="text-lg">Masuk dan lanjutkan petualanganmu menjelajahi keindahan Sumatera Utara 🌄</p>
                </div>
            </div>
        </div>

        {{-- Bagian kanan (form login) --}}
        <div class="flex w-full md:w-1/2 items-center justify-center px-8 py-12">
            <div class="w-full max-w-md space-y-6">
                @yield('content')
            </div>
        </div>
    </div>

    @vite('resources/js/app.js')
</body>

</html>
