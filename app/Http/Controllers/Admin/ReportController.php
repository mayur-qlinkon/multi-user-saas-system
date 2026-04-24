<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected $reportService;

    // Inject our robust service
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // 1. Parse the requested date range
        $dateRange = $this->parseDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $filterLabel = $dateRange['label'];
        $activeFilter = $dateRange['filter'];

        // 2. Fetch Data via Service (Heavy lifting is done efficiently in the DB)
        $salesSummary = $this->reportService->getSalesSummary($companyId, $startDate, $endDate);
        $purchaseSummary = $this->reportService->getPurchaseSummary($companyId, $startDate, $endDate);

        // Fetch top 10 best sellers and top 10 worst sellers
        $topProducts = $this->reportService->getProductPerformance($companyId, $startDate, $endDate, 10, 'desc');
        $lowProducts = $this->reportService->getProductPerformance($companyId, $startDate, $endDate, 10, 'asc');

        $salesBySource = $this->reportService->getSalesBySource($companyId, $startDate, $endDate);

        // 3. Return to the Dashboard View
        return view('admin.reports.analytics', compact(
            'salesSummary',
            'purchaseSummary',
            'topProducts',
            'lowProducts',
            'salesBySource',
            'startDate',
            'endDate',
            'filterLabel',
            'activeFilter'
        ));
    }

    /**
     * Helper logic to calculate precise start and end dates based on user selection.
     */
    private function parseDateRange(Request $request)
    {
        $filter = $request->input('date_filter', 'this_month'); // Default to 'This Month'

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $label = 'This Month';

        switch ($filter) {
            case 'today':
                $start = Carbon::today();
                $end = Carbon::today()->endOfDay();
                $label = 'Today';
                break;
            case 'this_week':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                $label = 'This Week';
                break;
            case 'this_month':
                // Defaults already set
                break;
            case 'this_year':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                $label = 'This Year';
                break;
            case 'custom':
                if ($request->filled(['start_date', 'end_date'])) {
                    $start = Carbon::parse($request->start_date)->startOfDay();
                    $end = Carbon::parse($request->end_date)->endOfDay();
                    $label = $start->format('d M Y').' to '.$end->format('d M Y');
                }
                break;
        }

        return [
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
            'label' => $label,
            'filter' => $filter,
        ];
    }
}
