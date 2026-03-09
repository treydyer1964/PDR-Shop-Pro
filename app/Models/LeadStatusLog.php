<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusLog extends Model
{
    protected $fillable = [
        'tenant_id', 'lead_id', 'status', 'notes', 'changed_by',
    ];

    // No enum cast — status is a plain string so old log entries don't blow up.

    public function statusEnum(): ?LeadStatus
    {
        return LeadStatus::tryFrom($this->status ?? '');
    }

    public function statusLabel(): string
    {
        return $this->statusEnum()?->label()
            ?? ucwords(str_replace('_', ' ', $this->status ?? ''));
    }

    public function statusDotClasses(): string
    {
        return $this->statusEnum()?->dotClasses() ?? 'bg-slate-400';
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
