@extends('layouts.auth')

@section('title', 'Register')

@section('content')

    <h2 class="text-xl font-semibold mb-6 text-gray-700 text-center">
        Create an account
    </h2>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4" enctype="multipart/form-data">
        @csrf

        {{-- Company Name --}}
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Company Name
            </label>

            <input type="text" name="company_name" value="{{ old('company_name') }}" required
                class="w-full border rounded px-3 py-2">

            @error('company_name')
                <p class="text-red-500 text-xs">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Full Name
            </label>

            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full border rounded px-3 py-2">

            @error('name')
                <p class="text-red-500 text-xs">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Email
            </label>

            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full border rounded px-3 py-2">

            @error('email')
                <p class="text-red-500 text-xs">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Password
            </label>

            <input type="password" name="password" required class="w-full border rounded px-3 py-2">

            @error('password')
                <p class="text-red-500 text-xs">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Confirm Password
            </label>

            <input type="password" name="password_confirmation" required class="w-full border rounded px-3 py-2">
        </div>

        {{-- Button --}}
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
            Register
        </button>

    </form>

    <div class="mt-6 text-center text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">
            Login
        </a>
    </div>

@endsection
