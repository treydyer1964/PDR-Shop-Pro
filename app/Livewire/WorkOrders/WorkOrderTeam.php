<?php

namespace App\Livewire\WorkOrders;

use App\Enums\Role;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderAssignment;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WorkOrderTeam extends Component
{
    public WorkOrder $workOrder;

    // Add-assignment form state
    public string $addRole    = '';
    public string $addUserId  = '';
    public string $addSplit   = '';

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder;
    }

    #[Computed]
    public function assignableRoles(): array
    {
        return Role::assignable();
    }

    #[Computed]
    public function currentRoleEnum(): ?Role
    {
        if ($this->addRole === '') return null;
        return Role::from($this->addRole);
    }

    #[Computed]
    public function availableStaff()
    {
        if ($this->addRole === '') return collect();

        return User::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->whereHas('roles', fn($q) => $q->where('name', $this->addRole))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function assignments()
    {
        return $this->workOrder->assignments()
            ->with('user')
            ->get()
            ->groupBy(fn($a) => $a->role->value);
    }

    public function updatedAddRole(): void
    {
        $this->addUserId = '';
        $this->addSplit  = '';
    }

    public function addAssignment(): void
    {
        $this->validate([
            'addRole'   => 'required|string',
            'addUserId' => 'required|exists:users,id',
            'addSplit'  => 'nullable|numeric|min:0|max:100',
        ]);

        $role = Role::from($this->addRole);

        // Check for duplicate
        $exists = WorkOrderAssignment::where('work_order_id', $this->workOrder->id)
            ->where('user_id', $this->addUserId)
            ->where('role', $this->addRole)
            ->exists();

        if ($exists) {
            $this->addError('addUserId', 'This person is already assigned in that role.');
            return;
        }

        // Auto-balance splits for roles that use split %
        $split = null;
        if ($role->usesSplitPct()) {
            $split = $this->addSplit !== '' ? (float) $this->addSplit : null;

            // If no split provided, auto-balance existing + new equally
            if ($split === null) {
                $currentCount = WorkOrderAssignment::where('work_order_id', $this->workOrder->id)
                    ->where('role', $this->addRole)
                    ->count();
                $newCount = $currentCount + 1;
                $evenSplit = round(100 / $newCount, 2);

                // Update existing assignments to equal split
                WorkOrderAssignment::where('work_order_id', $this->workOrder->id)
                    ->where('role', $this->addRole)
                    ->update(['split_pct' => $evenSplit]);

                $split = $evenSplit;
            }
        }

        WorkOrderAssignment::create([
            'tenant_id'     => $this->workOrder->tenant_id,
            'work_order_id' => $this->workOrder->id,
            'user_id'       => $this->addUserId,
            'role'          => $this->addRole,
            'split_pct'     => $split,
        ]);

        $this->workOrder->events()->create([
            'tenant_id'   => $this->workOrder->tenant_id,
            'user_id'     => auth()->id(),
            'type'        => 'team_updated',
            'description' => User::find($this->addUserId)?->name . ' assigned as ' . $role->label(),
        ]);

        $this->reset(['addRole', 'addUserId', 'addSplit']);
        $this->workOrder->load('assignments.user');
    }

    public function updateSplit(int $assignmentId, string $value): void
    {
        $assignment = WorkOrderAssignment::findOrFail($assignmentId);
        abort_unless($assignment->work_order_id === $this->workOrder->id, 403);

        $assignment->update(['split_pct' => is_numeric($value) ? (float) $value : null]);
        $this->workOrder->load('assignments.user');
    }

    public function removeAssignment(int $assignmentId): void
    {
        $assignment = WorkOrderAssignment::findOrFail($assignmentId);
        abort_unless($assignment->work_order_id === $this->workOrder->id, 403);

        $role     = $assignment->role;
        $userName = $assignment->user->name;
        $assignment->delete();

        // Re-balance equal splits after removal
        if ($role->usesSplitPct()) {
            $remaining = WorkOrderAssignment::where('work_order_id', $this->workOrder->id)
                ->where('role', $role->value)
                ->get();
            if ($remaining->count() > 0) {
                $evenSplit = round(100 / $remaining->count(), 2);
                WorkOrderAssignment::where('work_order_id', $this->workOrder->id)
                    ->where('role', $role->value)
                    ->update(['split_pct' => $evenSplit]);
            }
        }

        $this->workOrder->events()->create([
            'tenant_id'   => $this->workOrder->tenant_id,
            'user_id'     => auth()->id(),
            'type'        => 'team_updated',
            'description' => "{$userName} removed from {$role->label()}",
        ]);

        $this->workOrder->load('assignments.user');
    }

    public function render()
    {
        return view('livewire.work-orders.work-order-team');
    }
}
