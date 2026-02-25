<?php

namespace App\Livewire\Payroll;

use App\Models\PayRun;
use App\Models\User;
use App\Models\WorkOrderCommission;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreatePayRun extends Component
{
    public int $step = 1;

    // Step 1 — Details & filters
    public string $name        = '';
    public string $periodStart = '';
    public string $periodEnd   = '';
    public array  $staffIds    = []; // empty = all staff

    // Step 2 — Preview (computed)
    // Step 3 — Confirm → creates pay run

    public function mount(): void
    {
        $this->name = 'Pay Run — ' . now()->format('M j, Y');
    }

    #[Computed]
    public function availableStaff()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function preview()
    {
        $tenantId = auth()->user()->tenant_id;

        $query = WorkOrderCommission::with(['user', 'workOrder.vehicle', 'workOrder.customer'])
            ->where('tenant_id', $tenantId)
            ->where('is_paid', false)
            ->whereHas('workOrder', fn($q) => $q->whereNotNull('commissions_locked_at'));

        // Filter by staff
        if (!empty($this->staffIds)) {
            $query->whereIn('user_id', $this->staffIds);
        }

        // Filter by WO creation date range
        if ($this->periodStart) {
            $query->whereHas('workOrder', fn($q) => $q->whereDate('created_at', '>=', $this->periodStart));
        }
        if ($this->periodEnd) {
            $query->whereHas('workOrder', fn($q) => $q->whereDate('created_at', '<=', $this->periodEnd));
        }

        return $query->get()
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

    #[Computed]
    public function grandTotal(): float
    {
        return $this->preview->sum('total');
    }

    #[Computed]
    public function commissionIds(): array
    {
        return $this->preview
            ->flatMap(fn($row) => $row['commissions']->pluck('id'))
            ->all();
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'name' => 'required|string|max:255',
            ]);

            if (empty($this->commissionIds)) {
                $this->addError('name', 'No locked, unpaid commissions match your filters.');
                return;
            }
        }

        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function confirm(): void
    {
        if (empty($this->commissionIds)) {
            return;
        }

        $tenantId = auth()->user()->tenant_id;
        $now      = now();

        $payRun = PayRun::create([
            'tenant_id'    => $tenantId,
            'name'         => $this->name,
            'notes'        => null,
            'period_start' => $this->periodStart ?: null,
            'period_end'   => $this->periodEnd ?: null,
            'total_amount' => $this->grandTotal,
            'created_by'   => auth()->id(),
            'approved_by'  => auth()->id(),
            'approved_at'  => $now,
        ]);

        WorkOrderCommission::whereIn('id', $this->commissionIds)->update([
            'pay_run_id' => $payRun->id,
            'is_paid'    => true,
            'paid_at'    => $now,
        ]);

        session()->flash('success', "Pay run \"{$this->name}\" created — \${$this->fmt($this->grandTotal)} paid to " . count($this->preview) . ' staff member(s).');

        $this->redirect(route('payroll.show', $payRun), navigate: true);
    }

    private function fmt(float $value): string
    {
        return number_format($value, 2);
    }

    public function render()
    {
        return view('livewire.payroll.create-pay-run');
    }
}
