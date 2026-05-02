<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StorefrontSection;

use App\Services\BannerService;
use App\Services\EmailService;
use App\Services\StorefrontSectionService;

use App\Events\Orders\OrderPlaced;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StorefrontController extends Controller
{
    public function __construct(
        protected StorefrontSectionService $sectionService,
        protected BannerService $bannerService,
    ) {}

    // ════════════════════════════════════════════════════
    //  HOMEPAGE
    // ════════════════════════════════════════════════════

     public function index(?string $slug = null)
    {
        $company = $this->resolveCompany($slug);

        // ── Single store? Auto-redirect to store-level URL ──
        $activeStores = Store::where('company_id', $company->id)
            ->where('is_active', true)
            ->where('storefront_enabled', true)
            ->get(['id', 'slug', 'name', 'city', 'logo', 'business_hours']);

        if ($activeStores->count() === 1) {
            return redirect()->route('store.index', [
                'slug'       => $company->slug,
                'store_slug' => $activeStores->first()->slug,
            ]);
        }

        // ── Multi-store: show branch picker ──
        if ($activeStores->count() > 1) {
            return view('storefront.branches', compact('company', 'activeStores'));
        }

        // ── No stores online: fallback to old company homepage ──
        $heroBanners   = $this->safeGetBanners($company->id, 'home_top');

        // ── Storefront sections with products ──
        // Each section knows its own type and resolves its own products
        $sections = $this->sectionService->getLiveSectionsWithProducts($company->id);

        // ── Category nav (top bar + mobile drawer) ──
        $navCategories = $this->getNavCategories($company->id);

        Log::info('[Storefront] Homepage loaded', [
            'company' => $company->slug,
            'sections' => $sections->count(),
            'banners' => $heroBanners->count(),
        ]);

        return view('storefront.index', compact(
            'company',
            'heroBanners',
            'sections',
            'navCategories',
        ));
    }

    // ════════════════════════════════════════════════════
    //  CATEGORY PAGE
    // ════════════════════════════════════════════════════

    public function category(string $slug, string $categorySlug, Request $request)
    {
        $company = $this->resolveCompany($slug);
        $category = Category::where('company_id', $company->id)
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        // ── Category banner if exists ──
        $categoryBanners = $this->safeGetBanners($company->id, 'category_page', $category->id);

        // ── Products via pivot — sorted by pivot sort_order ──
        $perPage = 16;
        $sortBy = $request->get('sort', 'default');

        $query = Product::withoutGlobalScope('tenant')
            ->where('products.company_id', $company->id)
            ->where('products.is_active', true)
            ->where('products.show_in_storefront', true)
            ->whereNull('products.deleted_at')
            ->whereHas('categoryPivots', fn ($q) => $q->where('category_id', $category->id)
                ->where('category_products.is_active', true)
            )
            ->with([
                'media' => fn ($q) => $q->where('is_primary', true)->limit(1),
                'skus' => fn ($q) => $q->limit(1),
            ])
            ->join('category_products as cp',
                fn ($join) => $join
                    ->on('products.id', '=', 'cp.product_id')
                    ->where('cp.category_id', $category->id)
            )
            ->select('products.*');

        // Apply sort
        match ($sortBy) {
            'price_asc' => $query->leftJoin('product_skus as ps_sort', 'products.id', '=', 'ps_sort.product_id')
                ->orderByRaw('MIN(ps_sort.price) ASC')
                ->groupBy('products.id'),
            'price_desc' => $query->leftJoin('product_skus as ps_sort', 'products.id', '=', 'ps_sort.product_id')
                ->orderByRaw('MAX(ps_sort.price) DESC')
                ->groupBy('products.id'),
            'newest' => $query->orderBy('products.created_at', 'desc'),
            'name_asc' => $query->orderBy('products.name', 'asc'),
            default => $query->orderBy('cp.is_featured', 'desc')->orderBy('cp.sort_order', 'asc'),
        };

        $products = $query->paginate($perPage)->withQueryString();

        // ── Nav categories ──
        $navCategories = $this->getNavCategories($company->id);

        // ── All categories for sidebar filter ──
        $allCategories = Category::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        Log::info('[Storefront] Category page loaded', [
            'company' => $company->slug,
            'category' => $category->slug,
            'products' => $products->total(),
            'sort' => $sortBy,
        ]);

        return view('storefront.category', compact(
            'company',
            'category',
            'products',
            'navCategories',
            'allCategories',
            'categoryBanners',
            'sortBy',
        ));
    }

    // ════════════════════════════════════════════════════
    //  PRODUCT DETAIL
    // ════════════════════════════════════════════════════

    public function show(string $slug, string $productSlug)
    {
        $company = $this->resolveCompany($slug);

        $product = Product::with([
            'media',
            'skus.skuValues.attribute',
            'skus.skuValues.attributeValue',
            'skus.stocks:id,product_sku_id,qty', // needed by ProductSku::getIsInStockAttribute — prevents N+1
            'categories' => fn ($q) => $q->where('company_id', $company->id),
            'productUnit',
            'saleUnit',
        ])
            ->where('company_id', $company->id)
            ->where('slug', $productSlug)
            ->where('is_active', true)
            ->where('show_in_storefront', true)
            ->firstOrFail();

        // ── Related products from same category ──
        $related = collect();
        $primaryCategory = $product->categories->first();
        if ($primaryCategory) {
            $related = Product::where('company_id', $company->id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->where('show_in_storefront', true)
                ->whereHas('categoryPivots', fn ($q) => $q->where('category_id', $primaryCategory->id)->where('is_active', true)
                )
                ->with(['media' => fn ($q) => $q->where('is_primary', true)->limit(1), 'skus' => fn ($q) => $q->limit(1)])
                ->limit(6)
                ->get();
        }

        $navCategories = $this->getNavCategories($company->id);

        // Track product view (future: increment view counter)
        Log::info('[Storefront] Product viewed', [
            'company' => $company->slug,
            'product' => $product->slug,
            'id' => $product->id,
        ]);

        return view('storefront.product', compact(
            'company',
            'product',
            'related',
            'navCategories',
        ));
    }

    // ════════════════════════════════════════════════════
    //  SEARCH
    // ════════════════════════════════════════════════════

    public function search(string $slug, Request $request)
    {
        $company = $this->resolveCompany($slug);
        $query = trim($request->get('q', ''));
        $perPage = 16;

        $products = collect();
        $total = 0;

        if (strlen($query) >= 2) {
            $result = Product::where('company_id', $company->id)
                ->where('is_active', true)
                ->where('show_in_storefront', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('hsn_code', 'like', "%{$query}%")
                        ->orWhereHas('skus', fn ($sq) => $sq->where('sku', 'like', "%{$query}%")
                        );
                })
                ->with([
                    'media' => fn ($q) => $q->where('is_primary', true)->limit(1),
                    'skus' => fn ($q) => $q->limit(1),
                ])
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

            $products = $result;
            $total = $result->total();
        }

        $navCategories = $this->getNavCategories($company->id);

        Log::info('[Storefront] Search', [
            'company' => $company->slug,
            'query' => $query,
            'results' => $total,
        ]);

        return view('storefront.search', compact(
            'company',
            'products',
            'navCategories',
            'query',
            'total',
        ));
    }

    // ════════════════════════════════════════════════════
    //  SUGGEST — AJAX dropdown (header search)
    // ════════════════════════════════════════════════════

    public function suggest(string $slug, Request $request): JsonResponse
    {
        $company = $this->resolveCompany($slug);
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::where('company_id', $company->id)
            ->where('is_active', true)
            ->where('show_in_storefront', true)
            ->where('name', 'like', "%{$query}%")
            ->with([
                'media' => fn ($q) => $q->where('is_primary', true)->limit(1),
                'skus' => fn ($q) => $q->orderBy('price')->limit(1),
            ])
            ->limit(8)
            ->get()
            ->map(fn ($product) => [
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $product->primary_image_url,
                'price' => $product->skus->first()?->price ?? 0,
                'product_type' => $product->product_type ?? 'sellable',
            ]);

        return response()->json(['products' => $products]);
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════════

    /**
     * Resolve company by domain OR slug — 404 if not found.
     */
    private function resolveCompany(?string $slug = null): Company
    {
        $host = request()->getHost();

        // 🌐 1. Try custom domain first
        $company = Company::where('domain', $host)->first();

        // 🔁 2. Fallback to slug (SaaS default)
        if (! $company && $slug) {
            $company = Company::where('slug', $slug)->first();
        }

        // ❌ 3. Not found
        if (! $company) {
            abort(404);
        }

        // ✅ Set for global access (important for settings, views)
        request()->attributes->set('current_company_id', $company->id);

        return $company;
    }

    /**
     * Get banners safely — never crashes the page.
     * Falls back to empty collection on any error.
     */
    private function safeGetBanners(
        int $companyId,
        string $position,
        ?int $categoryId = null,
        ?int $productId = null,
    ): Collection {
        try {
            $now = now();

            return Banner::where('company_id', $companyId)
                ->where('position', $position)
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                // ── Targeting filter ──
                // If banner has a category_id set, only show it on that category's page
                // If banner has no category_id (null), show it on ALL category pages
                ->where(function ($q) use ($categoryId) {
                    $q->whereNull('category_id');
                    if ($categoryId) {
                        $q->orWhere('category_id', $categoryId);
                    }
                })
                ->where(function ($q) use ($productId) {
                    $q->whereNull('product_id');
                    if ($productId) {
                        $q->orWhere('product_id', $productId);
                    }
                })
                ->orderBy('sort_order')
                ->get();

        } catch (\Throwable $e) {
            Log::warning('[Storefront] Banner load failed', [
                'company_id' => $companyId,
                'position' => $position,
                'error' => $e->getMessage(),
            ]);

            return new Collection;
        }
    }

    /**
     * Get active categories for nav bar — cached per company.
     * Cache: 15 minutes. Invalidate when categories change.
     */
    public function getNavCategories(int $companyId): Collection
    {
        try {
            return Cache::remember(
                "storefront_nav_categories_{$companyId}",
                now()->addMinutes(15),
                fn () => Category::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->limit(12)
                    ->get(['id', 'name', 'slug', 'image'])
            );
        } catch (\Throwable $e) {
            Log::warning('[Storefront] Nav categories load failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return new Collection;
        }
    }

    public function trackView(string $slug, Request $request)
    {
        // Optional: You can resolve the company here if you want extra security
        // $company = $this->resolveCompany($slug);

        $sectionId = $request->input('section_id');

        if ($sectionId) {
            StorefrontSection::where('id', $sectionId)->increment('view_count');
        }

        return response()->json(['status' => 'logged']);
    }

    public function trackClick(string $slug, $id)
    {
        StorefrontSection::where('id', $id)->increment('click_count');

        return response()->json(['status' => 'logged']);
    }

    /**
     * Handle catalog product inquiry → creates an Order with order_type = 'inquiry'.
     */
    public function inquiry(string $slug, Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer'],
            'product_name' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $company = Company::where('slug', $slug)->firstOrFail();

        // 🛠️ Wrap in transaction to ensure both Order and OrderItem are created
        $order = DB::transaction(function() use ($company, $request) {
            // Fetch product and its first active SKU for the snapshot
            $product = Product::where('id', $request->product_id)->firstOrFail();
            $sku = $product->skus()->where('is_active', true)->first();

            $order = Order::create([
                'company_id'      => $company->id,
                'order_type'      => 'inquiry', // Essential for conditional display
                'source'          => 'storefront',
                'status'          => 'inquiry',
                'payment_status'  => 'pending',
                'customer_name'   => $request->customer_name,
                'customer_email'  => $request->customer_email,
                'customer_phone'  => $request->customer_phone,
                'customer_notes'  => $request->customer_notes,
                'admin_notes'     => 'Inquiry received for product: ' . $product->name,
                'subtotal'        => 0,
                'total_amount'    => 0,
                'items_count'     => 1,
                'items_qty'       => 1,
            ]);

            OrderItem::create([
                'order_id'      => $order->id,
                'product_id'    => $product->id,
                'sku_id'        => $sku?->id,
                'product_name'  => $product->name,
                'sku_code'      => $sku?->sku ?? $sku?->sku_code,
                'product_image' => $product->primary_image_url,
                'unit_price'    => 0, // Inquiries don't have fixed transaction prices
                'qty'           => 1,
                'line_total'    => 0,
            ]);

            return $order;
        });

        event(new OrderPlaced($order));
        app(EmailService::class)->sendOrderInquiryEmails($order, $company, $request->product_name);

        return redirect()->back()->with('success', 'Your inquiry has been submitted successfully!');
    }
}
