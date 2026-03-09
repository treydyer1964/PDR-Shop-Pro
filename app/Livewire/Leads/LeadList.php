<?php

namespace App\Livewire\Leads;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\StormEvent;
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

    #[Url(as: 'storm')]
    public string $filterStorm = '';

    #[Url(as: 'from')]
    public string $filterDateFrom = '';

    #[Url(as: 'to')]
    public string $filterDateTo = '';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'contact')]
    public bool $filterHasContact = false;

    public function updatedFilterStatus(): void     { $this->resetPage(); }
    public function updatedFilterRep(): void        { $this->resetPage(); }
    public function updatedFilterStorm(): void      { $this->resetPage(); }
    public function updatedFilterDateFrom(): void   { $this->resetPage(); }
    public function updatedFilterDateTo(): void     { $this->resetPage(); }
    public function updatedSearch(): void           { $this->resetPage(); }
    public function updatedFilterHasContact(): void { $this->resetPage(); }

    public function clearDateFilter(): void
    {
        $this->filterDateFrom = '';
        $this->filterDateTo   = '';
        $this->resetPage();
    }

    public function setDatePreset(string $preset): void
    {
        match ($preset) {
            'all'      => [$this->filterDateFrom, $this->filterDateTo] = ['', ''],
            'today'    => [$this->filterDateFrom, $this->filterDateTo] = [now()->toDateString(), ''],
            'week'     => [$this->filterDateFrom, $this->filterDateTo] = [now()->startOfWeek()->toDateString(), ''],
            'month'    => [$this->filterDateFrom, $this->filterDateTo] = [now()->startOfMonth()->toDateString(), ''],
            'year'     => [$this->filterDateFrom, $this->filterDateTo] = [now()->startOfYear()->toDateString(), ''],
            'lastyear' => [$this->filterDateFrom, $this->filterDateTo] = [
                now()->subYear()->startOfYear()->toDateString(),
                now()->subYear()->endOfYear()->toDateString(),
            ],
            default => null,
        };
        $this->resetPage();
    }

    #[Computed]
    public function leads()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Lead::forTenant($tenantId)
            ->with(['assignedUser', 'territory', 'stormEvent'])
            ->withCount(['followUps as pending_follow_ups_count' => fn($q) => $q->whereNull('completed_at')])
            ->when($this->filterStatus,   fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterRep,      fn($q) => $q->where('assigned_to', $this->filterRep))
            ->when($this->filterStorm,    fn($q) => $q->where('storm_event_id', $this->filterStorm))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo,      fn($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->when($this->filterHasContact, fn($q) => $q->where(fn($q2) =>
                $q2->whereNotNull('first_name')
                   ->orWhereNotNull('last_name')
                   ->orWhereNotNull('phone')
            ))
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
    public function stormEvents()
    {
        return StormEvent::forTenant(auth()->user()->tenant_id)
            ->orderByDesc('event_date')
            ->get(['id', 'name', 'city', 'state', 'event_date']);
    }

    #[Computed]
    public function statuses(): array
    {
        return LeadStatus::cases();
    }

    #[Computed]
    public function statusLabelOverrides(): array
    {
        return auth()->user()->tenant->lead_status_labels ?? [];
    }

    public function render()
    {
        return view('livewire.leads.lead-list');
    }
}
