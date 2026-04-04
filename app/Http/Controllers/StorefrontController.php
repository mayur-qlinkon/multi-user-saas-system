<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Services\BannerService;
use App\Services\StorefrontSectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StorefrontController extends Controller
{
    public function __construct(
        protected StorefrontSectionService $sectionService,
        protected BannerService            $bannerService,
    ) {}

    // ════════════════════════════════════════════════════
    //  HOMEPAGE
    // ════════════════════════════════════════════════════

    public function index(?string $slug = null)
    {
        $company = $this->resolveCompany($slug);

        // ── Hero banners (top of page) ──
        $heroBanners = $this->safeGetBanners($company->id, 'home_top');

        // ── Storefront sections with products ──
        // Each section knows its own type and resolves its own products
        $sections = $this->sectionService->getLiveSectionsWithProducts($company->id);

        // ── Category nav (top bar + mobile drawer) ──
        $navCategories = $this->getNavCategories($company->id);

        Log::info('[Storefront] Homepage loaded', [
            'company'   => $company->slug,
            'sections'  => $sections->count(),
            'banners'   => $heroBanners->count(),
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
        $company  = $this->resolveCompany($slug);
        $category = Category::where('company_id', $company->id)
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        // ── Category banner if exists ──
        $categoryBanners = $this->safeGetBanners($company->id, 'category_page', $category->id);

        // ── Products via pivot — sorted by pivot sort_order ──
        $perPage  = 16;
        $sortBy   = $request->get('sort', 'default');

        $query = Product::withoutGlobalScope('tenant')
            ->where('products.company_id', $company->id)
            ->where('products.is_active', true)
            ->where('products.show_in_storefront', true)
            ->whereNull('products.deleted_at')
            ->whereHas('categoryPivots', fn($q) =>
                $q->where('category_id', $category->id)
                ->where('category_products.is_active', true)
            )
            ->with([
                'media' => fn($q) => $q->where('is_primary', true)->limit(1),
                'skus'  => fn($q) => $q->limit(1),
            ])
            ->join('category_products as cp',
                fn($join) => $join
                    ->on('products.id', '=', 'cp.product_id')
                    ->where('cp.category_id', $category->id)
            )
            ->select('products.*');

        // Apply sort
        match($sortBy) {
            'price_asc'  => $query->leftJoin('product_skus as ps_sort', 'products.id', '=', 'ps_sort.product_id')
                       ->orderByRaw('MIN(ps_sort.price) ASC')
                       ->groupBy('products.id'),
            'price_desc' => $query->leftJoin('product_skus as ps_sort', 'products.id', '=', 'ps_sort.product_id')
                       ->orderByRaw('MAX(ps_sort.price) DESC')
                       ->groupBy('products.id'),
            'newest'     => $query->orderBy('products.created_at', 'desc'),
            'name_asc'   => $query->orderBy('products.name', 'asc'),
            default      => $query->orderBy('cp.is_featured', 'desc')->orderBy('cp.sort_order', 'asc'),
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
            'company'  => $company->slug,
            'category' => $category->slug,
            'products' => $products->total(),
            'sort'     => $sortBy,
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
                'categories' => fn($q) => $q->where('company_id', $company->id),
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
                ->whereHas('categoryPivots', fn($q) =>
                    $q->where('category_id', $primaryCategory->id)->where('is_active', true)
                )
                ->with(['media' => fn($q) => $q->where('is_primary', true)->limit(1), 'skus' => fn($q) => $q->limit(1)])
                ->limit(6)
                ->get();
        }

        $navCategories = $this->getNavCategories($company->id);

        // Track product view (future: increment view counter)
        Log::info('[Storefront] Product viewed', [
            'company' => $company->slug,
            'product' => $product->slug,
            'id'      => $product->id,
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
        $query   = trim($request->get('q', ''));
        $perPage = 16;

        $products = collect();
        $total    = 0;

        if (strlen($query) >= 2) {
            $result = Product::where('company_id', $company->id)
                ->where('is_active', true)
                ->where('show_in_storefront', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('hsn_code', 'like', "%{$query}%")
                      ->orWhereHas('skus', fn($sq) =>
                          $sq->where('sku', 'like', "%{$query}%")
                      );
                })
                ->with([
                    'media' => fn($q) => $q->where('is_primary', true)->limit(1),
                    'skus'  => fn($q) => $q->limit(1),
                ])
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

            $products = $result;
            $total    = $result->total();
        }

        $navCategories = $this->getNavCategories($company->id);

        Log::info('[Storefront] Search', [
            'company' => $company->slug,
            'query'   => $query,
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

    public function suggest(string $slug, Request $request): \Illuminate\Http\JsonResponse
    {
        $company = $this->resolveCompany($slug);
        $query   = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::where('company_id', $company->id)
            ->where('is_active', true)
            ->where('show_in_storefront', true)
            ->where('name', 'like', "%{$query}%")
            ->with([
                'media' => fn($q) => $q->where('is_primary', true)->limit(1),
                'skus'  => fn($q) => $q->orderBy('price')->limit(1),
            ])
            ->limit(8)
            ->get()
            ->map(fn($product) => [
                'name'  => $product->name,
                'slug'  => $product->slug,
                'image' => $product->primary_image_url,
                'price' => $product->skus->first()?->price ?? 0,
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
        if (!$company && $slug) {
            $company = Company::where('slug', $slug)->first();
        }

        // ❌ 3. Not found
        if (!$company) {
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
        int     $companyId,
        string  $position,
        ?int    $categoryId = null,
        ?int    $productId  = null,
    ): \Illuminate\Database\Eloquent\Collection {
        try {
            $now = now();

            return Banner::where('company_id', $companyId)
                ->where('position', $position)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
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
                'position'   => $position,
                'error'      => $e->getMessage(),
            ]);
            return new \Illuminate\Database\Eloquent\Collection();
        }
    }
    /**
     * Get active categories for nav bar — cached per company.
     * Cache: 15 minutes. Invalidate when categories change.
     */
    public function getNavCategories(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return Cache::remember(
                "storefront_nav_categories_{$companyId}",
                now()->addMinutes(15),
                fn() => Category::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->limit(12)
                    ->get(['id', 'name', 'slug', 'image'])
            );
        } catch (\Throwable $e) {
            Log::warning('[Storefront] Nav categories load failed', [
                'company_id' => $companyId,
                'error'      => $e->getMessage(),
            ]);
            return new \Illuminate\Database\Eloquent\Collection();
        }
    }
}