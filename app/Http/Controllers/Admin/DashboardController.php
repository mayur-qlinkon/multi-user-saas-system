<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceReturn;
use App\Models\Payment;
use App\Models\ProductSku;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // 🛡️ Default safe fallback data payload
        $data = [
            'is_owner' => false,
            'financials' => [
                'sales_this_month' => 0,
                'sales_today' => 0,
                'sales_returns_month' => 0,
                'received_today' => 0,
                // Placeholders for next steps
                'purchases_this_month' => 0,
                'purchases_today' => 0,
                'purchase_returns_month' => 0,
                'expense_today' => 0,
            ],
            'charts' => [
                'weekly_sales' => [],
                'top_customers' => collect(),
                'top_products' => collect(),
            ],
            'tables' => [
                'recent_sales' => collect(),
                'low_stock_skus' => collect(),
            ],
        ];

        try {
            $isOwner = $user->hasRole('owner') || $user->id === 1;
            $data['is_owner'] = $isOwner;

            // 1. Fetch Sales & Financial Metrics (Owner / Admin Only)
            if ($isOwner) {
                $data['financials'] = array_merge(
                    $data['financials'],
                    $this->getSalesMetrics($companyId),
                    $this->getPurchasesAndExpensesMetrics($companyId)
                );
            }

            // 2. Fetch Chart Data
            $data['charts'] = $this->getChartData($companyId);

            // 3. Fetch Table Data
            $data['tables']['recent_sales'] = $this->getRecentSales($companyId);
            $data['tables']['low_stock_skus'] = $this->getLowStockAlerts($companyId);

        } catch (Exception $e) {
            Log::error('Dashboard Aggregation Failed', [
                'user_id' => $user->id ?? 'Unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $request->session()->flash('warning', 'Some dashboard metrics could not be loaded at this time.');
        }

        return view('admin.dashboard', $data);
    }

    // ─────────────────────────────────────────────────────────────────
    // 🛠️ PRIVATE AGGREGATION ENGINES
    // ─────────────────────────────────────────────────────────────────

    /**
     * Gathers Invoice, Sales, and Returns metrics for the top cards.
     */
    private function getSalesMetrics(?int $companyId): array
    {
        // Fallback for super admins with no company
        if (! $companyId) {
            return [
                'sales_this_month' => 0.0,
                'sales_today' => 0.0,
                'sales_returns_month' => 0.0,
                'received_today' => 0.0,
            ];
        }
        $startOfMonth = now()->startOfMonth();
        $today = now()->startOfDay();

        // 💳 Total Sales (This Month)
        $salesThisMonth = Invoice::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->where('invoice_date', '>=', $startOfMonth)
            ->sum('grand_total');

        // 💳 Today Total Sales
        $salesToday = Invoice::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->whereDate('invoice_date', $today)
            ->sum('grand_total');

        // 💳 Sales Returns (This Month)
        $salesReturnsMonth = InvoiceReturn::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->where('return_date', '>=', $startOfMonth)
            ->sum('grand_total');

        // 💳 Received Today (Actual cash/bank inbound from sales)
        $receivedToday = Payment::where('company_id', $companyId)
            ->where('status', 'completed')
            ->where('type', 'received')
            ->whereDate('payment_date', $today)
            ->sum('amount');

        return [
            'sales_this_month' => (float) $salesThisMonth,
            'sales_today' => (float) $salesToday,
            'sales_returns_month' => (float) $salesReturnsMonth,
            'received_today' => (float) $receivedToday,
        ];
    }

    /**
     * Gathers Purchase and Expense metrics for the top cards.
     */
    private function getPurchasesAndExpensesMetrics(?int $companyId): array
    {
        $startOfMonth = now()->startOfMonth();
        $today = now()->startOfDay();

        // 🛒 Total Purchases (This Month)
        $purchasesThisMonth = Purchase::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->where('purchase_date', '>=', $startOfMonth)
            ->sum('total_amount');

        // 🛒 Today Total Purchases
        $purchasesToday = Purchase::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->whereDate('purchase_date', $today)
            ->sum('total_amount');

        // 🛒 Purchase Returns (This Month)
        $purchaseReturnsMonth = PurchaseReturn::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->where('return_date', '>=', $startOfMonth)
            ->sum('total_amount');

        // 💸 Today Total Expense (Excluding rejected or draft expenses)
        $expenseToday = Expense::where('company_id', $companyId)
            ->whereNotIn('status', ['draft', 'rejected'])
            ->whereDate('expense_date', $today)
            ->sum('total_amount');

        return [
            'purchases_this_month' => (float) $purchasesThisMonth,
            'purchases_today' => (float) $purchasesToday,
            'purchase_returns_month' => (float) $purchaseReturnsMonth,
            'expense_today' => (float) $expenseToday,
        ];
    }

    /**
     * Gathers aggregated data for the Bar and Pie Charts.
     */
    private function getChartData(?int $companyId): array
    {
        $startOfMonth = now()->startOfMonth();
        $last7Days = now()->subDays(6)->startOfDay();

        // 📊 Weekly Sales Chart (Grouped by Date)
        $weeklySales = Invoice::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->where('invoice_date', '>=', $last7Days)
            ->selectRaw('DATE(invoice_date) as date, SUM(grand_total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();
        // 📊 Weekly Purchases Chart (Grouped by Date)
        $weeklyPurchases = Purchase::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->where('purchase_date', '>=', $last7Days)
            ->selectRaw('DATE(purchase_date) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // 🥧 Top 5 Customers Pie Chart (Handles Walk-in Customers gracefully)
        $topCustomers = DB::table('invoices')
            ->leftJoin('clients', 'invoices.customer_id', '=', 'clients.id')
            ->where('invoices.company_id', $companyId)
            ->where('invoices.status', '!=', 'cancelled')
            ->where('invoices.invoice_date', '>=', $startOfMonth)
            ->whereNull('invoices.deleted_at')
            // ROOT FIX: Group by the exact raw expression, not the alias
            ->selectRaw('COALESCE(clients.name, invoices.customer_name, "Walk-in Customer") as name, SUM(invoices.grand_total) as total')
            ->groupByRaw('COALESCE(clients.name, invoices.customer_name, "Walk-in Customer")')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // 🥧 Top Selling Products (Uses InvoiceItems joined with Invoices and Units)
        $topProducts = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('units', 'invoice_items.unit_id', '=', 'units.id')
            ->where('invoices.company_id', $companyId)
            ->where('invoices.status', '!=', 'cancelled')
            ->where('invoices.invoice_date', '>=', $startOfMonth)
            ->whereNull('invoices.deleted_at')
            ->selectRaw('invoice_items.product_name, units.short_name as unit_name, SUM(invoice_items.quantity) as total_qty, SUM(invoice_items.total_amount) as total_revenue')
            ->groupBy('invoice_items.product_name', 'invoice_items.product_id', 'units.short_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return [
            'weekly_sales' => $weeklySales,
            'weekly_purchases' => $weeklyPurchases,
            'top_customers' => $topCustomers,
            'top_products' => $topProducts,
        ];
    }

    /**
     * Gathers recent sales table data with calculated due amounts.
     */
    private function getRecentSales(?int $companyId)
    {
        return Invoice::with('client')
            ->where('company_id', $companyId)
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($invoice) {
                // Calculate paid via payments relation
                $paidAmount = $invoice->payments()->where('status', 'completed')->sum('amount');
                $dueAmount = $invoice->grand_total - $paidAmount;

                return [
                    'reference' => $invoice->invoice_number,
                    'customer' => $invoice->customer_name ?: ($invoice->client->name ?? 'Walk-in Customer'),
                    'status' => $invoice->status,
                    'grand_total' => $invoice->grand_total,
                    'paid' => $paidAmount,
                    'due' => $dueAmount > 0 ? $dueAmount : 0,
                    'payment_status' => $invoice->payment_status,
                ];
            });
    }

    /**
     * Gathers system alerts (Low Stock).
     */
    private function getLowStockAlerts(?int $companyId)
    {
        return ProductSku::with('product')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNotNull('stock_alert')
            ->where('stock_alert', '>', 0)
            ->withSum('stocks as current_stock', 'qty')
            ->havingRaw('COALESCE(current_stock, 0) <= stock_alert')
            ->take(8)
            ->get();
    }
}
