<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StorefrontSection;
use App\Services\BannerService;
use App\Services\EmailService;
use App\Services\StorefrontSectionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StorePublicController extends Controller
{
    public function __construct(
        protected StorefrontSectionService $sectionService,
        protected BannerService $bannerService,
    ) {}

    // ── Resolve company + store from request attributes (set by ResolveStorePublic) ──
    private function context(): array
    {
        return [
            request()->attributes->get('current_company'),
            request()->attributes->get('current_store'),
        ];
    }

    public function index(string $slug, string $storeSlug)
    {
        [$company, $store] = $this->context();

        $heroBanners   = $this->safeBanners($company->id, 'home_top');
        $sections      = $this->sectionService->getLiveSectionsWithProducts($company->id);
        $navCategories = $this->navCategories($company->id);
        $otherStores   = $this->otherStores($company->id, $store->id);

        return view('storefront.store.index', compact(
            'company', 'store', 'heroBanners', 'sections', 'navCategories', 'otherStores'
        ));
    }

    public function category(string $slug, string $storeSlug, string $categorySlug, Request $request)
    {
        [$company, $store] = $this->context();

        $category = Category::where('company_id', $company->id)
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $sortBy   = $request->get('sort', 'default');
        $query    = Product::withoutGlobalScope('tenant')
            ->where('products.company_id', $company->id)
            ->where('products.is_active', true)
            ->where('products.show_in_storefront', true)
            ->whereNull('products.deleted_at')
            ->whereHas('categoryPivots', fn ($q) => $q
                ->where('category_id', $category->id)
                ->where('category_products.is_active', true))
            ->with([
                'media' => fn ($q) => $q->where('is_primary', true)->limit(1),
                'skus'  => fn ($q) => $q->limit(1),
            ])
            ->join('category_products as cp',
                fn ($join) => $join->on('products.id', '=', 'cp.product_id')
                    ->where('cp.category_id', $category->id))
            ->select('products.*');

        match ($sortBy) {
            'price_asc'  => $query->orderByRaw('(SELECT MIN(ps.price) FROM product_skus ps WHERE ps.product_id = products.id) ASC'),
            'price_desc' => $query->orderByRaw('(SELECT MAX(ps.price) FROM product_skus ps WHERE ps.product_id = products.id) DESC'),
            'newest'     => $query->orderBy('products.created_at', 'desc'),
            'name_asc'   => $query->orderBy('products.name', 'asc'),
            default      => $query->orderBy('cp.is_featured', 'desc')->orderBy('cp.sort_order', 'asc'),
        };

        $products      = $query->paginate(16)->withQueryString();
        $navCategories = $this->navCategories($company->id);
        $allCategories = Category::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        $otherStores   = $this->otherStores($company->id, $store->id);

        return view('storefront.store.category', compact(
            'company', 'store', 'category', 'products', 'navCategories', 'allCategories', 'otherStores', 'sortBy'
        ));
    }

    public function show(string $slug, string $storeSlug, string $productSlug)
    {
        [$company, $store] = $this->context();

        $product = Product::with([
            'media',
            'skus.skuValues.attribute',
            'skus.skuValues.attributeValue',
            'skus.stocks:id,product_sku_id,qty',
            'categories' => fn ($q) => $q->where('company_id', $company->id),
            'productUnit', 'saleUnit',
        ])
            ->where('company_id', $company->id)
            ->where('slug', $productSlug)
            ->where('is_active', true)
            ->where('show_in_storefront', true)
            ->firstOrFail();

        $navCategories = $this->navCategories($company->id);
        $otherStores   = $this->otherStores($company->id, $store->id);

        return view('storefront.store.product', compact('company', 'store', 'product', 'navCategories', 'otherStores'));
    }

    public function search(string $slug, string $storeSlug, Request $request)
    {
        [$company, $store] = $this->context();

        $term     = trim($request->get('q', ''));
        $products = strlen($term) >= 2
            ? Product::where('company_id', $company->id)
                ->where('is_active', true)
                ->where('show_in_storefront', true)
                ->where(fn ($q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%"))
                ->with([
                    'media' => fn ($q) => $q->where('is_primary', true)->limit(1),
                    'skus'  => fn ($q) => $q->limit(1),
                ])
                ->paginate(16)->withQueryString()
            : collect();

        $navCategories = $this->navCategories($company->id);
        $otherStores   = $this->otherStores($company->id, $store->id);

        return view('storefront.store.search', compact('company', 'store', 'products', 'term', 'navCategories', 'otherStores'));
    }

    public function suggest(string $slug, string $storeSlug, Request $request): JsonResponse
    {
        [$company, $store] = $this->context();
        $term = trim($request->get('q', ''));

        if (strlen($term) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::where('company_id', $company->id)
            ->where('is_active', true)
            ->where('show_in_storefront', true)
            ->where('name', 'like', "%{$term}%")
            ->with([
                'media' => fn ($q) => $q->where('is_primary', true)->limit(1),
                'skus'  => fn ($q) => $q->orderBy('price')->limit(1),
            ])
            ->limit(8)
            ->get()
            ->map(fn ($p) => [
                'name'  => $p->name,
                'slug'  => $p->slug,
                'image' => $p->primary_image_url,
                'price' => $p->skus->first()?->price ?? 0,
            ]);

        return response()->json(['products' => $products]);
    }

    public function inquiry(string $slug, string $storeSlug, Request $request)
    {
        $request->validate([
            'product_id'     => ['required', 'integer'],
            'product_name'   => ['required', 'string', 'max:255'],
            'customer_name'  => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        [$company, $store] = $this->context();

        $order = Order::create([
            'company_id'     => $company->id,
            'store_id'       => $store->id,       // ← tagged to specific store
            'order_type'     => 'inquiry',
            'source'         => 'storefront',
            'status'         => 'inquiry',
            'payment_status' => 'pending',
            'customer_name'  => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_notes' => $request->customer_notes,
            'admin_notes'    => 'Product inquiry: '.$request->product_name.' via '.$store->name,
            'subtotal'       => 0, 'total_amount' => 0, 'items_count' => 0, 'items_qty' => 0,
        ]);

        app(EmailService::class)->sendOrderInquiryEmails($order, $company, $request->product_name);

        return redirect()->back()->with('success', 'Inquiry submitted! We will contact you soon.');
    }

    public function trackView(string $slug, string $storeSlug, Request $request)
    {
        if ($id = $request->input('section_id')) {
            StorefrontSection::where('id', $id)->increment('view_count');
        }
        return response()->json(['status' => 'ok']);
    }

    public function trackClick(string $slug, string $storeSlug, $id)
    {
        StorefrontSection::where('id', $id)->increment('click_count');
        return response()->json(['status' => 'ok']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function otherStores(int $companyId, int $currentStoreId): Collection
    {
        return Cache::remember("store_switcher_{$companyId}", 600, fn () =>
            Store::where('company_id', $companyId)
                ->where('is_active', true)
                ->where('storefront_enabled', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'logo', 'city'])
        )->reject(fn ($s) => $s->id === $currentStoreId)->values();
    }

    private function navCategories(int $companyId): Collection
    {
        return Cache::remember("storefront_nav_categories_{$companyId}", 900, fn () =>
            Category::where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->limit(12)
                ->get(['id', 'name', 'slug', 'image'])
        );
    }

    private function safeBanners(int $companyId, string $position): Collection
    {
        try {
            return $this->bannerService->getActiveBanners($companyId, $position);
        } catch (\Throwable $e) {
            return new Collection;
        }
    }
}