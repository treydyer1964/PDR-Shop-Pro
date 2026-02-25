<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayRun extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'notes',
        'period_start',
        'period_end',
        'total_amount',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end'   => 'date',
        'approved_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(WorkOrderCommission::class)->with('user', 'workOrder');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function staffSummary(): \Illuminate\Support\Collection
    {
        return $this->commissions
            ->groupBy('user_id')
            ->map(fn($items) => [
                'user'   => $items->first()->user,
                'total'  => $items->sum('amount'),
                'count'  => $items->count(),
            ])
            ->values();
    }
}
