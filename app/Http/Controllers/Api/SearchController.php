<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductSku;
use App\Models\ProductStock;
use App\Models\Purchase;

class SearchController extends Controller
{
   public function searchSkus(Request $request)
{
    $term = $request->query('term');
    $warehouseId = $request->query('warehouse_id'); // Optional now
    $inStockOnly = $request->boolean('in_stock_only', false); // The strict switch

    if (!$term) {
        return response()->json([]);
    }

    $companyId = Auth::user()->company_id;

    try {
        // 1. Base Query: Fetch all active SKUs in the company matching the term
        $query = ProductSku::with(['product']) 
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('sku', 'like', "%{$term}%")
                  ->orWhere('barcode', 'like', "%{$term}%")
                  ->orWhereHas('product', function ($sq) use ($term) {
                      $sq->where('name', 'like', "%{$term}%");
                  });
            });

        // 2. Strict Stock Filter (Used ONLY for Invoices/POS)
        // If in_stock_only is true AND a warehouse is provided, enforce stock > 0
        if ($inStockOnly && $warehouseId) {
            $query->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                  ->where('qty', '>', 0);
            });
        }

        $skus = $query->take(20)->get();

        // 3. Fetch Stock Levels (If warehouse is selected, attach current stock for UI)
        $stocks = collect();
        if ($warehouseId && $skus->isNotEmpty()) {
            $skuIds = $skus->pluck('id');
            $stocks = ProductStock::whereIn('product_sku_id', $skuIds)
                        ->where('warehouse_id', $warehouseId)
                        ->get()
                        ->keyBy('product_sku_id');
        }

        // 4. Map the Results
        $results = $skus->map(function ($sku) use ($stocks) {
            // If we fetched stocks, grab it. Otherwise, default to 0.
            $stockRecord = $stocks->get($sku->id);
            $stockQty = $stockRecord ? $stockRecord->qty : 0;

            return [
                'product_sku_id' => $sku->id,
                'product_id'     => $sku->product_id,
                'product_name'   => $sku->product ? $sku->product->name : 'Unknown Product',
                'hsn_code'       => $sku->product ? $sku->product->hsn_code : null,
                'sku_code'       => $sku->sku,
                'price'          => (float) $sku->price, 
                'cost'           => (float) ($sku->cost ?? 0),
                'tax_percent'    => (float) ($sku->order_tax ?? 0), 
                'tax_type'       => $sku->tax_type ?? 'exclusive',
                'unit_id'        => $sku->unit_id ?? $sku->product->product_unit_id ?? null,
                'stock'          => (float) $stockQty, 
            ];
        });

        return response()->json($results);

    } catch (\Exception $e) {
        Log::error('Search SKUs Error: ' . $e->getMessage());
        return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
}
    /**
     * API Endpoint: Search Purchase Orders for Returns.
     * If no term is provided, returns the latest 10 received POs.
     */
    public function searchPurchases(Request $request)
    {
        $term = $request->query('term');
        
        $query = Purchase::with('supplier:id,name')
            ->where('company_id', Auth::user()->company_id)
            ->whereIn('status', ['received', 'partially_received']); // Only received POs can be returned

        // If user typed something, apply the filter
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('purchase_number', 'like', "%{$term}%")
                  ->orWhereHas('supplier', function ($sq) use ($term) {
                      $sq->where('name', 'like', "%{$term}%");
                  });
            });
        }

        // Always get the latest, limit to 10 to keep the dropdown clean
        $purchases = $query->latest()
            ->take(10)
            ->get(['id', 'purchase_number', 'supplier_id', 'total_amount', 'purchase_date']);

        return response()->json($purchases);
    }
}