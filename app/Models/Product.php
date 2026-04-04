<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\Tenantable; // The Iron Wall
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory, SoftDeletes, Tenantable,LogsActivity;

    protected $fillable = [
        // REMOVED store_id. Products belong to the company globally!
        'category_id',
        'supplier_id',
        'name',
        'slug',
        'type', // 'single' or 'variable'
        'barcode_symbology',
        'hsn_code',
        'product_unit_id',
        'sale_unit_id',
        'purchase_unit_id',
        'quantity_limitation',
        'note',
        'description',
        'specifications',
        'product_guide',
        'is_active',
        'show_in_storefront'
    ];

    protected $casts = [
        'specifications' => 'array',
        'product_guide'  => 'array',
        'is_active'      => 'boolean',
        'show_in_storefront' => 'boolean',
    ];

    // In Product model — add this property
    protected $appends = ['primary_image_url'];
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Logs every fillable attribute
            ->logOnlyDirty() // ONLY logs attributes that actually changed
            ->dontSubmitEmptyLogs() // Prevents logging if nothing was actually modified
            ->setDescriptionForEvent(fn(string $eventName) => "Product has been {$eventName}");
    }

    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate a unique slug if one wasn't provided
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(5);
            }
        });
    }

    // --- Core Relationships ---
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
     /**
     * Categories this product belongs to (via pivot).
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_products'
        )
        ->withPivot(['is_active', 'is_featured', 'sort_order', 'added_by'])
        ->withTimestamps()
        ->orderByPivot('sort_order', 'asc');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
     /**
     * Pivot relationship — products through category_products table.
     * Gives access to per-category sort_order, is_featured, is_active.
     */
    public function categoryPivots(): HasMany
    {
        return $this->hasMany(\App\Models\CategoryProduct::class);
    }
    public function sectionPivots(): HasMany
    {
        return $this->hasMany(StorefrontSectionProduct::class);
    }

    // --- Unit Relationships ---

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'product_unit_id');
    }

    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id');
    }

    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    // --- Architecture Relationships ---
    
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order', 'asc');
    }

    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class);
    }

    // --- Helper Methods ---

    /**
     * Safely get the main thumbnail URL for the product.
     */
    public function getPrimaryImageUrlAttribute()
    {
        // First, explicitly look for an 'image' that is marked as primary
        $primaryMedia = $this->media->where('media_type', 'image')->where('is_primary', true)->first();
        
        // If no primary is set, fallback to the first available image
        if (!$primaryMedia) {
            $primaryMedia = $this->media->where('media_type', 'image')->first();
        }

        // Fallback to a default placeholder if no images exist at all
        return $primaryMedia ? asset('storage/' . $primaryMedia->media_path) : asset('assets/images/no-product.png');
    }
     /**
     * Scope — only products visible on storefront.
     * Apply this on EVERY public-facing query.
     */
    public function scopeStorefrontVisible(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('show_in_storefront', true)->where('is_active', true);
    }
 
}