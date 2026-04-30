<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exceptions\InsufficientStockException;

use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductSku;
use App\Models\State;
use App\Models\Unit;
use App\Models\Warehouse;

use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PosController extends Controller
{
    protected PaymentService $paymentService;

    protected InventoryService $inventoryService;

    public function __construct(PaymentService $paymentService, InventoryService $inventoryService)
    {
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * 1. THE UI: Load the Full-Screen POS Interface
     */
    public function index()
    {
        $storeId = session('store_id') ?? Auth::user()->store_id;

        if (! $storeId) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Please select a Store Branch to access the POS.');
        }

        // 1. Load active global dictionaries (Tenantable trait handles company isolation)
        $categories = Category::where('is_active', true)->get();
        $clients = Client::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $states = State::where('is_active', true)->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        // 2. Load branch-specific data
        // $warehouses = Warehouse::where('store_id', $storeId)->get();
        $storeIds = auth_stores()->pluck('id');
        $warehouses = Warehouse::whereIn('store_id', $storeIds)->get();

        // 3. Fetch specific single records
        $defaultClient = Client::where('name', 'Walk-in Customer')->first();
        $companyState = Auth::user()->company->state->name ?? 'Unknown';

        return view('admin.pos.index', compact(
            'categories', 'warehouses', 'paymentMethods', 'defaultClient',
            'companyState', 'storeId', 'states', 'clients', 'units'
        ));
    }

    /**
     * 2. THE LIGHTNING API: Barcode Scanner Endpoint
     */
    public function scanItem(Request $request)
    {
        $term = trim($request->input('term', ''));
        $warehouseId = (int) $request->input('warehouse_id');
        $companyId = Auth::user()->company_id;

        if (empty($term) || ! $warehouseId) {
            return response()->json(['status' => 'error', 'message' => 'Invalid scan data.']);
        }

        // STEP A: The "Lightning" Exact Match (Barcode or SKU)
        // 🌟 BUGFIX: Using the correct 'productUnit' relation from your Product model
        $exactSku = ProductSku::with(['product.category', 'unit', 'product.saleUnit', 'product.productUnit'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereHas('product', function ($pq) {
                $pq->where('product_type', 'sellable'); // 🛡️ Block Catalog Items from Barcode Scanners
            })
            ->where(function ($query) use ($term) {
                $query->where('barcode', $term)->orWhere('sku', $term);
            })->first();

        if ($exactSku) {
            $stock = $this->inventoryService->getWarehouseStock($exactSku, $warehouseId);

            return response()->json([
                'status' => 'exact',
                'data' => $this->formatSkuForCart($exactSku, $stock),
            ]);
        }

        // STEP B: The "Fuzzy" Fallback (Manual Name Search)
        $fuzzySkus = ProductSku::with(['product.category', 'unit', 'product.saleUnit', 'product.productUnit'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereHas('product', function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->where('is_active', true)
                    ->where('product_type', 'sellable'); // 🛡️ Block Catalog Items
            })
            ->limit(15) // Keep it fast
            ->get();

        if ($fuzzySkus->isEmpty()) {
            return response()->json(['status' => 'empty', 'message' => 'No product found.']);
        }

        $formattedFuzzy = $fuzzySkus->map(function ($sku) use ($warehouseId) {
            $stock = $this->inventoryService->getWarehouseStock($sku, $warehouseId);

            return $this->formatSkuForCart($sku, $stock);
        });

        return response()->json([
            'status' => 'fuzzy',
            'data' => $formattedFuzzy,
        ]);
    }

    /**
     * API Endpoint: Fetch Paginated SKUs for the POS Visual Grid
     */
    public function fetchProducts(Request $request)
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 50)));
        $search = trim($request->query('search', ''));
        $categoryId = (int) $request->query('category_id', 0);
        $warehouseId = (int) $request->query('warehouse_id', 1);

        $query = ProductSku::with([
            'product.category',
            'skuValues.attributeValue',
            'unit',                  // 1. SKU Override Unit
            'product.saleUnit',      // 2. Product Sale Unit
            'product.productUnit',   // 3. Product Base Unit (Fallback)
            'product.media' => function ($q) {
                $q->where('is_primary', true)->where('media_type', 'image');
            },
        ])
            ->where('product_skus.is_active', true)
            ->whereHas('product', function ($q) {
                $q->where('is_active', true)
                  ->where('product_type', 'sellable'); // 🛡️ IRON WALL: Block 'Catalog' items from visual POS grid!
            });

        // Unified Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Category Filter
        if ($categoryId > 0) {
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Join products table for alphabetical ordering only (no scope interference)
        $paginator = $query
            ->join('products as _pos_p', '_pos_p.id', '=', 'product_skus.product_id')
            ->select('product_skus.*')
            ->orderBy('_pos_p.name', 'asc')
            ->orderBy('product_skus.sku', 'asc')
            ->paginate($perPage);

        $formattedData = $paginator->getCollection()->map(function ($sku) use ($warehouseId) {
            $variantName = $sku->skuValues->map(fn ($val) => $val->attributeValue->value)->implode(' / ');
            $imagePath = $sku->product->media->first()?->media_path;

            // 🌟 BUGFIX: Bulletproof Unit Resolution with Safe Operators (?->)
            $resolvedUnitId = $sku->unit_id ?? $sku->product?->sale_unit_id ?? $sku->product?->product_unit_id;
            $resolvedUnitName = $sku->unit?->name ?? $sku->product?->saleUnit?->name ?? $sku->product?->productUnit?->name ?? 'Unit';

            return [
                'product_sku_id' => $sku->id,
                'product_id' => $sku->product_id,
                'product_name' => $sku->product->name,
                'category_name' => $sku->product->category->name ?? 'Uncategorized',
                'sku_code' => $sku->sku,
                'barcode' => $sku->display_barcode,
                'actual_barcode' => $sku->barcode,
                'unit_id' => $resolvedUnitId,
                'unit_name' => $resolvedUnitName,
                'hsn_code' => $sku->product->hsn_code,
                'display_price' => (float) $sku->price,
                'unit_price' => (float) $sku->price,
                'tax_percent' => (float) ($sku->order_tax ?? 0),
                'tax_type' => $sku->tax_type,
                'variant_name' => $variantName,
                'image_url' => $imagePath ? asset('storage/'.$imagePath) : '',
                'stock' => $this->inventoryService->getWarehouseStock($sku, $warehouseId),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedData,
            'meta' => [
                'total_pages' => $paginator->lastPage(),
                'current_page' => $paginator->currentPage(),
            ],
        ]);
    }

    /**
     * Helper to consistently format SKU data for the Vue/Alpine Cart
     */
    private function formatSkuForCart(ProductSku $sku, float $stock): array
    {
        // 🌟 Apply the exact same bulletproof logic to scanned items
        $resolvedUnitId = $sku->unit_id ?? $sku->product?->sale_unit_id ?? $sku->product?->product_unit_id;
        $resolvedUnitName = $sku->unit?->name ?? $sku->product?->saleUnit?->name ?? $sku->product?->productUnit?->name ?? 'Unit';

        return [
            'product_sku_id' => $sku->id,
            'product_id' => $sku->product_id,
            'product_name' => $sku->product->name,
            'sku_code' => $sku->sku,
            'barcode' => $sku->display_barcode,
            'actual_barcode' => $sku->barcode,
            'unit_id' => $resolvedUnitId,
            'unit_name' => $resolvedUnitName,
            'hsn_code' => $sku->product->hsn_code,
            'display_price' => (float) $sku->price,
            'unit_price' => (float) $sku->price,
            'price' => (float) $sku->price, // 🌟 Safe fallback mapping
            'tax_percent' => (float) ($sku->order_tax ?? 0),
            'order_tax' => (float) ($sku->order_tax ?? 0), // 🌟 Guarantee Dynamic Tax Key
            'tax_type' => $sku->tax_type,
            'stock' => $stock,
        ];
    }

    /**
     * 🟢 GENERATE THERMAL RECEIPT (80mm)
     */
    public function receipt($id)
    {
        $companyId = Auth::user()->company_id;

        // 🌟 Eager load everything, including the polymorphic 'payments' relation
        $relations = [
            'items',
            'customer',
            'store',
            'creator',
            'payments.paymentMethod',
        ];

        if (batch_enabled()) {
            $relations[] = 'stockMovements';
        }

        $invoice = Invoice::with($relations)
            ->where('company_id', $companyId)
            ->findOrFail($id);

        // Grab the most recent payment for this invoice
        $payment = $invoice->payments->first();

        // Pass both to the view
        return view('admin.pos.receipt', compact('invoice', 'payment'));
    }

    /**
     * 🟢 QUICK ADD PRODUCT (POS Modal)
     * Creates a Single product, SKU, Image, and Opening Stock in one shot.
     */
    public function storeQuickProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'tax_type' => 'required|in:inclusive,exclusive',
            'sku' => 'nullable|string|max:100',
            'hsn_code' => 'nullable|string|max:20',
            'opening_stock' => 'nullable|numeric|min:0',
            'warehouse_id' => 'required|exists:warehouses,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $companyId = Auth::user()->company_id;

        try {
            $skuRecord = DB::transaction(function () use ($request, $companyId) {

                // 1. Create Parent Product (Defaulting to 'single' type)
                $product = Product::create([
                    'company_id' => $companyId,
                    'category_id' => $request->category_id,
                    'name' => $request->name,
                    'type' => 'single',
                    // Use the same unit for base, sale, and purchase for quick-adds
                    'product_unit_id' => $request->unit_id,
                    'sale_unit_id' => $request->unit_id,
                    'purchase_unit_id' => $request->unit_id,
                    'is_active' => true,
                ]);

                // 2. Generate a random SKU if none was provided
                $skuCode = $request->sku ?: strtoupper(Str::random(8));

                // 3. Create the Product SKU
                $sku = ProductSku::create([
                    'company_id' => $companyId,
                    'product_id' => $product->id,
                    'unit_id' => $request->unit_id,
                    'sku' => $skuCode,
                    'hsn_code' => $request->hsn_code,
                    'cost' => $request->cost,
                    'price' => $request->price,
                    'order_tax' => $request->tax_percent ?? 0,
                    'tax_type' => $request->tax_type,
                    'is_active' => true,
                ]);

                // 4. Handle Image Upload (If provided)
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('products', 'public');
                    ProductMedia::create([
                        'company_id' => $companyId,
                        'product_id' => $product->id,
                        'product_sku_id' => $sku->id,
                        'media_type' => 'image',
                        'media_path' => $path,
                        'is_primary' => true,
                    ]);
                }

                // 5. Set Opening Stock (If provided)
                $openingStockQty = (float) $request->opening_stock;
                if ($openingStockQty > 0) {
                    // 🌟 BUGFIX: We bypass 'setOpeningStock' and use 'addStock' directly
                    // with 'adjustment' because we know your database ENUM accepts that word!
                    $this->inventoryService->addStock(
                        sku: $sku,
                        warehouseId: $request->warehouse_id,
                        qty: $openingStockQty,
                        movementType: 'adjustment',
                        reference: null
                    );
                }

                // 6. Reload relations for the frontend response
                $sku->load(['product.category', 'unit', 'product.saleUnit', 'product.productUnit', 'product.media']);

                return $sku;
            });

            // Calculate stock specifically for the active warehouse in the POS
            $currentStock = $this->inventoryService->getWarehouseStock($skuRecord, (int) $request->warehouse_id);

            // Respond with our trusted standardized cart format
            return response()->json([
                'status' => 'success',
                'message' => 'Product created and ready for sale!',
                'data' => $this->formatSkuForCart($skuRecord, $currentStock),
            ]);

        } catch (Exception $e) {
            // Catch unique constraint violations (e.g., duplicate SKU)
            if ($e->getCode() == 23000) {
                return response()->json(['status' => 'error', 'message' => 'This SKU already exists in your company.'], 422);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to create product. '.$e->getMessage()], 500);
        }
    }

    /**
     * 3. THE CHECKOUT ENGINE: Create Invoice, Deduct Stock, Record Payment
     */
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'nullable|exists:clients,id',
            'customer_name' => 'nullable|string|max:255',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount_received' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_sku_id' => 'required|exists:product_skus,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'discount_type' => 'nullable|in:fixed,percent,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;
        $storeId = session('store_id') ?? Auth::user()->store_id;

        try {
            $invoice = DB::transaction(function () use ($request, $companyId, $storeId) {

                // A. Generate Invoice Number
                $prefix = 'POS-'.date('ym');
                $count = Invoice::where('company_id', $companyId)->where('invoice_number', 'like', "{$prefix}%")->count() + 1;
                $invoiceNumber = $prefix.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);

                // B. Determine Customer & State for GST
                $client = $request->customer_id ? Client::find($request->customer_id) : null;
                $supplyState = $client ? ($client->state->name ?? 'Gujarat') : Auth::user()->company->state->name;

                // C. Create the Parent Invoice Header
                $invoice = Invoice::create([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'warehouse_id' => $request->warehouse_id,
                    'customer_id' => $client->id ?? null,
                    'customer_name' => $client ? null : ($request->customer_name ?? 'Walk-in Customer'),
                    'created_by' => Auth::id(),
                    'invoice_number' => $invoiceNumber,
                    'source' => 'pos',
                    'invoice_date' => now()->toDateString(),
                    'supply_state' => $supplyState,
                    'gst_treatment' => $this->mapGstTreatment($client->registration_type ?? null),
                    'status' => 'confirmed',

                    'subtotal' => 0,
                    'tax_amount' => 0,
                    'grand_total' => 0,
                ]);

                $totalSubtotal = 0;
                $totalTax = 0;

                // D. Process Line Items & Deduct Stock
                foreach ($request->items as $item) {
                    $sku = ProductSku::findOrFail($item['product_sku_id']);
                    $qty = (float) $item['quantity'];
                    $price = (float) $item['unit_price'];
                    $taxPct = (float) $item['tax_percent'];

                    // Strict Inventory Deduction
                    $this->inventoryService->deductStock(
                        sku: $sku,
                        warehouseId: $request->warehouse_id,
                        qty: $qty,
                        movementType: 'sale',
                        reference: $invoice
                    );

                    $taxableValue = $qty * $price;
                    $taxAmount = $taxableValue * ($taxPct / 100);
                    $lineTotal = $taxableValue + $taxAmount;

                    $totalSubtotal += $taxableValue;
                    $totalTax += $taxAmount;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $sku->product_id,
                        'product_sku_id' => $sku->id,
                        'unit_id' => $item['unit_id'] ?? $sku->unit_id,
                        'product_name' => $item['product_name'],
                        'hsn_code' => $item['hsn_code'] ?? null,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'tax_type' => $item['tax_type'] ?? 'exclusive',
                        'tax_percent' => $taxPct,
                        'taxable_value' => $taxableValue,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $lineTotal,
                    ]);
                }

                // E. Finalize Invoice Totals & GST Splitting
                $discountAmount = (float) $request->input('discount_amount', 0);

                // 🌟 Subtract discount from the raw total
                $rawTotal = ($totalSubtotal - $discountAmount) + $totalTax;

                $isInterState = strtolower(trim($supplyState)) !== strtolower(trim(Auth::user()->company->state->name ?? ''));
                $roundOff = round(round($rawTotal) - $rawTotal, 2);
                $grandTotal = round($rawTotal);

                $invoice->update([
                    'subtotal' => $totalSubtotal,

                    // 🌟 SAVE THE DISCOUNT TO THE DB
                    'discount_type' => $request->input('discount_type') === 'percent' ? 'percentage' : $request->input('discount_type', 'fixed'),
                    'discount_value' => $request->input('discount_value', 0),
                    'discount_amount' => $discountAmount,

                    'taxable_amount' => $totalSubtotal,
                    'tax_amount' => $totalTax,
                    'igst_amount' => $isInterState ? $totalTax : 0,
                    'cgst_amount' => ! $isInterState ? ($totalTax / 2) : 0,
                    'sgst_amount' => ! $isInterState ? ($totalTax / 2) : 0,
                    'round_off' => $roundOff,
                    'grand_total' => $grandTotal,
                ]);

                // Best-seller counters — POS sales are always finalized on creation
                InvoiceService::applySaleCounters(
                    $invoice->items()->get(['product_id', 'product_sku_id', 'quantity'])->toArray(),
                    1
                );

                // F. Record the Payment
                $amountReceived = (float) $request->amount_received;
                if ($amountReceived > 0 && $request->payment_method_id) {
                    $this->paymentService->recordPayment($invoice, [
                        'amount' => $amountReceived,
                        'payment_method_id' => $request->payment_method_id,
                        'payment_date' => now(),
                        'status' => 'completed',
                        'notes' => 'POS Checkout',
                    ]);
                }

                return $invoice;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction completed successfully.',
                'invoice_id' => $invoice->id,
            ]);

        } catch (Exception $e) {
            Log::error('POS Checkout Failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),

                'company_id' => $companyId ?? null,
                'store_id' => $storeId ?? null,
                'warehouse_id' => $request->warehouse_id ?? null,
                'customer_id' => $request->customer_id ?? null,

                'items_count' => count($request->items ?? []),

                'payload' => $request->all(), // 🔥 very useful for debugging
            ]);

            if ($e instanceof InsufficientStockException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stock error: '.$e->getMessage(),
                ], 422);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Checkout failed. '.$e->getMessage(),
            ], 500);
        }
    }

    private function mapGstTreatment(?string $type): string
    {
        $map = [
            'registered' => 'registered',
            'unregistered' => 'unregistered',
            'composition' => 'composition',
            'overseas' => 'overseas',
            'sez' => 'sez',
        ];

        return $map[strtolower($type ?? '')] ?? 'unregistered';
    }
}
