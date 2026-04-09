<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Subscription</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            green: '#38cb89',
                            hover: '#2eb377'
                        }
                    },
                    boxShadow: {
                        'soft': '0 10px 40px rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #fafafa;
        }
        /* Exact ribbon cutout shape */
        .ribbon-shape {
            clip-path: polygon(100% 0, 100% 100%, 50% 80%, 0 100%, 0 0);
        }
        /* Standard small bullet points */
        ul.custom-bullets li::before {
            content: "•";
            color: #4b5563;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-8 text-gray-800 antialiased">

    {{-- ── 1. CURRENT SUBSCRIPTION BANNER ── --}}
    @if(isset($currentSubscription) && $currentSubscription)
        @php
            // Force integer casting to remove any decimal days
            $daysLeft = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($currentSubscription->expires_at)->startOfDay());
            $isExpiringSoon = $daysLeft <= 7;
        @endphp
        
        <div class="w-full max-w-5xl mb-12 bg-white rounded shadow-sm border {{ $isExpiringSoon ? 'border-amber-200' : 'border-gray-200' }} p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Current Active Plan</p>
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $currentSubscription->plan->name ?? 'Unknown Plan' }}
                </h2>
            </div>
            
            <div class="text-center sm:text-right">
                <p class="text-xs text-gray-500 font-medium mb-1">Valid until {{ \Carbon\Carbon::parse($currentSubscription->expires_at)->format('d M Y') }}</p>
                <div class="inline-block px-3 py-1 rounded {{ $isExpiringSoon ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-600' }} text-[11px] font-bold uppercase tracking-wider">
                    {{ $daysLeft }} days remaining
                </div>
            </div>
        </div>
    @endif

    {{-- ── 2. PAGE HEADER ── --}}
    <div class="text-center mb-12">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 tracking-tight">Choose your subscription plan</h1>
    </div>

    {{-- ── 3. PRICING GRID ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 items-stretch justify-center w-full max-w-5xl mx-auto">
        
        @forelse($availablePlans ?? [] as $plan)
            @php
                $isCurrentPlan = isset($currentSubscription) && $currentSubscription->plan_id === $plan->id;
            @endphp

            {{-- Pricing Card --}}
            <div class="relative bg-white rounded shadow-soft flex flex-col pt-10 pb-8 px-8 {{ $isCurrentPlan ? 'ring-2 ring-gray-200' : '' }}">
                
                {{-- Recommended Ribbon --}}
                @if($plan->is_recommended && !$isCurrentPlan)
                    <div class="absolute top-0 right-6 w-10 h-14 ribbon-shape flex justify-center pt-2.5 bg-brand-green">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </div>
                @endif

                {{-- Card Header --}}
                <div class="text-center mb-8">
                    <h3 class="text-xl text-gray-700 font-medium">{{ $plan->name }}</h3>
                    
                    {{-- Green Divider Line --}}
                    <div class="w-10 h-[2px] mx-auto mt-3 mb-6 bg-brand-green"></div>

                    {{-- Price --}}
                    @if($plan->price <= 0)
                        <h2 class="text-4xl font-black text-gray-900 tracking-tight my-2">FREE</h2>
                    @else
                        <div class="flex items-baseline justify-center gap-1 my-2">
                            <span class="text-2xl font-bold text-gray-800">₹</span>
                            <h2 class="text-4xl font-black text-gray-900 tracking-tight">{{ number_format($plan->price, 0) }}</h2>
                            @if($plan->billing_cycle !== 'lifetime')
                                <span class="text-lg text-gray-500 font-medium">/{{ $plan->billing_cycle === 'yearly' ? 'yr' : 'mo' }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Features List --}}
                <div class="flex-1 flex justify-center mb-10">
                    <ul class="custom-bullets space-y-3 text-[14px] text-gray-600 font-medium text-left pl-4">
                        <li>Up to {{ $plan->user_limit }} users</li>
                        <li>Up to {{ $plan->store_limit }} stores</li>
                        <li>Up to {{ $plan->product_limit }} products</li>
                        <li>Up to {{ $plan->employee_limit }} employees</li>
                        
                        @forelse($plan->modules ?? [] as $module)
                            <li>{{ $module->name }}</li>
                        @empty
                            <li class="text-gray-400">Basic features only</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Action Button --}}
                <div class="mt-auto">
                    @if($isCurrentPlan)
                        <button disabled class="w-full py-3.5 text-[13px] font-bold text-gray-400 bg-gray-100 uppercase tracking-widest cursor-not-allowed">
                            Current Plan
                        </button>
                    @else
                        @php
                            $defaultMailto = "mailto:sales@yourcompany.com?subject=" . urlencode("Upgrade Request: {$plan->name} Plan");
                            $link = $plan->button_link ?: $defaultMailto;
                        @endphp
                        
                        <a href="{{ $link }}" class="w-full py-3.5 text-[13px] font-bold text-white uppercase tracking-widest flex items-center justify-center transition-colors bg-brand-green hover:bg-brand-hover">
                            {{ $plan->button_text ?: 'GET STARTED' }}
                        </a>
                    @endif
                </div>

            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <p class="text-gray-500 font-medium">No plans configured yet.</p>
            </div>
        @endforelse

    </div>

</body>
</html>