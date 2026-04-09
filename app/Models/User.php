<?php

namespace App\Models;

use App\Models\Hrm\Employee;
use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @method bool hasRole(string $role)
 * @method bool hasAnyRole(array $roles)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes,Tenantable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'password',
        'phone_number',
        'image',
        'address',
        'state',
        'country',
        'zip_code',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /** * Company relationship */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Customer profile linked to this login.
     */
    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Relationship to Role
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function crmLeads(): BelongsToMany
    {
        return $this->belongsToMany(
            CrmLead::class,
            'crm_lead_assignees',
            'user_id',
            'crm_lead_id'
        );
    }

    /**
     * Relationship to Store
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_user')->withTimestamps();
    }

    /**
     * Backoffice-only users. Excludes customer logins and linked client profiles.
     */
    public function scopeInternal(Builder $query): Builder
    {
        return $query
            ->whereDoesntHave('roles', function (Builder $builder): void {
                $builder->where('slug', 'customer');
            })
            ->whereDoesntHave('client');
    }

    public function hasRole($role)
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasAnyRole(array $roles)
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function hasPermission($permission)
    {

        if (is_super_admin()) {
            return true;
        }

        // 1. Check if they are the owner (owners can do everything)
        if ($this->roles->contains('slug', 'owner')) {
            return true;
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->contains('slug', $permission);
    }

    /**
     * HRM Employee relationship
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Helper to get profile image URL
     */
    public function getAvatarUrlAttribute()
    {
        return $this->image
            ? asset('storage/'.$this->image)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Optimized for the Header Notification Dropdown
     */
    public function unreadNotificationsLimit()
    {
        return $this->unreadNotifications()->latest()->limit(5);
    }
}
