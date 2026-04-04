<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceReturn;
use App\Models\InvoiceReturnItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class InvoiceReturnService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * 🟢 STEP 1: CREATE DRAFT RETURN
     * Calculates all math and creates the Credit Note header and items.
     */
    public function createReturn(Invoice $invoice, array $data): InvoiceReturn
    {
        return DB::transaction(function () use ($invoice, $data) {
            $companyId = $invoice->company_id;

            // 1. Crunch the numbers in memory
            $mathEngine = $this->calculateReturnMath($data, $invoice);

            // 2. Generate Credit Note Number safely
            $returnNumber = $this->generateReturnNumber($companyId);

            // 3. Create the Header
            $invoiceReturn = InvoiceReturn::create(array_merge([
                'company_id'         => $companyId,
                'store_id'           => $data['store_id'],
                'warehouse_id'       => $data['warehouse_id'],
                'invoice_id'         => $invoice->id,
                
                'customer_id'        => $invoice->customer_id,
                'customer_name'      => $invoice->customer_name,
                'created_by'         => Auth::id(),
                'salesperson_id'     => $invoice->salesperson_id,
                'pos_terminal_id'    => $invoice->pos_terminal_id,
                
                'credit_note_number' => $returnNumber,
                'source'             => $invoice->source,
                'return_date'        => $data['return_date'],
                
                'return_type'        => $data['return_type'],
                'return_reason'      => $data['return_reason'] ?? 'other',
                'restock'            => $data['restock'] ?? true, // Will we put it back on the shelf?
                'stock_updated'      => false, // Stock only updates upon Confirmation
                
                'supply_state'       => $data['supply_state'],
                'gst_treatment'      => $data['gst_treatment'],
                'currency_code'      => $data['currency_code'] ?? 'INR',
                'exchange_rate'      => $data['exchange_rate'] ?? 1.0000,
                
                'notes'              => $data['notes'] ?? null,
                'terms_conditions'   => $data['terms_conditions'] ?? null,
                'status'             => 'draft',
                'refund_status'      => 'unrefunded',
            ], $mathEngine['header_totals']));

            // 4. Insert the Line Items
            foreach ($mathEngine['line_items'] as $itemData) {
                $itemData['invoice_return_id'] = $invoiceReturn->id;
                InvoiceReturnItem::create($itemData);
            }

            return $invoiceReturn;
        });
    }

    /**
     * 🟢 STEP 2: UPDATE DRAFT RETURN
     * Wipes old items, recalculates, and updates the header.
     */
    public function updateReturn(InvoiceReturn $return, array $data): InvoiceReturn
    {
        if ($return->status === 'confirmed') {
            throw new Exception("Cannot update a confirmed Credit Note. It is locked.");
        }

        return DB::transaction(function () use ($return, $data) {
            
            $mathEngine = $this->calculateReturnMath($data, $return->invoice);

            $return->update(array_merge([
                'store_id'         => $data['store_id'],
                'warehouse_id'     => $data['warehouse_id'],
                'return_date'      => $data['return_date'],
                'return_type'      => $data['return_type'],
                'return_reason'    => $data['return_reason'] ?? 'other',
                'restock'          => $data['restock'] ?? true,
                'supply_state'     => $data['supply_state'],
                'gst_treatment'    => $data['gst_treatment'],
                'notes'            => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
            ], $mathEngine['header_totals']));

            // Wipe and replace items safely
            $return->items()->delete();
            foreach ($mathEngine['line_items'] as $itemData) {
                $itemData['invoice_return_id'] = $return->id;
                InvoiceReturnItem::create($itemData);
            }

            return $return;
        });
    }

    /**
     * 🟢 STEP 3: CONFIRM RETURN & UPDATE STOCK
     * This is the critical ERP barrier. It locks the return and calls your InventoryService.
     */
    public function confirmReturn(InvoiceReturn $return): InvoiceReturn
    {
        if ($return->status === 'confirmed') {
            throw new Exception("This return has already been confirmed.");
        }

        return DB::transaction(function () use ($return) {
            
            // 1. Should we put the stock back on the shelf?
            if ($return->restock && !$return->stock_updated) {
                
                // Fetch items with their SKUs
                $items = $return->items()->with('sku')->get();

                foreach ($items as $item) {
                    if (!$item->sku) continue; // Skip non-inventory items

                    // 🌟 Call your robust InventoryService!
                    $this->inventoryService->addStock(
                        sku:          $item->sku,
                        warehouseId:  $return->warehouse_id,
                        qty:          $item->quantity,
                        movementType: 'sale_return',
                        reference:    $return
                    );

                    $item->update(['is_restocked' => true]);
                }

                $return->update(['stock_updated' => true]);
            }

            // 2. Lock the document
            $return->update([
                'status'      => 'confirmed',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return $return;
        });
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE INTERNAL ENGINE
    // ─────────────────────────────────────────────────────────

    /**
     * Calculates the exact financial values of the return.
     * Maps inputs to the original Invoice Items to ensure data integrity.
     */
    private function calculateReturnMath(array $data, Invoice $originalInvoice): array
    {
        $totalSubtotal = 0;
        $totalTax = 0;
        
        $companyState = Auth::user()->company->state->name ?? '';
        $isInterState = strtolower(trim($data['supply_state'])) !== strtolower(trim($companyState));
        
        $processedItems = [];

        foreach ($data['items'] as $itemData) {
            // Retrieve original invoice item for reference (prevents tampering)
            $originalLine = InvoiceItem::find($itemData['invoice_item_id']);
            if (!$originalLine || $originalLine->invoice_id !== $originalInvoice->id) {
                throw new Exception("Invalid Invoice Item referenced.");
            }

            $qty = (float) $itemData['quantity'];
            $price = (float) $itemData['unit_price'];
            $taxPct = (float) $itemData['tax_percent'];
            
            // Base Value
            $baseAmount = $qty * $price;
            
            // Line Discount
            $lineDiscountAmt = 0;
            if (in_array($itemData['discount_type'], ['percentage', 'percent'])) {
                $lineDiscountAmt = $baseAmount * ((float) $itemData['discount_amount'] / 100);
            } else {
                $lineDiscountAmt = (float) $itemData['discount_amount'];
            }
            $afterDiscount = max(0, $baseAmount - $lineDiscountAmt);

            // Taxes
            $taxableValue = 0;
            $taxAmount = 0;

            if ($itemData['tax_type'] === 'inclusive') {
                $taxableValue = $afterDiscount / (1 + ($taxPct / 100));
                $taxAmount = $afterDiscount - $taxableValue;
            } else {
                $taxableValue = $afterDiscount;
                $taxAmount = $taxableValue * ($taxPct / 100);
            }

            $lineTotal = $taxableValue + $taxAmount;

            $totalSubtotal += $taxableValue;
            $totalTax += $taxAmount;

            $processedItems[] = [
                'invoice_item_id' => $originalLine->id,
                'product_id'      => $itemData['product_id'] ?? $originalLine->product_id,
                'product_sku_id'  => $itemData['product_sku_id'] ?? $originalLine->product_sku_id,
                'unit_id'         => $itemData['unit_id'] ?? $originalLine->unit_id,
                'product_name'    => $itemData['product_name'],
                'hsn_code'        => $itemData['hsn_code'] ?? $originalLine->hsn_code,
                
                'quantity'        => $qty,
                'unit_price'      => $price,
                'is_restocked'    => false, // Updated later during confirmation
                
                'tax_type'        => $itemData['tax_type'],
                'discount_type'   => $itemData['discount_type'],
                'discount_amount' => $lineDiscountAmt,
                
                'taxable_value'   => $taxableValue,
                'tax_percent'     => $taxPct,
                
                'igst_amount'     => $isInterState ? $taxAmount : 0,
                'cgst_amount'     => !$isInterState ? ($taxAmount / 2) : 0,
                'sgst_amount'     => !$isInterState ? ($taxAmount / 2) : 0,
                'tax_amount'      => $taxAmount,
                
                'total_amount'    => $lineTotal,
            ];
        }

        // Global Financials
        $globalDiscountAmt = 0;
        if (in_array($data['discount_type'], ['percentage', 'percent'])) {
            $globalDiscountAmt = $totalSubtotal * ((float) ($data['discount_amount'] ?? 0) / 100);
        } else {
            $globalDiscountAmt = (float) ($data['discount_amount'] ?? 0);
        }

        $shipping = (float) ($data['shipping_charge'] ?? 0);
        $other = (float) ($data['other_charges'] ?? 0);

        $rawGrandTotal = ($totalSubtotal - $globalDiscountAmt) + $totalTax + $shipping + $other;
        $grandTotal = round($rawGrandTotal);
        $roundOff = round($grandTotal - $rawGrandTotal, 2);

        return [
            'header_totals' => [
                'subtotal'        => $totalSubtotal,
                'discount_type'   => $data['discount_type'] === 'percent' ? 'percentage' : $data['discount_type'],
                'discount_amount' => $globalDiscountAmt,
                'taxable_amount'  => max(0, $totalSubtotal - $globalDiscountAmt),
                
                'tax_amount'      => $totalTax,
                'igst_amount'     => $isInterState ? $totalTax : 0,
                'cgst_amount'     => !$isInterState ? ($totalTax / 2) : 0,
                'sgst_amount'     => !$isInterState ? ($totalTax / 2) : 0,
                
                'shipping_charge' => $shipping,
                'other_charges'   => $other,
                'round_off'       => $roundOff,
                'grand_total'     => $grandTotal,
            ],
            'line_items' => $processedItems
        ];
    }

    /**
     * Generates a sequential Credit Note number (e.g., CN-2603-0001)
     */
    protected function generateReturnNumber(int $companyId): string
    {
        $prefix = 'CN-' . date('ym');
        
        $latest = InvoiceReturn::withTrashed()
            ->where('company_id', $companyId)
            ->where('credit_note_number', 'like', "{$prefix}-%")
            ->orderBy('credit_note_number', 'desc')
            ->first();

        $nextSequence = $latest ? ((int) substr($latest->credit_note_number, -4)) + 1 : 1;
        
        return $prefix . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }
}