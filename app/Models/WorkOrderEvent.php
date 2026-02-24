<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderEvent extends Model
{
    protected $fillable = [
        'work_order_id',
        'tenant_id',
        'user_id',
        'type',
        'description',
    ];

    // Event type constants
    const TYPE_SUPPLEMENT       = 'supplement_submitted';
    const TYPE_HOLD_STARTED     = 'hold_started';
    const TYPE_HOLD_RELEASED    = 'hold_released';
    const TYPE_NOTE             = 'note';
    const TYPE_KICKED           = 'kicked';
    const TYPE_STATUS_CHANGED   = 'status_changed';

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            self::TYPE_SUPPLEMENT     => 'Supplement Submitted',
            self::TYPE_HOLD_STARTED   => 'Put On Hold',
            self::TYPE_HOLD_RELEASED  => 'Hold Released',
            self::TYPE_NOTE           => 'Note',
            self::TYPE_KICKED         => 'Kicked',
            self::TYPE_STATUS_CHANGED => 'Status Changed',
            default                   => ucwords(str_replace('_', ' ', $this->type)),
        };
    }
}
