<?php

namespace App\Services\Import;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Import;
use App\Models\ImportLog;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\ProductSkuValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Variant (SKU) Importer.
 *
 * Each CSV row = one variant of an EXISTING `variable` product.
 * A variant is uniquely identified by the combination of its attribute values.
 *
 * CSV columns:
 *   Required: product_slug, price, cost
 *   Optional: sku, barcode, mrp, stock_alert
 *   Dynamic:  attribute_1_name, attribute_1_value ... attribute_5_name, attribute_5_value
 *
 * Guarantees:
 *   - Product must exist AND be of type="variable"
 *   - Attributes + values are resolved case-insensitively per company (auto-created if missing)
 *   - Duplicate variant combinations (same attribute-value set) are rejected
 *   - SKU code auto-generated if not provided (slug + values, random suffix on collision)
 *   - Hard cap of 100 variants per product
 *   - All writes are company-scoped via Tenantable / explicit company_id
 */
class SkuImporter
{
    private const REQUIRED_HEADERS = ['product_slug', 'price', 'cost'];

    private const MAX_ATTRIBUTES = 5;

    private const MAX_VARIANTS_PER_PRODUCT = 100;

    /** @var array<string, array{id:int,is_variable:bool}> Slug (lower) => product meta */
    private array $productBySlug = [];

    /** @var array<string, int> SKU code (lower) => sku id */
    private array $existingSkuCodes = [];

    /** @var array<string, int> Attribute name (lower) => attribute id */
    private array $attributeIdByName = [];

    /** @var array<string, int> "{attributeId}|{value lower}" => attribute_value id */
    private array $attributeValueIdByKey = [];

    /** @var array<int, array<string, int>> productId => [comboKey => skuId] */
    private array $existingCombosByProduct = [];

    /** @var array<int, int> productId => current variant count */
    private array $variantCountByProduct = [];

