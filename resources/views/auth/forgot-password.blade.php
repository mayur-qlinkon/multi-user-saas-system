@extends('layouts.auth')

@section('title', 'Forgot Password — '.get_system_setting('app_name', config('app.name')))
@section('heading', 'Reset your password')
@section('subheading', "Enter your registered email and we'll send you a one-time OTP")

@section('panel')
    <div class="space-y-7">
        <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center">
            <svg class="w-7 h-7 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>

        <div>
            <h1 class="text-3xl xl:text-4xl font-800 text-white leading-snug tracking-tight mb-3">
                Forgot your<br><span class="text-brand-300">password?</span>
            </h1>
            <p class="text-brand-100/60 text-sm font-300 leading-relaxed">
                No worries. We'll send a secure OTP to your inbox — no links, no confusion.
            </p>
        </div>

        <div class="space-y-3.5">
            @foreach ([
                ['n' => '1', 'text' => 'Enter your registered email address'],
                ['n' => '2', 'text' => 'Check your inbox for the OTP code'],
                ['n' => '3', 'text' => 'Enter the OTP and set a new password'],
            ] as $step)
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-brand-500/25 border border-brand-400/25 flex items-center justify-center shrink-0">
                        <span class="text-xs font-700 text-brand-300">{{ $step['n'] }}</span>
                    </div>
                    <span class="text-sm text-brand-100/55 font-400">{{ $step['text'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('password.email') }}" novalidate class="space-y-5">
        @csrf

        {{-- Email --}}
        <div class="space-y-1.5">
            <label for="email" class="block text-xs font-600 text-gray-600 uppercase tracking-wider">
                Email Address
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    placeholder="you@company.com" required autofocus autocomplete="email"
                    class="w-full rounded-xl text-sm pl-10 pr-4 py-3 border outline-none transition-all
                    {{ $errors->has('email') ? 'border-red-400 bg-red-50 text-red-700' : 'border-gray-300 bg-white text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10' }}">
            </div>
            @error('email')
                <p class="text-xs text-red-600 flex items-center gap-1.5 mt-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="w-full bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-600 text-sm py-3 rounded-xl transition-colors shadow-sm">
            Send OTP
        </button>

        {{-- Back to login --}}
        <div class="text-center pt-1">
            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-600 font-500 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Sign In
            </a>
        </div>
    </form>
@endsection
