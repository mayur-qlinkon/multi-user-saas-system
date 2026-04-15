<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePurchaseRequest;
use App\Http\Requests\Admin\UpdatePurchaseRequest;
use App\Models\ProductSku;
use App\Models\Purchase;
use App\Models\PurchaseReturnItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    /**
     * Display a listing of the purchases.
     */
    public function index(Request $request)
    {
        // Tenantable trait automatically restricts this to the current company
        $query = Purchase::with(['supplier', 'warehouse', 'store'])->latest();

        // 1. Text Search (PO Number, Invoice, Supplier Name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Status Filter (Added)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Payment Status Filter (Added)
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 4. Paginate and append query string (Crucial for Page 2, Page 3 etc.)
        $purchases = $query->paginate(15)->withQueryString();

        return view('admin.purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create(Request $request)
    {
        // Fetch active masters for the dropdowns
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->get();

        // 🌟 CATCH THE REORDER DATA
        $prefillSkuId = $request->query('sku_id');
        $prefillQty = $request->query('qty');

        $selectedSku = null;
        if ($prefillSkuId) {
            // Eager load product to get the name and details for the frontend
            $selectedSku = ProductSku::with('product')->find($prefillSkuId);
        }

        $units = Unit::where('is_active', true)->get();

        return view('admin.purchases.create', [
            'suppliers' => $suppliers,
            'stores' => $stores,
            'warehouses' => $warehouses,
            'units' => $units,
            'batchEnabled' => batch_enabled(),
            'selectedSku' => $selectedSku, // 👈 Pass to view
            'prefillQty' => $prefillQty,
        ]);
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(StorePurchaseRequest $request)
    {
        try {
            // Pass validated data to our robust service
            $purchase = $this->purchaseService->createPurchase($request->validated());

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Purchase Order created!', 'redirect' => route('admin.purchases.show', $purchase->id)]);
            }

            return redirect()->route('admin.purchases.show', $purchase->id)
                ->with('success', 'Purchase Order generated successfully.');

        } catch (\Exception $e) {
            Log::error('Purchase Creation Failed: '.$e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', 'Failed to create purchase: '.$e->getMessage());
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        // Eager load everything needed for the view/invoice template
        $purchase->load([
            'supplier',
            'store',
            'warehouse',
            'creator',
            'updater',
            'items.product',
            'items.productSku',
            'items.unit',
        ]);

        return view('admin.purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit(Purchase $purchase)
    {
        // 🛡️ ERP GUARD: Do not allow editing financial data of received purchases
        if ($purchase->status === 'received') {
            return redirect()->route('admin.purchases.show', $purchase->id)
                ->with('error', 'Cannot edit a fully received purchase. Please process a Purchase Return to make adjustments.');
        }

        $purchase->load(['items.product', 'items.productSku', 'items.unit']);

        $purchase->items->each(function ($item) {
            if ($item->manufacturing_date) {
                $item->manufacturing_date = Carbon::parse($item->manufacturing_date)->format('d-m-Y');
            }
            if ($item->expiry_date) {
                $item->expiry_date = Carbon::parse($item->expiry_date)->format('d-m-Y');
            }
        });

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $units = Unit::where('is_active', true)->get();

        return view('admin.purchases.edit', [
            'purchase' => $purchase,
            'suppliers' => $suppliers,
            'stores' => $stores,
            'warehouses' => $warehouses,
            'units' => $units,
            'batchEnabled' => batch_enabled(),
        ]);
    }

    /**
     * Update the specified purchase in storage.
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        try {
            $this->purchaseService->updatePurchase($purchase, $request->validated());

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Purchase Order updated!', 'redirect' => route('admin.purchases.show', $purchase->id)]);
            }

            return redirect()->route('admin.purchases.show', $purchase->id)
                ->with('success', 'Purchase Order updated successfully.');

        } catch (\Exception $e) {
            Log::error('Purchase Update Failed: '.$e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', 'Failed to update purchase: '.$e->getMessage());
        }
    }

    /**
     * API Endpoint: Fetch Purchase Order details for processing a return.
     *
     * * @param int $id
     * @return JsonResponse
     */
    /**
     * API Endpoint: Fetch Purchase Order details for processing a return.
     */
    public function getForReturn(Request $request, $id)
    {
        $purchase = Purchase::with(['items.product', 'items.productSku', 'items.unit'])
            ->where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        $excludeReturnId = $request->query('exclude_return_id');

        // 1. Get all previously returned quantities for these PO items in one single query
        $purchaseItemIds = $purchase->items->pluck('id');

        $returnedQuery = PurchaseReturnItem::whereIn('purchase_item_id', $purchaseItemIds)
            ->whereHas('purchaseReturn', function ($q) {
                $q->where('status', '!=', 'cancelled'); // Count both Drafts and Returned
            });

        // If we are editing an existing return via AJAX, don't count its own items against the limit!
        if ($excludeReturnId) {
            $returnedQuery->where('purchase_return_id', '!=', $excludeReturnId);
        }

        $returnedQuantities = $returnedQuery
            ->selectRaw('purchase_item_id, SUM(quantity) as total_returned')
            ->groupBy('purchase_item_id')
            ->pluck('total_returned', 'purchase_item_id');

        // 2. Map available quantities and filter out items that are fully returned
        $filteredItems = $purchase->items->map(function ($item) use ($returnedQuantities) {
            $returned = (float) $returnedQuantities->get($item->id, 0);
            $item->available_qty = max(0, (float) $item->quantity - $returned);

            return $item;
        })->filter(function ($item) {
            return $item->available_qty > 0; // Hide rows that are already 100% returned!
        })->values();

        $purchase->setRelation('items', $filteredItems);

        return response()->json($purchase);
    }

    /**
     * Update only the payment status and balance of a Purchase Order.
     */
    public function updatePayment(Request $request, Purchase $purchase)
    {
        try {
            $request->validate([
                'payment_status' => ['required', 'string', 'in:unpaid,partial,paid'],
                'amount_paid' => ['nullable', 'numeric', 'min:0'],
            ]);

            $status = $request->payment_status;
            $total = $purchase->total_amount;
            $paid = (float) $request->amount_paid;

            // Smart Balance Math
            if ($status === 'paid') {
                $balance = 0;
            } elseif ($status === 'unpaid') {
                $balance = $total;
            } else {
                // Partial Logic
                if ($paid >= $total) {
                    $status = 'paid';
                    $balance = 0;
                } else {
                    $balance = max(0, $total - $paid);
                }
            }

            // Force update the database
            $purchase->update([
                'payment_status' => $status,
                'balance_amount' => $balance,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Payment Update Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy(Purchase $purchase)
    {
        // 🛡️ ERP GUARD: Never delete received purchases. It breaks the stock ledger.
        if ($purchase->status === 'received') {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete a received purchase. Stock is already in the warehouse.'], 403);
            }

            return back()->with('error', 'Cannot delete a received purchase. Stock is already in the warehouse.');
        }

        DB::transaction(function () use ($purchase) {
            // Delete items first (Cascade handles this usually, but explicit is safer)
            $purchase->items()->delete();
            $purchase->delete();
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Purchase Order deleted successfully.']);
        }

        return redirect()->route('admin.purchases.index')
            ->with('success', 'Purchase Order deleted successfully.');
    }

    public function downloadPdf(Purchase $purchase)
    {
        $purchase->load(['supplier', 'store', 'warehouse', 'items.product', 'items.productSku', 'items.unit']);

        $pdf = Pdf::loadView('admin.purchases.pdf', compact('purchase'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Purchase_Order_'.$purchase->purchase_number.'.pdf');
    }
}
