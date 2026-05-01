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
        'user_type', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'user_type'=> 'string',
    ];

    /**
     * True when this user is an employee-only login (not a full system user).
     * Employee users do not consume user_limit seats.
     */
    public function isEmployeeType(): bool
    {
        return $this->user_type === 'employee';
    }

    /**
     * Scope: only full system users (admins, managers, owners, etc.)
     * Excludes employee-only logins from counts and queries where relevant.
     */
    public function scopeFullUsers(Builder $query): Builder
    {
        return $query->where('user_type', 'full');
    }

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
     * Full system users only.
     * Excludes: customer-role users, client-linked users, and employee-type logins.
     * Employee users are managed entirely through the HRM module, not the Users panel.
     */
    public function scopeInternal(Builder $query): Builder
    {
        return $query
            ->where('user_type', 'full')           // exclude employee-type logins
            ->whereDoesntHave('client');             // exclude storefront customer accounts
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
     * Alias for hasPermission to match standard policy syntax
     */
    public function hasPermissionTo($permission)
    {
        return $this->hasPermission($permission);
    }

    /**
     * Check if the user has ANY of the given permissions in an array
     */
    public function hasAnyPermission(array $permissions)
    {
        if (is_super_admin()) {
            return true;
        }

        // 1. Check if they are the owner (owners can do everything)
        if ($this->roles->contains('slug', 'owner')) {
            return true;
        }

        // Flatten the user's permissions and check if any intersect with the requested array
        $userPermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->toArray();

        return ! empty(array_intersect($permissions, $userPermissions));
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

    /**
     * Route notifications for the mail channel.
     * This strictly prevents Laravel from trying to send emails to blank/invalid addresses.
     */
    public function routeNotificationForMail($notification)
    {
        // Only allow the notification to send if the email is perfectly valid
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) ? $this->email : null;
    }
}
