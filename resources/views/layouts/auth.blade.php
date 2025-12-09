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

    {{-- Navbar --}}
    @include('partials.navbar')

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
                {{-- Success Message --}}
                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                {{-- Error Message --}}
                @if (session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    @vite('resources/js/app.js')
</body>

</html>
