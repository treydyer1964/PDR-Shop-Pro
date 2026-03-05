<?php

namespace App\Models;

use App\Enums\HailEventStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HailEventWatch extends Model
{
    protected $fillable = [
        'tenant_id',
        'hail_event_id',
        'status',
        'notes',
        'storm_event_id',
        'created_by',
    ];

    protected $casts = [
        'status' => HailEventStatus::class,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────────

    public function hailEvent(): BelongsTo
    {
        return $this->belongsTo(HailEvent::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function stormEvent(): BelongsTo
    {
        return $this->belongsTo(StormEvent::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    public function isWatching(): bool
    {
        return $this->status === HailEventStatus::Watching;
    }

    public function isPassed(): bool
    {
        return $this->status === HailEventStatus::Passed;
    }

    public function isActivated(): bool
    {
        return $this->status === HailEventStatus::Activated;
    }
}
