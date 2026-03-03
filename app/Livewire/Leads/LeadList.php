<?php

namespace App\Livewire\Leads;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LeadList extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $filterStatus = '';

    #[Url(as: 'rep')]
    public string $filterRep = '';

    #[Url(as: 'q')]
    public string $search = '';

    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterRep(): void    { $this->resetPage(); }
    public function updatedSearch(): void       { $this->resetPage(); }

    #[Computed]
    public function leads()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Lead::forTenant($tenantId)
            ->with(['assignedUser', 'territory'])
            ->withCount(['followUps as pending_follow_ups_count' => fn($q) => $q->whereNull('completed_at')])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterRep,    fn($q) => $q->where('assigned_to', $this->filterRep))
            ->when($this->search,       fn($q) => $q->where(function($q2) {
                $q2->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name',  'like', "%{$this->search}%")
                   ->orWhere('phone',      'like', "%{$this->search}%")
                   ->orWhere('address',    'like', "%{$this->search}%");
            }))
            ->latest();

        // Sales advisors see only their own leads
        if ($user->isFieldStaff()) {
            $query->where('assigned_to', $user->id);
        }

        return $query->paginate(20);
    }

    #[Computed]
    public function reps()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['owner', 'sales_manager', 'sales_advisor']))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function statuses(): array
    {
        return LeadStatus::cases();
    }

    public function render()
    {
        return view('livewire.leads.lead-list');
    }
}
