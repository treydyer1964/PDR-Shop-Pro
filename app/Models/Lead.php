<?php

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'tenant_id', 'first_name', 'last_name', 'phone', 'email',
        'address', 'city', 'state', 'zip', 'lat', 'lng',
        'status', 'source', 'job_type_interest',
        'vehicle_year', 'vehicle_make', 'vehicle_model',
        'notes', 'damage_level', 'assigned_to', 'territory_id', 'storm_event_id',
        'converted_work_order_id', 'converted_at', 'created_by',
    ];

    protected $casts = [
        'status'       => LeadStatus::class,
        'source'       => LeadSource::class,
        'converted_at' => 'datetime',
        'lat'          => 'float',
        'lng'          => 'float',
    ];

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function stormEvent(): BelongsTo
    {
        return $this->belongsTo(StormEvent::class);
    }

    public function convertedWorkOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'converted_work_order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class)->orderBy('scheduled_at');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(LeadStatusLog::class)->latest();
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function hasName(): bool
    {
        return ! empty(trim((string) $this->first_name));
    }

    public function leadStatusLabel(array $overrides = []): string
    {
        return $overrides[$this->status->value] ?? $this->status->label();
    }

    public function damageLevelLabel(): string
    {
        return match($this->damage_level) {
            'no_damage' => 'No Damage',
            'light'     => 'Light',
            'medium'    => 'Medium',
            'severe'    => 'Severe',
            'smoked'    => 'Smoked',
            default     => '',
        };
    }

    public function locationLabel(): string
    {
        $parts = array_filter([$this->address, $this->city, $this->state]);
        return implode(', ', $parts);
    }

    public function nextFollowUp(): ?LeadFollowUp
    {
        return $this->followUps()
            ->whereNull('completed_at')
            ->where('scheduled_at', '>=', now())
            ->first();
    }

    public function isConverted(): bool
    {
        return $this->status === LeadStatus::Converted;
    }
}
