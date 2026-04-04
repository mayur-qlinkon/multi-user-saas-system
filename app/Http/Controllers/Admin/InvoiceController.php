<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInvoiceRequest;
use App\Http\Requests\Admin\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Warehouse;
use App\Models\Company;
use App\Models\Unit;
use App\Models\Store;
use App\Models\State;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\InventoryService;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;
    protected PaymentService $paymentService;
    protected InventoryService $inventoryService;

    public function __construct(
        InvoiceService $invoiceService, 
        PaymentService $paymentService,
        InventoryService $inventoryService
    ) {
        $this->invoiceService = $invoiceService;
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a list of all invoices (Unified: POS + Direct)
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['client', 'creator','payments'])->latest();

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Show the dedicated B2B/Direct Invoice creation form
     */
    public function create()
    {
        $company = Company::first();
        $companyState = $company->state->name ?? 'Unknown';        
        $clients    = Client::where('is_active', true)->get();
        $warehouses = Warehouse::all();
        $stores     = Store::all();
        $units      = Unit::all();
        $states     = State::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.invoices.create', compact('clients', 'warehouses', 'stores', 'units','companyState','states'));
    }

    /**
     * Store a new Invoice and trigger Stock/Payment
     */
    public function store(StoreInvoiceRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                
                $validated = $request->validated();
                
                // 1. Create Invoice via Service (Handles GST & Stock)
                $invoice = $this->invoiceService->createInvoice(
                    $validated, 
                    Auth::user()->company_id
                );

                // 2. Handle Initial Payment if provided           
                if (!empty($validated['amount_paid']) && $validated['amount_paid'] > 0) {
                    // 🌟 Pass the RAW amount the customer handed over directly to the service!
                    $this->paymentService->recordPayment($invoice, [
                        'amount'            => $validated['amount_paid'],
                        'payment_method_id' => $validated['payment_method_id'] ?? null,
                        'payment_date'      => now(),
                        'status'            => 'completed',
                        'notes'             => 'Initial payment received at invoice creation.'
                    ]);
                }

                return redirect()->route('admin.invoices.show', $invoice->id)
                                 ->with('success', 'Invoice generated successfully!');
            });

        } catch (\Exception $e) {
            Log::error("Invoice Creation Failed: " . $e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * View detailed Invoice (The Bill Preview)
     */
   public function show(Invoice $invoice)
    {
        // 🌟 Added 'company' and 'store.state' to eager load the new header data!
        $invoice->load([
            'items.sku.product', 
            'client', 
            'payments.paymentMethod', 
            'returns',
            'stockMovements',
            'company', 
            'store.state'
        ]);
        
        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Cannot edit a cancelled invoice.');
        }
        $company = Company::first();
        $companyState = $company->state->name ?? 'Unknown';       
        $clients    = Client::where('is_active', true)->get();
        $warehouses = Warehouse::all();
        $stores     = Store::all();
        $units      = Unit::all();

        $invoice->load(['items.sku', 'payments']);

        return view('admin.invoices.edit', compact('invoice', 'clients', 'warehouses', 'stores', 'units','companyState'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        try {
            return DB::transaction(function () use ($request, $invoice) {
                
                $validated = $request->validated();

                // 1. Update Invoice (Reverses old stock, updates rows, deducts new stock)
                $invoice = $this->invoiceService->updateInvoice(
                    $invoice,
                    $validated,
                    Auth::user()->company_id
                );

                // 2. Sync Payments (Updates existing, creates new, or deletes if set to 0)               
                if (isset($validated['amount_paid'])) {
                    // 🌟 Pass the RAW amount received
                    $this->paymentService->updateInitialPayment($invoice, [
                        'amount' => $validated['amount_paid'],
                        'payment_method_id' => $validated['payment_method_id'] ?? null,
                    ]);
                }
                
                return redirect()->route('admin.invoices.show', $invoice->id)
                                 ->with('success', 'Invoice updated successfully!');
            });
        } catch (\Exception $e) {
            Log::error("Invoice Update Failed: " . $e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Add a payment to an existing invoice from the Index/Show page
     */
    public function addPayment(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Cannot add payments to a cancelled invoice.');
        }

        // 1. Validate the incoming request
        $request->validate([
            'amount_paid'       => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        // 2. Security: Calculate actual balance due directly from the database
        $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
        $balanceDue = round($invoice->grand_total - $totalPaid, 2);

        if ($balanceDue <= 0) {
            return back()->with('error', 'This invoice is already fully paid.');
        }

        // 3. Security: Prevent overpayment (Strict mode)
        if (round($request->amount_paid, 2) > $balanceDue) {
            return back()->with('error', "Payment cannot exceed the balance due of ₹{$balanceDue}");
        }

        try {
            DB::transaction(function () use ($request, $invoice) {
                // 4. Record the payment using our robust service
                // (This will automatically trigger syncDocumentPaymentStatus to update the Invoice to 'partial' or 'paid')
                $this->paymentService->recordPayment($invoice, [
                    'amount'            => $request->amount_paid,
                    'payment_method_id' => $request->payment_method_id,
                    'payment_date'      => now(),
                    'status'            => 'completed',
                    'notes'             => 'Payment added from invoice list.'
                ]);
            });

            return back()->with('success', 'Payment recorded successfully!');
        } catch (\Exception $e) {
            Log::error("Quick Payment Failed: " . $e->getMessage());
            return back()->with('error', 'Failed to record payment. Please try again.');
        }
    }

    /**
     * Download Invoice as PDF
     */
    public function downloadPdf(Invoice $invoice)
    {
        // Load all required relations
        $invoice->load([
            'items.sku.product', 
            'client', 
            'payments.paymentMethod',
            'company',
            'store.state'
        ]);
        $companyInfo = $invoice->store ?? Auth::user()->company;

        // Load the view and pass data
        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice', 'companyInfo'));
        
        // Optional: Configure PDF settings for A4 paper
        $pdf->setPaper('A4', 'portrait');

        // Download the file
        $safeFilename = str_replace(['/', '\\'], '-', $invoice->invoice_number);

        // Download the file safely
        return $pdf->download('Invoice-' . $safeFilename . '.pdf');
    }

    /**
     * Cancel an Invoice (Reverse Stock & Ledger)
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Invoice is already cancelled.');
        }

        try {
            DB::transaction(function () use ($invoice) {
                // 🌟 Delegate entirely to the Service
                $this->invoiceService->cancelInvoice($invoice);
            });

            return back()->with('success', 'Invoice has been cancelled and stock reversed.');
        } catch (\Exception $e) {
            Log::error("Invoice Cancellation Failed: " . $e->getMessage());
            return back()->with('error', 'Failed to cancel invoice: ' . $e->getMessage());
        }
    }
}