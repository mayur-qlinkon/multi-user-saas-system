<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\ProductBatch;
use App\Models\ProductSku;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    // ──────────────────────────────────────────────────────────────
    // PUBLIC API — These are the only methods you call from outside
    // ──────────────────────────────────────────────────────────────

    /**
     * Add stock to a warehouse.
     * Used by: Purchase receive, Purchase return receive, Stock adjustment (up)
     *     
     * @throws \InvalidArgumentException
     */
    public function addStock(
        ProductSku $sku,
        int        $warehouseId,
        float      $qty,
        string     $movementType,
        mixed      $reference  = null,
        ?int       $batchId    = null,
        ?string    $batchNumber = null,
         ?float     $unitCost   = null
    ): void {
        if ($qty <= 0) {
            throw new \InvalidArgumentException("addStock qty must be > 0. Got: {$qty}");
        }

        $this->guardWarehouseBelongsToCompany($warehouseId, $sku->company_id);

        DB::transaction(function () use (
            $sku,
            $warehouseId,
            $qty,
            $movementType,
            $reference,
            $batchId,
            $batchNumber,
            $unitCost
        ) {
            $this->_add(
                $sku,
                $warehouseId,
                $qty,
                $movementType,
                $reference,
                $batchId,
                $batchNumber,
                $unitCost
            );
        });

    }

    /**
     * Deduct stock from a warehouse.
     * Used by: Sale, POS, Transfer out, Purchase return to supplier
     *
     * @throws InsufficientStockException
     * @throws \InvalidArgumentException
     */
    public function deductStock(
            ProductSku $sku,
            int        $warehouseId,
            float      $qty,
            string     $movementType,
            mixed      $reference = null,
            ?int       $batchId = null,
            ?string    $batchNumber = null,
            ?float     $unitCost = null
        ): void {
        if ($qty <= 0) {
            throw new \InvalidArgumentException("deductStock qty must be > 0. Got: {$qty}");
        }

        $this->guardWarehouseBelongsToCompany($warehouseId, $sku->company_id);

        DB::transaction(function () use ($sku, $warehouseId, $qty, $movementType, $reference) {
            $this->_deduct($sku, $warehouseId, $qty, $movementType, $reference);
        });
    }

    /**
     * Set stock to a specific quantity (manual adjustment).
     * Used by: Stock count / physical audit
     * Calculates difference and creates a +/- movement automatically.
     */
    public function adjustStock(
        ProductSku $sku,
        int        $warehouseId,
        float      $newQty,
        mixed      $reference = null
    ): void {
        if ($newQty < 0) {
            throw new \InvalidArgumentException("adjustStock newQty cannot be negative. Got: {$newQty}");
        }

        $this->guardWarehouseBelongsToCompany($warehouseId, $sku->company_id);

        DB::transaction(function () use ($sku, $warehouseId, $newQty, $reference) {

            $stock = ProductStock::lockForUpdate()->firstOrCreate(
                [
                    'company_id'     => $sku->company_id,
                    'product_sku_id' => $sku->id,
                    'warehouse_id'   => $warehouseId,
                ],
                ['qty' => 0]
            );

            $difference = $newQty - $stock->qty;

            if ($difference == 0) {
                return; // Nothing changed — no movement needed
            }

            $stock->update(['qty' => $newQty]);

            StockMovement::create([
                'company_id'     => $sku->company_id,
                'product_sku_id' => $sku->id,
                'warehouse_id'   => $warehouseId,
                'batch_id'       => null,
                'quantity'       => $difference, // Positive or negative
                'movement_type'  => 'adjustment',
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id'   => $reference?->id,
                'balance_after'  => $stock->refresh()->qty,
            ]);
        });
    }

    /**
     * Transfer stock between two warehouses atomically.
     * Used by: Warehouse transfer module
     * Both deduct + add happen in ONE transaction — no partial state possible.
     *
     * @throws InsufficientStockException
     */
    public function transferStock(
        ProductSku $sku,
        int        $fromWarehouseId,
        int        $toWarehouseId,
        float      $qty,
        mixed      $reference = null
    ): void {
        if ($qty <= 0) {
            throw new \InvalidArgumentException("transferStock qty must be > 0. Got: {$qty}");
        }

        if ($fromWarehouseId === $toWarehouseId) {
            throw new \InvalidArgumentException("Source and destination warehouse cannot be the same.");
        }

        $this->guardWarehouseBelongsToCompany($fromWarehouseId, $sku->company_id);
        $this->guardWarehouseBelongsToCompany($toWarehouseId, $sku->company_id);

        // Single top-level transaction — private methods have NO nested transactions
        DB::transaction(function () use ($sku, $fromWarehouseId, $toWarehouseId, $qty, $reference) {
            $this->_deduct($sku, $fromWarehouseId, $qty, 'transfer_out', $reference);
            $this->_add($sku, $toWarehouseId, $qty, 'transfer_in', $reference);
        });
    }

    /**
     * Set opening stock when a new product/SKU is created.
     * Creates 'opening' movements — distinct from adjustments in reports.
     *
     * @param  array  $stockData  [['warehouse_id' => 1, 'qty' => 100], ...]
     */
    public function setOpeningStock(ProductSku $sku, array $stockData): void
    {
        foreach ($stockData as $row) {
            $qty = (float) ($row['qty'] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $this->guardWarehouseBelongsToCompany($row['warehouse_id'], $sku->company_id);

            // 'opening' movement type — do NOT use 'adjustment' for this
            // Keeps your stock audit report clean and accurate
            $this->addStock(
                sku:          $sku,
                warehouseId:  $row['warehouse_id'],
                qty:          $qty,
                movementType: 'opening',
                reference:    null,                
            );
        }
    }

    // ──────────────────────────────────────────────────────────────
    // READ HELPERS
    // ──────────────────────────────────────────────────────────────

    /**
     * Total stock of a SKU across ALL warehouses.
     */
    public function getStock(ProductSku $sku): float
    {
        return (float) ProductStock::where('product_sku_id', $sku->id)->sum('qty');
    }

    /**
     * Stock of a SKU in a specific warehouse.
     */
    public function getWarehouseStock(ProductSku $sku, int $warehouseId): float
    {
        return (float) ProductStock::where('product_sku_id', $sku->id)
            ->where('warehouse_id', $warehouseId)
            ->value('qty') ?? 0.0;
    }

    /**
     * Stock of a SKU across all warehouses, broken down per warehouse.
     * Useful for: product detail page, transfer form
     *
     * @return \Illuminate\Support\Collection  [warehouse_id => qty]
     */
    public function getStockBreakdown(ProductSku $sku): \Illuminate\Support\Collection
    {
        return ProductStock::where('product_sku_id', $sku->id)
            ->with('warehouse:id,name')
            ->get()
            ->mapWithKeys(fn($s) => [
                $s->warehouse_id => [
                    'warehouse' => $s->warehouse->name,
                    'qty'       => (float) $s->qty,
                ]
            ]);
    }

    /**
     * Is stock available for a deduction?
     * Use this to validate BEFORE attempting deductStock — e.g. in sale form.
     */
    public function hasStock(ProductSku $sku, int $warehouseId, float $qty): bool
    {
        return $this->getWarehouseStock($sku, $warehouseId) >= $qty;
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE CORE — No DB::transaction() here. Called by public methods.
    // ──────────────────────────────────────────────────────────────

    /**
     * Core add logic — no transaction wrapper (caller owns the transaction).
     */
    private function _add(
        ProductSku $sku,
        int        $warehouseId,
        float      $qty,
        string     $movementType,
        mixed      $reference,
        ?int       $batchId = null,
        ?string    $batchNumber = null,
        ?float     $unitCost = null
    ): void {
        $stock = ProductStock::lockForUpdate()->firstOrCreate(
            [
                'company_id'     => $sku->company_id,
                'product_sku_id' => $sku->id,
                'warehouse_id'   => $warehouseId,
            ],
            ['qty' => 0]
        );

        $stock->increment('qty', $qty);        

        StockMovement::create([
            'company_id'     => $sku->company_id,
            'store_id'       => Auth::user()->store_id ?? $stock->warehouse->store_id, // 🌟 NEW
            'product_sku_id' => $sku->id,
            'warehouse_id'   => $warehouseId,
            'unit_id'        => $sku->product->product_unit_id, // 🌟 NEW
            'batch_id'       => $batchId ?? null,
             'batch_number'   => $batchNumber,
            'unit_cost'      => $unitCost ?? $sku->cost, // 🌟 NEW: Critical for valuation
            'direction'      => 'in', // 🌟 NEW
            'user_id'        => Auth::id(), // 🌟 NEW: The Audit Trail
            'quantity'       => $qty,
            'movement_type'  => $movementType,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id'   => $reference?->id,
            'balance_after'  => $stock->refresh()->qty,
        ]);
    }

    /**
     * Core deduct logic — no transaction wrapper (caller owns the transaction).
     *
     * @throws InsufficientStockException
     */
    private function _deduct(
        ProductSku $sku,
        int        $warehouseId,
        float      $qty,
        string     $movementType,
        mixed      $reference,
        ?float     $unitCost = null // 🌟 NEW: For exact COGS tracking
    ): void {
        $stock = ProductStock::lockForUpdate()
            ->where('company_id', $sku->company_id)
            ->where('product_sku_id', $sku->id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (! $stock || $stock->qty < $qty) {
            throw new InsufficientStockException(
                sku:       $sku,
                available: (float) ($stock?->qty ?? 0),
                requested: $qty
            );
        }

        $stock->decrement('qty', $qty);

        StockMovement::create([
            'company_id'     => $sku->company_id,
            'store_id'       => Auth::user()->store_id ?? $stock->warehouse->store_id, // 🌟 NEW
            'product_sku_id' => $sku->id,
            'warehouse_id'   => $warehouseId,
            'unit_id'        => $sku->product->product_unit_id, // 🌟 NEW
            'unit_cost'      => $unitCost ?? $sku->cost, // 🌟 NEW: Locks in the Cost of Goods Sold!
            'direction'      => 'out', // 🌟 NEW
            'user_id'        => Auth::id(), // 🌟 NEW
            'quantity'       => -$qty,
            'movement_type'  => $movementType,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id'   => $reference?->id,
            'balance_after'  => $stock->refresh()->qty,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // GUARDS
    // ──────────────────────────────────────────────────────────────

    /**
     * Prevent cross-company stock writes.
     * Critical in multi-tenant SaaS — never skip this.
     *
     * @throws \InvalidArgumentException
     */
    private function guardWarehouseBelongsToCompany(int $warehouseId, int $companyId): void
    {
        $exists = Warehouse::where('id', $warehouseId)
            ->where('company_id', $companyId)
            ->exists();

        if (! $exists) {
            throw new \InvalidArgumentException(
                "Warehouse [{$warehouseId}] does not belong to company [{$companyId}]. Possible data leak."
            );
        }
    }
}