<?php

namespace App\Livewire\Leads;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\StormEvent;
use App\Models\Territory;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LeadForm extends Component
{
    public ?Lead $lead = null;

    public string $first_name        = '';
    public string $last_name         = '';
    public string $phone             = '';
    public string $email             = '';
    public string $address           = '';
    public string $city              = '';
    public string $state             = '';
    public string $zip               = '';
    public string $lat               = '';
    public string $lng               = '';
    public string $status            = 'new';
    public string $source            = 'door_to_door';
    public string $job_type_interest = 'insurance';
    public string $vehicle_year      = '';
    public string $vehicle_make      = '';
    public string $vehicle_model     = '';
    public string $notes             = '';
    public string $damage_level      = '';
    public string $assigned_to       = '';
    public string $territory_id      = '';
    public string $storm_event_id    = '';

    public function mount(?Lead $lead = null): void
    {
        if ($lead && $lead->exists) {
            $this->lead          = $lead;
            $this->first_name    = $lead->first_name;
            $this->last_name     = $lead->last_name ?? '';
            $this->phone         = $lead->phone ?? '';
            $this->email         = $lead->email ?? '';
            $this->address       = $lead->address ?? '';
            $this->city          = $lead->city ?? '';
            $this->state         = $lead->state ?? '';
            $this->zip           = $lead->zip ?? '';
            $this->lat           = $lead->lat ? (string) $lead->lat : '';
            $this->lng           = $lead->lng ? (string) $lead->lng : '';
            $this->status        = $lead->status->value;
            $this->source        = $lead->source->value;
            $this->job_type_interest = $lead->job_type_interest ?? '';
            $this->vehicle_year  = $lead->vehicle_year ?? '';
            $this->vehicle_make  = $lead->vehicle_make ?? '';
            $this->vehicle_model = $lead->vehicle_model ?? '';
            $this->notes         = $lead->notes ?? '';
            $this->damage_level  = $lead->damage_level ?? '';
            $this->assigned_to     = $lead->assigned_to ? (string) $lead->assigned_to : '';
            $this->territory_id    = $lead->territory_id ? (string) $lead->territory_id : '';
            $this->storm_event_id  = $lead->storm_event_id ? (string) $lead->storm_event_id : '';
        } else {
            // Default assignee to current user if they're a rep
            $user = auth()->user();
            if (! $user->canAccessAnalytics()) {
                $this->assigned_to = (string) $user->id;
            }

            // Pre-fill lat/lng from map click (?lat=X&lng=Y)
            if (request()->filled('lat')) {
                $this->lat = (string) request()->query('lat');
            }
            if (request()->filled('lng')) {
                $this->lng = (string) request()->query('lng');
            }
        }
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
    public function territories()
    {
        return Territory::forTenant(auth()->user()->tenant_id)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color']);
    }

    #[Computed]
    public function stormEvents()
    {
        return StormEvent::forTenant(auth()->user()->tenant_id)
            ->orderByDesc('event_date')
            ->get(['id', 'name', 'city', 'state']);
    }

    #[Computed]
    public function statuses(): array
    {
        return LeadStatus::cases();
    }

    #[Computed]
    public function sources(): array
    {
        return LeadSource::cases();
    }

    public function save(): void
    {
        $this->validate([
            'first_name'        => 'nullable|string|max:100',
            'last_name'         => 'nullable|string|max:100',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:150',
            'address'           => 'nullable|string|max:200',
            'city'              => 'nullable|string|max:100',
            'state'             => 'nullable|string|size:2',
            'zip'               => 'nullable|string|max:10',
            'lat'               => 'nullable|numeric',
            'lng'               => 'nullable|numeric',
            'status'            => 'required|string',
            'source'            => 'required|string',
            'job_type_interest' => 'nullable|string',
            'vehicle_year'      => 'nullable|string|max:4',
            'vehicle_make'      => 'nullable|string|max:50',
            'vehicle_model'     => 'nullable|string|max:50',
            'notes'             => 'nullable|string',
            'damage_level'      => 'nullable|string|max:20',
            'assigned_to'       => 'nullable|integer',
            'territory_id'      => 'nullable|integer',
            'storm_event_id'    => 'nullable|integer',
        ]);

        $user = auth()->user();
        $data = [
            'tenant_id'          => $user->tenant_id,
            'first_name'         => $this->first_name ?: null,
            'last_name'          => $this->last_name ?: null,
            'phone'              => $this->phone ?: null,
            'email'              => $this->email ?: null,
            'address'            => $this->address ?: null,
            'city'               => $this->city ?: null,
            'state'              => $this->state ?: null,
            'zip'                => $this->zip ?: null,
            'lat'                => $this->lat !== '' ? (float) $this->lat : null,
            'lng'                => $this->lng !== '' ? (float) $this->lng : null,
            'status'             => $this->status,
            'source'             => $this->source,
            'job_type_interest'  => $this->job_type_interest ?: null,
            'vehicle_year'       => $this->vehicle_year ?: null,
            'vehicle_make'       => $this->vehicle_make ?: null,
            'vehicle_model'      => $this->vehicle_model ?: null,
            'notes'              => $this->notes ?: null,
            'damage_level'       => $this->damage_level ?: null,
            'assigned_to'        => $this->assigned_to ?: null,
            'territory_id'       => $this->territory_id ?: null,
            'storm_event_id'     => $this->storm_event_id ?: null,
        ];

        if ($this->lead && $this->lead->exists) {
            $oldStatus = $this->lead->status->value;
            $this->lead->update($data);

            if ($oldStatus !== $this->status) {
                $this->lead->statusLogs()->create([
                    'tenant_id'  => $user->tenant_id,
                    'lead_id'    => $this->lead->id,
                    'status'     => $this->status,
                    'changed_by' => $user->id,
                ]);
            }

            session()->flash('success', 'Lead updated.');
            $this->redirect(route('leads.show', $this->lead), navigate: true);
        } else {
            $data['created_by'] = $user->id;
            $lead = Lead::create($data);

            $lead->statusLogs()->create([
                'tenant_id'  => $user->tenant_id,
                'lead_id'    => $lead->id,
                'status'     => $this->status,
                'changed_by' => $user->id,
            ]);

            session()->flash('success', 'Lead created.');
            $this->redirect(route('leads.show', $lead), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.leads.lead-form');
    }
}
