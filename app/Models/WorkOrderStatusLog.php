<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderStatusLog extends Model
{
    protected $fillable = [
        'work_order_id',
        'tenant_id',
        'user_id',
        'status',
        'entered_at',
        'exited_at',
        'notes',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'exited_at'  => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Duration in hours for this status (null if still current) */
    public function durationHours(): ?float
    {
        if (! $this->exited_at) {
            return null;
        }

        return $this->entered_at->diffInMinutes($this->exited_at) / 60;
    }

    /** Duration in whole days for this status */
    public function durationDays(): ?int
    {
        if (! $this->exited_at) {
            return null;
        }

        return (int) $this->entered_at->diffInDays($this->exited_at);
    }
}
