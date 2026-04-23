@php
    $companyEmail = "qlinkon@gmail.com";
    $companyPhone = 9925199251;


@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
  <title>QLINKON · Plans & Pricing</title>
  <!-- Tailwind (as specified) + custom fine-tuning -->
  <script src="{{ asset('assets/js/tailwind.min.js') }}"></script>
  <style>
    /* smooth custom enhancements — keeps the best of Tailwind + refined details */
    @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
    * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    .card-hover { transition: all 0.2s ease; }
    .card-hover:hover { transform: translateY(-4px); box-shadow: 0 25px 30px -12px rgba(0, 0, 0, 0.15); }
    .ribbon-recommended {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 85%, 0 100%);
    }
    .feature-icon { background: #e6f7ec; color: #0b7e45; }
    .pricing-toggle-shadow { box-shadow: 0 2px 8px rgba(0,0,0,0.04), 0 0 0 1px rgba(0,0,0,0.02); }
  </style>
</head>
<body class="bg-slate-50 antialiased text-gray-800">

  <!-- header — clean, minimal -->
  <header class="border-b border-gray-200/70 bg-white/90 backdrop-blur-sm sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-emerald-600 rounded-xl flex items-center justify-center shadow-sm shadow-emerald-200">
          <span class="text-white font-bold text-xl">Q</span>
        </div>
        <span class="font-semibold text-xl tracking-tight text-gray-800">QLINKON</span>
        <span class="ml-2 text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full border border-emerald-200/60">SaaS</span>
      </div>
      <nav class="hidden sm:flex items-center gap-6 text-sm font-medium text-gray-600">
        <a href="#" class="hover:text-emerald-700 transition">Product</a>
        <a href="#" class="hover:text-emerald-700 transition">Solutions</a>
        <a href="#" class="text-emerald-700 font-semibold">Pricing</a>
        <a href="#" class="hover:text-emerald-700 transition">Resources</a>
      </nav>
      <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500 hidden sm:block">Have questions?</span>
        <a href="mailto:{{ $companyEmail }}" class="text-sm bg-white border border-gray-300 hover:border-emerald-300 hover:bg-emerald-50/50 px-4 py-2 rounded-full font-medium transition shadow-sm">
          Contact us
        </a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20 pt-10">

    <!-- Hero section -->
    <div class="text-center max-w-3xl mx-auto mb-12">
      <div class="inline-flex items-center gap-2 bg-emerald-50 px-4 py-1.5 rounded-full text-emerald-800 text-sm font-medium border border-emerald-200/70 mb-5">
        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> 
        Flexible plans · 14-day free trial on all plans
      </div>
      <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-gray-900 mb-4">
        Plans that scale <br class="hidden sm:block">with your business
      </h1>
      <p class="text-lg text-gray-600 max-w-2xl mx-auto">
        From inventory to storefront — everything you need to run smarter. 
        No hidden fees, no surprises.
      </p>
    </div>

    <!-- Billing toggle (static design, monthly active) — just UI/UX polish -->
    <div class="flex justify-center mb-12">
      <div class="bg-white p-1 rounded-2xl pricing-toggle-shadow inline-flex items-center">
        <button class="px-5 py-2 rounded-xl text-sm font-medium bg-emerald-600 text-white shadow-sm">Monthly billing</button>
        <button class="px-5 py-2 rounded-xl text-sm font-medium text-gray-500 hover:text-gray-800 transition">Yearly (save 20%)</button>
      </div>
    </div>

    <!-- Pricing cards — 3 plans using actual plan schema fields (static representation) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch">

      <!-- Plan 1: Essential -->
      <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xl shadow-gray-200/30 card-hover flex flex-col relative">
        <div class="p-6 md:p-7 flex-1">
          <h3 class="text-xl font-bold text-gray-800">Essential</h3>
          <p class="text-gray-500 text-sm mt-0.5">Perfect for startups & small shops</p>
          {{-- <div class="mt-5 flex items-baseline gap-1">
            <span class="text-4xl font-extrabold text-gray-900">$39</span>
            <span class="text-gray-500 font-medium">/month</span>
          </div> --}}
          <p class="text-sm text-gray-500 mt-1">billed monthly · 14-day free trial</p>
          
          <div class="mt-6 space-y-3.5">
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700"><span class="font-semibold">3</span> team members</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700"><span class="font-semibold">1</span> store location</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700">Up to <span class="font-semibold">500</span> products</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700">Up to <span class="font-semibold">10</span> employees</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700">Core inventory & POS</span>
            </div>
          </div>

          <!-- extra module hints (light) -->
          <div class="mt-6 border-t border-dashed border-gray-200 pt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Includes modules</p>
            <div class="flex flex-wrap gap-1.5">
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Inventory</span>
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">POS</span>
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Invoicing</span>
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">CRM lite</span>
            </div>
          </div>
        </div>
        <div class="px-6 pb-7 pt-2">
          <a href="mailto:{{ $companyEmail }}?subject=Essential%20Plan%20inquiry" class="block w-full text-center bg-white border-2 border-gray-300 hover:border-emerald-300 hover:bg-emerald-50/30 text-gray-800 font-semibold py-3.5 rounded-xl transition-all shadow-sm">
            Start free trial →
          </a>
          <p class="text-xs text-gray-400 text-center mt-3">No credit card required</p>
        </div>
      </div>

      <!-- Plan 2: Professional — RECOMMENDED (green ribbon) -->
      <div class="bg-white rounded-3xl border-2 border-emerald-200 shadow-2xl shadow-emerald-100/50 card-hover flex flex-col relative transform scale-[1.02] lg:scale-100">
        <!-- Recommended ribbon -->
        <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
          <div class="ribbon-recommended text-white px-7 py-1.5 text-sm font-bold tracking-wide shadow-lg">
            ⭐ MOST POPULAR
          </div>
        </div>
        <div class="p-6 md:p-7 flex-1 pt-7">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Professional</h3>
            <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 rounded-full">recommended</span>
          </div>
          <p class="text-gray-500 text-sm mt-0.5">For growing businesses & teams</p>
          {{-- <div class="mt-5 flex items-baseline gap-1">
            <span class="text-4xl font-extrabold text-gray-900">$79</span>
            <span class="text-gray-500 font-medium">/month</span>
          </div> --}}
          <p class="text-sm text-gray-500 mt-1">billed monthly · 14-day trial</p>

          <div class="mt-6 space-y-3.5">
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5 bg-emerald-100 text-emerald-800">✓</span>
              <span class="text-gray-700"><span class="font-semibold">10</span> team members</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5 bg-emerald-100 text-emerald-800">✓</span>
              <span class="text-gray-700"><span class="font-semibold">3</span> stores / locations</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5 bg-emerald-100 text-emerald-800">✓</span>
              <span class="text-gray-700">Up to <span class="font-semibold">5,000</span> products</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5 bg-emerald-100 text-emerald-800">✓</span>
              <span class="text-gray-700">Up to <span class="font-semibold">30</span> employees</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5 bg-emerald-100 text-emerald-800">✓</span>
              <span class="text-gray-700">Advanced reports & bulk import</span>
            </div>
          </div>

          <div class="mt-6 border-t border-dashed border-gray-200 pt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Everything in Essential, plus</p>
            <div class="flex flex-wrap gap-1.5">
              <span class="bg-emerald-50 text-emerald-800 text-xs px-3 py-1 rounded-full border border-emerald-200">Purchasing</span>
              <span class="bg-emerald-50 text-emerald-800 text-xs px-3 py-1 rounded-full border border-emerald-200">Full CRM</span>
              <span class="bg-emerald-50 text-emerald-800 text-xs px-3 py-1 rounded-full border border-emerald-200">Storefront</span>
              <span class="bg-emerald-50 text-emerald-800 text-xs px-3 py-1 rounded-full border border-emerald-200">HRM basics</span>
              <span class="bg-emerald-50 text-emerald-800 text-xs px-3 py-1 rounded-full border border-emerald-200">Bulk upload</span>
            </div>
          </div>
        </div>
        <div class="px-6 pb-7 pt-2">
          <a href="mailto:{{ $companyEmail }}?subject=Professional%20Plan" class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3.5 rounded-xl transition shadow-md shadow-emerald-200">
            Get started →
          </a>
          <p class="text-xs text-gray-400 text-center mt-3">Questions? <a href="mailto:sales@qlinkon.com" class="text-emerald-600 underline">Contact sales</a></p>
        </div>
      </div>

      <!-- Plan 3: Business / Scale -->
      <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xl shadow-gray-200/30 card-hover flex flex-col">
        <div class="p-6 md:p-7 flex-1">
          <h3 class="text-xl font-bold text-gray-800">Business</h3>
          <p class="text-gray-500 text-sm mt-0.5">For high‑volume & multi‑channel</p>
          {{-- <div class="mt-5 flex items-baseline gap-1">
            <span class="text-4xl font-extrabold text-gray-900">$199</span>
            <span class="text-gray-500 font-medium">/month</span>
          </div> --}}
          <p class="text-sm text-gray-500 mt-1">billed monthly · 14-day trial</p>
          
          <div class="mt-6 space-y-3.5">
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700"><span class="font-semibold">30</span> team members</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700"><span class="font-semibold">10</span> stores / locations</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700">Up to <span class="font-semibold">25,000</span> products</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700">Up to <span class="font-semibold">100</span> employees</span>
            </div>
            <div class="flex items-start gap-3">
              <span class="feature-icon w-5 h-5 rounded-full flex items-center justify-center text-sm shrink-0 mt-0.5">✓</span>
              <span class="text-gray-700">Manufacturing & production</span>
            </div>
          </div>

          <div class="mt-6 border-t border-dashed border-gray-200 pt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">All pro features +</p>
            <div class="flex flex-wrap gap-1.5">
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Production</span>
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Plant education</span>
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Challan & dispatch</span>
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Inquiry mgmt</span>
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">Expense tracking</span>
            </div>
          </div>
        </div>
        <div class="px-6 pb-7 pt-2">
          <a href="mailto:{{ $companyEmail }}?subject=Business%20plan%20inquiry" class="block w-full text-center bg-white border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-800 font-semibold py-3.5 rounded-xl transition-all shadow-sm">
            Contact sales →
          </a>
          <p class="text-xs text-gray-400 text-center mt-3">or <a href="mailto:{{ $companyEmail }}" class="underline">request demo</a></p>
        </div>
      </div>
    </div>

    <!-- Module showcase — all core modules included (SaaS module list) -->
    <div class="mt-20 mb-16">
      <div class="text-center mb-10">
        <span class="text-emerald-700 font-semibold text-sm tracking-wide">POWERFUL SUITE</span>
        <h2 class="text-3xl font-bold text-gray-900 mt-2">Everything your business needs</h2>
        <p class="text-gray-500 max-w-2xl mx-auto mt-3">All plans include access to our core modules — upgrade to unlock advanced limits.</p>
      </div>
      
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3 text-sm">
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">📦</div>
          <p class="font-medium">Inventory</p>
          <p class="text-xs text-gray-400">Catalog & stock</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">🛒</div>
          <p class="font-medium">POS</p>
          <p class="text-xs text-gray-400">Checkout & receipts</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">🧾</div>
          <p class="font-medium">Invoicing</p>
          <p class="text-xs text-gray-400">Quotes & invoices</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">📥</div>
          <p class="font-medium">Purchases</p>
          <p class="text-xs text-gray-400">PO & suppliers</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">🤝</div>
          <p class="font-medium">CRM</p>
          <p class="text-xs text-gray-400">Leads & pipelines</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">👥</div>
          <p class="font-medium">HRM</p>
          <p class="text-xs text-gray-400">Payroll & attendance</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">🖥️</div>
          <p class="font-medium">Storefront</p>
          <p class="text-xs text-gray-400">B2B / B2C</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">📊</div>
          <p class="font-medium">Reports</p>
          <p class="text-xs text-gray-400">Analytics & export</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">🏭</div>
          <p class="font-medium">Production</p>
          <p class="text-xs text-gray-400">BOM & work orders</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">🌱</div>
          <p class="font-medium">Plant Edu</p>
          <p class="text-xs text-gray-400">Guides & care</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">📎</div>
          <p class="font-medium">Challan</p>
          <p class="text-xs text-gray-400">Dispatch & returns</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">⬆️</div>
          <p class="font-medium">Bulk Import</p>
          <p class="text-xs text-gray-400">CSV / Excel</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">💰</div>
          <p class="font-medium">Expenses</p>
          <p class="text-xs text-gray-400">Track spending</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm text-center hover:border-emerald-200 transition">
          <div class="text-emerald-600 text-lg mb-1">📬</div>
          <p class="font-medium">Inquiries</p>
          <p class="text-xs text-gray-400">Pre‑sales & forms</p>
        </div>
      </div>
    </div>

    <!-- Contact us / enterprise CTA — exactly as requested: contact details provided -->
    <div class="mt-12 bg-gradient-to-br from-emerald-50 to-white rounded-3xl border border-emerald-200/60 p-8 md:p-10 shadow-inner">
      <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
        <div>
          <h3 class="text-2xl md:text-3xl font-bold text-gray-900">Need a custom plan?</h3>
          <p class="text-gray-600 mt-2 text-lg max-w-xl">We offer tailored solutions for larger teams, franchises, and specific industry needs. Our team is ready to help.</p>
          <div class="mt-6 flex flex-wrap gap-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white rounded-full shadow-sm flex items-center justify-center text-emerald-700 border border-emerald-200">📧</div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Email us</p>
                <a href="mailto:{{ $companyEmail }}" class="font-semibold text-gray-800 hover:text-emerald-700 text-lg">{{ $companyEmail }}</a>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white rounded-full shadow-sm flex items-center justify-center text-emerald-700 border border-emerald-200">📞</div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Call / WhatsApp</p>
                <a href="https://wa.me/{{ $companyPhone }}" class="font-semibold text-gray-800 hover:text-emerald-700 text-lg">+91 {{ $companyPhone }}</a>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-white/70 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white w-full lg:w-auto min-w-[260px]">
          <p class="text-sm font-semibold text-gray-700 mb-2">📋 Quick contact</p>
          <p class="text-sm text-gray-500 mb-3">We reply within 24 hours</p>
          <div class="space-y-2 text-sm">
            <p><span class="font-medium">Support:</span> {{ $companyEmail }}</p>
            {{-- <p><span class="font-medium">Partnerships:</span> partners@qlinkon.com</p> --}}
            <p class="pt-2 border-t border-gray-200 mt-2 text-gray-600">🏢 QLINKON HQ · Remote-first</p>
          </div>
        </div>
      </div>
    </div>

    <!-- subtle footer note: multi-store / multi-tenant ready -->
    <div class="mt-12 text-center text-xs text-gray-400 border-t border-gray-200 pt-8">
      <p>QLINKON SaaS — Row‑level tenancy · Built on Laravel · All plans include 99.9% uptime SLA & daily backups.</p>
      <p class="mt-2">© 2026 QLINKON. Plans are static representation. Contact us for tailored onboarding.</p>
    </div>
  </main>
</body>
</html>