@extends('layouts.auth')

@section('title', 'Login')

@section('content')

    <h2 class="text-xl font-semibold mb-6 text-gray-700 text-center">
        Login to your account
    </h2>

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Email
            </label>

            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">

            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Password
            </label>

            <input type="password" name="password" required
                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">

            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember --}}
        <div class="flex items-center justify-between text-sm">

            <label class="flex items-center space-x-2">
                <input type="checkbox" name="remember">
                <span>Remember me</span>
            </label>

            <a href="#" class="text-blue-600 hover:underline">
                Forgot password?
            </a>

        </div>

        {{-- Button --}}
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
            Login
        </button>

    </form>

    <div class="mt-6 text-center text-sm text-gray-600">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-blue-600 hover:underline">
            Register
        </a>
    </div>

@endsection
