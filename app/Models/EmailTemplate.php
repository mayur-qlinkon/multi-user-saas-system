<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'key',
        'subject',
        'body',
    ];

    // NOTE: Tenantable trait is intentionally NOT used here.
    // EmailService must query both global templates (company_id IS NULL)
    // and tenant-specific overrides in the same resolution chain.

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check whether this template is a global (fallback) template.
     */
    public function isGlobal(): bool
    {
        return $this->company_id === null;
    }
}
