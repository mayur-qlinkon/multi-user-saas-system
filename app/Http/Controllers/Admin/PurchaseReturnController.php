<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePurchaseReturnRequest;
use App\Http\Requests\Admin\UpdatePurchaseReturnRequest;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Unit;
use App\Services\PurchaseReturnService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $purchaseReturnService
    ) {}

    /**
     * 1. DISPLAY LISTING
     * Standard index with search, filters, and relationships eager-loaded.
     */
    public function index(Request $request)
    {
        // Tenantable trait automatically scopes this to the current company
        $query = PurchaseReturn::with(['supplier', 'warehouse', 'purchase'])->latest();

        // -- Text Search --
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('supplier_credit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('purchase', function ($pq) use ($search) {
                      $pq->where('purchase_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // -- Status Filters --
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $purchaseReturns = $query->paginate(15)->withQueryString();

        return view('admin.purchase-returns.index', compact('purchaseReturns'));
    }

    /**
     * 2. SHOW CREATE FORM
     * If a ?purchase_id=XX is passed, we can pre-load the original PO data.
     */
    public function create(Request $request)
    {
        // We load these so the UI can display them, though realistically 
        // a return inherits the supplier/warehouse of the original PO.
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->get();

        // Optional: If user clicks "Return" from a specific Purchase Order view
        $originalPurchase = null;
        if ($request->filled('purchase_id')) {
            $originalPurchase = Purchase::with(['items.product', 'items.productSku', 'items.unit'])
                ->findOrFail($request->purchase_id);
        }

        return view('admin.purchase-returns.create', compact(
            'suppliers', 
            'warehouses', 
            'stores', 
            'units', 
            'originalPurchase'
        ));
    }

    /**
     * 3. STORE RECORD
     * Wrapped in try-catch with deep logging for developer sanity.
     */
    public function store(StorePurchaseReturnRequest $request)
    {
        try {
            // Service handles Math, DB creation, and Stock Deductions
            $purchaseReturn = $this->purchaseReturnService->createPurchaseReturn($request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Purchase Return created successfully!', 
                    'redirect' => route('admin.purchase-returns.show', $purchaseReturn->id)
                ]);
            }

            return redirect()->route('admin.purchase-returns.show', $purchaseReturn->id)
                             ->with('success', 'Purchase Return generated successfully.');

        } catch (\Exception $e) {
            // 🐛 DEVELOPER DEBUGGING: Logs exactly where it failed without exposing to user
            Log::error('Purchase Return Creation Failed', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
                'data'  => $request->except(['_token']) // Log submitted data for tracing
            ]);
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            
            // Fallback safe: returns user to form with their typed inputs
            return back()->withInput()->with('error', 'Failed to create return: ' . $e->getMessage());
        }
    }

    /**
     * 4. SHOW RECORD
     * Fully eager-loaded for formal invoice/document generation.
     */
    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load([
            'supplier', 
            'store', 
            'warehouse', 
            'purchase',      // The original PO
            'creator', 
            'items.product', 
            'items.productSku', 
            'items.unit',
            'items.originalPurchaseItem' // Optional: if you defined this relation
        ]);

        return view('admin.purchase-returns.show', compact('purchaseReturn'));
    }

    /**
     * 5. SHOW EDIT FORM
     */
    public function edit(PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status === 'returned') {
            return redirect()->route('admin.purchase-returns.show', $purchaseReturn->id)
                             ->with('error', 'Cannot edit a finalized return.');
        }

        $purchaseReturn->load([
            'purchase.items.product', 
            'purchase.items.productSku', 
            'purchase.items.unit',
            'items' 
        ]);
        
        // 🌟 NEW: Calculate available quantities strictly EXCLUDING the current draft return
        $purchaseItemIds = $purchaseReturn->purchase->items->pluck('id');
        
        $returnedQuantities = \App\Models\PurchaseReturnItem::whereIn('purchase_item_id', $purchaseItemIds)
            ->where('purchase_return_id', '!=', $purchaseReturn->id) // Exclude current draft
            ->whereHas('purchaseReturn', function ($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->selectRaw('purchase_item_id, SUM(quantity) as total_returned')
            ->groupBy('purchase_item_id')
            ->pluck('total_returned', 'purchase_item_id');

        foreach ($purchaseReturn->purchase->items as $item) {
            $returned = (float) $returnedQuantities->get($item->id, 0);
            $item->available_qty = max(0, (float) $item->quantity - $returned);
        }
        // ----------

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->get();

        return view('admin.purchase-returns.edit', compact('purchaseReturn', 'suppliers', 'warehouses', 'stores', 'units'));
    }

    /**
     * 6. UPDATE RECORD
     */
    public function update(UpdatePurchaseReturnRequest $request, PurchaseReturn $purchaseReturn)
    {
        try {
            $this->purchaseReturnService->updatePurchaseReturn($purchaseReturn, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Purchase Return updated successfully!', 
                    'redirect' => route('admin.purchase-returns.show', $purchaseReturn->id)
                ]);
            }

            return redirect()->route('admin.purchase-returns.show', $purchaseReturn->id)
                             ->with('success', 'Purchase Return updated successfully.');

        } catch (\Exception $e) {
            Log::error('Purchase Return Update Failed', [
                'return_id' => $purchaseReturn->id,
                'error'     => $e->getMessage(),
                'data'      => $request->except(['_token'])
            ]);
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', 'Failed to update return: ' . $e->getMessage());
        }
    }
    /**
     * Update only the refund/payment status of a Purchase Return.
     */
    public function updatePayment(Request $request, PurchaseReturn $purchaseReturn)
    {
        try {
            $request->validate([
                'payment_status' => ['required', 'string', 'in:pending,adjusted,refunded'],
            ]);

            $purchaseReturn->update([
                'payment_status' => $request->payment_status
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Refund status updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Return Payment Update Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 422);
        }
    }


    /**
     * 7. DESTROY RECORD
     * Safe transactional delete with guards.
     */
    public function destroy(PurchaseReturn $purchaseReturn)
    {
        // 🛡️ ERP GUARD: Never delete finalized returns, it corrupts the stock ledger history.
        if ($purchaseReturn->status === 'returned') {
            $msg = 'Cannot delete a finalized return. Stock has already been deducted.';
            
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        try {
            DB::transaction(function () use ($purchaseReturn) {
                // Delete children first to prevent orphaned records if cascade fails
                $purchaseReturn->items()->delete();
                $purchaseReturn->delete();
            });

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Purchase Return deleted successfully.']);
            }
            
            return redirect()->route('admin.purchase-returns.index')
                             ->with('success', 'Purchase Return deleted successfully.');
                             
        } catch (\Exception $e) {
            Log::error('Purchase Return Deletion Failed', ['return_id' => $purchaseReturn->id, 'error' => $e->getMessage()]);
            
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete record. Please check logs.'], 500);
            }
            return back()->with('error', 'Failed to delete Purchase Return.');
        }
    }
    public function downloadPdf(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['supplier', 'store', 'warehouse', 'purchase', 'items.product', 'items.productSku', 'items.unit']);
        
        $pdf = Pdf::loadView('admin.purchase-returns.pdf', compact('purchaseReturn'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('Return_Note_' . $purchaseReturn->return_number . '.pdf');
    }
}