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
use App\Models\Quotation;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
                'recent_activities' => collect(),
            ],
        ];

        try {
            // Pre-load relations consumed by this controller AND the admin layout view composer.
            // loadMissing is idempotent — the composer's later call on the same $user instance
            // becomes a complete no-op, eliminating the duplicate roles + permissions queries.
            $user->loadMissing(['roles.permissions', 'stores', 'employee']);

            // Use the already-loaded collection — no raw DB query (hasRole() always hits DB).
            $isOwner = $user->roles->contains('slug', 'owner') || $user->id === 1;
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
            $data['tables']['recent_activities'] = $this->getRecentActivities($companyId);

        } catch (Exception $e) {
            Log::error('Dashboard Aggregation Failed', [
                'user_id' => $user->id ?? 'Unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // $request->session()->flash('warning', 'Some dashboard metrics could not be loaded at this time.');
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
        $todayDate = $today->toDateString();

        // 💳 Sales this month + today in one query (conditional SUM avoids a second round-trip).
         $storeIds = dashboard_store_ids(); // null = owner sees all, array = staff sees own store(s)

        $invoiceAgg = Invoice::where('company_id', $companyId)
            ->when($storeIds, fn ($q) => $q->whereIn('store_id', $storeIds))
            ->where('status', '!=', 'cancelled')
            ->where('invoice_date', '>=', $startOfMonth)
            ->selectRaw(
                'SUM(grand_total) as sales_this_month,
                 SUM(CASE WHEN DATE(invoice_date) = ? THEN grand_total ELSE 0 END) as sales_today',
                [$todayDate]
            )
            ->first();

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
            'sales_this_month' => (float) ($invoiceAgg->sales_this_month ?? 0),
            'sales_today' => (float) ($invoiceAgg->sales_today ?? 0),
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
        $todayDate = $today->toDateString();

        $storeIds = dashboard_store_ids(); // null = owner sees all, array = staff sees own store(s)
        // 🛒 Purchases this month + today in one query.
        $purchaseAgg = Purchase::where('company_id', $companyId)
            ->when($storeIds, fn ($q) => $q->whereIn('store_id', $storeIds))
            ->where('status', '!=', 'cancelled')
            ->where('purchase_date', '>=', $startOfMonth)
            ->selectRaw(
                'SUM(total_amount) as purchases_this_month,
                 SUM(CASE WHEN DATE(purchase_date) = ? THEN total_amount ELSE 0 END) as purchases_today',
                [$todayDate]
            )
            ->first();

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
            'purchases_this_month' => (float) ($purchaseAgg->purchases_this_month ?? 0),
            'purchases_today' => (float) ($purchaseAgg->purchases_today ?? 0),
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

        $storeIds = dashboard_store_ids(); // null = owner sees all, array = staff sees own store(s)
        // 📊 Weekly Sales Chart (Grouped by Date)
        $weeklySales = Invoice::where('company_id', $companyId)
            ->when($storeIds, fn ($q) => $q->whereIn('store_id', $storeIds))
            ->where('status', '!=', 'cancelled')
            ->where('invoice_date', '>=', $last7Days)
            ->selectRaw('DATE(invoice_date) as date, SUM(grand_total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();
        // 📊 Weekly Purchases Chart (Grouped by Date)
        $weeklyPurchases = Purchase::where('company_id', $companyId)
            ->when($storeIds, fn ($q) => $q->whereIn('store_id', $storeIds))
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
        // 🌟 FIX 1: Eager load 'payments' to prevent N+1 Database Timeouts
        return Invoice::with(['client', 'payments'])
            ->where('company_id', $companyId)
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($invoice) {
                // 🌟 FIX 2: Calculate from the pre-loaded collection (no extra DB queries!)
                $paidAmount = $invoice->payments->where('status', 'completed')->sum('amount');

                // 🌟 FIX 3: Cast to (float) to prevent PHP 8 crashes if grand_total is ever NULL
                $grandTotal = (float) ($invoice->grand_total ?? 0);
                $dueAmount = $grandTotal - $paidAmount;

                return [
                    'reference' => $invoice->invoice_number,
                    'customer' => $invoice->customer_name ?: ($invoice->client->name ?? 'Walk-in Customer'),
                    'status' => $invoice->status,
                    'grand_total' => $grandTotal,
                    'paid' => $paidAmount,
                    'due' => $dueAmount > 0 ? $dueAmount : 0,
                    'payment_status' => $invoice->payment_status,
                ];
            });
    }
    /**
     * Aggregates recent activity across Sales, Purchases, Expenses & Quotations.
     */
    private function getRecentActivities(?int $companyId)
    {
        if (! $companyId) return collect();

        $invoices = Invoice::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->latest('created_at')->take(6)
            ->get(['id', 'invoice_number', 'customer_name', 'grand_total', 'created_at'])
            ->map(fn ($r) => [
                'type'        => 'sale',
                'icon'        => 'shopping-cart',
                'color'       => 'emerald',
                'label'       => 'New Sale',
                'reference'   => $r->invoice_number,
                'description' => $r->customer_name ?: 'Walk-in Customer',
                'amount'      => (float) $r->grand_total,
                'time'        => $r->created_at,
            ]);

        $purchases = Purchase::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->latest('created_at')->take(6)
            ->get(['id', 'purchase_number', 'total_amount', 'created_at'])
            ->map(fn ($r) => [
                'type'        => 'purchase',
                'icon'        => 'package',
                'color'       => 'blue',
                'label'       => 'Purchase Added',
                'reference'   => $r->purchase_number,
                'description' => 'Stock purchase recorded',
                'amount'      => (float) $r->total_amount,
                'time'        => $r->created_at,
            ]);

        $expenses = Expense::where('company_id', $companyId)
            ->whereNotIn('status', ['draft', 'rejected'])
            ->latest('created_at')->take(6)
            ->get(['id', 'reference_number', 'notes', 'total_amount', 'created_at'])
            ->map(fn ($r) => [
                'type'        => 'expense',
                'icon'        => 'receipt-indian-rupee',
                'color'       => 'orange',
                'label'       => 'Expense Added',
                'reference'   => $r->reference_number ?: 'EXP-' . $r->id,
                'description' => $r->notes ? Str::limit($r->notes, 40) : 'General Expense',
                'amount'      => (float) $r->total_amount,
                'time'        => $r->created_at,
            ]);

        $quotations = Quotation::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->latest('created_at')->take(6)
            ->get(['id', 'quotation_number', 'customer_name', 'grand_total', 'created_at'])
            ->map(fn ($r) => [
                'type'        => 'quotation',
                'icon'        => 'file-text',
                'color'       => 'violet',
                'label'       => 'Quotation Created',
                'reference'   => $r->quotation_number,
                'description' => $r->customer_name ?: 'Walk-in Customer',
                'amount'      => (float) $r->grand_total,
                'time'        => $r->created_at,
            ]);

        return $invoices->concat($purchases)->concat($expenses)->concat($quotations)
            ->sortByDesc('time')
            ->take(10)
            ->values();
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
