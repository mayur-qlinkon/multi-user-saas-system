<?php

namespace App\Services\Import;

use App\Models\Category;
use App\Models\Import;
use App\Models\ImportLog;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductImporter
{
    private const REQUIRED_HEADERS = ['name', 'category_slug'];

    private const VALID_PRODUCT_TYPES = ['sellable', 'catalog'];

    /**
     * Extract the unique key used to detect in-file duplicates for a row.
     * Prefers the provided slug; falls back to slugified name.
     */
    public function extractUniqueKey(array $row): ?string
    {
        $slug = trim($row['slug'] ?? '');
        if ($slug !== '') {
            return Str::slug($slug);
        }

        $name = trim($row['name'] ?? '');
        if ($name === '') {
            return null;
        }

        return Str::slug($name);
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
     * @param  int  $remainingSlots  Max new products that may still be created; PHP_INT_MAX = unlimited.
     * @return array{success: int, failed: int, skipped: int, created: int, updated: int, limit_skipped: int}
     */
    public function processChunk(Import $import, array $rows, int $startRow, int $companyId, int $remainingSlots = PHP_INT_MAX): array
    {
        $success = 0;
        $failed = 0;
        $skipped = 0;
        $created = 0;
        $updated = 0;
        $limitSkipped = 0;
        $importMode = $import->import_mode ?? 'create_or_update';
        $isDryRun = (bool) ($import->is_dry_run ?? false);

        // Pre-load lookups for this company
        $categorySlugs = Category::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->pluck('id', 'slug')
            ->toArray();

        $unitShortNames = Unit::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereNotNull('short_name')
            ->pluck('id', 'short_name')
            ->toArray();

        $productSlugs = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->pluck('id', 'slug')
            ->toArray();

        foreach ($rows as $index => $row) {
            $rowNumber = (int) ($row['_row_number'] ?? ($startRow + $index));

            $row = $this->sanitizeRowToUtf8($row);

            try {
                $errors = $this->validateRow($row, $categorySlugs, $unitShortNames);

                if (! empty($errors)) {
                    $this->logError($import, $rowNumber, $row, implode('; ', $errors));
                    $failed++;

                    continue;
                }

                $rowSkipped = false;
                $rowLimitBlocked = false;
                $rowAction = null;

                try {
                    DB::transaction(function () use ($row, $companyId, $importMode, $isDryRun, $categorySlugs, $unitShortNames, &$productSlugs, &$rowSkipped, &$rowLimitBlocked, &$rowAction, &$remainingSlots) {
                        $name = trim($row['name']);
                        $slug = ! empty($row['slug'])
                            ? Str::slug(trim($row['slug']))
                            : Str::slug($name).'-'.Str::random(5);

                        $categorySlug = Str::slug(trim($row['category_slug']));
                        $categoryId = $categorySlugs[$categorySlug] ?? null;

                        $unitId = null;
                        if (! empty($row['unit'])) {
                            $unitKey = trim($row['unit']);
                            $unitId = $unitShortNames[$unitKey] ?? null;
                        }

                        $productType = 'sellable';
                        if (! empty($row['product_type']) && in_array(strtolower(trim($row['product_type'])), self::VALID_PRODUCT_TYPES, true)) {
                            $productType = strtolower(trim($row['product_type']));
                        }
                        $productGuideArray = null;
                        if (! empty($row['product_guide'])) {
                            $productGuideArray = [];
                            $pairs = explode('|', $row['product_guide']);
                            foreach ($pairs as $pair) {
                                $parts = explode(':', $pair, 2); // Split by the first colon only
                                if (count($parts) === 2) {
                                    $productGuideArray[] = [
                                        'title' => trim($parts[0]),
                                        'description' => trim($parts[1]),
                                    ];
                                }
                            }
                        }

                        $existing = Product::withoutGlobalScopes()
                            ->where('company_id', $companyId)
                            ->where('slug', $slug)
                            ->first();

                        if ($existing) {
                            if ($importMode === 'create_only') {
                                $rowSkipped = true;

                                return;
                            }
                            $existing->update([
                                'name' => $name,
                                'category_id' => $categoryId,
                                'product_unit_id' => $unitId,
                                'sale_unit_id' => $unitId,
                                'purchase_unit_id' => $unitId,
                                'product_type' => $productType,
                                'description' => trim($row['description'] ?? '') ?: $existing->description,
                                'product_guide' => $productGuideArray ?? $existing->product_guide,
                            ]);
                            $rowAction = 'updated';
                        } else {
                            if ($importMode === 'update_only') {
                                $rowSkipped = true;

                                return;
                            }

                            // Plan product limit — block new creates when limit is exhausted.
                            if ($remainingSlots <= 0) {
                                $rowLimitBlocked = true;

                                return;
                            }

                            $product = new Product;
                            $product->company_id = $companyId;
                            $product->name = $name;
                            $product->slug = $slug;
                            $product->category_id = $categoryId;
                            $product->product_unit_id = $unitId;
                            $product->sale_unit_id = $unitId;
                            $product->purchase_unit_id = $unitId;
                            $product->type = 'single'; // Phase 1: all imports are single-type
                            $product->product_type = $productType;
                            $product->description = trim($row['description'] ?? '') ?: null;
                            $product->is_active = true;
                            $product->show_in_storefront = true;
                            $product->product_guide = $productGuideArray;
                            $product->save();

                            $productSlugs[$slug] = $product->id;
                            $remainingSlots--;
                            $rowAction = 'created';
                        }

                        if ($isDryRun && $rowAction !== null) {
                            throw DryRunRollback::for($rowAction);
                        }
                    });
                } catch (DryRunRollback $e) {
                    $rowAction = $e->action;
                }

                if ($rowLimitBlocked) {
                    $this->logError($import, $rowNumber, $row, 'Product limit exceeded. Upgrade your plan to import more products.');
                    $limitSkipped++;
                } elseif ($rowSkipped) {
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

        return [
            ...compact('success', 'failed', 'skipped', 'created', 'updated'),
            'limit_skipped' => $limitSkipped,
        ];
    }

    private function validateRow(array $row, array $categorySlugs, array $unitShortNames): array
    {
        $errors = [];

        if (empty(trim($row['name'] ?? ''))) {
            $errors[] = 'Name is required';
        }

        // Category is required and must exist
        if (empty(trim($row['category_slug'] ?? ''))) {
            $errors[] = 'Category slug is required';
        } else {
            $catSlug = Str::slug(trim($row['category_slug']));
            if (! isset($categorySlugs[$catSlug])) {
                $errors[] = "Category '{$row['category_slug']}' not found. Import categories first.";
            }
        }

        // Unit is optional but must exist if provided
        if (! empty($row['unit'])) {
            $unitKey = trim($row['unit']);
            if (! isset($unitShortNames[$unitKey])) {
                $errors[] = "Unit '{$unitKey}' not found. Import units first.";
            }
        }

        // product_type validation
        if (! empty($row['product_type'])) {
            $pt = strtolower(trim($row['product_type']));
            if (! in_array($pt, self::VALID_PRODUCT_TYPES, true)) {
                $errors[] = "Invalid product_type '{$row['product_type']}'. Must be: sellable or catalog.";
            }
        }

        return $errors;
    }

    /**
     * Sanitize array to strict UTF-8 to prevent JsonEncodingExceptions in logs.
     */
    private function sanitizeRowToUtf8(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            // Convert strings to UTF-8, guessing from common Excel encodings
            $safeKey = is_string($key)
                ? mb_convert_encoding($key, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252')
                : $key;

            $safeValue = is_string($value)
                ? mb_convert_encoding($value, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252')
                : $value;

            $clean[$safeKey] = $safeValue;
        }

        return $clean;
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
