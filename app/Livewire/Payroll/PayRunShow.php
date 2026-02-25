<?php

namespace App\Livewire\Payroll;

use App\Models\PayRun;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PayRunShow extends Component
{
    public PayRun $payRun;

    public function mount(PayRun $payRun): void
    {
        abort_unless($payRun->tenant_id === auth()->user()->tenant_id, 403);
        $this->payRun = $payRun;
    }

    #[Computed]
    public function staffSummary()
    {
        return $this->payRun->commissions()
            ->with(['user', 'workOrder.vehicle', 'workOrder.customer'])
            ->get()
            ->groupBy('user_id')
            ->map(fn($items) => [
                'user'        => $items->first()->user,
                'commissions' => $items,
                'total'       => $items->sum('amount'),
            ])
            ->values()
            ->sortBy(fn($row) => $row['user']->name)
            ->values();
    }

    public function render()
    {
        return view('livewire.payroll.pay-run-show');
    }
}
