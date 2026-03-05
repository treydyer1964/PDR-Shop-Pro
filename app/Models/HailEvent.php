<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HailEvent extends Model
{
    protected $fillable = [
        'event_date',
        'centroid_lat',
        'centroid_lng',
        'max_size_inches',
        'min_size_inches',
        'report_count',
        'coverage_radius_miles',
        'primary_state',
        'primary_county',
    ];

    protected $casts = [
        'event_date'            => 'date',
        'centroid_lat'          => 'float',
        'centroid_lng'          => 'float',
        'max_size_inches'       => 'float',
        'min_size_inches'       => 'float',
        'coverage_radius_miles' => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────────

    public function reports(): HasMany
    {
        return $this->hasMany(HailReport::class);
    }

    public function alertLogs(): HasMany
    {
        return $this->hasMany(HailAlertLog::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    public function locationLabel(): string
    {
        return collect([$this->primary_county, $this->primary_state])->filter()->join(', ');
    }

    public function sizeColor(): string
    {
        return match(true) {
            $this->max_size_inches >= 2.5  => '#ef4444',
            $this->max_size_inches >= 1.75 => '#f97316',
            $this->max_size_inches >= 1.0  => '#eab308',
            default                        => '#22c55e',
        };
    }

    public function sizeLabel(): string
    {
        return match(true) {
            $this->max_size_inches >= 2.5  => 'Baseball+',
            $this->max_size_inches >= 1.75 => 'Golf Ball',
            $this->max_size_inches >= 1.5  => 'Ping Pong',
            $this->max_size_inches >= 1.0  => 'Quarter',
            $this->max_size_inches >= 0.75 => 'Penny',
            default                        => 'Pea',
        };
    }

    public function sizeBadgeClasses(): string
    {
        return match(true) {
            $this->max_size_inches >= 2.5  => 'bg-red-100 text-red-700',
            $this->max_size_inches >= 1.75 => 'bg-orange-100 text-orange-700',
            $this->max_size_inches >= 1.0  => 'bg-yellow-100 text-yellow-700',
            default                        => 'bg-green-100 text-green-700',
        };
    }
}
