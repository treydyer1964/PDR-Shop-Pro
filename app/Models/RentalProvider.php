<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalProvider extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'phone', 'notes', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workOrderRentals(): HasMany
    {
        return $this->hasMany(WorkOrderRental::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
