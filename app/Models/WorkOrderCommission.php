<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderCommission extends Model
{
    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'user_id',
        'role',
        'amount',
        'split_pct',
        'rate_pct',
        'notes',
        'is_paid',
        'paid_at',
        'pay_run_id',
    ];

    protected $casts = [
        'role'      => Role::class,
        'amount'    => 'decimal:2',
        'split_pct' => 'decimal:2',
        'rate_pct'  => 'decimal:4',
        'is_paid'   => 'boolean',
        'paid_at'   => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
