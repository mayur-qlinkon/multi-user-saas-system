<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch;
use App\Models\ProductSku;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. MASTER STOCK: Consolidated view with warehouse breakdown
        // We use withSum() to let MySQL calculate the total across all warehouses instantly.
        $masterStock = ProductSku::has('product') // 🌟 ADD THIS: Only load SKUs that have a parent product
            ->with([
                'product.category',
                'product.productUnit',
                'stocks.warehouse.store',
            ])
            ->withSum('stocks as total_qty', 'qty')
            ->orderByDesc('total_qty')
            ->paginate(15, ['*'], 'stock_page');

        // 2. LOW STOCK ALERTS: The Action List
        // We use a powerful subquery to compare live total stock against the SKU's alert threshold.
        $lowStockAlerts = ProductSku::has('product') // 🌟 ADD THIS HERE TOO
            ->with(['product.category', 'stocks.warehouse.store'])
            ->where(function ($query) {
                // Subquery to get total physical stock
                $query->selectRaw('COALESCE(SUM(qty), 0)')
                    ->from('product_stocks')
                    ->whereColumn('product_stocks.product_sku_id', 'product_skus.id');
            }, '<=', DB::raw('product_skus.stock_alert')) // Compare against alert level
            ->where('stock_alert', '>', 0) // Only check items that have an alert threshold set
            ->paginate(15, ['*'], 'alert_page');

        // 3. STOCK MOVEMENT LEDGER: The Audit Trail
        // Fetching the chronologically ordered movements with who did it and where it went.
        $movements = StockMovement::with([
            'sku.product',
            'warehouse.store',
            'user:id,name', // Only load the user's ID and Name to save memory
        ])
            ->when($request->search_movement, function ($q, $term) {
                // Optional: Allow searching the ledger by Challan/Invoice reference
                $q->whereHas('sku.product', function ($p) use ($term) {
                    $p->where('name', 'like', "%{$term}%");
                })->orWhere('reference_id', 'like', "%{$term}%");
            })
            ->latest()
            ->paginate(20, ['*'], 'ledger_page');

        // Calculate total inventory valuation across the entire company
        // (Sum of all stock quantities * their respective SKU costs)
        $totalValuation = DB::table('product_stocks')
            ->join('product_skus', 'product_stocks.product_sku_id', '=', 'product_skus.id')
            ->where('product_stocks.company_id', Auth::user()->company_id)
            ->selectRaw('SUM(product_stocks.qty * product_skus.cost) as total')
            ->value('total') ?? 0;

        // 4. BATCH TRACKING REPORT (only when feature is enabled)
        $batchReport = null;
        if (batch_enabled()) {
            $batchReport = ProductBatch::with(['sku.product', 'warehouse', 'supplier'])
                ->where('remaining_qty', '>', 0)
                ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expiry_date')
                ->paginate(20, ['*'], 'batch_page');
        }

        return view('admin.reports.inventory', compact(
            'masterStock',
            'lowStockAlerts',
            'movements',
            'totalValuation',
            'batchReport'
        ));
    }
}
