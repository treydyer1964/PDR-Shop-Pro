<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HailReport extends Model
{
    protected $fillable = [
        'hail_event_id',
        'report_date',
        'report_time',
        'lat',
        'lng',
        'size_inches',
        'location_name',
        'county',
        'state',
        'source',
    ];

    protected $casts = [
        'report_date'  => 'date',
        'lat'          => 'float',
        'lng'          => 'float',
        'size_inches'  => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────────

    public function hailEvent(): BelongsTo
    {
        return $this->belongsTo(HailEvent::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    public function sizeColor(): string
    {
        return match(true) {
            $this->size_inches >= 2.5  => '#ef4444',
            $this->size_inches >= 1.75 => '#f97316',
            $this->size_inches >= 1.0  => '#eab308',
            default                    => '#22c55e',
        };
    }

    public function locationLabel(): string
    {
        return collect([$this->county, $this->state])->filter()->join(', ');
    }
}
