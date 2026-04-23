<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @if (get_system_setting('app_favicon'))
        <link rel="icon" href="{{ asset('storage/'.get_system_setting('app_favicon')) }}">
    @endif
    <script src="{{ asset('assets/js/tailwind.min.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#f0fdfa',
                            100: '#ccfbf1',
                            400: '#2dd4bf',
                            500: '#0f766e',
                            600: '#115e59',
                            700: '#134e4a',
                            800: '#0d3b37',
                            900: '#082b28',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="{{ asset('assets/js/alpinejs.min.js') }}"></script>
    <style>
        body { font-family: Poppins, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 antialiased">

<div class="min-h-screen flex flex-col items-center justify-center p-6 sm:p-10 bg-gray-50">

    {{-- Static Qlinkon Logo --}}
    <div class="mb-8">
        {{-- Make sure to place your logo in public/assets/images/logo.png or adjust the path --}}
        <img src="{{ asset('assets/images/logo.png') }}" alt="Qlinkon" class="h-10 w-auto object-contain">
    </div>

    {{-- Centered Form Card --}}
    <div class="w-full max-w-[420px] bg-white p-8 sm:p-10 rounded-2xl shadow-sm border border-gray-100">

        {{-- Page heading --}}
        @hasSection('heading')
            <div class="mb-7 text-center">
                <h2 class="text-2xl font-700 text-gray-900 tracking-tight">@yield('heading')</h2>
                @hasSection('subheading')
                    <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">@yield('subheading')</p>
                @endif
            </div>
        @endif

        {{-- Flash messages --}}
        @if (session('status'))
            <div class="mb-5 flex items-start gap-3 bg-brand-50 border border-brand-200 text-brand-800 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif
        @if (session('success'))
            <div class="mb-5 flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 shrink-0 mt-0.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 shrink-0 mt-0.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Form content --}}
        @yield('content')

    </div>
</div>

@yield('scripts')

</body>
</html>
