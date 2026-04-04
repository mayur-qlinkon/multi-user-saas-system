<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin - Qlinkon')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">    
     <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif']
                    },
                    colors: {
                        brand: {
                            500: '#0f766e',
                            600: '#115e59',
                            700: '#134e4a'
                        }
                    }
                }

            }

        }
    </script>    
    <script src="{{ asset('assets/js/lucide.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>    
    <script defer src="{{ asset('assets/js/alpinejs.min.js') }}"></script>  

    <style>
        :root {
        --brand-50: #f0fdfa;
        --brand-100: #ccfbf1;
        --brand-500: #0f766e;
        --brand-600: #115e59;
        --brand-700: #134e4a;
    }
        [x-cloak] {

            display: none !important
        }

        body {

            font-family: Poppins, sans-serif
        }
    </style>

    @yield('styles')

</head>

<body class="bg-gray-100 text-gray-800">

    <div x-data="{ sidebar: false }" class="flex h-screen overflow-hidden">
        {{-- Mobile overlay --}}
        <div x-show="sidebar" x-cloak @click="sidebar=false" class="fixed inset-0 bg-black/40 z-40 lg:hidden"></div>
        {{-- Sidebar --}}

        <aside
            class="fixed lg:static inset-y-0 left-0 w-64 bg-white border-r border-gray-200 z-50 transform lg:translate-x-0 transition-transform"
            :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="h-16 flex items-center px-6 border-b">

                <div class="flex items-center gap-2">

                    <div class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                    </div>

                    <div>
                        <p class="font-bold text-gray-800">Qlinkon</p>
                        <p class="text-xs text-gray-400 font-medium">SUPER ADMIN</p>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-1 text-sm font-medium">
                <a href="{{ route('platform.dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('platform.dashboard') ? 'bg-gray-100 text-brand-600' : 'hover:bg-gray-100' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    Dashboard
                </a>



                <a href="{{ route('platform.companies.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('platform.companies.*') ? 'bg-gray-100 text-brand-600' : 'hover:bg-gray-100' }}">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    Companies
                </a>

                <a href="{{ route('platform.modules.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg
             {{ request()->routeIs('platform.modules.*') ? 'bg-gray-100 text-brand-600' : 'hover:bg-gray-100' }}">

                    <i data-lucide="boxes" class="w-4 h-4"></i>
                    Modules
                </a>


                <a href="{{ route('platform.plans.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg
   {{ request()->routeIs('platform.plans.*') ? 'bg-gray-100 text-brand-600' : 'hover:bg-gray-100' }}">

                    <i data-lucide="layers" class="w-4 h-4"></i>
                    Plans
                </a>
                <a href="{{ route('platform.subscriptions.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg
   {{ request()->routeIs('platform.subscriptions.*') ? 'bg-gray-100 text-brand-600' : 'hover:bg-gray-100' }}">

                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    Subscriptions
                </a>
                <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg
   {{ request()->routeIs('platform.system.*') ? 'bg-gray-100 text-brand-600' : 'hover:bg-gray-100' }}">

                    <i data-lucide="settings" class="w-4 h-4"></i>
                    System Settings
                </a>
                <a href="{{ url('/platform/seeders') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="flower-2" class="w-4 h-4"></i>
                    Seeders
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    Activity Logs
                </a>
            </nav>

        </aside>
        {{-- Main --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Topbar --}}
            <header class="bg-white border-b h-16 flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <button @click="sidebar=!sidebar" class="lg:hidden text-gray-600">

                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <h1 class="font-semibold text-gray-700">
                        @yield('header', 'Dashboard')
                    </h1>
                </div>

                {{-- Profile --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open=!open" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center">

                            <i data-lucide="user" class="w-4 h-4"></i>

                        </div>
                    </button>

                    <div x-show="open" @click.outside="open=false" x-cloak
                        class="absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow">
                        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">
                            Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

            </header>

            {{-- Content --}}
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>

        </div>

    </div>

    <script>
        lucide.createIcons()
    </script>

    @yield('scripts')

</body>

</html>
