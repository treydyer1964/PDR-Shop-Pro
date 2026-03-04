<?php

namespace App\Livewire\Leads;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Territory;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LeadMap extends Component
{
    #[Computed]
    public function allLeads(): array
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Lead::forTenant($tenantId)
            ->with(['assignedUser'])
            ->whereNotNull('lat')
            ->whereNotNull('lng');

        if ($user->isFieldStaff()) {
            $query->where('assigned_to', $user->id);
        }

        return $query->get()->map(fn($lead) => [
            'id'          => $lead->id,
            'lat'         => $lead->lat,
            'lng'         => $lead->lng,
            'name'        => $lead->fullName(),
            'status'      => $lead->status->value,
            'statusLabel' => $lead->status->label(),
            'color'       => $this->statusColor($lead->status),
            'phone'       => $lead->phone,
            'address'     => $lead->locationLabel(),
            'rep'         => $lead->assignedUser?->name,
            'url'         => route('leads.show', $lead->id),
        ])->toArray();
    }

    #[Computed]
    public function unlocatedLeads()
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Lead::forTenant($tenantId)
            ->with(['assignedUser'])
            ->where(fn($q) => $q->whereNull('lat')->orWhereNull('lng'));

        if ($user->isFieldStaff()) {
            $query->where('assigned_to', $user->id);
        }

        return $query->latest()->get();
    }

    #[Computed]
    public function territories(): array
    {
        if (auth()->user()->isFieldStaff()) {
            return [];
        }

        return Territory::forTenant(auth()->user()->tenant_id)
            ->with('assignedUser')
            ->where('active', true)
            ->whereNotNull('boundary')
            ->get()
            ->map(fn($t) => [
                'name'     => $t->name,
                'color'    => $t->color,
                'boundary' => $t->boundary,
                'rep'      => $t->assignedUser?->name,
            ])->toArray();
    }

    #[Computed]
    public function statuses(): array
    {
        return LeadStatus::cases();
    }

    private function statusColor(LeadStatus $status): string
    {
        return match($status) {
            LeadStatus::New            => '#3b82f6',
            LeadStatus::Contacted      => '#eab308',
            LeadStatus::AppointmentSet => '#22c55e',
            LeadStatus::NoAnswer       => '#94a3b8',
            LeadStatus::NotInterested  => '#ef4444',
            LeadStatus::Converted      => '#a855f7',
            LeadStatus::Lost           => '#64748b',
        };
    }

    public function render()
    {
        return view('livewire.leads.lead-map');
    }
}
