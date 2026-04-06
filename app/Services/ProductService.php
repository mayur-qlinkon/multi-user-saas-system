<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Category;
use App\Models\ProductMedia;
use App\Models\StockMovement;
use App\Models\AttributeValue;
use App\Models\CategoryProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductService
{
    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }
    /**
     * Get paginated products with advanced filtering and eager loading.
     */
    public function getProductsList(array $filters = [], int $perPage = 15)
    {
        // Eager load relationships to prevent N+1 performance issues
        $query = Product::with(['category', 'media', 'skus.stocks']);

        // 1. Search Filter (Search by Product Name, SKU, or Barcode)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('skus', function ($skuQuery) use ($search) {
                      $skuQuery->where('sku', 'like', "%{$search}%")
                               ->orWhere('barcode', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Category Filter — now uses pivot (supports multi-category)
        if (!empty($filters['category_id'])) {
            $query->whereHas('categoryPivots', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        // 3. Status Filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    /**
     * Toggle the active status of a product.
     */
    public function toggleStatus(Product $product): bool
    {
        $product->is_active = !$product->is_active;
        $saved = $product->save();

        // Mirror active state to all pivot rows
        // So storefront queries stay consistent
        if ($saved) {
            CategoryProduct::where('product_id', $product->id)
                ->update(['is_active' => $product->is_active]);
        }

        return $saved;
    }

    /**
     * Soft delete the product.
     */
    public function deleteProduct(Product $product): bool
    {
        // Because of SoftDeletes, this just sets deleted_at.
        // It keeps the history intact for past invoices!
        return $product->delete();
    }

    public function duplicateProduct(Product $product): Product
    {
        $product->loadMissing(['skus.skuValues', 'media', 'categoryPivots']);
        return DB::transaction(function () use ($product) {

            // ── Clone core product ──
            $newProduct = $product->replicate([
                'created_at', 'updated_at', 'deleted_at',
            ]);

            $newProduct->name              = 'Copy of ' . $product->name;
            $newProduct->slug              = null; // let model boot regenerate
            $newProduct->is_active         = false;
            $newProduct->show_in_storefront = false;
            $newProduct->save();

            // ── Clone SKUs ──
            foreach ($product->skus as $sku) {
                $newSku = $sku->replicate(['created_at', 'updated_at']);
                $newSku->product_id = $newProduct->id;

                // Make SKU unique — append -COPY suffix
                $newSku->sku = $sku->sku . '-COPY';
                if (ProductSku::where('company_id', $product->company_id)
                    ->where('sku', $newSku->sku)->exists()) {
                    $newSku->sku .= '-' . strtoupper(Str::random(3));
                }
                $newSku->save();

                // ── Clone SKU attribute values ──
                foreach ($sku->skuValues as $skuValue) {
                    $newSku->skuValues()->create([
                        'attribute_id'       => $skuValue->attribute_id,
                        'attribute_value_id' => $skuValue->attribute_value_id,
                    ]);
                }
                // Note: stock is NOT cloned — new product starts at zero stock
            }

            // ── Clone media records (same file paths, no re-upload) ──
            foreach ($product->media as $media) {
                $newProduct->media()->create([
                    'media_type' => $media->media_type,
                    'media_path' => $media->media_path,
                    'is_primary' => $media->is_primary,
                    'sort_order' => $media->sort_order,
                ]);
            }

            // ── Clone category pivots ──
            foreach ($product->categoryPivots as $pivot) {
                CategoryProduct::attachProduct(
                    categoryId: $pivot->category_id,
                    productId:  $newProduct->id,
                    isActive:   false, // always inactive on duplicate
                );
            }

            Log::info('[ProductService] Duplicated', [
                'original_id' => $product->id,
                'new_id'      => $newProduct->id,
            ]);

            return $newProduct;
        });
    }

    public function createProduct(array $data, int $companyId): Product
    {
        return DB::transaction(function () use ($data, $companyId) {
            
            // 1. Create the Parent Product
            $primaryCategoryId = !empty($data['categories'])
                ? (int) $data['categories'][0]
                : ($data['category_id'] ?? null);

            $product = Product::create([
                'category_id'       => $primaryCategoryId,
                'supplier_id'       => $data['supplier_id'] ?? null,
                'product_unit_id'   => $data['product_unit_id'],
                'sale_unit_id'      => $data['sale_unit_id'],
                'purchase_unit_id'  => $data['purchase_unit_id'],
                'quantity_limitation' => $data['quantity_limitation'] ?? null,
                'name'              => $data['name'],
                'type'              => $data['type'],
                'barcode_symbology' => $data['barcode_symbology'],
                'hsn_code'          => $data['hsn_code'] ?? null,
                'description'       => $data['description'] ?? null,
                'product_guide'     => $data['product_guide'] ?? null,
                'is_active'         => $data['is_active'] ?? true,
            ]);

            $skuMap = []; // 🌟 Initialize map BEFORE creating SKUs

            // 2. Handle Single Product Setup
            if ($data['type'] === 'single') {
                $sku = $product->skus()->create([
                    'sku'         => $data['single_sku'],
                    'barcode'     => $data['single_barcode'] ?? null,
                    'price'       => $data['single_price'],
                    'cost'        => $data['single_cost'],
                    'mrp'         => $data['single_mrp'] ?? 0,
                    'stock_alert' => $data['single_stock_alert'] ?? 0,
                    'order_tax'   => $data['single_order_tax'] ?? 0,
                    'tax_type'    => $data['single_tax_type'] ?? 'exclusive',
                ]);

                if (!empty($data['single_stock'])) {
                    $this->processInitialStock($sku, $data['single_stock']);
                }
            }

            // 3. Handle Variable Product Setup
            if ($data['type'] === 'variable') {
                // 🌟 Added $varIndex to map the frontend array position to the DB ID
                foreach ($data['variations'] as $varIndex => $varData) { 
                    
                    $selectedAttrValIds = array_filter(array_values($varData['attrs'] ?? []));

                    $skuString = !empty($varData['sku']) 
                        ? $varData['sku'] 
                        : $this->generateVariableSku($data['name'], $selectedAttrValIds, $companyId);

                    $sku = $product->skus()->create([
                        'sku'         => $skuString,
                        'barcode'     => $varData['barcode'] ?? null,
                        'price'       => $varData['price'],
                        'cost'        => $varData['cost'],
                        'mrp'         => $varData['mrp'] ?? 0,
                        'stock_alert' => $varData['stock_alert'] ?? 0,
                        'order_tax'   => $varData['order_tax'] ?? 0,
                        'tax_type'    => $varData['tax_type'] ?? 'exclusive',
                    ]);

                    // 🌟 Map the frontend array index to the actual Database ID
                    $skuMap[$varIndex] = $sku->id;

                    if (isset($varData['attrs'])) {
                        foreach ($varData['attrs'] as $attrId => $attrValId) {
                            if (!empty($attrValId)) { 
                                $sku->skuValues()->create([
                                    'attribute_id'       => $attrId,
                                    'attribute_value_id' => $attrValId,
                                ]);
                            }
                        }
                    }

                    if (!empty($varData['stock'])) {
                        $this->processInitialStock($sku, $varData['stock']);
                    }
                }
            }

            // 4. 🌟 MOVED & UPDATED: Handle Dynamic Media (Images & YouTube)
            // It now runs AFTER SKUs exist so we can link them properly.
            if (!empty($data['media'])) {
                foreach ($data['media'] as $index => $mediaItem) {
                    
                    // 🌟 Resolve the actual SKU ID from our map
                    $productSkuId = (isset($mediaItem['sku_index']) && $mediaItem['sku_index'] !== '') 
                        ? ($skuMap[$mediaItem['sku_index']] ?? null) 
                        : null;

                    if ($mediaItem['type'] === 'image' && isset($mediaItem['file'])) {
                        $path = $this->imageService->upload($mediaItem['file'], 'products', [
                            'width'   => 800,
                            'height'  => 800,
                            'crop'    => true,
                            'format'  => 'webp',
                            'quality' => 80
                        ]);
                        
                        $product->media()->create([
                            'product_sku_id' => $productSkuId, // 🌟 Assign SKU
                            'media_type'     => 'image',
                            'media_path'     => $path,
                            'is_primary'     => (isset($data['primary_media_index']) && (int)$data['primary_media_index'] === $index),
                            'sort_order'     => $index,
                        ]);
                        
                    } elseif ($mediaItem['type'] === 'youtube' && !empty($mediaItem['url'])) {
                        $product->media()->create([
                            'product_sku_id' => $productSkuId, // 🌟 Assign SKU
                            'media_type'     => 'youtube',
                            'media_path'     => $mediaItem['url'],
                            'is_primary'     => false,
                            'sort_order'     => $index,
                        ]);
                    }
                }
            }

            // 5. ── Sync categories to pivot table ──
            $categoryIds = $data['categories']
                ?? ($data['category_id'] ? [$data['category_id']] : []);

            if (!empty($categoryIds)) {
                $this->syncCategories($product, $categoryIds);
            }

            return $product;
        });
    }

    /**
     * Update an existing product, syncing its SKUs, Attributes, and Media.
     */
    public function updateProduct(Product $product, array $data, int $companyId): Product
    {
        return DB::transaction(function () use ($product, $data, $companyId) {
            
            // 1. Update Core Product Details
            $primaryCategoryId = !empty($data['categories'])
                ? (int) $data['categories'][0]
                : ($data['category_id'] ?? $product->category_id);

            $product->update([
                'category_id'       => $primaryCategoryId,
                'supplier_id'       => $data['supplier_id'] ?? null,
                'product_unit_id'   => $data['product_unit_id'],
                'sale_unit_id'      => $data['sale_unit_id'],
                'purchase_unit_id'  => $data['purchase_unit_id'],
                'name'              => $data['name'],
                'type'              => $data['type'],
                'barcode_symbology' => $data['barcode_symbology'],
                'hsn_code'          => $data['hsn_code'] ?? null,
                'quantity_limitation' => $data['quantity_limitation'] ?? null,
                'description'       => $data['description'] ?? null,
                'product_guide'     => $data['product_guide'] ?? null,
                'is_active'         => $data['is_active'] ?? true,
            ]);

            $skuMap = []; // 🌟 Initialize map BEFORE variation loop

            // 2. Handle Single Product Sync
            if ($data['type'] === 'single') {
                // If they changed from Variable to Single, delete old variations
                $product->skus()->where('sku', '!=', $data['single_sku'])->delete();

                $product->skus()->updateOrCreate(
                    ['product_id' => $product->id], // Find existing
                    [
                        'sku'         => $data['single_sku'],
                        'barcode'     => $data['single_barcode'] ?? null,
                        'price'       => $data['single_price'],
                        'cost'        => $data['single_cost'],
                        'mrp'         => $data['single_mrp'] ?? 0,
                        'stock_alert' => $data['single_stock_alert'] ?? 0,
                    ]
                );
            }

            // 3. Handle Variable Product Sync
            if ($data['type'] === 'variable') {
                
                $keptSkuIds = [];

                // 🌟 Added $varIndex to map the frontend array position to the DB ID
                foreach ($data['variations'] as $varIndex => $varData) { 
                    
                    $selectedAttrValIds = array_filter(array_values($varData['attrs'] ?? []));
                    
                    $skuString = !empty($varData['sku']) 
                        ? $varData['sku'] 
                        : $this->generateVariableSku($data['name'], $selectedAttrValIds, $companyId);

                    // Update existing variation, or create a new one
                    $sku = $product->skus()->updateOrCreate(
                        [
                            'id' => $varData['id'] ?? null, // If frontend sends ID, update it
                            'product_id' => $product->id 
                        ],
                        [
                            'sku'         => $skuString,
                            'barcode'     => $varData['barcode'] ?? null,
                            'price'       => $varData['price'],
                            'cost'        => $varData['cost'],
                            'mrp'         => $varData['mrp'] ?? 0,
                            'stock_alert' => $varData['stock_alert'] ?? 0,
                        ]
                    );

                    $keptSkuIds[] = $sku->id;
                    $skuMap[$varIndex] = $sku->id; // 🌟 MAP IT: Link frontend index to DB ID

                    // ONLY add stock if this is a brand new variation being added during the edit
                    if ($sku->wasRecentlyCreated && !empty($varData['stock'])) {
                        $this->processInitialStock($sku, $varData['stock']);
                    }

                    // Sync Attributes (Delete old ones, recreate new ones)
                    $sku->skuValues()->delete();
                    foreach ($varData['attrs'] ?? [] as $attrId => $attrValId) {
                        if (!empty($attrValId)) {
                            $sku->skuValues()->create([
                                'attribute_id'       => $attrId,
                                'attribute_value_id' => $attrValId,
                            ]);
                        }
                    }
                }

                // Delete any SKUs that were removed from the frontend UI
                $product->skus()->whereNotIn('id', $keptSkuIds)->delete();
            }

            // 4. Handle Media Sync (Update, Delete, Reorder)
            $keptMediaIds = [];

            if (!empty($data['media'])) {
                foreach ($data['media'] as $index => $mediaItem) {
                    $isPrimary = (isset($data['primary_media_index']) && (int)$data['primary_media_index'] === $index);

                    // 🌟 Resolve the actual SKU ID from our map
                    $productSkuId = (isset($mediaItem['sku_index']) && $mediaItem['sku_index'] !== '') 
                        ? ($skuMap[$mediaItem['sku_index']] ?? null) 
                        : null;

                    if (!empty($mediaItem['id'])) {
                        // A. EXISTING MEDIA: Update sort order, primary status, and SKU assignment
                        $existingMedia = $product->media()->find($mediaItem['id']);
                        if ($existingMedia) {
                            $existingMedia->update([
                                'product_sku_id' => $productSkuId, // 🌟 Assign/Update SKU mapping
                                'sort_order'     => $index,
                                'is_primary'     => $isPrimary,
                            ]);
                            $keptMediaIds[] = $existingMedia->id;
                        }
                    } else {
                        // B. NEW MEDIA
                        if ($mediaItem['type'] === 'image' && isset($mediaItem['file'])) {
                            // Upload new image with WebP Compression & Resizing
                            $path = $this->imageService->upload($mediaItem['file'], 'products', [
                                'width'   => 800,
                                'height'  => 800,
                                'crop'    => true,
                                'format'  => 'webp',
                                'quality' => 80
                            ]);
                            $newMedia = $product->media()->create([
                                'product_sku_id' => $productSkuId, // 🌟 Assign SKU
                                'media_type'     => 'image',
                                'media_path'     => $path,
                                'is_primary'     => $isPrimary,
                                'sort_order'     => $index,
                            ]);
                            $keptMediaIds[] = $newMedia->id;
                            
                        } elseif ($mediaItem['type'] === 'youtube' && !empty($mediaItem['url'])) {
                            // Save new YouTube link
                            $newMedia = $product->media()->create([
                                'product_sku_id' => $productSkuId, // 🌟 Assign SKU
                                'media_type'     => 'youtube',
                                'media_path'     => $mediaItem['url'],
                                'is_primary'     => false,
                                'sort_order'     => $index,
                            ]);
                            $keptMediaIds[] = $newMedia->id;
                        }
                    }
                }
            }

            // C. CLEANUP: Delete any media that was removed from the UI
            $mediaToDelete = $product->media()->whereNotIn('id', $keptMediaIds)->get();
            foreach ($mediaToDelete as $oldMedia) {
                // Delete the physical file from the server if it's an image
                if ($oldMedia->media_type === 'image') {
                    $isShared = ProductMedia::where('media_path', $oldMedia->media_path)
                                    ->where('id', '!=', $oldMedia->id)
                                    ->exists();

                    if (!$isShared && method_exists($this->imageService, 'delete')) {
                        $this->imageService->delete($oldMedia->media_path);
                    }
                }
                // Delete the database record
                $oldMedia->delete();
            }

            // ── Sync categories to pivot table ──
            $categoryIds = $data['categories']
                ?? ($data['category_id'] ? [$data['category_id']] : []);

            if (!empty($categoryIds)) {
                $this->syncCategories($product, $categoryIds);
            }

            return $product;
        });
    }



    /**
     * 🌟 NEW REUSABLE FUNCTION: Generate a smart SKU based on Product Name and Attributes
     */
  
    public function generateVariableSku(string $productName, array $attributeValueIds, int $companyId): string
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Base SKU from Product Name
        |--------------------------------------------------------------------------
        */
        $baseSku = Str::upper(Str::slug($productName)); 
        // Example: "Tshirt" -> "TSHIRT"
        /*
        |--------------------------------------------------------------------------
        | 2. Fetch attribute values with their attribute names
        |--------------------------------------------------------------------------
        */
        $attrValues = AttributeValue::with('attribute')
            ->whereIn('id', $attributeValueIds)
            ->get();
        /*
        |--------------------------------------------------------------------------
        | 3. Sort attributes so SIZE always comes last
        |--------------------------------------------------------------------------
        */

        $sorted = $attrValues->sortBy(function ($item) {
            return strtolower($item->attribute->name) === 'size' ? 999 : 1;
        });
        /*
        |--------------------------------------------------------------------------
        | 4. Build SKU segments
        |--------------------------------------------------------------------------
        */
        $segments = [];
        foreach ($sorted as $attr) {
            $value = Str::upper(Str::slug($attr->value));
            // If attribute is size → shorten to 1 character
            if (strtolower($attr->attribute->name) === 'size') {
                // Example: LARGE -> L
                $value = substr($value, 0, 1);
            }
            $segments[] = $value;
        }
        /*
        |--------------------------------------------------------------------------
        | 5. Combine all parts
        |--------------------------------------------------------------------------
        */
        $baseSku = $baseSku . '-' . implode('-', $segments);
        /*
        |--------------------------------------------------------------------------
        | 6. Ensure uniqueness inside company
        |--------------------------------------------------------------------------
        */
        $finalSku = $baseSku;
        if (ProductSku::where('company_id', $companyId)
            ->where('sku', $finalSku)
            ->exists()) {
            $finalSku .= '-' . strtoupper(Str::random(3));
        }
        return $finalSku;
    }
    /**
     * Private helper to cleanly manage the Ledger and Stock creation
     */
    private function processInitialStock($sku, array $stockData): void
    {
        foreach ($stockData as $stock) {
            $qty = (int) $stock['qty'];
            
            if ($qty > 0) {
                // Add physical stock to the warehouse
                $sku->stocks()->create([
                    'warehouse_id' => $stock['warehouse_id'],
                    'qty'          => $qty,
                ]);

                // Log it in the Immutable Ledger
                StockMovement::create([
                    'product_sku_id' => $sku->id,
                    'warehouse_id'   => $stock['warehouse_id'],
                    'quantity'       => $qty,
                    'movement_type'  => 'adjustment', // 'adjustment' is standard for initial opening stock
                    'reference_type' => Product::class,
                    'reference_id'   => $sku->product_id,
                ]);
            }
        }
    }
    /**
     * Sync product categories to pivot table.
     * Preserves existing sort_order and is_featured for unchanged categories.
     * Removes from old, adds to new, ignores unchanged.
     */
        private function syncCategories(Product $product, array $categoryIds): void
        {
            if (empty($categoryIds)) return;

            // Clean input
            $categoryIds = array_values(array_unique(
                array_filter(array_map('intval', $categoryIds))
            ));

            // Validate — only keep IDs that exist in this company's categories
            $validCategoryIds = Category::whereIn('id', $categoryIds)
                ->where('company_id', $product->company_id)
                ->pluck('id')
                ->toArray();

            if (empty($validCategoryIds)) {
                Log::warning('[ProductService] No valid category IDs', [
                    'product_id' => $product->id,
                    'sent_ids'   => $categoryIds,
                    'company_id' => $product->company_id,
                ]);
                return;
            }

            // Update primary category for backward compat
            $product->update(['category_id' => $validCategoryIds[0]]);

            // ✅ Correct direction: product → many categories
            // Get current category IDs this product belongs to
            $existingCategoryIds = CategoryProduct::where('product_id', $product->id)
                ->pluck('category_id')
                ->toArray();

            $toAdd    = array_diff($validCategoryIds, $existingCategoryIds);
            $toRemove = array_diff($existingCategoryIds, $validCategoryIds);

            // Remove from categories no longer selected
            if (!empty($toRemove)) {
                CategoryProduct::where('product_id', $product->id)
                    ->whereIn('category_id', $toRemove)
                    ->delete();
            }

            // Add to new categories
            foreach ($toAdd as $categoryId) {
                CategoryProduct::attachProduct(
                    categoryId: $categoryId,
                    productId:  $product->id,
                    isActive:   $product->is_active,
                );
            }

            Log::info('[ProductService] Categories synced', [
                'product_id' => $product->id,
                'added'      => $toAdd,
                'removed'    => $toRemove,
                'final'      => $validCategoryIds,
            ]);
        }
}