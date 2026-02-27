<?php

namespace App\Livewire\WorkOrders;

use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderEvent;
use App\Models\WorkOrderRental;
use App\Models\WorkOrderStatusLog;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WorkOrderShow extends Component
{
    public WorkOrder $workOrder;

    // Status transition
    public bool $showTransitionConfirm = false;
    public string $transitionNotes     = '';
    public string $transitionDate      = ''; // editable — defaults to today in mount

    // On Hold modal
    public bool $showHoldModal  = false;
    public string $holdReason   = '';

    // Kick modal
    public bool $showKickModal  = false;
    public string $kickReason   = '';

    // Add Note
    public bool $showNoteForm   = false;
    public string $noteText     = '';

    // Supplement log
    public bool $showSupplementForm = false;
    public string $supplementNotes  = '';

    // Sub-task date editing (inline)
    public ?string $editingSubTask = null;
    public string $subTaskDate     = '';

    // Status log date editing
    public ?int   $editingLogId  = null;
    public string $editLogDate   = '';

    public function mount(WorkOrder $workOrder): void
    {
        abort_unless($workOrder->tenant_id === auth()->user()->tenant_id, 403);
        $this->workOrder     = $workOrder;
        $this->transitionDate = now()->toDateString();
    }

    #[Computed]
    public function statuses(): array
    {
        return WorkOrderStatus::cases();
    }

    #[Computed]
    public function nextStatus(): ?WorkOrderStatus
    {
        return $this->workOrder->status->next();
    }

    #[Computed]
    public function events()
    {
        return $this->workOrder->events()->with('user')->get();
    }

    #[Computed]
    public function statusLogs()
    {
        return $this->workOrder->statusLogs()->with('user')->get();
    }

    #[Computed]
    public function recentNotes()
    {
        return $this->workOrder->events()
            ->where('type', WorkOrderEvent::TYPE_NOTE)
            ->with('user')
            ->latest()
            ->take(2)
            ->get();
    }

    #[Computed]
    public function rentalSummary(): ?WorkOrderRental
    {
        return WorkOrderRental::where('work_order_id', $this->workOrder->id)
            ->with(['vehicle', 'segments'])
            ->first();
    }

    #[Computed]
    public function expenseTotal(): float
    {
        return (float) $this->workOrder->expenses()->sum('amount');
    }

    #[Computed]
    public function netAmount(): float
    {
        return ($this->workOrder->invoice_total ?? 0) - $this->expenseTotal;
    }

    #[Computed]
    public function teamCount(): int
    {
        return $this->workOrder->assignments()->count();
    }

    // ── Status transitions ─────────────────────────────────────────────────────

    public function advanceStatus(): void
    {
        $next = $this->workOrder->status->next();

        if (! $next || $this->workOrder->isOnHold() || $this->workOrder->isKicked()) {
            return;
        }

        $at = $this->transitionDate
            ? \Carbon\Carbon::parse($this->transitionDate)->startOfDay()
            : now();

        $this->workOrder->transitionTo($next, $this->transitionNotes ?: null, $at);
        $this->workOrder->refresh();
        $this->transitionNotes = '';
        $this->transitionDate  = now()->toDateString();

        unset($this->nextStatus);
        unset($this->events);
    }

    public function revertStatus(): void
    {
        $prev = $this->workOrder->status->previous();

        if (! $prev) {
            return;
        }

        $this->workOrder->transitionTo($prev, 'Reverted by user.');
        $this->workOrder->refresh();

        unset($this->nextStatus);
        unset($this->events);
        unset($this->statusLogs);
    }

    public function jumpToStatus(string $statusValue): void
    {
        $status = WorkOrderStatus::from($statusValue);
        $at = $this->transitionDate
            ? \Carbon\Carbon::parse($this->transitionDate)->startOfDay()
            : now();

        $this->workOrder->transitionTo($status, null, $at);
        $this->workOrder->refresh();
        $this->transitionDate = now()->toDateString();

        unset($this->nextStatus);
        unset($this->events);
    }

    // ── On Hold ────────────────────────────────────────────────────────────────

    public function hold(): void
    {
        $this->validate(['holdReason' => 'required|string|max:500']);
        $this->workOrder->putOnHold($this->holdReason);
        $this->workOrder->refresh();
        $this->showHoldModal = false;
        $this->holdReason = '';
    }

    public function releaseHold(): void
    {
        $this->workOrder->releaseHold();
        $this->workOrder->refresh();
    }

    // ── Kick ──────────────────────────────────────────────────────────────────

    public function kick(): void
    {
        $this->validate(['kickReason' => 'required|string|max:500']);
        $this->workOrder->kick($this->kickReason);
        $this->workOrder->refresh();
        $this->showKickModal = false;
        $this->kickReason = '';
    }

    // ── Notes & Supplements ────────────────────────────────────────────────────

    public function addNote(): void
    {
        $this->validate(['noteText' => 'required|string|max:2000']);

        $this->workOrder->events()->create([
            'tenant_id'   => $this->workOrder->tenant_id,
            'user_id'     => auth()->id(),
            'type'        => WorkOrderEvent::TYPE_NOTE,
            'description' => $this->noteText,
        ]);

        $this->workOrder->refresh();
        $this->noteText = '';
        $this->showNoteForm = false;

        unset($this->events);
    }

    public function logSupplement(): void
    {
        $this->validate(['supplementNotes' => 'nullable|string|max:2000']);

        $this->workOrder->events()->create([
            'tenant_id'   => $this->workOrder->tenant_id,
            'user_id'     => auth()->id(),
            'type'        => WorkOrderEvent::TYPE_SUPPLEMENT,
            'description' => $this->supplementNotes ?: 'Supplement submitted.',
        ]);

        $this->workOrder->refresh();
        $this->supplementNotes = '';
        $this->showSupplementForm = false;

        unset($this->events);
    }

    // ── Sub-tasks ──────────────────────────────────────────────────────────────

    public function toggleSubTask(string $task): void
    {
        match ($task) {
            'teardown' => $this->workOrder->update([
                'teardown_completed_at' => $this->workOrder->teardown_completed_at ? null : now(),
            ]),
            'needs_parts_pre_repair' => $this->workOrder->update([
                'needs_parts_pre_repair' => ! $this->workOrder->needs_parts_pre_repair,
            ]),
            'needs_parts_reassembly' => $this->workOrder->update([
                'needs_parts_reassembly' => ! $this->workOrder->needs_parts_reassembly,
            ]),
            'needs_body_shop' => $this->workOrder->update([
                'needs_body_shop' => ! $this->workOrder->needs_body_shop,
            ]),
            'needs_glass' => $this->workOrder->update([
                'needs_glass' => ! $this->workOrder->needs_glass,
            ]),
            default => null,
        };

        $this->workOrder->refresh();
    }

    public function startEditSubTaskDate(string $field, ?string $currentDate = null): void
    {
        $this->editingSubTask = $field;
        $this->subTaskDate    = $currentDate
            ? \Carbon\Carbon::parse($currentDate)->toDateString()
            : now()->toDateString();
    }

    public function updateSubTaskDate(string $field): void
    {
        $this->validate(['subTaskDate' => 'required|date']);

        $this->workOrder->update([$field => $this->subTaskDate]);
        $this->workOrder->refresh();
        $this->editingSubTask = null;
        $this->subTaskDate    = '';
    }

    public function clearSubTaskDate(string $field): void
    {
        $this->workOrder->update([$field => null]);
        $this->workOrder->refresh();
    }

    // ── Status log date editing ────────────────────────────────────────────────

    public function startEditLogDate(int $logId): void
    {
        $log = WorkOrderStatusLog::findOrFail($logId);
        abort_unless($log->tenant_id === auth()->user()->tenant_id, 403);
        $this->editingLogId = $logId;
        $this->editLogDate  = $log->entered_at->toDateString();
    }

    public function saveLogDate(string $date): void
    {
        $this->editLogDate = $date;
        $this->validate(['editLogDate' => 'required|date']);

        $log = WorkOrderStatusLog::findOrFail($this->editingLogId);
        abort_unless($log->tenant_id === auth()->user()->tenant_id, 403);

        $log->update(['entered_at' => \Carbon\Carbon::parse($this->editLogDate)->startOfDay()]);

        $this->editingLogId = null;
        $this->editLogDate  = '';
        $this->workOrder->refresh();
        unset($this->statusLogs);
    }

    public function cancelEditLogDate(): void
    {
        $this->editingLogId = null;
        $this->editLogDate  = '';
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-show');
    }
}
