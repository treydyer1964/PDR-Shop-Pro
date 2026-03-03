<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Territory extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'color', 'boundary', 'assigned_user_id', 'active',
    ];

    protected $casts = [
        'boundary' => 'array',
        'active'   => 'boolean',
    ];

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
