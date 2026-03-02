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
        'plate_number', 'current_odometer', 'last_service_odometer',
        'service_interval_miles', 'service_alert_threshold_miles',
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

    /**
     * Miles remaining until next service is due.
     * Negative = overdue.
     */
    public function milesToNextService(): ?int
    {
        if ($this->last_service_odometer === null || $this->current_odometer === null) {
            return null;
        }
        return ($this->last_service_odometer + $this->service_interval_miles) - $this->current_odometer;
    }

    /**
     * Returns 'overdue', 'due_soon', or 'ok'.
     * Returns null if odometer data is not available.
     */
    public function serviceStatus(): ?string
    {
        $miles = $this->milesToNextService();
        if ($miles === null) {
            return null;
        }
        if ($miles <= 0) {
            return 'overdue';
        }
        if ($miles <= $this->service_alert_threshold_miles) {
            return 'due_soon';
        }
        return 'ok';
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
