@extends('layouts.auth')

@section('title', 'Daftar Akun | Itinerary Sumut')

@section('content')
    <div class="text-center mb-10">
        <a href="/" class="text-3xl font-bold text-green-700">
            Itinerary<span class="text-emerald-500">Sumut</span>
        </a>
        <p class="text-gray-600 mt-2">Buat akun baru dan mulai rencanakan perjalananmu</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        {{-- Nama Lengkap --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
            <input id="password" type="password" name="password" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Konfirmasi Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata
                Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none">
        </div>

        {{-- Tombol Register --}}
        <button type="submit"
            class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
            Daftar Sekarang
        </button>

        {{-- Divider --}}
        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="px-3 text-gray-400 text-sm">atau</span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        {{-- Tombol Daftar dengan Google
        <a href="{{ route('google.login') }}"
            class="w-full border border-gray-300 py-3 rounded-lg flex items-center justify-center gap-3 hover:bg-gray-100 transition">
            <img src="{{ asset('images/google-icon.svg') }}" alt="Google" class="w-5 h-5">
            <span class="font-medium text-gray-700">Daftar dengan Google</span>
        </a> --}}

        {{-- Link ke Login --}}
        <p class="text-center text-gray-600 text-sm mt-8">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-green-600 font-semibold hover:underline">Masuk Sekarang</a>
        </p>
    </form>
@endsection
