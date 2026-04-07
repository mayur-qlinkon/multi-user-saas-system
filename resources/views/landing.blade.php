<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ get_system_setting('app_name', 'Qlinkon') }} — Smart ERP for Indian SMEs">
    <title>{{ get_system_setting('app_name', 'Qlinkon') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @if (get_system_setting('app_favicon'))
        <link rel="icon" href="{{ asset('storage/' . get_system_setting('app_favicon')) }}">
    @endif
    <script src="{{ asset('assets/js/tailwind.min.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#f0fdfa', 100: '#ccfbf1', 500: '#0f766e', 600: '#115e59', 700: '#134e4a' }
                    }
                }
            }
        }
    </script>
    <script defer src="{{ asset('assets/js/alpinejs.min.js') }}"></script>
    <style>
        body { font-family: Poppins, sans-serif; }
        [x-cloak] { display: none !important; }
        .hero-gradient {
            background: linear-gradient(135deg, #0f766e 0%, #134e4a 60%, #0f172a 100%);
        }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased">

    @php
        $actionUrl = route('login');

        if (auth()->check()) {
            $user = auth()->user();

            // Grabs the slug from the user's associated company, or falls back to 'store'
            $companySlug = $user->company->slug ?? request()->route('slug') ?? 'store';

            if ($user->hasRole('customer')) {
                $actionUrl = route('storefront.portal.dashboard', ['slug' => $companySlug]);
            } elseif ($user->hasRole('owner')) {
                $actionUrl = route('admin.dashboard');
            } elseif ($user->hasRole('employee')) {
                $actionUrl = route('admin.hrm.employee.dashboard');
            } else {
                // Fallback for Super Admin or other roles
                $actionUrl = route('admin.dashboard');
            }
        }
    @endphp


    {{-- ════════════════════════════════════════════════════
         NAVBAR
    ════════════════════════════════════════════════════ --}}
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                @if (get_system_setting('app_logo'))
                    <img src="{{ asset('storage/' . get_system_setting('app_logo')) }}"
                        alt="{{ get_system_setting('app_name', 'Qlinkon') }}"
                        class="h-8 w-auto object-contain">
                @else
                    <div class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(get_system_setting('app_name', 'Q'), 0, 1)) }}
                    </div>
                    <span class="font-bold text-gray-800 text-lg">{{ get_system_setting('app_name', 'Qlinkon') }}</span>
                @endif
            </div>
            <a href="{{ $actionUrl }}"
                class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                {{ auth()->check() ? 'Dashboard' : 'Login' }}
            </a>
        </div>
    </nav>

    {{-- ════════════════════════════════════════════════════
         HERO
    ════════════════════════════════════════════════════ --}}
    <section class="hero-gradient min-h-screen flex items-center pt-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-24 lg:py-32 text-center">
            <span class="inline-block text-xs font-bold tracking-widest text-brand-100 uppercase mb-4 bg-white/10 px-4 py-1.5 rounded-full">
                Smart ERP for Indian SMEs
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                Run Your Business<br>
                <span class="text-brand-100">Smarter, Faster.</span>
            </h1>
            <p class="text-base sm:text-lg text-teal-100/80 max-w-2xl mx-auto mb-10 leading-relaxed">
                {{ get_system_setting('app_name', 'Qlinkon') }} provides a complete ERP solution — invoicing,
                inventory, POS, attendance &amp; more. Built for Indian small businesses, it offers
                powerful multi‑store handling so you can manage all your outlets in one software.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#contact"
                    class="w-full sm:w-auto bg-white text-brand-700 hover:bg-brand-50 font-bold px-8 py-3.5 rounded-xl text-sm transition-colors shadow-md">
                    Get in Touch
                </a>
                <a href="{{ $actionUrl }}"
                    class="w-full sm:w-auto border border-white/30 text-white hover:bg-white/10 font-semibold px-8 py-3.5 rounded-xl text-sm transition-colors">
                    {{ auth()->check() ? 'Go to Dashboard →' : 'Login to Dashboard →' }}
                </a>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════
         FEATURES STRIP
    ════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-gray-50 border-y border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-800 mb-12">
                Everything your business needs
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @foreach ([
                    ['icon' => '🧾', 'label' => 'GST Invoicing'],
                    ['icon' => '📦', 'label' => 'Inventory'],
                    ['icon' => '🖥️', 'label' => 'POS System'],
                    ['icon' => '⏱️', 'label' => 'Attendance'],
                    ['icon' => '📊', 'label' => 'Reports'],
                    ['icon' => '👥', 'label' => 'Multi-Users'],
                ] as $f)
                    <div class="bg-white rounded-2xl p-5 text-center shadow-sm border border-gray-100">
                        <span class="text-3xl block mb-2">{{ $f['icon'] }}</span>
                        <p class="text-xs font-semibold text-gray-700">{{ $f['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════
         CONTACT / INQUIRY FORM
    ════════════════════════════════════════════════════ --}}
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-3">Get in Touch</h2>
                <p class="text-gray-500 text-sm">Have questions? Drop us a message and our team will get back to you.</p>
            </div>

            @if (session('success'))
                <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                    <span class="text-xl">✅</span>
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('landing.inquire') }}"
                class="bg-gray-50 rounded-2xl border border-gray-200 p-8 space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            Your Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            placeholder="Rajesh Kumar"
                            class="w-full border @error('name') border-red-400 @else border-gray-300 @enderror rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            placeholder="+91 9876543210"
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        placeholder="rajesh@mybusiness.com"
                        class="w-full border @error('email') border-red-400 @else border-gray-300 @enderror rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <textarea name="message" rows="4"
                        placeholder="Tell us about your business and what you need..."
                        class="w-full border @error('message') border-red-400 @else border-gray-300 @enderror rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white resize-none">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl text-sm transition-colors shadow-sm">
                    Send Message
                </button>
            </form>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════════════════ --}}
    <footer class="bg-gray-900 text-gray-400 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm">
            <p class="font-semibold text-white">
                {{ get_system_setting('app_name', 'Qlinkon') }}
            </p>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs">
                @if (get_system_setting('support_email'))
                    <a href="mailto:{{ get_system_setting('support_email') }}" class="hover:text-white transition-colors">
                        {{ get_system_setting('support_email') }}
                    </a>
                @endif
                @if (get_system_setting('support_phone'))                    
                     <a href="tel:{{ get_system_setting('support_phone') }}" class="hover:text-white transition-colors">
                        {{ get_system_setting('support_phone') }}
                    </a>
                @endif
                
                <a href="{{ $actionUrl }}" class="hover:text-white transition-colors">
                    {{ auth()->check() ? 'Dashboard' : 'Admin Login' }}
                </a>
            </div>
            <p class="text-xs">&copy; {{ date('Y') }} {{ get_system_setting('app_name', 'Qlinkon') }}. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
