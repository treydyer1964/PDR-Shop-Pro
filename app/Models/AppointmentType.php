<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppointmentType extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'color', 'active', 'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Default types seeded per tenant
    public static array $defaults = [
        ['name' => 'Drop-Off',              'color' => 'blue',   'sort_order' => 1],
        ['name' => 'Pick-Up',               'color' => 'green',  'sort_order' => 2],
        ['name' => 'Inspection',            'color' => 'purple', 'sort_order' => 3],
        ['name' => 'Supplement Inspection', 'color' => 'orange', 'sort_order' => 4],
        ['name' => 'Vehicle Delivery',      'color' => 'teal',   'sort_order' => 5],
    ];

    public static function seedForTenant(int $tenantId): void
    {
        foreach (self::$defaults as $type) {
            self::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $type['name']],
                array_merge($type, ['tenant_id' => $tenantId, 'active' => true])
            );
        }
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function badgeClasses(): string
    {
        return match($this->color) {
            'green'  => 'bg-green-100 text-green-700',
            'purple' => 'bg-purple-100 text-purple-700',
            'orange' => 'bg-orange-100 text-orange-700',
            'teal'   => 'bg-teal-100 text-teal-700',
            'red'    => 'bg-red-100 text-red-600',
            default  => 'bg-blue-100 text-blue-700',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
