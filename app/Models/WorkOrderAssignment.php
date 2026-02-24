<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderAssignment extends Model
{
    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'user_id',
        'role',
        'split_pct',
    ];

    protected $casts = [
        'role'      => Role::class,
        'split_pct' => 'decimal:2',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roleLabel(): string
    {
        return $this->role->label();
    }

    public function usesSplit(): bool
    {
        return $this->role->usesSplitPct();
    }
}
