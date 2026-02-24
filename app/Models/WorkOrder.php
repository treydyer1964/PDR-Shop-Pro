<?php

namespace App\Models;

use App\Enums\WorkOrderJobType;
use App\Enums\WorkOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    protected $fillable = [
        'tenant_id',
        'location_id',
        'customer_id',
        'vehicle_id',
        'ro_number',
        'job_type',
        'status',
        'invoice_total',
        'notes',
        // Insurance
        'insurance_company_id',
        'claim_number',
        'policy_number',
        'adjuster_name',
        'adjuster_phone',
        'adjuster_email',
        'deductible',
        'insurance_pre_inspected',
        'has_rental_coverage',
        // On Hold
        'on_hold',
        'held_at',
        'hold_reason',
        // Kicked
        'kicked',
        'kicked_at',
        'kicked_reason',
        // Sub-tasks
        'teardown_completed_at',
        'needs_parts_pre_repair',
        'parts_pre_repair_ordered_at',
        'parts_pre_repair_received_at',
        'needs_parts_reassembly',
        'parts_reassembly_ordered_at',
        'parts_reassembly_received_at',
        'needs_body_shop',
        'body_shop_sent_at',
        'body_shop_returned_at',
        'needs_glass',
        'glass_sent_at',
        'glass_returned_at',
    ];

    protected $casts = [
        'job_type'                    => WorkOrderJobType::class,
        'status'                      => WorkOrderStatus::class,
        'invoice_total'               => 'decimal:2',
        'deductible'                  => 'decimal:2',
        'insurance_pre_inspected'     => 'boolean',
        'has_rental_coverage'         => 'boolean',
        'on_hold'                     => 'boolean',
        'held_at'                     => 'datetime',
        'kicked'                      => 'boolean',
        'kicked_at'                   => 'datetime',
        'teardown_completed_at'       => 'datetime',
        'needs_parts_pre_repair'      => 'boolean',
        'parts_pre_repair_ordered_at' => 'datetime',
        'parts_pre_repair_received_at'=> 'datetime',
        'needs_parts_reassembly'      => 'boolean',
        'parts_reassembly_ordered_at' => 'datetime',
        'parts_reassembly_received_at'=> 'datetime',
        'needs_body_shop'             => 'boolean',
        'body_shop_sent_at'           => 'datetime',
        'body_shop_returned_at'       => 'datetime',
        'needs_glass'                 => 'boolean',
        'glass_sent_at'               => 'datetime',
        'glass_returned_at'           => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(WorkOrderStatusLog::class)->orderBy('entered_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorkOrderEvent::class)->orderByDesc('created_at');
    }

    // ── Status helpers ─────────────────────────────────────────────────────────

    public function isOnHold(): bool
    {
        return (bool) $this->on_hold;
    }

    public function isKicked(): bool
    {
        return (bool) $this->kicked;
    }

    public function isDelivered(): bool
    {
        return $this->status === WorkOrderStatus::Delivered;
    }

    public function isInsuranceJob(): bool
    {
        return $this->job_type === WorkOrderJobType::Insurance;
    }

    /**
     * Label for "Inspected" stage — changes based on whether insurance pre-inspected.
     */
    public function inspectedLabel(): string
    {
        return $this->insurance_pre_inspected
            ? 'Supplement Submitted'
            : 'Estimate Submitted';
    }

    /**
     * Transition to a new status, closing the current log entry and opening a new one.
     */
    public function transitionTo(WorkOrderStatus $newStatus, ?string $notes = null, ?\Carbon\Carbon $at = null): void
    {
        $at     = $at ?? now();
        $userId = auth()->id();

        // Close the current open log entry
        $this->statusLogs()
            ->whereNull('exited_at')
            ->update(['exited_at' => $at]);

        // Open a new log entry
        $this->statusLogs()->create([
            'tenant_id'  => $this->tenant_id,
            'user_id'    => $userId,
            'status'     => $newStatus->value,
            'entered_at' => $at,
            'notes'      => $notes,
        ]);

        // Update the denormalized status column for easy querying
        $this->update(['status' => $newStatus->value]);

        // Log the event
        $suffix = $at->isToday() ? '' : ' (backdated to ' . $at->format('M j, Y') . ')';
        $this->events()->create([
            'tenant_id'   => $this->tenant_id,
            'user_id'     => $userId,
            'type'        => WorkOrderEvent::TYPE_STATUS_CHANGED,
            'description' => "Status changed to: {$newStatus->label()}{$suffix}",
        ]);
    }

    public function putOnHold(string $reason): void
    {
        $now = now();
        $userId = auth()->id();

        $this->update([
            'on_hold'     => true,
            'held_at'     => $now,
            'hold_reason' => $reason,
        ]);

        $this->events()->create([
            'tenant_id'   => $this->tenant_id,
            'user_id'     => $userId,
            'type'        => WorkOrderEvent::TYPE_HOLD_STARTED,
            'description' => "Put on hold: {$reason}",
        ]);
    }

    public function releaseHold(): void
    {
        $userId = auth()->id();

        $this->update([
            'on_hold'     => false,
            'held_at'     => null,
            'hold_reason' => null,
        ]);

        $this->events()->create([
            'tenant_id'   => $this->tenant_id,
            'user_id'     => $userId,
            'type'        => WorkOrderEvent::TYPE_HOLD_RELEASED,
            'description' => 'Hold released.',
        ]);
    }

    public function kick(string $reason): void
    {
        $now = now();
        $userId = auth()->id();

        $this->update([
            'kicked'       => true,
            'kicked_at'    => $now,
            'kicked_reason'=> $reason,
        ]);

        $this->events()->create([
            'tenant_id'   => $this->tenant_id,
            'user_id'     => $userId,
            'type'        => WorkOrderEvent::TYPE_KICKED,
            'description' => "Kicked: {$reason}",
        ]);
    }

    // ── Analytics helpers ──────────────────────────────────────────────────────

    /** Calendar days from Acquired to now (or to Delivered) */
    public function daysInShop(): int
    {
        $start = $this->created_at;
        $end   = $this->isDelivered() ? $this->updated_at : now();

        return (int) $start->diffInDays($end);
    }

    /** Days waiting on insurance approval */
    public function daysWaitingOnInsurance(): ?int
    {
        $log = $this->statusLogs
            ->where('status', WorkOrderStatus::WaitingOnInsurance->value)
            ->first();

        if (! $log) {
            return null;
        }

        $end = $log->exited_at ?? now();
        return (int) $log->entered_at->diffInDays($end);
    }

    /** Count of supplement submissions */
    public function supplementCount(): int
    {
        return $this->events->where('type', WorkOrderEvent::TYPE_SUPPLEMENT)->count();
    }

    // ── RO Number generation ───────────────────────────────────────────────────

    public static function generateRoNumber(int $tenantId): string
    {
        $year = now()->year;

        $last = static::where('tenant_id', $tenantId)
            ->where('ro_number', 'like', "WO-{$year}-%")
            ->orderByDesc('id')
            ->value('ro_number');

        if ($last) {
            $seq = (int) substr(strrchr($last, '-'), 1) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('WO-%d-%04d', $year, $seq);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('kicked', false)->where('status', '!=', WorkOrderStatus::Delivered->value);
    }

    public function scopeWithStatus($query, WorkOrderStatus $status)
    {
        return $query->where('status', $status->value);
    }
}
