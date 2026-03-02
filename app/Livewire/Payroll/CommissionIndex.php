<?php

namespace App\Livewire\Payroll;

use App\Models\User;
use App\Models\WorkOrderCommission;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CommissionIndex extends Component
{
    public string $filterStatus  = 'unpaid'; // unpaid | paid | all
    public string $filterStaff   = '';
    public bool   $isFieldStaff  = false;

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isFieldStaff()) {
            $this->isFieldStaff = true;
            $this->filterStaff  = (string) $user->id;
        }
    }

    // Field staff cannot change their own staff filter
    public function updatedFilterStaff(): void
    {
        if ($this->isFieldStaff) {
            $this->filterStaff = (string) auth()->id();
        }
    }

    #[Computed]
    public function staff()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function commissions()
    {
        $tenantId = auth()->user()->tenant_id;

        $query = WorkOrderCommission::with(['user', 'workOrder.vehicle', 'workOrder.customer'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at');

        if ($this->filterStatus === 'unpaid') {
            $query->where('is_paid', false);
        } elseif ($this->filterStatus === 'paid') {
            $query->where('is_paid', true);
        }

        if ($this->filterStaff !== '') {
            $query->where('user_id', $this->filterStaff);
        }

        return $query->get()->groupBy('user_id')->map(fn($items) => [
            'user'        => $items->first()->user,
            'commissions' => $items,
            'total'       => $items->sum('amount'),
            'paid'        => $items->where('is_paid', true)->sum('amount'),
            'unpaid'      => $items->where('is_paid', false)->sum('amount'),
        ])->values()->sortBy(fn($r) => $r['user']->name)->values();
    }

    #[Computed]
    public function grandTotal(): float
    {
        return $this->commissions->sum('total');
    }

    public function render()
    {
        return view('livewire.payroll.commission-index');
    }
}
