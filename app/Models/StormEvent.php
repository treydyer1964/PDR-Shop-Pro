<?php

namespace App\Models;

use App\Enums\StormType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StormEvent extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'event_date',
        'city',
        'state',
        'storm_type',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'storm_type' => StormType::class,
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────────

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // ── Relationships ─────────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    public function locationLabel(): string
    {
        return collect([$this->city, $this->state])->filter()->join(', ');
    }
}
