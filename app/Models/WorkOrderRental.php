<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrderRental extends Model
{
    protected $fillable = [
        'tenant_id', 'work_order_id', 'rental_vehicle_id', 'rental_provider_id',
        'has_insurance_coverage', 'insurance_daily_rate', 'notes',
    ];

    protected $casts = [
        'has_insurance_coverage' => 'boolean',
        'insurance_daily_rate'   => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(RentalVehicle::class, 'rental_vehicle_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(RentalProvider::class, 'rental_provider_id');
    }

    public function segments(): HasMany
    {
        return $this->hasMany(RentalSegment::class)->orderBy('start_date');
    }

    public function reimbursement(): HasOne
    {
        return $this->hasOne(RentalReimbursement::class);
    }

    /** Total days across all completed segments */
    public function totalDays(): int
    {
        return (int) $this->segments->whereNotNull('end_date')->sum('days');
    }

    /** Total internal cost = vehicle daily rate × total days */
    public function totalInternalCost(): float
    {
        if (! $this->vehicle) {
            return 0.0;
        }
        return round((float) $this->vehicle->internal_daily_cost * $this->totalDays(), 2);
    }

    /** Total amount billable to insurance = insurance daily rate × total days */
    public function totalInsuranceBillable(): ?float
    {
        if (! $this->has_insurance_coverage || ! $this->insurance_daily_rate) {
            return null;
        }
        return round((float) $this->insurance_daily_rate * $this->totalDays(), 2);
    }
}
