@extends('layouts.auth')

@section('title', 'Sign In — '.get_system_setting('app_name', config('app.name')))
@section('heading', 'Welcome back')
@section('subheading', 'Sign in to your workspace to continue')

@section('panel')
    <div class="space-y-7">
        <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center">
            <svg class="w-7 h-7 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div>
            <h1 class="text-3xl xl:text-4xl font-800 text-white leading-snug tracking-tight mb-3">
                Your business,<br><span class="text-brand-300">your platform.</span>
            </h1>
            <p class="text-brand-100/60 text-sm font-300 leading-relaxed">
                Access invoices, inventory, POS, attendance and more — all in one place.
            </p>
        </div>

        <div class="space-y-3 pt-1">
            @foreach ([
                ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text' => 'Secure role-based access'],
                ['icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'text' => 'Multi-store management'],
                ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'text' => 'GST-compliant invoicing'],
            ] as $f)
                <div class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-full bg-brand-500/25 border border-brand-400/25 flex items-center justify-center shrink-0">
                        <svg class="w-2.5 h-2.5 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-brand-100/55 font-400">{{ $f['text'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('login.store') }}" novalidate class="space-y-5"
        x-data="{ showPwd: false }">
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
                <input id="email" type="text" name="email" value="{{ old('email') }}"
                    placeholder="Email or Employee Code" required autofocus autocomplete="username"
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

        {{-- Password --}}
        <div class="space-y-1.5">
            <label for="password" class="block text-xs font-600 text-gray-600 uppercase tracking-wider">
                Password
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </span>
                <input id="password" :type="showPwd ? 'text' : 'password'" name="password"
                    placeholder="••••••••" required autocomplete="current-password"
                    class="w-full rounded-xl text-sm pl-10 pr-10 py-3 border outline-none transition-all
                    {{ $errors->has('password') ? 'border-red-400 bg-red-50 text-red-700' : 'border-gray-300 bg-white text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10' }}">
                <button type="button" @click="showPwd = !showPwd"
                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path x-show="!showPwd" stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        <path x-show="showPwd" stroke-linecap="round" stroke-linejoin="round"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="text-xs text-red-600 flex items-center gap-1.5 mt-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Remember + Forgot --}}
        <div class="flex items-center justify-between text-sm pt-0.5">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="remember"
                    class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500/20 cursor-pointer">
                <span class="text-gray-600 text-xs font-500">Remember me</span>
            </label>
            <a href="{{ route('password.request') }}"
                class="text-xs font-600 text-brand-600 hover:text-brand-700 hover:underline transition-colors">
                Forgot password?
            </a>
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="w-full bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-600 text-sm py-3 rounded-xl transition-colors shadow-sm mt-1">
            Sign In
        </button>
    </form>
@endsection
