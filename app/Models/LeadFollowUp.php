<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowUp extends Model
{
    protected $fillable = [
        'tenant_id', 'lead_id', 'scheduled_at', 'completed_at',
        'notes', 'created_by', 'completed_by',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'completed_at'  => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isOverdue(): bool
    {
        return ! $this->isCompleted() && $this->scheduled_at->isPast();
    }
}
