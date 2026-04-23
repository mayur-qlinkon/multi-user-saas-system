<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ProductBatch;
use App\Models\ProductSku;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PurchaseService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    // ──────────────────────────────────────────────────────────────
    // PUBLIC API
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a new purchase order and calculate all financial lines.
     * If status is 'received', it automatically triggers inventory addition.
     */
    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $companyId = Auth::user()->company_id;

            // 1. Determine Tax Type (Intra-state vs Inter-state)
            $taxType = $this->determineTaxType($companyId, (int) $data['supplier_id']);
            $data['tax_type'] = $taxType;

            // 2. Snapshots for compliance
            $data['company_gst_number'] = Company::find($companyId)->gst_number ?? null;
            $data['supplier_gst_number'] = Supplier::find($data['supplier_id'])->gstin ?? null;

            // 4. Calculate all math (Items + Global)
            $calculatedData = $this->calculateFinancials($data);

            // 🌟 FIX: Extract items array so Eloquent doesn't try to save it to the purchases table
            $items = $calculatedData['items'];
            unset($calculatedData['items']);

            // 5. Create Purchase Header
            $purchaseNumber = $this->generatePurchaseNumber($companyId);

            $purchase = Purchase::create(array_merge($calculatedData, [
                'purchase_number' => $purchaseNumber,
                'company_id' => $companyId,
                'created_by' => Auth::id(),
                // Make sure company_gst_number and supplier_gst_number are included if calculateFinancials dropped them
                'company_gst_number' => $data['company_gst_number'],
                'supplier_gst_number' => $data['supplier_gst_number'],
            ]));

            // 6. Create Line Items
            foreach ($items as $itemData) { // 👈 Use the extracted $items array
                $itemData['company_id'] = $companyId;
                // If the whole PO is received, mark line items as fully received
                $itemData['quantity_received'] = ($purchase->status === 'received') ? $itemData['quantity'] : 0;

                $purchase->items()->create($itemData);
            }

            // 7. Handle Stock if Received immediately
            if ($purchase->status === 'received') {
                $this->receiveStock($purchase, $items); // 👈 Use extracted $items here too
            }

            return $purchase->load('items');
        });
    }

    /**
     * Update an existing purchase order.
     * Enforces strict accounting guards to prevent modifying received stock.
     */
    public function updatePurchase(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {

            // 🛡️ GUARD: Do not allow modifying items or status if already received.
            // Standard ERP practice: Requires a Purchase Return to reverse.
            if ($purchase->status === 'received') {
                if (isset($data['status']) && $data['status'] !== 'received') {
                    throw new InvalidArgumentException('Cannot revert a fully received purchase. Please create a Purchase Return instead.');
                }

                // Allow updating notes/references, but NOT items or financial amounts
                $purchase->update([
                    'supplier_invoice_number' => $data['supplier_invoice_number'] ?? $purchase->supplier_invoice_number,
                    'supplier_invoice_date' => $data['supplier_invoice_date'] ?? $purchase->supplier_invoice_date,
                    'notes' => $data['notes'] ?? $purchase->notes,
                    'updated_by' => Auth::id(),
                ]);

                return $purchase;
            }

            // 1. Capture old status BEFORE update (getOriginal is reset by Eloquent after save)
            $oldStatus = $purchase->status;

            // 2. Recalculate Tax Type in case Supplier changed
            $data['tax_type'] = $this->determineTaxType($purchase->company_id, (int) $data['supplier_id']);

            // 3. Recalculate all financials
            $calculatedData = $this->calculateFinancials($data);

            // 🌟 PRODUCTION FIX 1: Prevent wiping out previous payments on edit!
            $amountPreviouslyPaid = $purchase->total_amount - $purchase->balance_amount;
            $newBalance = max(0, $calculatedData['total_amount'] - $amountPreviouslyPaid);
            $calculatedData['balance_amount'] = $newBalance;

            if ($newBalance <= 0 && $calculatedData['total_amount'] > 0) {
                $calculatedData['payment_status'] = 'paid';
            } elseif ($newBalance < $calculatedData['total_amount']) {
                $calculatedData['payment_status'] = 'partial';
            } else {
                $calculatedData['payment_status'] = 'unpaid';
            }

            // 🌟 PRODUCTION FIX 2: Safely extract items before updating header
            $items = $calculatedData['items'];
            unset($calculatedData['items']);

            // 4. Update Header
            $purchase->update(array_merge($calculatedData, [
                'updated_by' => Auth::id(),
            ]));

            // 5. Sync Line Items (Create, Update, Delete)
            $this->syncLineItems($purchase, $items);

            // 6. Handle Stock if status transitioned to Received
            if ($oldStatus !== 'received' && $purchase->status === 'received') {
                $this->receiveStock($purchase, $calculatedData['items']);
            }

            return $purchase->load('items');
        });
    }

    // ──────────────────────────────────────────────────────────────
    // CORE BUSINESS LOGIC & MATH
    // ──────────────────────────────────────────────────────────────

    /**
     * Processes inventory addition using the robust InventoryService
     */
    private function receiveStock(Purchase $purchase, array $itemsData): void
    {
        $batchEnabled = batch_enabled();

        $skus = ProductSku::whereIn('id', array_column($itemsData, 'product_sku_id'))
            ->get()
            ->keyBy('id');
        foreach ($itemsData as $item) {

            $batchId = null;
            $batchNumber = null;

            if ($batchEnabled) {

                $secureBatchNumber = ! empty($item['batch_number'])
                    ? $item['batch_number']
                    : 'B-'.now()->format('ym').'-'.strtoupper(Str::random(5));

                $batch = ProductBatch::create([
                    'company_id' => $purchase->company_id,
                    'product_sku_id' => $item['product_sku_id'],
                    'warehouse_id' => $purchase->warehouse_id,
                    'supplier_id' => $purchase->supplier_id,
                    'batch_number' => $secureBatchNumber,
                    'manufacturing_date' => $item['manufacturing_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'purchase_price' => $item['unit_cost'],
                    'qty' => $item['quantity'],
                    'remaining_qty' => $item['quantity'],
                ]);

                $batchId = $batch->id;
                $batchNumber = $batch->batch_number;
            }

            $sku = $skus[$item['product_sku_id']];

            $this->inventoryService->addStock(
                sku: $sku,
                warehouseId: $purchase->warehouse_id,
                qty: $item['quantity'],
                movementType: 'purchase',
                reference: $purchase,
                batchId: $batchId,
                batchNumber: $batchNumber,
                unitCost: $item['unit_cost']
            );

            // $item->update([
            //     'quantity_received' => $item->quantity
            // ]);

            $sku->update([
                'cost' => $item['unit_cost'],
            ]);

            logger()->info('Purchase Stock Added', [
                'sku_id' => $sku->id,
                'warehouse' => $purchase->warehouse_id,
                'qty' => $item['quantity'],
                'batch_id' => $batchId,
            ]);
        }
    }

    /**
     * Handles creating, updating, and deleting items on an existing Purchase.
     */
    /**
     * Handles creating, updating, and deleting items on an existing Purchase.
     */
    private function syncLineItems(Purchase $purchase, array $itemsData): void
    {
        $existingItemIds = $purchase->items()->pluck('id')->toArray();
        $providedItemIds = array_filter(array_column($itemsData, 'id'));

        // 1. Delete items removed from the form
        $itemsToDelete = array_diff($existingItemIds, $providedItemIds);
        if (! empty($itemsToDelete)) {
            PurchaseItem::whereIn('id', $itemsToDelete)->delete();
        }

        $batchEnabled = batch_enabled();
        $isReceived = ($purchase->status === 'received');

        // 2. Update or Create
        foreach ($itemsData as $itemData) {
            $itemData['company_id'] = $purchase->company_id;

            // 🌟 FIX 1: Properly assign quantity_received like we do in createPurchase!
            $itemData['quantity_received'] = $isReceived ? $itemData['quantity'] : 0;

            // 🌟 FIX 2: "if batch enabled then..." -> clear them out if disabled!
            if (! $batchEnabled) {
                $itemData['batch_number'] = null;
                $itemData['manufacturing_date'] = null;
                $itemData['expiry_date'] = null;
            }

            if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                // 🌟 FIX 3: Use Eloquent's find()->update() instead of where()->update()
                // This forces Laravel to use $fillable, automatically throwing away
                // the extra frontend UI fields (like product_name) so it doesn't crash MySQL.
                PurchaseItem::find($itemData['id'])->update($itemData);
            } else {
                // Create
                $purchase->items()->create($itemData);
            }
        }
    }

    /**
     * Engine for calculating all line item subtotals, inclusive/exclusive taxes, and global totals.
     */
    private function calculateFinancials(array $data): array
    {
        $globalSubtotal = 0;
        $globalTaxable = 0;
        $globalCgst = 0;
        $globalSgst = 0;
        $globalIgst = 0;
        $globalTaxAmt = 0;

        $taxType = $data['tax_type']; // 'cgst_sgst' or 'igst'

        foreach ($data['items'] as &$item) {
            $qty = (float) $item['quantity'];
            $cost = (float) $item['unit_cost'];
            $taxPct = (float) ($item['tax_percent'] ?? 0);
            $isInclusive = ($item['tax_type'] ?? 'exclusive') === 'inclusive';

            // Extract new discount fields
            $discountType = $item['discount_type'] ?? 'percent';
            $discountValueInput = (float) ($item['discount_value'] ?? 0);

            // 🌟 ROOT FIX: Match ENUM and retain the UI input values
            $item['discount_type'] = ($discountType === 'fixed') ? 'fixed' : 'percentage';
            $item['discount_value'] = $discountValueInput;

            // Base Subtotal
            $itemSubtotal = $qty * $cost;
            $item['subtotal'] = round($itemSubtotal, 4);

            // Calculate Item-Level Line Discount
            $discountAmt = 0.0;
            if ($item['discount_type'] === 'percentage') {
                $discountAmt = $itemSubtotal * ($discountValueInput / 100);
            } else {
                $discountAmt = $discountValueInput;
            }

            // Prevent negative totals and apply discount
            $afterDiscount = max(0, $itemSubtotal - $discountAmt);
            $item['discount_amount'] = round($discountAmt, 4);

            // Inclusive / Exclusive Tax Math
            $taxableAmt = 0.0;
            $taxAmt = 0.0;

            if ($isInclusive) {
                // Price includes tax -> extract it backward
                $taxableAmt = $afterDiscount / (1 + ($taxPct / 100));
                $taxAmt = $afterDiscount - $taxableAmt;
            } else {
                // Price excludes tax -> add it on top
                $taxableAmt = $afterDiscount;
                $taxAmt = $taxableAmt * ($taxPct / 100);
            }

            // 🌟 ROOT FIX: Map to invoice_items column names exactly
            $item['taxable_value'] = round($taxableAmt, 4);
            $item['tax_amount'] = round($taxAmt, 4);
            $item['total_amount'] = round($taxableAmt + $taxAmt, 4);

            // Split Indian GST
            $item['cgst_amount'] = 0;
            $item['sgst_amount'] = 0;
            $item['igst_amount'] = 0;
            $item['cgst_percent'] = 0;
            $item['sgst_percent'] = 0;
            $item['igst_percent'] = 0;

            if ($taxType === 'cgst_sgst') {
                $item['cgst_percent'] = $taxPct / 2;
                $item['sgst_percent'] = $taxPct / 2;
                $item['cgst_amount'] = round($taxAmt / 2, 4);
                $item['sgst_amount'] = round($taxAmt / 2, 4);
            } elseif ($taxType === 'igst') {
                $item['igst_percent'] = $taxPct;
                $item['igst_amount'] = round($taxAmt, 4);
            }

            // Aggregate up to global totals
            $globalSubtotal += $item['subtotal'];
            $globalTaxable += $item['taxable_value'];
            $globalTaxAmt += $item['tax_amount'];
            $globalCgst += $item['cgst_amount'];
            $globalSgst += $item['sgst_amount'];
            $globalIgst += $item['igst_amount'];
        }
        unset($item);

        // Apply Global Extra Charges & Rounding
        // 🌟 ROOT FIX: Normalize global discount type to match database ENUM and retain value
        $data['discount_type'] = (isset($data['discount_type']) && $data['discount_type'] === 'fixed') ? 'fixed' : 'percentage';
        $data['discount_value'] = (float) ($data['discount_value'] ?? 0);
        
        $globalDiscount = (float) ($data['discount_amount'] ?? 0); // Flat bill cash discount
        $shipping = (float) ($data['shipping_cost'] ?? 0);
        $other = (float) ($data['other_charges'] ?? 0);

        // 🌟 ROOT FIX: Global Discount reduces the GRAND TOTAL, not the Taxable Value.
        $totalBeforeRound = $globalTaxable + $globalTaxAmt - $globalDiscount + $shipping + $other;

        // Indian accounting standard: Round to nearest Rupee
        $roundedTotal = round($totalBeforeRound);
        $roundOff = $roundedTotal - $totalBeforeRound;

        // Assign to header data
        $data['subtotal'] = round($globalSubtotal, 2);
        $data['taxable_amount'] = round($globalTaxable, 2); // Header retains taxable_amount
        $data['cgst_amount'] = round($globalCgst, 2);
        $data['sgst_amount'] = round($globalSgst, 2);
        $data['igst_amount'] = round($globalIgst, 2);
        $data['tax_amount'] = round($globalTaxAmt, 2);
        $data['round_off'] = round($roundOff, 2);
        $data['total_amount'] = $roundedTotal;
        $data['balance_amount'] = $roundedTotal; // Default, overridden in updatePurchase if needed

        return $data;
    }

    /**
     * Determines whether to apply IGST or CGST/SGST based on States
     */
    private function determineTaxType(int $companyId, int $supplierId): string
    {
        $company = Company::find($companyId);
        $supplier = Supplier::find($supplierId);

        // Fallback if missing data
        if (! $company || ! $supplier || ! $company->state_id || ! $supplier->state_id) {
            return 'none';
        }

        return ($company->state_id === $supplier->state_id) ? 'cgst_sgst' : 'igst';
    }

    /**
     * Auto-generates the next Purchase Order Number
     */
    private function generatePurchaseNumber(int $companyId): string
    {
        $year = now()->year;

        $lastPurchase = Purchase::where('company_id', $companyId)
            ->withTrashed() // 🌟 THE FIX: Look in the trash bin too!
            ->where('purchase_number', 'like', "PO-{$year}-%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($lastPurchase) {
            $parts = explode('-', $lastPurchase->purchase_number);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('PO-%s-%05d', $year, $sequence);
    }
}
