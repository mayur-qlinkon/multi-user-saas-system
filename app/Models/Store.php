<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'company_id', 'name', 'slug', 'email', 'phone', 'upi_id', 'logo', 'signature',
        'gst_number', 'currency', 'address', 'city', 'state_id', 'zip_code', 'country',
        'office_lat', 'office_lng', 'gps_radius_meters', 'is_active',
        // Billing Fields
        'bank_name', 'account_name', 'account_number', 'ifsc_code', 'branch_name',
        'invoice_prefix', 'quotation_prefix', 'purchase_prefix', 'next_invoice_number',
        'default_tax_type', 'default_payment_terms', 'round_off_amounts',
        'invoice_footer_note', 'invoice_terms',
        // Public Storefront Fields
        'tagline', 'description', 'whatsapp', 'instagram', 'facebook', 'twitter',
        'seo_title', 'seo_description', 'business_hours', 'map_embed_url',
        'storefront_enabled',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'storefront_enabled' => 'boolean',
        'next_invoice_number' => 'integer',
        'office_lat' => 'float',
        'office_lng' => 'float',
        'gps_radius_meters' => 'integer',
        'round_off_amounts' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name).'-'.Str::random(5);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_user');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers & Fallback Accessors
    |--------------------------------------------------------------------------
    | These accessors ensure that if a store doesn't have a specific setting,
    | it seamlessly falls back to the global company setting.
    */

    public function getBankNameAttribute($value)
    {
        return $value ?: get_setting('bank_name');
    }

    public function getAccountNameAttribute($value)
    {
        return $value ?: get_setting('account_name');
    }

    public function getAccountNumberAttribute($value)
    {
        return $value ?: get_setting('account_number');
    }

    public function getIfscCodeAttribute($value)
    {
        return $value ?: get_setting('ifsc_code');
    }

    public function getBranchNameAttribute($value)
    {
        return $value ?: get_setting('branch_name');
    }

    public function getInvoicePrefixAttribute($value)
    {
        return $value ?: get_setting('invoice_prefix', 'INV-');
    }

    public function getQuotationPrefixAttribute($value)
    {
        return $value ?: get_setting('quotation_prefix', 'QTN-');
    }

    public function getPurchasePrefixAttribute($value)
    {
        return $value ?: get_setting('purchase_prefix', 'PO-');
    }

    public function getDefaultTaxTypeAttribute($value)
    {
        return $value ?: get_setting('default_tax_type');
    }

    public function getDefaultPaymentTermsAttribute($value)
    {
        return $value ?: get_setting('default_payment_terms');
    }

    public function getRoundOffAmountsAttribute($value)
    {
        return $value !== null ? $value : get_setting('round_off_amounts', true);
    }

    public function getInvoiceFooterNoteAttribute($value)
    {
        return $value ?: get_setting('invoice_footer_note');
    }

    public function getInvoiceTermsAttribute($value)
    {
        return $value ?: get_setting('default_terms');
    }

    public function getUpiIdAttribute($value)
    {
        return $value ?: get_setting('upi_id');
    }

    public function getLogoUrlAttribute()
    {
        // For logo, we might also want to fallback to company logo if store logo is empty
        if ($this->logo) {
            return asset('storage/'.$this->logo);
        }
        if (get_setting('company_logo')) {
            return asset('storage/'.get_setting('company_logo'));
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name);
    }

    public function getSignatureUrlAttribute()
    {
        if ($this->signature) {
            return asset('storage/'.$this->signature);
        }
        if (get_setting('company_signature')) {
            return asset('storage/'.get_setting('company_signature'));
        }

        return null;
    }
     public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function stockTransfersOut()
    {
        return $this->hasMany(StockTransfer::class, 'from_store_id');
    }

    public function stockTransfersIn()
    {
        return $this->hasMany(StockTransfer::class, 'to_store_id');
    }

    public function pricingRules()
    {
        return $this->hasMany(StorePricingRule::class);
    }

    // ── Public storefront accessors (with company-level fallback) ───────

    public function getPublicPhoneAttribute(): ?string
    {
        return $this->phone ?: get_setting('call_number');
    }

    public function getPublicEmailAttribute(): ?string
    {
        return $this->email ?: get_setting('support_email');
    }

    public function getPublicWhatsappAttribute(): ?string
    {
        return $this->whatsapp ?: get_setting('whatsapp');
    }

    public function getPublicAddressAttribute(): ?string
    {
        $parts = array_filter([$this->address, $this->city, $this->zip_code]);
        return $parts ? implode(', ', $parts) : get_setting('storefront_address');
    }

    public function getPublicTaglineAttribute(): ?string
    {
        return $this->tagline ?: get_setting('storefront_tagline');
    }

    public function getPublicInstagramAttribute(): ?string
    {
        return $this->instagram ?: get_setting('instagram');
    }

    public function getPublicFacebookAttribute(): ?string
    {
        return $this->facebook ?: get_setting('facebook');
    }

    public function getPublicUrlAttribute(): string
    {
        return url("/{$this->company->slug}/{$this->slug}");
    }
}
