<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleColor extends Model
{
    protected $fillable = ['name', 'sort_order', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('sort_order')->orderBy('name');
    }
}
