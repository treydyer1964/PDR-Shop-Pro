<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'phone', 'email',
        'address', 'city', 'state', 'zip',
        'logo_path', 'remit_address',
        'rental_daily_rate', 'advisor_per_car_bonus',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active'              => 'boolean',
            'rental_daily_rate'   => 'decimal:2',
            'advisor_per_car_bonus' => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public static function createWithOwner(array $tenantData, array $ownerData): static
    {
        $tenant = static::create([
            ...$tenantData,
            'slug' => Str::slug($tenantData['name']),
        ]);

        $owner = $tenant->users()->create([
            ...$ownerData,
            'active' => true,
        ]);

        $owner->roles()->attach('owner');

        // Create a default location
        $tenant->locations()->create([
            'name'   => $tenantData['name'],
            'active' => true,
        ]);

        return $tenant;
    }
}
