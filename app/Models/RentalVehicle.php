<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalVehicle extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'vin', 'year', 'make', 'model',
        'color', 'internal_daily_cost', 'active', 'notes',
    ];

    protected $casts = [
        'internal_daily_cost' => 'decimal:2',
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

    public function displayName(): string
    {
        $parts = array_filter([$this->year, $this->make, $this->model]);
        return $this->name ?: implode(' ', $parts) ?: 'Vehicle #' . $this->id;
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
