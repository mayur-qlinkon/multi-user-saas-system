<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get Sales Summary (Gross Sales, Returns, Net Sales)
     */
    public function getSalesSummary(int $companyId, string $startDate, string $endDate)
    {
        // 1. Gross Sales (Confirmed Invoices)
        $grossSales = Invoice::where('company_id', $companyId)
            ->where('status', 'confirmed')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('grand_total');

        // 2. Total Returns (Confirmed Credit Notes)
        $totalReturns = DB::table('invoice_returns')
            ->where('company_id', $companyId)
            ->where('status', 'confirmed')
            ->whereBetween('return_date', [$startDate, $endDate])
            ->sum('grand_total');

        return [
            'gross_sales' => $grossSales,
            'returns' => $totalReturns,
            'net_sales' => $grossSales - $totalReturns,
        ];
    }

    /**
     * Get Purchase Summary
     */
    public function getPurchaseSummary(int $companyId, string $startDate, string $endDate)
    {
        $purchases = Purchase::where('company_id', $companyId)
            ->whereIn('status', ['received', 'partially_received'])
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('total_amount');

        $returns = DB::table('purchase_returns')
            ->where('company_id', $companyId)
            ->where('status', 'returned')
            ->whereBetween('return_date', [$startDate, $endDate])
            ->sum('total_amount');

        return [
            'total_purchases' => $purchases,
            'purchase_returns' => $returns,
            'net_purchases' => $purchases - $returns,
        ];
    }

    /**
     * Get Top Selling Products (By Quantity or Revenue)
     */
    public function getProductPerformance(int $companyId, string $startDate, string $endDate, int $limit = 10, string $orderBy = 'desc')
    {
        // We join invoice_items with invoices to ensure we only count 'confirmed' sales
        return InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('product_skus', 'invoice_items.product_sku_id', '=', 'product_skus.id')
            ->join('products', 'product_skus.product_id', '=', 'products.id')
            ->where('invoices.company_id', $companyId)
            ->where('invoices.status', 'confirmed')
            ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
            ->select(
                'products.name as product_name',
                'product_skus.sku as sku_code',
                DB::raw('SUM(invoice_items.quantity) as total_qty_sold'),
                DB::raw('SUM(invoice_items.total_amount) as total_revenue')
            )
            ->groupBy('product_skus.id', 'products.name', 'product_skus.sku')
            // Order by highest or lowest quantity sold
            ->orderBy('total_qty_sold', $orderBy)
            ->limit($limit)
            ->get();
    }

    /**
     * Get Sales grouped by Source (POS vs Online vs Direct)
     */
    public function getSalesBySource(int $companyId, string $startDate, string $endDate)
    {
        return Invoice::where('company_id', $companyId)
            ->where('status', 'confirmed')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->select('source', DB::raw('SUM(grand_total) as total_revenue'), DB::raw('COUNT(id) as invoice_count'))
            ->groupBy('source')
            ->get();
    }
}
