<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalSegment extends Model
{
    protected $fillable = [
        'tenant_id', 'work_order_rental_id', 'start_date', 'end_date', 'days', 'notes',
        'odometer_out', 'odometer_in', 'miles_driven', 'fuel_level_out', 'fuel_level_in',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workOrderRental(): BelongsTo
    {
        return $this->belongsTo(WorkOrderRental::class);
    }

    /** Compute and store days whenever end_date is set */
    public function computeDays(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }
        // Inclusive of start, exclusive of end (standard rental day count)
        return (int) Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date));
    }

    protected static function booted(): void
    {
        static::saving(function (self $segment) {
            if ($segment->end_date) {
                $segment->days = $segment->computeDays();
            } else {
                $segment->days = null;
            }

            // Auto-compute miles driven when odometer_in is set
            if ($segment->odometer_in !== null && $segment->odometer_out !== null) {
                $segment->miles_driven = max(0, $segment->odometer_in - $segment->odometer_out);
            }
        });

        static::saved(function (self $segment) {
            // Sync vehicle current odometer to the highest odometer_in seen
            if ($segment->odometer_in !== null) {
                $rental = $segment->workOrderRental()->with('vehicle')->first();
                $vehicle = $rental?->vehicle;
                if ($vehicle && ($vehicle->current_odometer === null || $segment->odometer_in > $vehicle->current_odometer)) {
                    $vehicle->update(['current_odometer' => $segment->odometer_in]);
                }
            }
        });
    }

    public function isOpen(): bool
    {
        return $this->end_date === null;
    }
}
