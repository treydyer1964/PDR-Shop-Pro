<?php

namespace App\Livewire\Payroll;

use App\Models\PayRun;
use App\Models\WorkOrderCommission;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PayRunList extends Component
{
    #[Computed]
    public function payRuns()
    {
        return PayRun::where('tenant_id', auth()->user()->tenant_id)
            ->with(['createdBy', 'approvedBy'])
            ->withCount('commissions')
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return WorkOrderCommission::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_paid', false)
            ->whereHas('workOrder', fn($q) => $q->whereNotNull('commissions_locked_at'))
            ->count();
    }

    public function render()
    {
        return view('livewire.payroll.pay-run-list');
    }
}
