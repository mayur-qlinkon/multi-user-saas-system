<?php

namespace App\Services\Import;

use App\Models\Import;
use App\Models\ImportLog;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Support\Facades\DB;

class SkuImporter
{
    private const REQUIRED_HEADERS = ['product_slug', 'sku', 'price', 'cost'];

    /**
     * Extract the unique key used to detect in-file duplicates for a row.
     * SKUs use the sku code (case-insensitive) as the natural key within a company.
     */
    public function extractUniqueKey(array $row): ?string
    {
        $sku = trim($row['sku'] ?? '');
        if ($sku === '') {
            return null;
        }

        return strtolower($sku);
    }

    /**
     * @return array{valid: bool, message: string}
     */
    public function validateHeaders(array $headers): array
    {
        $headers = array_map(fn ($h) => strtolower(trim($h)), $headers);

        foreach (self::REQUIRED_HEADERS as $required) {
            if (! in_array($required, $headers, true)) {
                return ['valid' => false, 'message' => "Missing required column: {$required}"];
            }
        }

        return ['valid' => true, 'message' => 'Headers valid'];
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     * @return array{success: int, failed: int}
     */
    public function processChunk(Import $import, array $rows, int $startRow, int $companyId): array
    {
        $success = 0;
        $failed = 0;
        $skipped = 0;
        $created = 0;
        $updated = 0;
        $importMode = $import->import_mode ?? 'create_or_update';
        $isDryRun = (bool) ($import->is_dry_run ?? false);

        // Pre-load lookups
        $productSlugs = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->pluck('id', 'slug')
            ->toArray();

        $existingSkus = ProductSku::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->pluck('id', 'sku')
            ->toArray();

        foreach ($rows as $index => $row) {
            $rowNumber = (int) ($row['_row_number'] ?? ($startRow + $index));

            try {
                $errors = $this->validateRow($row, $productSlugs, $existingSkus);

                if (! empty($errors)) {
                    $this->logError($import, $rowNumber, $row, implode('; ', $errors));
                    $failed++;

                    continue;
                }

                $rowSkipped = false;
                $rowAction = null;

                try {
                    DB::transaction(function () use ($row, $companyId, $importMode, $isDryRun, $productSlugs, &$existingSkus, &$rowSkipped, &$rowAction) {
                        $productSlug = trim($row['product_slug']);
                        $productId = $productSlugs[$productSlug] ?? null;
                        $skuCode = trim($row['sku']);
                        $price = (float) $row['price'];
                        $cost = (float) $row['cost'];
                        $mrp = ! empty($row['mrp']) ? (float) $row['mrp'] : null;
                        $barcode = ! empty($row['barcode']) ? trim($row['barcode']) : null;
                        $stockAlert = ! empty($row['stock_alert']) ? (int) $row['stock_alert'] : 0;

                        $existing = ProductSku::withoutGlobalScopes()
                            ->where('company_id', $companyId)
                            ->where('sku', $skuCode)
                            ->first();

                        if ($existing) {
                            if ($importMode === 'create_only') {
                                $rowSkipped = true;

                                return;
                            }
                            $existing->update([
                                'product_id' => $productId,
                                'price' => $price,
                                'cost' => $cost,
                                'mrp' => $mrp ?? $existing->mrp,
                                'barcode' => $barcode ?? $existing->barcode,
                                'stock_alert' => $stockAlert,
                            ]);
                            $rowAction = 'updated';
                        } else {
                            if ($importMode === 'update_only') {
                                $rowSkipped = true;

                                return;
                            }
                            $sku = new ProductSku;
                            $sku->company_id = $companyId;
                            $sku->product_id = $productId;
                            $sku->sku = $skuCode;
                            $sku->price = $price;
                            $sku->cost = $cost;
                            $sku->mrp = $mrp;
                            $sku->barcode = $barcode;
                            $sku->stock_alert = $stockAlert;
                            $sku->tax_type = 'exclusive';
                            $sku->order_tax = 0;
                            $sku->is_active = true;
                            $sku->save();

                            $existingSkus[$skuCode] = $sku->id;
                            $rowAction = 'created';
                        }

                        if ($isDryRun && $rowAction !== null) {
                            throw DryRunRollback::for($rowAction);
                        }
                    });
                } catch (DryRunRollback $e) {
                    $rowAction = $e->action;
                }

                if ($rowSkipped) {
                    $skipped++;
                } else {
                    $success++;
                    if ($rowAction === 'created') {
                        $created++;
                    } elseif ($rowAction === 'updated') {
                        $updated++;
                    }
                }
            } catch (\Throwable $e) {
                $this->logError($import, $rowNumber, $row, 'Unexpected error: '.$e->getMessage());
                $failed++;
            }
        }

        return compact('success', 'failed', 'skipped', 'created', 'updated');
    }

    private function validateRow(array $row, array $productSlugs, array $existingSkus): array
    {
        $errors = [];

        // product_slug required and must exist
        if (empty(trim($row['product_slug'] ?? ''))) {
            $errors[] = 'Product slug is required';
        } else {
            $slug = trim($row['product_slug']);
            if (! isset($productSlugs[$slug])) {
                $errors[] = "Product '{$slug}' not found. Import products first.";
            }
        }

        // sku required
        if (empty(trim($row['sku'] ?? ''))) {
            $errors[] = 'SKU code is required';
        }

        // price required and numeric
        if (empty(trim($row['price'] ?? ''))) {
            $errors[] = 'Price is required';
        } elseif (! is_numeric($row['price']) || (float) $row['price'] < 0) {
            $errors[] = "Invalid price: '{$row['price']}'";
        }

        // cost required and numeric
        if (empty(trim($row['cost'] ?? ''))) {
            $errors[] = 'Cost is required';
        } elseif (! is_numeric($row['cost']) || (float) $row['cost'] < 0) {
            $errors[] = "Invalid cost: '{$row['cost']}'";
        }

        // mrp optional but numeric if provided
        if (! empty($row['mrp']) && (! is_numeric($row['mrp']) || (float) $row['mrp'] < 0)) {
            $errors[] = "Invalid MRP: '{$row['mrp']}'";
        }

        return $errors;
    }

    private function logError(Import $import, int $rowNumber, array $rowData, string $message): void
    {
        ImportLog::create([
            'import_id' => $import->id,
            'row_number' => $rowNumber,
            'row_data' => array_filter($rowData, fn ($v, $k) => ! str_starts_with((string) $k, '_'), ARRAY_FILTER_USE_BOTH),
            'error_message' => $message,
        ]);
    }
}
