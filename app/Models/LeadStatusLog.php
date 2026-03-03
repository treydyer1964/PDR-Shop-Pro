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

    protected $casts = [
        'status' => LeadStatus::class,
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
