<?php

namespace App\Livewire\Payroll;

use App\Models\User;
use App\Models\WorkOrderCommission;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CommissionIndex extends Component
{
    public string $filterStatus   = 'unpaid'; // unpaid | paid | all
    public string $filterStaff    = '';
    public bool   $isFieldStaff   = false;
    public bool   $isSalesManager = false;

    // Roles a Sales Manager is allowed to see commissions for
    private const SM_VISIBLE_ROLES = ['sales_manager', 'sales_advisor'];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isFieldStaff()) {
            $this->isFieldStaff = true;
            $this->filterStaff  = (string) $user->id;
        } elseif ($user->isManager()) {
            $this->isSalesManager = true;
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
        $query = User::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->orderBy('name');

        if ($this->isSalesManager) {
            $query->whereHas('roles', fn($q) => $q->whereIn('name', self::SM_VISIBLE_ROLES));
        }

        return $query->get();
    }

    #[Computed]
    public function commissions()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $query = WorkOrderCommission::with(['user', 'workOrder.vehicle', 'workOrder.customer'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at');

        if ($this->filterStatus === 'unpaid') {
            $query->where('is_paid', false);
        } elseif ($this->filterStatus === 'paid') {
            $query->where('is_paid', true);
        }

        // Sales Manager: scope to SM + Advisor commissions only
        if ($this->isSalesManager && $this->filterStaff === '') {
            $query->whereIn('user_id', function ($sub) use ($tenantId) {
                $sub->select('users.id')
                    ->from('users')
                    ->join('role_user', 'role_user.user_id', '=', 'users.id')
                    ->where('users.tenant_id', $tenantId)
                    ->whereIn('role_user.role_name', self::SM_VISIBLE_ROLES);
            });
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
