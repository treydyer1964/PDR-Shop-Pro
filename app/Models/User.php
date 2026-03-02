<?php

namespace App\Models;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'phone', 'password',
        'active', 'commission_rate', 'sales_manager_override_rate',
        'per_car_bonus', 'subject_to_manager_override',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'            => 'datetime',
            'password'                     => 'hashed',
            'active'                       => 'boolean',
            'subject_to_manager_override'  => 'boolean',
            'commission_rate'              => 'decimal:2',
            'sales_manager_override_rate'  => 'decimal:2',
            'per_car_bonus'               => 'decimal:2',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_name', 'id', 'name');
    }

    // ── Role helpers ─────────────────────────────────────────────────────────

    public function hasRole(RoleEnum|string $role): bool
    {
        $value = $role instanceof RoleEnum ? $role->value : $role;
        return $this->roles->contains('name', $value);
    }

    public function hasAnyRole(array $roles): bool
    {
        return collect($roles)->some(fn($r) => $this->hasRole($r));
    }

    public function isOwner(): bool      { return $this->hasRole(RoleEnum::OWNER); }
    public function isPdrTech(): bool    { return $this->hasRole(RoleEnum::PDR_TECH); }
    public function isAdvisor(): bool    { return $this->hasRole(RoleEnum::SALES_ADVISOR); }
    public function isManager(): bool    { return $this->hasRole(RoleEnum::SALES_MANAGER); }
    public function isRiTech(): bool     { return $this->hasRole(RoleEnum::RI_TECH); }
    public function isPorter(): bool     { return $this->hasRole(RoleEnum::PORTER); }
    public function isBookkeeper(): bool { return $this->hasRole(RoleEnum::BOOKKEEPER); }

    public function canAccessFinancials(): bool
    {
        return $this->hasAnyRole([RoleEnum::OWNER, RoleEnum::BOOKKEEPER, RoleEnum::SALES_MANAGER]);
    }

    public function canManageStaff(): bool
    {
        return $this->hasRole(RoleEnum::OWNER);
    }

    /** Payroll access: Owner and Bookkeeper only (not Sales Manager). */
    public function canAccessPayroll(): bool
    {
        return $this->hasAnyRole([RoleEnum::OWNER, RoleEnum::BOOKKEEPER]);
    }

    /** Analytics access: Owner, Bookkeeper, and Sales Manager. */
    public function canAccessAnalytics(): bool
    {
        return $this->hasAnyRole([RoleEnum::OWNER, RoleEnum::BOOKKEEPER, RoleEnum::SALES_MANAGER]);
    }

    /** Can create new work orders: Owner, Bookkeeper, Sales Manager, Sales Advisor. */
    public function canCreateWorkOrders(): bool
    {
        return $this->hasAnyRole([RoleEnum::OWNER, RoleEnum::BOOKKEEPER, RoleEnum::SALES_MANAGER, RoleEnum::SALES_ADVISOR]);
    }

    /**
     * Full WO list visibility (all work orders in tenant).
     * Owner, Bookkeeper, and Sales Manager see everything.
     */
    public function canSeeAllWorkOrders(): bool
    {
        return $this->hasAnyRole([RoleEnum::OWNER, RoleEnum::BOOKKEEPER, RoleEnum::SALES_MANAGER]);
    }

    /**
     * Field staff: roles that only see their assigned work orders.
     * PDR Tech, R&I Tech, Porter, and Sales Advisor.
     */
    public function isFieldStaff(): bool
    {
        return $this->hasAnyRole([RoleEnum::PDR_TECH, RoleEnum::RI_TECH, RoleEnum::PORTER, RoleEnum::SALES_ADVISOR]);
    }

    public function getRoleLabels(): string
    {
        return $this->roles->map(fn($r) => $r->label())->implode(', ');
    }
}
