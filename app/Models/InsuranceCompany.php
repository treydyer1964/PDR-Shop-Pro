<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceCompany extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'phone',
        'website',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
