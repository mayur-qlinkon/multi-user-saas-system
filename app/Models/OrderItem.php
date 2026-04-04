<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
 
class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'sku_id',
        'product_name',
        'sku_label',
        'sku_code',
        'product_image',
        'hsn_code',
        'unit_price',
        'cost_price',
        'qty',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'line_total',
        'status',
        'notes',
    ];
 
    protected $casts = [
        'unit_price'      => 'decimal:2',
        'cost_price'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'line_total'      => 'decimal:2',
        'qty'             => 'integer',
    ];
 
    // ════════════════════════════════════════════════════
    //  RELATIONSHIPS
    // ════════════════════════════════════════════════════
 
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
 
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
 
    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }
 
    // ════════════════════════════════════════════════════
    //  ACCESSORS
    // ════════════════════════════════════════════════════
 
    /**
     * Display name — combines product name + variant label
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->sku_label
            ? "{$this->product_name} ({$this->sku_label})"
            : $this->product_name;
    }
 
    /**
     * Margin — how much profit on this item
     */
    public function getMarginAttribute(): float
    {
        if ($this->cost_price <= 0) return 0;
        return round((($this->unit_price - $this->cost_price) / $this->unit_price) * 100, 1);
    }
 
    public function getFormattedLineTotalAttribute(): string
    {
        return '₹' . number_format($this->line_total, 2);
    }
 
    // ════════════════════════════════════════════════════
    //  STATIC FACTORY
    // ════════════════════════════════════════════════════
 
    /**
     * Build from cart item array — used in OrderService
     */
    public static function fromCartItem(array $cartItem): array
    {
        return [
            'product_id'    => $cartItem['product_id'],
            'sku_id'        => $cartItem['sku_id'],
            'product_name'  => $cartItem['name'],
            'sku_label'     => $cartItem['variant'] ?? null,
            'product_image' => $cartItem['image'] ?? null,
            'unit_price'    => $cartItem['price'],
            'cost_price'    => 0, // filled by service from SKU
            'qty'           => $cartItem['qty'],
            'discount_amount' => 0,
            'tax_rate'      => 0, // filled by service from product
            'tax_amount'    => 0,
            'line_total'    => $cartItem['price'] * $cartItem['qty'],
        ];
    }
}
 
 