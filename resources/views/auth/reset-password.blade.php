@extends('layouts.auth')

@section('title', 'New Password — ' . config('app.name'))
@section('heading', 'Set new password')
@section('subheading', "Choose a strong password you haven't used before")

@section('content')

    <form method="POST" action="{{ route('password.update') }}" novalidate class="space-y-5" x-data="{
        password: '',
        confirm: '',
        show1: false,
        show2: false,
        get strength() {
            if (!this.password.length) return 0;
            let s = 0;
            if (this.password.length >= 8) s++;
            if (/[A-Z]/.test(this.password)) s++;
            if (/[0-9]/.test(this.password)) s++;
            if (/[^A-Za-z0-9]/.test(this.password)) s++;
            return s;
        },
        get strengthLabel() { return ['', 'Weak', 'Fair', 'Good', 'Strong'][this.strength]; },
        get strengthColor() { return ['', '#ef4444', '#f59e0b', '#3b82f6', '#22c55e'][this.strength]; },
    }">
        @csrf

        {{-- Hidden token + email --}}
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        {{-- ── Email display (read-only) ── --}}
        <div class="flex items-center gap-3 px-4 py-3 rounded-[var(--radius-input)] border"
            style="border-color:var(--border-subtle); background:var(--bg-input)">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                style="color:var(--text-muted)">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span class="text-sm font-400 flex-1 truncate" style="color:var(--text-secondary)">{{ $email }}</span>
            <span class="text-xs font-500 px-2 py-0.5 rounded-full bg-brand-500/15 text-brand-400">Verified</span>
        </div>

        {{-- ── New Password ── --}}
        <div class="space-y-1.5">
            <label for="password" class="block text-xs font-600 uppercase tracking-widest"
                style="color:var(--text-secondary)">
                New Password <span style="color:var(--accent)">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none"
                    style="color:var(--text-muted)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                <input id="password" :type="show1 ? 'text' : 'password'" name="password" x-model="password"
                    placeholder="Min. 8 characters" required autofocus
                    class="w-full rounded-[var(--radius-input)] text-sm pl-10 pr-10 py-3 border transition-all duration-200
                    {{ $errors->has('password')
                        ? 'border-red-500/60 bg-red-500/5 text-red-300 placeholder-red-400/40'
                        : 'border-[var(--border-subtle)] bg-[var(--bg-input)] text-[var(--text-primary)] placeholder-[var(--text-muted)]' }}" />
                <button type="button" @click="show1 = !show1"
                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 transition-colors"
                    style="color:var(--text-muted)" :style="show1 ? 'color:var(--accent)' : ''">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path x-show="!show1" stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <path x-show="show1" stroke-linecap="round" stroke-linejoin="round"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>

            {{-- Strength meter --}}
            <div x-show="password.length > 0" style="display:none" class="space-y-1 pt-0.5">
                <div class="flex gap-1">
                    <template x-for="i in 4" :key="i">
                        <div class="h-1 flex-1 rounded-full transition-all duration-300"
                            :style="'background:' + (i <= strength ? strengthColor : 'var(--border-subtle)')">
                        </div>
                    </template>
                </div>
                <p class="text-xs font-500" :style="'color:' + strengthColor" x-text="strengthLabel"></p>
            </div>

            @error('password')
                <p class="text-xs font-500 mt-1 flex items-center gap-1.5" style="color:var(--danger)">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- ── Confirm Password ── --}}
        <div class="space-y-1.5">
            <label for="password_confirmation" class="block text-xs font-600 uppercase tracking-widest"
                style="color:var(--text-secondary)">
                Confirm Password <span style="color:var(--accent)">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none"
                    style="color:var(--text-muted)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
                <input id="password_confirmation" :type="show2 ? 'text' : 'password'" name="password_confirmation"
                    x-model="confirm" placeholder="Repeat password" required
                    class="w-full rounded-[var(--radius-input)] text-sm pl-10 pr-10 py-3 border transition-all duration-200
                       border-[var(--border-subtle)] bg-[var(--bg-input)] text-[var(--text-primary)] placeholder-[var(--text-muted)]"
                    :class="confirm.length > 0 && password !== confirm ?
                        '!border-red-500/60' :
                        (confirm.length > 0 && password === confirm ? '!border-green-500/60' : '')" />
                <button type="button" @click="show2 = !show2"
                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 transition-colors"
                    style="color:var(--text-muted)" :style="show2 ? 'color:var(--accent)' : ''">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path x-show="!show2" stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <path x-show="show2" stroke-linecap="round" stroke-linejoin="round"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>

            {{-- Match / mismatch hint --}}
            <p x-show="confirm.length > 3 && password !== confirm" style="display:none"
                class="text-xs font-500 mt-1 flex items-center gap-1.5" style="color:var(--danger)">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span style="color:var(--danger)">Passwords do not match</span>
            </p>
            <p x-show="confirm.length > 3 && password === confirm" style="display:none"
                class="text-xs font-500 mt-1 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2.5" style="color:var(--success)">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span style="color:var(--success)">Passwords match</span>
            </p>
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
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Reset Password
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
