<?php

namespace App\Services;

use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create a new Purchase Return and process stock if status is 'returned'.
     */
    public function createPurchaseReturn(array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            $companyId = Auth::user()->company_id;

            // 1. Perform Financial Math
            $financials = $this->calculateFinancials($data['items'], $data['tax_type'], (float) ($data['discount_amount'] ?? 0));

            // 2. Generate Unique Return Number
            $returnNumber = $this->generateReturnNumber($companyId);

            // 3. Create the Header Record
            $purchaseReturn = PurchaseReturn::create([
                'company_id' => $companyId,
                'store_id' => $data['store_id'],
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'purchase_id' => $data['purchase_id'],
                'created_by' => Auth::id(),
                'return_number' => $returnNumber,
                'supplier_credit_note_number' => $data['supplier_credit_note_number'] ?? null,
                'return_date' => $data['return_date'],
                'status' => $data['status'],
                'tax_type' => $data['tax_type'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,

                // Inject calculated financials
                'subtotal' => $financials['subtotal'],
                'discount_amount' => $financials['discount_amount'],
                'taxable_amount' => $financials['taxable_amount'],
                'cgst_amount' => $financials['cgst_amount'],
                'sgst_amount' => $financials['sgst_amount'],
                'igst_amount' => $financials['igst_amount'],
                'tax_amount' => $financials['tax_amount'],
                'total_amount' => $financials['total_amount'],
            ]);

            // 4. Create Line Items
            $this->createLineItems($purchaseReturn, $financials['items']);

            // 5. If status is 'returned', immediately deduct from inventory
            if ($purchaseReturn->status === 'returned') {
                $this->processStockDeduction($purchaseReturn);
            }

            return $purchaseReturn;
        });
    }

    /**
     * Update an existing Purchase Return.
     */
    public function updatePurchaseReturn(PurchaseReturn $purchaseReturn, array $data): PurchaseReturn
    {
        // 🛡️ ERP GUARD: Service-level protection against modifying finalized records
        if ($purchaseReturn->status === 'returned') {
            throw new \Exception('Cannot modify a Purchase Return that has already been dispatched to the supplier.');
        }

        return DB::transaction(function () use ($purchaseReturn, $data) {

            // 1. Perform Financial Math
            $financials = $this->calculateFinancials($data['items'], $data['tax_type'], (float) ($data['discount_amount'] ?? 0));

            // 2. Update Header
            $purchaseReturn->update([
                'store_id' => $data['store_id'],
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'return_date' => $data['return_date'],
                'supplier_credit_note_number' => $data['supplier_credit_note_number'] ?? null,
                'status' => $data['status'],
                'tax_type' => $data['tax_type'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,

                'subtotal' => $financials['subtotal'],
                'discount_amount' => $financials['discount_amount'],
                'taxable_amount' => $financials['taxable_amount'],
                'cgst_amount' => $financials['cgst_amount'],
                'sgst_amount' => $financials['sgst_amount'],
                'igst_amount' => $financials['igst_amount'],
                'tax_amount' => $financials['tax_amount'],
                'total_amount' => $financials['total_amount'],
            ]);

            // 3. Sync Line Items (Delete missing, update existing, create new)
            $this->syncLineItems($purchaseReturn, $financials['items']);

            // 4. If status CHANGED to 'returned', deduct stock
            if ($purchaseReturn->status === 'returned') {
                $this->processStockDeduction($purchaseReturn);
            }

            return $purchaseReturn;
        });
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────

    /**
     * Deduct items from the warehouse using the InventoryService
     */
    private function processStockDeduction(PurchaseReturn $purchaseReturn): void
    {
        // Eager load items and SKUs to prevent N+1 queries
        $purchaseReturn->load('items.productSku');

        foreach ($purchaseReturn->items as $item) {
            $this->inventoryService->deductStock(
                sku: $item->productSku,
                warehouseId: $purchaseReturn->warehouse_id,
                qty: (float) $item->quantity,
                movementType: 'purchase_return',
                reference: $purchaseReturn
            );
        }
    }

    /**
     * Create completely new line items
     */
    private function createLineItems(PurchaseReturn $purchaseReturn, array $processedItems): void
    {
        $itemsToInsert = array_map(function ($item) use ($purchaseReturn) {
            $item['purchase_return_id'] = $purchaseReturn->id;

            return $item;
        }, $processedItems);

        PurchaseReturnItem::insert($itemsToInsert);
    }

    /**
     * Sync line items for updates (diffing IDs)
     */
    private function syncLineItems(PurchaseReturn $purchaseReturn, array $processedItems): void
    {
        $existingItemIds = $purchaseReturn->items()->pluck('id')->toArray();
        $submittedItemIds = array_filter(array_column($processedItems, 'id'));

        // 1. Delete items removed from the UI
        $itemsToDelete = array_diff($existingItemIds, $submittedItemIds);
        if (! empty($itemsToDelete)) {
            PurchaseReturnItem::whereIn('id', $itemsToDelete)->delete();
        }

        // 2. Process updates and creations
        foreach ($processedItems as $itemData) {
            $itemId = $itemData['id'] ?? null;
            unset($itemData['id']); // Remove ID from array before insert/update

            if ($itemId && in_array($itemId, $existingItemIds)) {
                PurchaseReturnItem::where('id', $itemId)->update($itemData);
            } else {
                $itemData['purchase_return_id'] = $purchaseReturn->id;
                PurchaseReturnItem::create($itemData);
            }
        }
    }

    /**
     * Calculate line item totals, taxes, and global aggregate totals.
     */
    private function calculateFinancials(array $rawItems, string $taxType, float $globalDiscountAmount): array
    {
        $subtotal = 0.0;
        $totalCgst = 0.0;
        $totalSgst = 0.0;
        $totalIgst = 0.0;

        $processedItems = [];

        foreach ($rawItems as $item) {
            $qty = (float) $item['quantity'];
            $unitCost = (float) $item['unit_cost'];
            $taxPercent = (float) ($item['tax_percent'] ?? 0);

            // Base gross
            $grossAmount = $qty * $unitCost;
            $taxableAmount = $grossAmount; // In returns, line discounts are rare, but we base it on original unit cost

            // Calculate Tax based on Header Tax Type (Intra-state vs Inter-state)
            $taxAmount = $taxableAmount * ($taxPercent / 100);
            $cgstAmt = 0;
            $sgstAmt = 0;
            $igstAmt = 0;
            $cgstPct = 0;
            $sgstPct = 0;
            $igstPct = 0;

            if ($taxType === 'cgst_sgst') {
                $cgstPct = $taxPercent / 2;
                $sgstPct = $taxPercent / 2;
                $cgstAmt = $taxAmount / 2;
                $sgstAmt = $taxAmount / 2;
            } elseif ($taxType === 'igst') {
                $igstPct = $taxPercent;
                $igstAmt = $taxAmount;
            }

            $lineTotal = $taxableAmount + $taxAmount;

            // Fetch HSN code from original purchase item for compliance
            $originalItem = PurchaseItem::find($item['purchase_item_id']);

            $processedItems[] = [
                'id' => $item['id'] ?? null,
                'purchase_item_id' => $item['purchase_item_id'],
                'product_id' => $item['product_id'],
                'product_sku_id' => $item['product_sku_id'],
                'unit_id' => $item['unit_id'],
                'hsn_code' => $originalItem?->hsn_code,
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'tax_percent' => $taxPercent,
                'cgst_percent' => $cgstPct,
                'sgst_percent' => $sgstPct,
                'igst_percent' => $igstPct,
                'taxable_amount' => round($taxableAmount, 4),
                'cgst_amount' => round($cgstAmt, 4),
                'sgst_amount' => round($sgstAmt, 4),
                'igst_amount' => round($igstAmt, 4),
                'tax_amount' => round($taxAmount, 4),
                'total_price' => round($lineTotal, 4),
                'batch_number' => $item['batch_number'] ?? null,
                'return_reason' => $item['return_reason'] ?? null,
                'notes' => $item['notes'] ?? null,
            ];

            $subtotal += $taxableAmount;
            $totalCgst += $cgstAmt;
            $totalSgst += $sgstAmt;
            $totalIgst += $igstAmt;
        }

        $totalTax = $totalCgst + $totalSgst + $totalIgst;

        // Global calculations
        $taxableAfterGlobalDiscount = max(0, $subtotal - $globalDiscountAmount);

        // Note: For exact Indian Accounting, if global discount is applied, tax must be re-proportioned.
        // For simplicity in standard ERPs, Returns usually map exactly to original line item taxes.
        // We subtract the global discount from the final total.
        $grandTotal = $subtotal + $totalTax - $globalDiscountAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($globalDiscountAmount, 2),
            'taxable_amount' => round($taxableAfterGlobalDiscount, 2),
            'cgst_amount' => round($totalCgst, 2),
            'sgst_amount' => round($totalSgst, 2),
            'igst_amount' => round($totalIgst, 2),
            'tax_amount' => round($totalTax, 2),
            'total_amount' => round($grandTotal, 2),
            'items' => $processedItems,
        ];
    }

    /**
     * Generate unique Return Number
     */
    private function generateReturnNumber(int $companyId): string
    {
        $year = date('Y');

        $lastReturn = PurchaseReturn::where('company_id', $companyId)
            ->where('return_number', 'like', "PR-RTN-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastReturn) {
            $parts = explode('-', $lastReturn->return_number);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('PR-RTN-%s-%05d', $year, $sequence);
    }
}
