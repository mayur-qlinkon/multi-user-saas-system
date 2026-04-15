<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductSku;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;

class LabelController extends Controller
{
    /**
     * Display the Label Printing UI dashboard.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;

        // Fetch categories for the filter dropdown
        $categories = Category::where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        // 🌟 DYNAMIC STORE LOGIC (Matches your layout switcher perfectly)
        $stores = Auth::user()->stores ?? collect();
        $currentStoreId = session('store_id');
        $currentStore = $currentStoreId ? $stores->firstWhere('id', $currentStoreId) : $stores->first();

        // Fallback to 'My Store' just in case they have no stores assigned
        $storeName = $currentStore ? $currentStore->name : 'My Store';

        return view('admin.products.labels', compact('categories', 'storeName'));
    }

    /**
     * Streams a raw PNG image of a Barcode or QR code.
     * Accessible via <img src="..."> tags.
     */
    public function renderImage(Request $request)
    {
        $type = strtolower(trim($request->query('type', '')));
        $value = trim($request->query('value', ''));
        $size = max(48, min(600, (int) $request->query('size', 200)));

        if (empty($value) || ! in_array($type, ['qr', 'barcode'], true)) {
            $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

            return response($transparentPng, 400)->header('Content-Type', 'image/png');
        }

        try {
            // 🌟 ROOT FIX 1: Clean any stray whitespace output from other files to prevent binary corruption
            if (ob_get_length()) {
                ob_clean();
            }

            $headers = [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=604800',
            ];

            if ($type === 'qr') {
                $options = new QROptions([
                    'eccLevel' => QRCode::ECC_M,
                    'scale' => max(2, (int) round($size / 40)),
                    'quietzoneSize' => 1,
                    'imageBase64' => false,
                ]);

                // 🌟 ROOT FIX 2: Safely detect which version of Chillerlan is installed
                if (defined('chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG')) {
                    $options->outputType = QRCode::OUTPUT_IMAGE_PNG;
                }

                $image = (new QRCode($options))->render($value);

                return response($image, 200, $headers);
            }

            if ($type === 'barcode') {
                $generator = new BarcodeGeneratorPNG;
                $widthFactor = max(1, (int) round($size / 80));
                $height = max(30, (int) round($size * 0.45));

                // 🌟 ROOT FIX 3: Use the base interface constant to avoid PHP syntax errors
                $image = $generator->getBarcode(
                    $value,
                    BarcodeGenerator::TYPE_CODE_128,
                    $widthFactor,
                    $height
                );

                return response($image, 200, $headers);
            }

        } catch (\Exception $e) {
            // 🌟 ROOT FIX 4: If generation fails, return the actual error as plain text!
            if (ob_get_length()) {
                ob_clean();
            }

            return response('Image Error: '.$e->getMessage(), 500)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * API Endpoint: Fetch Paginated SKUs with Unified Search & Filters
     */
    public function fetchProducts(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $perPage = min(100, max(1, (int) $request->query('per_page', 50)));
        $search = trim($request->query('search', ''));
        $categoryId = (int) $request->query('category_id', 0);

        // Base Query targeting SKUs directly (The ERP Way)
        $query = ProductSku::with([
            'product.category',
            'skuValues.attributeValue',
            'product.media' => function ($q) {
                $q->where('is_primary', true)->where('media_type', 'image');
            },
        ])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            });

        // Unified Search: SKU, Barcode, Parent Product Name, Parent HSN Code
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('hsn_code', 'like', "%{$search}%");
                    });
            });
        }

        // Category Filter
        if ($categoryId > 0) {
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Execute Pagination
        $paginator = $query->latest('id')->paginate($perPage);

        // Format data to match legacy Javascript expectations
        $formattedData = $paginator->getCollection()->map(function ($sku) {

            // Build the variant string (e.g., "Red / Large")
            $variantName = $sku->skuValues->map(function ($val) {
                return $val->attributeValue->value;
            })->implode(' / ');

            // Get image
            $imagePath = $sku->product->media->first()?->media_path;

            return [
                'unique_id' => $sku->id, // Serves as the HTML data-id
                'id' => $sku->product_id, // Parent ID
                'sku_id' => $sku->id,
                'name' => $sku->product->name,
                'sku' => $sku->sku,
                'display_price' => $sku->price,
                'category_name' => $sku->product->category->name ?? 'N/A',
                'variant_name' => $variantName,
                'label_value' => $sku->display_barcode, // Uses our Model fallback rule
                'actual_barcode' => $sku->barcode, // Optional: Pass raw barcode to UI if needed
                'image_url' => $imagePath ? asset('storage/'.$imagePath) : '',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedData,
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * API Endpoint: Fetch fully populated data for specifically checked SKUs
     */
    public function fetchSelectedSkus(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // Handle input (Legacy accepted comma separated strings or JSON arrays)
        $rawIds = $request->input('product_ids', []);
        if (is_string($rawIds)) {
            $rawIds = json_decode($rawIds, true) ?? explode(',', $rawIds);
        }

        $skuIds = array_filter(array_map('intval', $rawIds));

        if (empty($skuIds)) {
            return response()->json(['status' => 'error', 'message' => 'No valid SKUs provided.']);
        }

        // Fetch exactly the requested SKUs in the exact order requested using FIELD()
        $idString = implode(',', $skuIds);

        $skus = ProductSku::with(['product.category', 'skuValues.attributeValue', 'product.media'])
            ->where('company_id', $companyId)
            ->whereIn('id', $skuIds)
            ->orderByRaw("FIELD(id, {$idString})")
            ->get();

        $formattedData = $skus->map(function ($sku) {
            $variantName = $sku->skuValues->map(fn ($val) => $val->attributeValue->value)->implode(' / ');
            $imagePath = $sku->product->media->where('is_primary', true)->first()?->media_path;

            return [
                'unique_id' => $sku->id,
                'name' => $sku->product->name,
                'sku' => $sku->sku,
                'display_price' => $sku->price,
                'variant_name' => $variantName,
                'label_value' => $sku->display_barcode,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedData,
        ]);
    }
}
