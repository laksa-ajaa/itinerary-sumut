@extends('layouts.auth')

@section('title', 'Login | Itinerary Sumut')

@section('content')
    <div class="text-center mb-10">
        <a href="/" class="text-3xl font-bold text-green-700">
            Itinerary<span class="text-emerald-500">Sumut</span>
        </a>
        <p class="text-gray-600 mt-2">Masuk ke akunmu untuk mulai menjelajah</p>
    </div>

    <form method="POST" action="{{ route('login.process') }}" class="space-y-6">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full px-4 py-3 border {{ $errors->has('email') ? 'border-red-300' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none {{ $errors->has('email') ? 'focus:border-red-500' : '' }}">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
            <input id="password" type="password" name="password" required
                class="w-full px-4 py-3 border {{ $errors->has('password') ? 'border-red-300' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-green-600 focus:outline-none {{ $errors->has('password') ? 'focus:border-red-500' : '' }}">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tombol Login --}}
        <button type="submit"
            class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
            Masuk Sekarang
        </button>

        {{-- Divider --}}
        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="px-3 text-gray-400 text-sm">atau</span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        {{-- Tombol Login Google
        <a href=""
            class="w-full border border-gray-300 py-3 rounded-lg flex items-center justify-center gap-3 hover:bg-gray-100 transition">
            <img src="{{ asset('images/google-icon.svg') }}" alt="Google" class="w-5 h-5">
            <span class="font-medium text-gray-700">Masuk dengan Google</span>
        </a> --}}

        {{-- Register Link --}}
        <p class="text-center text-gray-600 text-sm mt-8">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-green-600 font-semibold hover:underline">Daftar Sekarang</a>
        </p>
    </form>
@endsection