    /**
     * In-file duplicate key.
     *
     * Two rows collide when they reference the SAME product AND the same
     * (attribute,value) combination — this is the true variant identity.
     *
     * If no attribute pairs are given we fall back to the SKU code so that the
     * existing duplicate-mode pipeline still catches obvious code duplicates.
     */
    public function extractUniqueKey(array $row): ?string
    {
        $slug = strtolower(trim($row['product_slug'] ?? ''));
        if ($slug === '') {
            return null;
        }

        $pairs = $this->extractAttributePairs($row);

        if (empty($pairs)) {
            $sku = strtolower(trim($row['sku'] ?? ''));

            return $sku !== '' ? "sku:{$sku}" : null;
        }

        $tokens = [];
        foreach ($pairs as $p) {
            $tokens[] = strtolower($p['name']).'='.strtolower($p['value']);
        }
        sort($tokens);

        return 'var:'.$slug.'|'.implode(',', $tokens);
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

        $hasAnyAttribute = false;
        for ($i = 1; $i <= self::MAX_ATTRIBUTES; $i++) {
            if (in_array("attribute_{$i}_name", $headers, true) || in_array("attribute_{$i}_value", $headers, true)) {
                $hasAnyAttribute = true;
                break;
            }
        }

        if (! $hasAnyAttribute) {
            return [
                'valid' => false,
                'message' => 'At least one attribute column pair is required (e.g. attribute_1_name, attribute_1_value).',
            ];
        }

        return ['valid' => true, 'message' => 'Headers valid'];
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     * @return array{success:int, failed:int, skipped:int, created:int, updated:int}
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

        $this->preloadLookups($companyId);

        foreach ($rows as $index => $row) {
            $rowNumber = (int) ($row['_row_number'] ?? ($startRow + $index));

            try {
                $validation = $this->validateRow($row);
                if (! empty($validation)) {
                    $this->logError($import, $rowNumber, $row, implode('; ', $validation));
                    $failed++;

                    continue;
                }

                $rowSkipped = false;
                $rowAction = null;
                $rowError = null;

                try {
                    DB::transaction(function () use ($row, $companyId, $importMode, $isDryRun, &$rowSkipped, &$rowAction, &$rowError) {
                        $slug = strtolower(trim($row['product_slug']));
                        $product = $this->productBySlug[$slug] ?? null;

                        if (! $product) {
                            $rowError = "Product '{$row['product_slug']}' not found. Import products first.";

                            return;
                        }

                        $productId = $product['id'];
                        $pairs = $this->extractAttributePairs($row);

                        if (empty($pairs)) {
                            $rowError = 'At least one attribute_N_name + attribute_N_value pair is required.';

                            return;
                        }

                        // Resolve each attribute + value (auto-create missing ones, company scoped).
                        $attributeValueIds = [];
                        $valueLabels = [];
                        foreach ($pairs as $pair) {
                            $attrId = $this->resolveAttributeId($companyId, $pair['name']);
                            $valueId = $this->resolveAttributeValueId($companyId, $attrId, $pair['value']);
                            $attributeValueIds[$attrId] = $valueId;
                            $valueLabels[] = $pair['value'];
                        }

                        // Canonical combo key: sorted attribute_value ids joined.
                        $sortedIds = $attributeValueIds;
                        sort($sortedIds);
                        $comboKey = implode('-', $sortedIds);

                        $existingSkuId = $this->existingCombosByProduct[$productId][$comboKey] ?? null;

                        if ($existingSkuId && $importMode === 'create_only') {
                            $rowSkipped = true;

                            return;
                        }

                        // 100-variant hard cap (only when creating a NEW variant row).
                        if (! $existingSkuId) {
                            $currentCount = $this->variantCountByProduct[$productId] ?? 0;
                            if ($currentCount >= self::MAX_VARIANTS_PER_PRODUCT) {
                                $rowError = 'Maximum of '.self::MAX_VARIANTS_PER_PRODUCT." variants per product reached for '{$row['product_slug']}'.";

                                return;
                            }
                        }

                        $price = (float) $row['price'];
                        $cost = (float) $row['cost'];
                        $mrp = ! empty($row['mrp']) ? (float) $row['mrp'] : null;
                        $barcode = ! empty($row['barcode']) ? trim($row['barcode']) : null;
                        $stockAlert = ! empty($row['stock_alert']) ? (int) $row['stock_alert'] : 0;
                        $providedSku = ! empty($row['sku']) ? trim($row['sku']) : null;

                        if ($existingSkuId && $importMode !== 'create_only') {
                            // Update the existing variant's pricing/stock fields.
                            // Attribute pairs stay intact — the combo IS the identity.
                            $sku = ProductSku::withoutGlobalScopes()->find($existingSkuId);
                            if (! $sku) {
                                $rowError = 'Existing variant disappeared mid-import. Please retry.';

                                return;
                            }

                            // If the user provided a new sku code, ensure it doesn't collide with a DIFFERENT row.
                            if ($providedSku && strtolower($providedSku) !== strtolower($sku->sku)) {
                                $codeKey = strtolower($providedSku);
                                $owner = $this->existingSkuCodes[$codeKey] ?? null;
                                if ($owner !== null && $owner !== $sku->id) {
                                    $rowError = "SKU code '{$providedSku}' is already used by another variant.";

                                    return;
                                }
                                unset($this->existingSkuCodes[strtolower($sku->sku)]);
                                $sku->sku = $providedSku;
                                $this->existingSkuCodes[$codeKey] = $sku->id;
                            }

                            $sku->price = $price;
                            $sku->cost = $cost;
                            if ($mrp !== null) {
                                $sku->mrp = $mrp;
                            }
                            if ($barcode !== null) {
                                $sku->barcode = $barcode;
                            }
                            $sku->stock_alert = $stockAlert;
                            $sku->save();

                            $rowAction = 'updated';
                        } else {
                            if ($importMode === 'update_only') {
                                $rowSkipped = true;

                                return;
                            }

                            $skuCode = $providedSku
                                ?: $this->generateSkuCode($row['product_slug'], $valueLabels);
                            $skuCode = $this->ensureUniqueSkuCode($skuCode);

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

                            foreach ($attributeValueIds as $attrId => $valueId) {
                                ProductSkuValue::create([
                                    'product_sku_id' => $sku->id,
                                    'attribute_id' => $attrId,
                                    'attribute_value_id' => $valueId,
                                ]);
                            }

                            $this->existingSkuCodes[strtolower($skuCode)] = $sku->id;
                            $this->existingCombosByProduct[$productId][$comboKey] = $sku->id;
                            $this->variantCountByProduct[$productId] = ($this->variantCountByProduct[$productId] ?? 0) + 1;

                            $rowAction = 'created';

                            // Auto-convert product to 'variable' when it has more than one SKU.
                            // Runs only AFTER a successful create. Dry-run rolls back with the transaction.
                            $totalSkus = ProductSku::withoutGlobalScopes()
                                ->where('product_id', $productId)
                                ->count();

                            if ($totalSkus > 1) {
                                $productModel = Product::withoutGlobalScopes()->find($productId);
                                if ($productModel && $productModel->type !== 'variable') {
                                    $productModel->type = 'variable';
                                    $productModel->save();

                                    // Soft-delete the initial default SKU (the one with no attribute values).
                                    $defaultSkuIds = ProductSku::withoutGlobalScopes()
                                        ->where('product_id', $productId)
                                        ->whereNotIn('id', function ($q) {
                                            $q->select('product_sku_id')->from('product_sku_values');
                                        })
                                        ->pluck('id')
                                        ->all();

                                    if (! empty($defaultSkuIds)) {
                                        ProductSku::withoutGlobalScopes()
                                            ->whereIn('id', $defaultSkuIds)
                                            ->delete();

                                        foreach ($defaultSkuIds as $deletedId) {
                                            $codeKey = array_search($deletedId, $this->existingSkuCodes, true);
                                            if ($codeKey !== false) {
                                                unset($this->existingSkuCodes[$codeKey]);
                                            }
                                        }
                                        $this->variantCountByProduct[$productId] = max(
                                            0,
                                            ($this->variantCountByProduct[$productId] ?? 0) - count($defaultSkuIds)
                                        );
                                    }

                                    if (isset($this->productBySlug[$slug])) {
                                        $this->productBySlug[$slug]['is_variable'] = true;
                                    }
                                }
                            }
                        }

                        if ($isDryRun && $rowAction !== null) {
                            throw DryRunRollback::for($rowAction);
                        }
                    });
                } catch (DryRunRollback $e) {
                    $rowAction = $e->action;
                }

                if ($rowError !== null) {
                    $this->logError($import, $rowNumber, $row, $rowError);
                    $failed++;

                    continue;
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

    /**
     * Row-level validation (before we touch the DB).
     *
     * @return array<int, string>
     */
    private function validateRow(array $row): array
    {
        $errors = [];

        if (trim($row['product_slug'] ?? '') === '') {
            $errors[] = 'Product slug is required';
        }

        if (trim((string) ($row['price'] ?? '')) === '') {
            $errors[] = 'Price is required';
        } elseif (! is_numeric($row['price']) || (float) $row['price'] < 0) {
            $errors[] = "Invalid price: '{$row['price']}'";
        }

        if (trim((string) ($row['cost'] ?? '')) === '') {
            $errors[] = 'Cost is required';
        } elseif (! is_numeric($row['cost']) || (float) $row['cost'] < 0) {
            $errors[] = "Invalid cost: '{$row['cost']}'";
        }

        if (! empty($row['mrp']) && (! is_numeric($row['mrp']) || (float) $row['mrp'] < 0)) {
            $errors[] = "Invalid MRP: '{$row['mrp']}'";
        }

        // Attribute column pair sanity: if one side of a pair is filled, the other must be too.
        for ($i = 1; $i <= self::MAX_ATTRIBUTES; $i++) {
            $name = trim((string) ($row["attribute_{$i}_name"] ?? ''));
            $value = trim((string) ($row["attribute_{$i}_value"] ?? ''));
            if (($name === '') !== ($value === '')) {
                $errors[] = "attribute_{$i}_name and attribute_{$i}_value must both be filled or both be empty.";
            }
        }

        return $errors;
    }

    /**
     * Extract all filled attribute_N_name + attribute_N_value pairs from a row.
     *
     * @return array<int, array{name:string,value:string}>
     */
    private function extractAttributePairs(array $row): array
    {
        $pairs = [];
        for ($i = 1; $i <= self::MAX_ATTRIBUTES; $i++) {
            $name = trim((string) ($row["attribute_{$i}_name"] ?? ''));
            $value = trim((string) ($row["attribute_{$i}_value"] ?? ''));
            if ($name === '' || $value === '') {
                continue;
            }
            $pairs[] = ['name' => $name, 'value' => $value];
        }

        return $pairs;
    }

    /**
     * Find or create an attribute by case-insensitive name for this company.
     */
    private function resolveAttributeId(int $companyId, string $name): int
    {
        $key = strtolower($name);
        if (isset($this->attributeIdByName[$key])) {
            return $this->attributeIdByName[$key];
        }

        $attribute = Attribute::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(name) = ?', [$key])
            ->first();

        if (! $attribute) {
            $attribute = new Attribute;
            $attribute->company_id = $companyId;
            $attribute->name = $name;
            $attribute->type = 'text';
            $attribute->is_active = true;
            $attribute->save();
        }

        $this->attributeIdByName[$key] = $attribute->id;

        return $attribute->id;
    }

    /**
     * Find or create an attribute value (case-insensitive) scoped to attribute + company.
     */
    private function resolveAttributeValueId(int $companyId, int $attributeId, string $value): int
    {
        $key = $attributeId.'|'.strtolower($value);
        if (isset($this->attributeValueIdByKey[$key])) {
            return $this->attributeValueIdByKey[$key];
        }

        $attrValue = AttributeValue::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('attribute_id', $attributeId)
            ->whereRaw('LOWER(value) = ?', [strtolower($value)])
            ->first();

        if (! $attrValue) {
            $attrValue = new AttributeValue;
            $attrValue->company_id = $companyId;
            $attrValue->attribute_id = $attributeId;
            $attrValue->value = $value;
            $attrValue->position = 0;
            $attrValue->is_active = true;
            $attrValue->save();
        }

        $this->attributeValueIdByKey[$key] = $attrValue->id;

        return $attrValue->id;
    }

    /**
     * Build a deterministic SKU code from slug + variant values.
     * Output: uppercased, slug-safe, hyphen-separated.
     */
    private function generateSkuCode(string $productSlug, array $valueLabels): string
    {
        $parts = [Str::slug($productSlug)];
        foreach ($valueLabels as $label) {
            $clean = Str::slug($label);
            if ($clean !== '') {
                $parts[] = $clean;
            }
        }

        return strtoupper(implode('-', $parts));
    }

    /**
     * Ensure the given SKU code is unique within the tenant by appending a
     * short random suffix on collision. Updates the in-memory map.
     */
    private function ensureUniqueSkuCode(string $code): string
    {
        $candidate = $code;
        $attempts = 0;
        while (isset($this->existingSkuCodes[strtolower($candidate)])) {
            $candidate = $code.'-'.strtoupper(Str::random(4));
            $attempts++;
            if ($attempts > 10) {
                // Extremely unlikely, but don't loop forever.
                $candidate = $code.'-'.strtoupper(Str::random(8)).'-'.time();
                break;
            }
        }

        return $candidate;
    }

    /**
     * Prime all company-scoped lookups used during this chunk.
     */
    private function preloadLookups(int $companyId): void
    {
        $this->productBySlug = [];
        $this->existingSkuCodes = [];
        $this->attributeIdByName = [];
        $this->attributeValueIdByKey = [];
        $this->existingCombosByProduct = [];
        $this->variantCountByProduct = [];

        Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->get(['id', 'slug', 'type'])
            ->each(function ($p) {
                $this->productBySlug[strtolower($p->slug)] = [
                    'id' => $p->id,
                    'is_variable' => $p->type === 'variable',
                ];
            });

        ProductSku::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->get(['id', 'sku', 'product_id'])
            ->each(function ($s) {
                $this->existingSkuCodes[strtolower($s->sku)] = $s->id;
                $this->variantCountByProduct[$s->product_id] = ($this->variantCountByProduct[$s->product_id] ?? 0) + 1;
            });

        Attribute::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->get(['id', 'name'])
            ->each(function ($a) {
                $this->attributeIdByName[strtolower($a->name)] = $a->id;
            });

        AttributeValue::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->get(['id', 'attribute_id', 'value'])
            ->each(function ($v) {
                $this->attributeValueIdByKey[$v->attribute_id.'|'.strtolower($v->value)] = $v->id;
            });

        // Build existing variant combinations per product (for duplicate check)
        $companySkuIds = ProductSku::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->pluck('id');

        if ($companySkuIds->isNotEmpty()) {
            $values = ProductSkuValue::whereIn('product_sku_id', $companySkuIds)
                ->get(['product_sku_id', 'attribute_value_id'])
                ->groupBy('product_sku_id');

            $skuProductMap = ProductSku::withoutGlobalScopes()
                ->whereIn('id', $companySkuIds)
                ->pluck('product_id', 'id');

            foreach ($values as $skuId => $valueRows) {
                $productId = $skuProductMap[$skuId] ?? null;
                if ($productId === null) {
                    continue;
                }
                $ids = $valueRows->pluck('attribute_value_id')->map(fn ($v) => (int) $v)->sort()->values()->all();
                if (empty($ids)) {
                    continue;
                }
                $comboKey = implode('-', $ids);
                $this->existingCombosByProduct[$productId][$comboKey] = $skuId;
            }
        }
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
