<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInvoiceReturnRequest;
use App\Http\Requests\Admin\UpdateInvoiceReturnRequest;
use App\Models\Invoice;
use App\Models\InvoiceReturn;
use App\Models\Store;
use App\Models\State;
use App\Models\Warehouse;
use App\Services\InvoiceReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class InvoiceReturnController extends Controller
{
    protected InvoiceReturnService $returnService;

    // 🌟 Inject our powerful service
    public function __construct(InvoiceReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Display a listing of Credit Notes (Returns).
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        // 1. Start the Base Query and lock it to the Company
        $query = InvoiceReturn::with(['customer', 'invoice'])
            ->where('company_id', $companyId);

        // 2. Apply Search Logic (Safely Grouped!)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            
            // The closure function($q) puts parentheses around the OR statements in SQL
            // e.g., WHERE company_id = 1 AND (credit_note_number LIKE %..% OR customer_name LIKE %..%)
            $query->where(function ($q) use ($searchTerm) {
                $q->where('credit_note_number', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_name', 'like', "%{$searchTerm}%");
            });
        }

        // 3. Apply Status Filter (Draft / Confirmed)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 4. Apply Return Type Filter (Refund / Credit Note / Replacement)
        if ($request->filled('return_type')) {
            $query->where('return_type', $request->return_type);
        }

        // 5. Order, Paginate, and preserve URL parameters (withQueryString)
        $returns = $query->latest('return_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.invoice-returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a return.
     * Note: We specifically pass the original Invoice here!
     */
    public function create(Invoice $invoice)
    {
        abort_if($invoice->company_id !== Auth::user()->company_id, 403);
        
        if ($invoice->status === 'draft' || $invoice->status === 'cancelled') {
            return back()->with('error', 'You can only create a return for a confirmed invoice.');
        }

        // Load the original invoice items with their SKUs
        $invoice->load(['items.sku', 'client']);
        
        $companyId = Auth::user()->company_id;
        $stores = Store::where('company_id', $companyId)->where('is_active', true)->get();
        $warehouses = Warehouse::where('company_id', $companyId)->get();
        $states = State::where('is_active', true)->orderBy('name')->get();
        $companyState = Auth::user()->company->state->name ?? 'Unknown';

        return view('admin.invoice-returns.create', compact('invoice', 'stores', 'warehouses', 'states', 'companyState'));
    }

    /**
     * Store a newly created return in storage.
     */
    public function store(StoreInvoiceReturnRequest $request, Invoice $invoice)
    {
        abort_if($invoice->company_id !== Auth::user()->company_id, 403);

        try {
            // Let the service handle the math and DB insertion
            $invoiceReturn = $this->returnService->createReturn($invoice, $request->validated());

            return redirect()->route('admin.invoice-returns.show', $invoiceReturn->id)
                ->with('success', 'Draft Credit Note created successfully. Please review and confirm.');

        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create return: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified return.
     */
    public function show(InvoiceReturn $invoiceReturn)
    {
        abort_if($invoiceReturn->company_id !== Auth::user()->company_id, 403);

        $invoiceReturn->load(['items.product', 'items.sku', 'customer', 'store', 'warehouse', 'invoice']);
        
        return view('admin.invoice-returns.show', compact('invoiceReturn'));
    }

    /**
     * Show the form for editing the return.
     */
    public function edit(InvoiceReturn $invoiceReturn)
    {
        abort_if($invoiceReturn->company_id !== Auth::user()->company_id, 403);
        
        if ($invoiceReturn->status === 'confirmed') {
            return redirect()->route('admin.invoice-returns.show', $invoiceReturn->id)
                ->with('error', 'Confirmed Credit Notes cannot be edited. They are locked for accounting.');
        }

        $invoiceReturn->load(['items', 'invoice.items']);
        $invoice = $invoiceReturn->invoice; // Need original invoice context for the UI
        
        $companyId = Auth::user()->company_id;
        $stores = Store::where('company_id', $companyId)->where('is_active', true)->get();
        $warehouses = Warehouse::where('company_id', $companyId)->get();
        $states = State::where('is_active', true)->orderBy('name')->get();
        $companyState = Auth::user()->company->state->name ?? 'Unknown';

        return view('admin.invoice-returns.edit', compact('invoiceReturn', 'invoice', 'stores', 'warehouses', 'states', 'companyState'));
    }

    /**
     * Update the specified return.
     */
    public function update(UpdateInvoiceReturnRequest $request, InvoiceReturn $invoiceReturn)
    {
        abort_if($invoiceReturn->company_id !== Auth::user()->company_id, 403);

        try {
            $this->returnService->updateReturn($invoiceReturn, $request->validated());

            return redirect()->route('admin.invoice-returns.show', $invoiceReturn->id)
                ->with('success', 'Credit Note updated successfully.');

        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update return: ' . $e->getMessage());
        }
    }

    /**
     * 🟢 THE CRITICAL ERP ACTION: Confirm the return and restore stock.
     */
    public function confirm(InvoiceReturn $invoiceReturn)
    {
        abort_if($invoiceReturn->company_id !== Auth::user()->company_id, 403);

        try {
            // This triggers the InventoryService under the hood!
            $this->returnService->confirmReturn($invoiceReturn);

            return back()->with('success', 'Credit Note confirmed! Stock has been securely returned to the warehouse.');

        } catch (Exception $e) {
            Log::error("Update Failed: " . $e->getMessage());
            return back()->with('error', 'Confirmation failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the draft return.
     */
    public function destroy(InvoiceReturn $invoiceReturn)
    {
        abort_if($invoiceReturn->company_id !== Auth::user()->company_id, 403);
        
        if ($invoiceReturn->status === 'confirmed') {
            return back()->with('error', 'Cannot delete a confirmed Credit Note.');
        }

        $invoiceReturn->delete();

        return redirect()->route('admin.invoice-returns.index')
            ->with('success', 'Draft Credit Note deleted successfully.');
    }
}