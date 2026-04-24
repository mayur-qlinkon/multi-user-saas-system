<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity,Tenantable;

    protected $fillable = [
        'company_id',
        'store_id',
        'key',
        'value',
        'group',
        'type',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'value'])
            ->logOnlyDirty()           // only log what actually changed
            ->dontSubmitEmptyLogs()    // skip if nothing changed
            ->setDescriptionForEvent(fn (string $event) => "Setting {$this->key} was {$event}");
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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get a setting value
     */
    public static function get(string $key, $default = null, $companyId = null)
    {
        $query = static::where('key', $key);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $setting = $query->first();

        return $setting?->value ?? $default;
    }

    /**
     * Set or update a setting
     */
    public static function set(string $key, $value, $companyId = null, $group = null, $type = null)
    {
        return static::updateOrCreate(
            [
                'key' => $key,
                'company_id' => $companyId,
            ],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
            ]
        );
    }

    /**
     * Get settings by group
     */
    public static function group(string $group, $companyId = null)
    {
        $query = static::where('group', $group);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->pluck('value', 'key');
    }
}
