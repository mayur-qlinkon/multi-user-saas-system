<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuotationRequest;
use App\Http\Requests\Admin\UpdateQuotationRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\State;
use App\Models\Store;
use App\Models\Unit;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    /**
     * Display a listing of the quotations.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $quotations = Quotation::with(['customer', 'creator'])
            ->where('company_id', $companyId)
            ->latest('quotation_date')
            ->latest('id')
            ->paginate(15);

        return view('admin.quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new quotation.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;

        $stores = Store::where('company_id', $companyId)->where('is_active', true)->get();
        $clients = Client::with('state')->where('company_id', $companyId)->where('is_active', true)->get();
        $warehouses = Warehouse::where('company_id', $companyId)->get();
        $states = State::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->get();
        $companyState = Auth::user()->company->state->name ?? 'Unknown';

        return view('admin.quotations.create', compact('stores', 'clients', 'warehouses', 'units', 'companyState', 'states'));
    }

    /**
     * Show the form for editing the quotation.
     */
    public function edit(Quotation $quotation)
    {
        abort_if($quotation->company_id !== Auth::user()->company_id, 403);

        if ($quotation->status === 'converted') {
            return redirect()->route('admin.quotations.show', $quotation->id)
                ->with('error', 'Converted quotations cannot be edited.');
        }

        $quotation->load('items');

        $stores = Store::where('company_id', $quotation->company_id)->where('is_active', true)->get();
        $clients = Client::with('state')->where('company_id', $quotation->company_id)->where('is_active', true)->get();
        $warehouses = Warehouse::where('company_id', $quotation->company_id)->get();
        $units = Unit::where('is_active', true)->get();
        $companyState = Auth::user()->company->state->name ?? 'Unknown';

        return view('admin.quotations.edit', compact('quotation', 'stores', 'clients', 'warehouses', 'units', 'companyState'));
    }

    /**
     * Store a newly created quotation in storage.
     */
    public function store(StoreQuotationRequest $request)
    {
        try {
            DB::beginTransaction();

            $companyId = Auth::user()->company_id;
            $company = Auth::user()->company;

            // 1. Generate Quotation Number (Bulletproof Logic preventing Duplicate Entry errors)
            $prefix = 'QT-'.date('ym');
            $latestQuotation = Quotation::withTrashed()
                ->where('company_id', $companyId)
                ->where('quotation_number', 'like', "{$prefix}-%")
                ->orderBy('quotation_number', 'desc')
                ->first();

            if ($latestQuotation) {
                $lastSequence = (int) substr($latestQuotation->quotation_number, -4);
                $nextSequence = $lastSequence + 1;
            } else {
                $nextSequence = 1;
            }
            $quotationNumber = $prefix.'-'.str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            // 2. Resolve Customer Snapshot Data
            $client = $request->customer_id ? Client::find($request->customer_id) : null;
            $customerName = $client ? $client->name : $request->customer_name;
            $supplyState = $request->supply_state ?? ($client ? ($client->state->name ?? 'Gujarat') : $company->state->name);

            // 🌟 3. CALCULATE EVERYTHING IN MEMORY FIRST (Fixes Double-Logging)
            $mathEngine = $this->calculateQuotationMath($request->validated(), $supplyState, $company->state->name ?? '');

            // Accept status from the request (Save as Draft vs Save & Mark as Sent).
            $status = in_array($request->input('status'), ['draft', 'sent'], true)
                ? $request->input('status')
                : 'draft';
            $isSent = $status === 'sent';

            // 🌟 4. CREATE QUOTATION ONCE (Combines form data + calculated totals)
            $quotation = Quotation::create(array_merge([
                'company_id' => $companyId,
                'store_id' => $request->store_id,
                'customer_id' => $client->id ?? null,
                'customer_name' => $customerName,
                'customer_phone' => $request->customer_phone ?? ($client->phone ?? null),
                'customer_email' => $request->customer_email ?? ($client->email ?? null),
                'customer_gstin' => $request->customer_gstin ?? ($client->gst_number ?? null),
                'billing_address' => $request->billing_address ?? null,
                'shipping_address' => $request->shipping_address ?? null,
                'created_by' => Auth::id(),
                'quotation_number' => $quotationNumber,
                'reference_number' => $request->reference_number,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'supply_state' => $supplyState,
                'gst_treatment' => $request->gst_treatment,
                'currency_code' => $request->currency_code ?? 'INR',
                'exchange_rate' => $request->exchange_rate ?? 1.0000,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'status' => $status,
                'is_sent' => $isSent,
                'sent_at' => $isSent ? now() : null,
                'sent_by' => $isSent ? Auth::id() : null,
            ], $mathEngine['header_totals']));

            // 5. INSERT LINE ITEMS
            foreach ($mathEngine['line_items'] as $itemData) {
                $itemData['quotation_id'] = $quotation->id;
                QuotationItem::create($itemData);
            }

            DB::commit();

            return redirect()->route('admin.quotations.show', $quotation->id)
                ->with('success', 'Quotation created successfully!');

        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to create quotation. '.$e->getMessage());
        }
    }

    /**
     * Display the specified quotation.
     */
    public function show(Quotation $quotation)
    {
        abort_if($quotation->company_id !== Auth::user()->company_id, 403);
        $quotation->load(['items', 'customer', 'store', 'creator']);

        return view('admin.quotations.show', compact('quotation'));
    }

    /**
     * Update the specified quotation in storage.
     */
    public function update(UpdateQuotationRequest $request, Quotation $quotation)
    {
        abort_if($quotation->company_id !== Auth::user()->company_id, 403);

        try {
            DB::beginTransaction();

            $company = Auth::user()->company;
            $client = $request->customer_id ? Client::find($request->customer_id) : null;
            $customerName = $client ? $client->name : $request->customer_name;
            $supplyState = $request->supply_state ?? ($client ? ($client->state->name ?? 'Gujarat') : $company->state->name);

            // 🌟 1. CALCULATE EVERYTHING IN MEMORY FIRST
            $mathEngine = $this->calculateQuotationMath($request->validated(), $supplyState, $company->state->name ?? '');

            // Decide the status for this update:
            //  - If the form explicitly posts 'draft' or 'sent', use it.
            //  - Otherwise keep the existing status untouched.
            $submittedStatus = $request->input('status');
            $status = in_array($submittedStatus, ['draft', 'sent'], true)
                ? $submittedStatus
                : $quotation->status;

            $headerUpdate = array_merge([
                'store_id' => $request->store_id,
                'customer_id' => $client->id ?? null,
                'customer_name' => $customerName,
                'customer_phone' => $request->customer_phone ?? ($client->phone ?? null),
                'customer_email' => $request->customer_email ?? ($client->email ?? null),
                'customer_gstin' => $request->customer_gstin ?? ($client->gst_number ?? null),
                'billing_address' => $request->billing_address ?? null,
                'shipping_address' => $request->shipping_address ?? null,
                'reference_number' => $request->reference_number,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'supply_state' => $supplyState,
                'gst_treatment' => $request->gst_treatment,
                'currency_code' => $request->currency_code ?? 'INR',
                'exchange_rate' => $request->exchange_rate ?? 1.0000,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'status' => $status,
            ], $mathEngine['header_totals']);

            // Mark-as-sent stamps: only set on the draft → sent transition.
            if ($status === 'sent' && $quotation->status !== 'sent') {
                $headerUpdate['is_sent'] = true;
                $headerUpdate['sent_at'] = now();
                $headerUpdate['sent_by'] = Auth::id();
            }

            $quotation->update($headerUpdate);

            // 3. WIPE OLD ITEMS AND INSERT NEW ONES
            $quotation->items()->delete();
            foreach ($mathEngine['line_items'] as $itemData) {
                $itemData['quotation_id'] = $quotation->id;
                QuotationItem::create($itemData);
            }

            DB::commit();

            // 🌟 FIX: Added $quotation->id to the route parameters!
            return redirect()->route('admin.quotations.show', $quotation->id)->with('success', 'Quotation updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to update quotation. '.$e->getMessage());
        }
    }

    /**
     * Remove the quotation (Soft Delete).
     */
    public function destroy(Quotation $quotation)
    {
        abort_if($quotation->company_id !== Auth::user()->company_id, 403);

        if ($quotation->status === 'converted') {
            return back()->with('error', 'Cannot delete a quotation that has already been converted to an invoice.');
        }

        $quotation->delete();

        return redirect()->route('admin.quotations.index')->with('success', 'Quotation archived successfully.');
    }

    /**
     * Mark the quotation as sent to customer.
     */
    public function markAsSent(Quotation $quotation)
    {
        abort_if($quotation->company_id !== Auth::user()->company_id, 403);

        $quotation->update([
            'status' => 'sent',
            'is_sent' => true,
            'sent_at' => now(),
            'sent_by' => Auth::id(),
        ]);

        return back()->with('success', 'Quotation marked as sent.');
    }

    /**
     * Download the quotation as a PDF.
     */
    public function downloadPdf(Quotation $quotation)
    {
        abort_if($quotation->company_id !== Auth::user()->company_id, 403);

        $quotation->load(['items', 'customer', 'store', 'creator']);
        $company = $quotation->company ?? Auth::user()->company;

        $pdf = Pdf::loadView('admin.quotations.pdf', compact('quotation', 'company'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Quotation_'.$quotation->quotation_number.'.pdf');
    }

    /**
     * 🟢 CONVERT TO INVOICE
     */
    public function convertToInvoice(Quotation $quotation)
    {
        abort_if($quotation->company_id !== Auth::user()->company_id, 403);

        if ($quotation->status === 'converted') {
            return back()->with('error', 'This quotation has already been converted to an invoice.');
        }

        try {
            DB::beginTransaction();

            $companyId = $quotation->company_id;

            // Generate New Invoice Number safely
            $prefix = 'INV-'.date('ym');
            $latestInvoice = Invoice::withTrashed()
                ->where('company_id', $companyId)
                ->where('invoice_number', 'like', "{$prefix}-%")
                ->orderBy('invoice_number', 'desc')
                ->first();
            $nextSequence = $latestInvoice ? ((int) substr($latestInvoice->invoice_number, -4)) + 1 : 1;
            $invoiceNumber = $prefix.'-'.str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            // Create the Invoice Header
            $invoice = Invoice::create([
                'company_id' => $companyId,
                'store_id' => $quotation->store_id,
                'warehouse_id' => Warehouse::where('company_id', $companyId)->first()->id,
                'customer_id' => $quotation->customer_id,
                'customer_name' => $quotation->customer_name,
                'billing_address' => $quotation->billing_address,
                'shipping_address' => $quotation->shipping_address,
                'created_by' => Auth::id(),
                'invoice_number' => $invoiceNumber,
                'source' => 'direct',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'supply_state' => $quotation->supply_state,
                'gst_treatment' => $quotation->gst_treatment,
                'currency_code' => $quotation->currency_code,
                'exchange_rate' => $quotation->exchange_rate,
                'subtotal' => $quotation->subtotal,
                'discount_type' => $quotation->discount_type,
                'discount_value' => $quotation->discount_value,
                'discount_amount' => $quotation->discount_amount,
                'taxable_amount' => $quotation->taxable_amount,
                'cgst_amount' => $quotation->cgst_amount,
                'sgst_amount' => $quotation->sgst_amount,
                'igst_amount' => $quotation->igst_amount,
                'tax_amount' => $quotation->tax_amount,
                'shipping_charge' => $quotation->shipping_charge,
                'other_charges' => $quotation->other_charges,
                'round_off' => $quotation->round_off,
                'grand_total' => $quotation->grand_total,
                'notes' => $quotation->notes,
                'terms_conditions' => $quotation->terms_conditions,
                'status' => 'draft',
                'payment_status' => 'unpaid',
            ]);

            // Deep Copy Line Items
            foreach ($quotation->items as $qItem) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $qItem->product_id,
                    'product_sku_id' => $qItem->product_sku_id,
                    'unit_id' => $qItem->unit_id,
                    'product_name' => $qItem->product_name,
                    'hsn_code' => $qItem->hsn_code,
                    'quantity' => $qItem->quantity,
                    'unit_price' => $qItem->unit_price,
                    'tax_type' => $qItem->tax_type,
                    'discount_type' => $qItem->discount_type,
                    'discount_value' => $qItem->discount_value,
                    'discount_amount' => $qItem->discount_amount,
                    'taxable_value' => $qItem->taxable_value,
                    'tax_percent' => $qItem->tax_percent,
                    'cgst_amount' => $qItem->cgst_amount,
                    'sgst_amount' => $qItem->sgst_amount,
                    'igst_amount' => $qItem->igst_amount,
                    'tax_amount' => $qItem->tax_amount,
                    'total_amount' => $qItem->total_amount,
                ]);
            }

            // Lock the Quotation & Trace it
            $quotation->update([
                'status' => 'converted',
                'converted_to_invoice_id' => $invoice->id,
                'converted_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.invoices.edit', $invoice->id)
                ->with('success', 'Quotation successfully converted to a Draft Invoice! Please confirm to deduct stock.');

        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to convert quotation. '.$e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────
    // NEW IN-MEMORY MATH ENGINE
    // ─────────────────────────────────────────────────────────

    private function calculateQuotationMath(array $data, string $supplyState, string $companyState): array
    {
        $totalSubtotal = 0;
        $totalTax = 0;
        $isInterState = strtolower(trim($supplyState)) !== strtolower(trim($companyState));
        $processedItems = [];

        foreach ($data['items'] as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $taxPct = (float) $item['tax_percent'];

            // Base
            $baseAmount = $qty * $price;

            // Discount
            $lineDiscountType = (isset($item['discount_type']) && $item['discount_type'] === 'fixed') ? 'fixed' : 'percentage';
            $lineDiscountValue = (float) ($item['discount_value'] ?? 0);
            
            $lineDiscountAmt = 0;
            if ($lineDiscountType === 'percentage') {
                $lineDiscountAmt = $baseAmount * ($lineDiscountValue / 100);
            } else {
                $lineDiscountAmt = $lineDiscountValue;
            }
            $afterDiscount = max(0, $baseAmount - $lineDiscountAmt);

            // Taxes
            $taxableValue = 0;
            $taxAmount = 0;

            if ($item['tax_type'] === 'inclusive') {
                $taxableValue = $afterDiscount / (1 + ($taxPct / 100));
                $taxAmount = $afterDiscount - $taxableValue;
            } else {
                $taxableValue = $afterDiscount;
                $taxAmount = $taxableValue * ($taxPct / 100);
            }

            $lineTotal = $taxableValue + $taxAmount;
            $totalSubtotal += $taxableValue;
            $totalTax += $taxAmount;

            // Push processed item back to array
            $processedItems[] = [
                'product_id' => $item['product_id'] ?? null,
                'product_sku_id' => $item['product_sku_id'] ?? null,
                'unit_id' => $item['unit_id'] ?? null,
                'product_name' => $item['product_name'],
                'sku_code' => $item['sku_code'] ?? null,
                'hsn_code' => $item['hsn_code'] ?? null,
                'quantity' => $qty,
                'unit_price' => $price,
                'tax_type' => $item['tax_type'],
                'discount_type' => $lineDiscountType,
                'discount_value' => $lineDiscountValue,
                'discount_amount' => round($lineDiscountAmt, 4),
                'taxable_value' => $taxableValue,
                'tax_percent' => $taxPct,
                'igst_amount' => $isInterState ? $taxAmount : 0,
                'cgst_amount' => ! $isInterState ? ($taxAmount / 2) : 0,
                'sgst_amount' => ! $isInterState ? ($taxAmount / 2) : 0,
                'tax_amount' => $taxAmount,
                'total_amount' => $lineTotal,
            ];
        }

        // Global Financials
        $globalDiscountType = (isset($data['discount_type']) && $data['discount_type'] === 'fixed') ? 'fixed' : 'percentage';
        $globalDiscountValue = (float) ($data['discount_value'] ?? 0);

        $globalDiscountAmt = 0;
        if ($globalDiscountType === 'percentage') {
            $globalDiscountAmt = $totalSubtotal * ($globalDiscountValue / 100);
        } else {
            $globalDiscountAmt = $globalDiscountValue;
        }

        $shipping = (float) ($data['shipping_charge'] ?? 0);
        $other = (float) ($data['other_charges'] ?? 0);

        $rawGrandTotal = ($totalSubtotal - $globalDiscountAmt) + $totalTax + $shipping + $other;
        $grandTotal = round($rawGrandTotal);
        $roundOff = round($grandTotal - $rawGrandTotal, 2);

        return [
            'header_totals' => [
                'subtotal' => $totalSubtotal,
                'discount_type' => $globalDiscountType,
                'discount_value' => $globalDiscountValue,
                'discount_amount' => round($globalDiscountAmt, 4),
                'taxable_amount' => $totalSubtotal - $globalDiscountAmt,
                'tax_amount' => $totalTax,
                'igst_amount' => $isInterState ? $totalTax : 0,
                'cgst_amount' => ! $isInterState ? ($totalTax / 2) : 0,
                'sgst_amount' => ! $isInterState ? ($totalTax / 2) : 0,
                'shipping_charge' => $shipping,
                'other_charges' => $other,
                'round_off' => $roundOff,
                'grand_total' => $grandTotal,
            ],
            'line_items' => $processedItems,
        ];
    }
}
