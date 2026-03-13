<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalReimbursement extends Model
{
    protected $fillable = [
        'tenant_id', 'work_order_id', 'work_order_rental_id',
        'submitted_at', 'submitted_by',
        'insurance_amount_received', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'submitted_at'             => 'datetime',
        'insurance_amount_received' => 'decimal:2',
    ];

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isPaid(): bool
    {
        return $this->insurance_amount_received !== null && $this->insurance_amount_received > 0;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function workOrderRental(): BelongsTo
    {
        return $this->belongsTo(WorkOrderRental::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
