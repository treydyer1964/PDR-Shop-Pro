<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HailAlertSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'home_lat',
        'home_lng',
        'home_address',
        'radius_miles',
        'min_size_inches',
        'email_alerts',
        'sms_alerts',
        'alert_cooldown_hours',
        'active',
    ];

    protected $casts = [
        'home_lat'             => 'float',
        'home_lng'             => 'float',
        'min_size_inches'      => 'float',
        'email_alerts'         => 'boolean',
        'sms_alerts'           => 'boolean',
        'active'               => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    public function hasHomeBase(): bool
    {
        return $this->home_lat !== null && $this->home_lng !== null;
    }
}
