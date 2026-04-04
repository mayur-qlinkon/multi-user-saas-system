@extends('layouts.auth')

@section('title', 'Forgot Password — ' . config('app.name'))
@section('heading', 'Reset your password')
@section('subheading', "Enter your email and we'll send you a reset link")

{{-- ── Custom left panel ── --}}
@section('panel')
    <div class="space-y-6">
        <div
            class="w-16 h-16 rounded-2xl bg-brand-500/15 border border-brand-500/25 flex items-center justify-center shadow-glow">
            <svg class="w-8 h-8 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
        </div>

        <h1 class="text-4xl xl:text-5xl font-800 text-white leading-tight tracking-tight">
            Forgot your<br />
            <span class="text-brand-400">password?</span>
        </h1>

        <p class="text-brand-200/70 text-lg font-300 leading-relaxed max-w-sm">
            No worries. Enter your email and we'll send a secure reset link to your inbox.
        </p>

        <div class="space-y-3 pt-2">
            <div class="flex items-center gap-3">
                <div
                    class="w-6 h-6 rounded-full bg-brand-500/20 border border-brand-500/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-600 text-brand-400">1</span>
                </div>
                <span class="text-sm font-400 text-brand-200/70">Enter your registered email</span>
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="w-6 h-6 rounded-full bg-brand-500/20 border border-brand-500/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-600 text-brand-400">2</span>
                </div>
                <span class="text-sm font-400 text-brand-200/70">Check your inbox for the link</span>
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="w-6 h-6 rounded-full bg-brand-500/20 border border-brand-500/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-600 text-brand-400">3</span>
                </div>
                <span class="text-sm font-400 text-brand-200/70">Create a new strong password</span>
            </div>
        </div>
    </div>
@endsection

{{-- ── Form ── --}}
@section('content')

    <form method="POST" action="{{ route('password.email') }}" novalidate class="space-y-5">
        @csrf

        {{-- ── Email ── --}}
        <div class="space-y-1.5">
            <label for="email" class="block text-xs font-600 uppercase tracking-widest"
                style="color:var(--text-secondary)">
                Email Address <span style="color:var(--accent)">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none"
                    style="color:var(--text-muted)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    placeholder="you@example.com" required autofocus
                    class="w-full rounded-[var(--radius-input)] text-sm pl-10 py-3 border transition-all duration-200
                    {{ $errors->has('email')
                        ? 'border-red-500/60 bg-red-500/5 text-red-300 placeholder-red-400/40'
                        : 'border-[var(--border-subtle)] bg-[var(--bg-input)] text-[var(--text-primary)] placeholder-[var(--text-muted)]' }}" />
            </div>
            @error('email')
                <p class="text-xs font-500 flex items-center gap-1.5 mt-1" style="color:var(--danger)">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- ── Submit ── --}}
        <div class="pt-1">
            <button type="submit"
                class="relative overflow-hidden group w-full px-6 py-3 rounded-[var(--radius-input)]
                   bg-brand-500 hover:bg-brand-600 active:bg-brand-700
                   text-white text-sm font-600 tracking-wide
                   transition-all duration-200 cursor-pointer
                   focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:ring-offset-2 focus:ring-offset-[var(--bg-page)]
                   shadow-glow-sm hover:shadow-glow">
                <span
                    class="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/10 to-transparent pointer-events-none"></span>
                <span class="relative flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Send Reset Link
                </span>
            </button>
        </div>

        {{-- ── Back to login ── --}}
        <div class="text-center">
            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-1.5 text-sm font-500 hover:underline transition-colors"
                style="color:var(--text-secondary)">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Sign In
            </a>
        </div>

    </form>

@endsection
