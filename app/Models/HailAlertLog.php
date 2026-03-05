<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HailAlertLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'hail_event_id',
        'triggered_at',
        'delivery_method',
        'recipient',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function hailEvent(): BelongsTo
    {
        return $this->belongsTo(HailEvent::class);
    }
}
