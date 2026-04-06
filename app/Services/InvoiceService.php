<?php

namespace App\Services;

use App\Models\Challan;
use App\Models\ChallanItem;
use App\Models\Invoice;
use App\Models\ProductSku;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    protected InventoryService $inventory;

    public function __construct(InventoryService $inventory)
    {
        $this->inventory = $inventory;
    }

    /**
     * Create a unified Invoice (POS or Direct)
     */
    public function createInvoice(array $data, int $companyId): Invoice
    {
        return DB::transaction(function () use ($data, $companyId) {

            // 1. Generate Invoice Number (Logic to be customized per company)
            $invoiceNumber = $this->generateInvoiceNumber($companyId, $data['source']);

            // 2. Prepare Tax Calculations
            $isInterState = $this->isInterStateSale($data['supply_state'], (int) $data['store_id']);

            // 3. Create the Header
            $invoice = Invoice::create([
                'company_id' => $companyId,
                'store_id' => $data['store_id'],
                'warehouse_id' => $data['warehouse_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'created_by' => Auth::id(),
                'salesperson_id' => $data['salesperson_id'] ?? Auth::id(),
                'invoice_number' => $invoiceNumber,
                'source' => $data['source'] ?? 'direct',
                'invoice_date' => $data['invoice_date'] ?? now(),
                'due_date' => $data['due_date'] ?? null,
                'supply_state' => $data['supply_state'],
                'gst_treatment' => $data['gst_treatment'] ?? 'unregistered',
                'currency_code' => $data['currency_code'] ?? 'INR',
                'status' => $data['status'] ?? 'confirmed',
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,           // 🌟 ADD THIS
                'terms_conditions' => $data['terms_conditions'] ?? null, // 🌟 ADD THIS
            ]);

            $totals = [
                'subtotal' => 0,
                'taxable' => 0,
                'cgst' => 0,
                'sgst' => 0,
                'igst' => 0,
                'tax' => 0,
            ];

            // 4. Process Items & Inventory
            foreach ($data['items'] as $item) {
                $sku = ProductSku::with('product')->findOrFail($item['product_sku_id']);

                // 🌟 Execute the GST Math Engine
                $itemTax = $this->calculateItemTax(
                    $item['quantity'],
                    $item['unit_price'],
                    $item['discount_type'] ?? 'fixed',
                    $item['discount_value'] ?? 0,
                    $item['tax_percent'] ?? 0,
                    $item['tax_type'] ?? 'exclusive',
                    $isInterState
                );

                // Create Item Snapshot
                $invoice->items()->create([
                    'product_id' => $sku->product_id,
                    'product_sku_id' => $sku->id,
                    'unit_id' => $item['unit_id'],
                    'product_name' => $sku->product->name,
                    'hsn_code' => $sku->product->hsn_code,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],

                    // Disounts
                    'discount_type' => (isset($item['discount_type']) && $item['discount_type'] === 'percent') ? 'percentage' : 'fixed',
                    'discount_amount' => $itemTax['discount_amount'],

                    // Tax & Totals
                    'taxable_value' => $itemTax['taxable'],
                    'tax_percent' => $item['tax_percent'] ?? 0,
                    'tax_type' => $item['tax_type'] ?? 'exclusive',
                    'cgst_amount' => $itemTax['cgst'],
                    'sgst_amount' => $itemTax['sgst'],
                    'igst_amount' => $itemTax['igst'],
                    'tax_amount' => $itemTax['total_tax'],
                    'total_amount' => $itemTax['taxable'] + $itemTax['total_tax'],
                ]);

                // Update Running Totals
                $totals['subtotal'] += $itemTax['taxable']; // Subtotal is technically the sum of Taxable Values in GST
                $totals['taxable'] += $itemTax['taxable'];
                $totals['cgst'] += $itemTax['cgst'];
                $totals['sgst'] += $itemTax['sgst'];
                $totals['igst'] += $itemTax['igst'];
                $totals['tax'] += $itemTax['total_tax'];

                // 5. Deduct Inventory — pass batch info when converting from a challan
                $this->inventory->deductStock(
                    sku: $sku,
                    warehouseId: $data['warehouse_id'],
                    qty: $item['quantity'],
                    movementType: 'sale',
                    reference: $invoice,
                    batchId: $item['batch_id'] ?? null,
                    batchNumber: $item['batch_number'] ?? null,
                );
            }

            // 6. Update Challan Item qty_invoiced when converting from a challan
            if (! empty($data['challan_id'])) {
                foreach ($data['items'] as $item) {
                    if (! empty($item['challan_item_id'])) {
                        ChallanItem::where('id', $item['challan_item_id'])
                            ->increment('qty_invoiced', $item['quantity']);
                    }
                }

                // Recalculate challan status (partially_converted vs converted_to_invoice)
                $challan = Challan::find($data['challan_id']);
                $challan?->recalculateStatus();
            }

            // 7. Finalize Header Totals & Apply Global Discount
            $itemsSum = $totals['taxable'] + $totals['tax'];
            $globalDiscountType = $data['global_discount_type'] ?? 'fixed';
            $globalDiscountValue = (float) ($data['global_discount_value'] ?? 0);

            $globalDiscountAmount = 0;
            if ($globalDiscountType === 'percent') {
                $globalDiscountAmount = $itemsSum * ($globalDiscountValue / 100);
            } else {
                $globalDiscountAmount = $globalDiscountValue;
            }

            // Calculate Grand Total (Base + Shipping - Discount)
            $shippingCharge = (float) ($data['shipping_charge'] ?? 0);
            $grandTotal = max(0, $itemsSum - $globalDiscountAmount + $shippingCharge);

            $roundedTotal = round($grandTotal);
            $roundOff = $roundedTotal - $grandTotal;

            $invoice->update([
                'subtotal' => $totals['subtotal'],
                'taxable_amount' => $totals['taxable'],
                'cgst_amount' => $totals['cgst'],
                'sgst_amount' => $totals['sgst'],
                'igst_amount' => $totals['igst'],
                'tax_amount' => $totals['tax'],

                // 🌟 FIX & SAVE: Global Discount
                'discount_type' => $globalDiscountType === 'percent' ? 'percentage' : 'fixed',
                'discount_amount' => $globalDiscountAmount,
                'shipping_charge' => $shippingCharge,

                'round_off' => $roundOff,
                'grand_total' => $roundedTotal,
            ]);

            return $invoice;
        });
    }

    /**
     * Update an Invoice (Reverse stock, wipe items, recreate, deduct new stock)
     */
    public function updateInvoice(Invoice $invoice, array $data, int $companyId): Invoice
    {
        // 1. Reverse old stock (Add it back to the OLD warehouse)
        foreach ($invoice->items as $oldItem) {
            $sku = ProductSku::find($oldItem->product_sku_id);
            if ($sku) {
                // Note: Assuming your InventoryService has an 'addStock' method
                // to reverse deductions. If it's called something else, adjust the name!
                $this->inventory->addStock(
                    $sku,
                    $invoice->warehouse_id,
                    $oldItem->quantity,
                    'sale_return',
                    $invoice
                );
            }
        }

        // 2. Wipe old items clean
        $invoice->items()->delete();

        // 3. Prepare new tax calculations
        $isInterState = $this->isInterStateSale($data['supply_state'], (int) $data['store_id']);

        // 4. Update Header (Basic info only, totals come later)
        $invoice->update([
            'store_id' => $data['store_id'],
            'warehouse_id' => $data['warehouse_id'],
            'customer_id' => $data['customer_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'] ?? null,
            'supply_state' => $data['supply_state'],
            'notes' => $data['notes'] ?? null,
            'terms_conditions' => $data['terms_conditions'] ?? null,
        ]);

        $totals = [
            'subtotal' => 0, 'taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0, 'tax' => 0,
        ];

        // 5. Process New Items & Deduct New Stock
        foreach ($data['items'] as $item) {
            $sku = ProductSku::with('product')->findOrFail($item['product_sku_id']);

            $itemTax = $this->calculateItemTax(
                $item['quantity'],
                $item['unit_price'],
                $item['discount_type'] ?? 'fixed',
                $item['discount_value'] ?? 0,
                $item['tax_percent'] ?? 0,
                $item['tax_type'] ?? 'exclusive',
                $isInterState
            );

            $invoice->items()->create([
                'product_id' => $sku->product_id,
                'product_sku_id' => $sku->id,
                'unit_id' => $item['unit_id'],
                'product_name' => $sku->product->name,
                'hsn_code' => $sku->product->hsn_code,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_type' => (isset($item['discount_type']) && $item['discount_type'] === 'percent') ? 'percentage' : 'fixed',
                'discount_amount' => $itemTax['discount_amount'],
                'taxable_value' => $itemTax['taxable'],
                'tax_percent' => $item['tax_percent'] ?? 0,
                'tax_type' => $item['tax_type'] ?? 'exclusive',
                'cgst_amount' => $itemTax['cgst'],
                'sgst_amount' => $itemTax['sgst'],
                'igst_amount' => $itemTax['igst'],
                'tax_amount' => $itemTax['total_tax'],
                'total_amount' => $itemTax['taxable'] + $itemTax['total_tax'],
            ]);

            $totals['subtotal'] += $itemTax['taxable'];
            $totals['taxable'] += $itemTax['taxable'];
            $totals['cgst'] += $itemTax['cgst'];
            $totals['sgst'] += $itemTax['sgst'];
            $totals['igst'] += $itemTax['igst'];
            $totals['tax'] += $itemTax['total_tax'];

            // Deduct from the NEW warehouse selection
            $this->inventory->deductStock(
                $sku,
                $data['warehouse_id'],
                $item['quantity'],
                'sale',
                $invoice
            );
        }

        // 6. Finalize Header Totals & Apply Global Discount
        $itemsSum = $totals['taxable'] + $totals['tax'];
        $globalDiscountType = $data['global_discount_type'] ?? 'fixed';
        $globalDiscountValue = (float) ($data['global_discount_value'] ?? 0);

        $globalDiscountAmount = 0;
        if ($globalDiscountType === 'percent') {
            $globalDiscountAmount = $itemsSum * ($globalDiscountValue / 100);
        } else {
            $globalDiscountAmount = $globalDiscountValue;
        }

        $shippingCharge = (float) ($data['shipping_charge'] ?? 0);
        $grandTotal = max(0, $itemsSum - $globalDiscountAmount + $shippingCharge);

        $roundedTotal = round($grandTotal);
        $roundOff = $roundedTotal - $grandTotal;

        $invoice->update([
            'subtotal' => $totals['subtotal'],
            'taxable_amount' => $totals['taxable'],
            'cgst_amount' => $totals['cgst'],
            'sgst_amount' => $totals['sgst'],
            'igst_amount' => $totals['igst'],
            'tax_amount' => $totals['tax'],
            'discount_type' => $globalDiscountType === 'percent' ? 'percentage' : 'fixed',
            'discount_amount' => $globalDiscountAmount,
            'shipping_charge' => $shippingCharge,
            'round_off' => $roundOff,
            'grand_total' => $roundedTotal,
        ]);

        return $invoice;
    }

    /**
     * Determine if CGST/SGST or IGST applies
     */
    protected function isInterStateSale(string $supplyState, int $storeId): bool
    {
        // 1. Fetch the store and its related state
        $store = Store::with('state')->find($storeId);

        // 2. Safely extract the store's state name (fallback to global setting if missing)
        $sellerState = ($store && $store->state)
            ? $store->state->name
            : get_setting('company_state', '');

        // 3. Compare the strings carefully (ignoring case and extra spaces)
        return strtolower(trim($sellerState)) !== strtolower(trim($supplyState));
    }

    /**
     * Indian Tax Splitter & Math Engine
     * Flow: Subtotal -> Discount -> Taxable -> GST Calculation
     */
    protected function calculateItemTax($qty, $price, $discountType, $discountValue, $taxPercent, $taxType, $isInterState): array
    {
        $baseSubtotal = $qty * $price;

        // 1. Calculate and Deduct Line Discount FIRST
        $discountAmount = 0;
        if ($discountType === 'percent') {
            $discountAmount = $baseSubtotal * ($discountValue / 100);
        } else {
            $discountAmount = $discountValue;
        }

        // Prevent negative values
        $afterDiscount = max(0, $baseSubtotal - $discountAmount);

        // 2. Determine Taxable Value & Total Tax
        $taxable = 0;
        $totalTax = 0;

        if ($taxType === 'inclusive') {
            // Extract tax backwards
            $taxable = $afterDiscount / (1 + ($taxPercent / 100));
            $totalTax = $afterDiscount - $taxable;
        } else {
            // Apply tax forwards
            $taxable = $afterDiscount;
            $totalTax = $taxable * ($taxPercent / 100);
        }

        // 3. Split GST based on State Match
        if ($isInterState) {
            return [
                'discount_amount' => $discountAmount,
                'taxable' => $taxable,
                'igst' => $totalTax,
                'cgst' => 0,
                'sgst' => 0,
                'total_tax' => $totalTax,
            ];
        }

        // Intra-state (Split equally)
        return [
            'discount_amount' => $discountAmount,
            'taxable' => $taxable,
            'igst' => 0,
            'cgst' => $totalTax / 2,
            'sgst' => $totalTax / 2,
            'total_tax' => $totalTax,
        ];
    }

    protected function generateInvoiceNumber($companyId, $source): string
    {
        $prefix = ($source === 'pos') ? 'POS' : 'INV';
        $year = date('y').'-'.(date('y') + 1);
        $count = Invoice::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->withTrashed()
            ->count() + 1;

        return $prefix.'/'.$year.'/'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cancel an Invoice and reverse all stock deductions
     */
    public function cancelInvoice(Invoice $invoice): void
    {
        // 1. Mark as cancelled
        $invoice->update(['status' => 'cancelled']);

        // 2. Reverse stock for all line items
        foreach ($invoice->items as $item) {
            $sku = ProductSku::find($item->product_sku_id);
            if ($sku) {
                // Return stock to the warehouse it was originally sold from
                $this->inventory->addStock(
                    $sku,
                    $invoice->warehouse_id,
                    $item->quantity,
                    'sale_return',
                    $invoice
                );
            }
        }

        // Optional: If you want to automatically void associated payments
        foreach ($invoice->payments as $payment) {
            $payment->update(['status' => 'cancelled']);
        }
    }
}
