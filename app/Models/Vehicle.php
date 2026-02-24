<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'tenant_id', 'customer_id',
        'vin', 'year', 'make', 'model', 'trim',
        'body_style', 'drive_type', 'engine', 'color', 'plate', 'notes',
    ];

    protected function casts(): array
    {
        return ['year' => 'integer'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function getDescriptionAttribute(): string
    {
        return trim("{$this->year} {$this->make} {$this->model} {$this->trim}");
    }

    public function getShortDescriptionAttribute(): string
    {
        return trim("{$this->year} {$this->make} {$this->model}");
    }
}
