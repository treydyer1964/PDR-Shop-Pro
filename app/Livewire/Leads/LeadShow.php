<?php

namespace App\Livewire\Leads;

use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\Lead;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LeadShow extends Component
{
    public Lead $lead;

    public string $newStatus = '';
    public string $statusNote = '';
    public bool   $showStatusForm = false;

    public function mount(Lead $lead): void
    {
        $this->lead      = $lead;
        $this->newStatus = $lead->status->value;
    }

    #[Computed]
    public function statusLogs()
    {
        return $this->lead->statusLogs()->with('changedBy')->get();
    }

    #[Computed]
    public function statuses(): array
    {
        return LeadStatus::cases();
    }

    public function openStatusForm(): void
    {
        $this->showStatusForm = true;
        $this->statusNote     = '';
    }

    public function cancelStatusForm(): void
    {
        $this->showStatusForm = false;
        $this->newStatus      = $this->lead->status->value;
    }

    public function updateStatus(): void
    {
        $this->validate([
            'newStatus'  => 'required|string',
            'statusNote' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        if ($this->newStatus !== $this->lead->status->value) {
            $this->lead->update(['status' => $this->newStatus]);

            $this->lead->statusLogs()->create([
                'tenant_id'  => $user->tenant_id,
                'lead_id'    => $this->lead->id,
                'status'     => $this->newStatus,
                'notes'      => $this->statusNote ?: null,
                'changed_by' => $user->id,
            ]);

            unset($this->statusLogs);
        }

        $this->showStatusForm = false;
        $this->lead->refresh();
    }

    public function convertToWorkOrder(): void
    {
        abort_unless(auth()->user()->canCreateWorkOrders(), 403);
        abort_if($this->lead->isConverted(), 403);

        $user = auth()->user();

        // Find or create the customer from lead data
        $customer = Customer::where('tenant_id', $user->tenant_id)
            ->where(function ($q) {
                if ($this->lead->phone) {
                    $q->orWhere('phone', $this->lead->phone);
                }
                if ($this->lead->email) {
                    $q->orWhere('email', $this->lead->email);
                }
            })
            ->first();

        if (! $customer && ($this->lead->phone || $this->lead->email || $this->lead->first_name)) {
            $customer = Customer::create([
                'tenant_id'  => $user->tenant_id,
                'first_name' => $this->lead->first_name,
                'last_name'  => $this->lead->last_name ?? '',
                'phone'      => $this->lead->phone,
                'email'      => $this->lead->email,
                'address'    => $this->lead->address,
                'city'       => $this->lead->city,
                'state'      => $this->lead->state,
                'zip'        => $this->lead->zip,
                'created_by' => $user->id,
            ]);
        }

        // Mark lead as converted
        $this->lead->update(['status' => LeadStatus::Converted->value]);
        $this->lead->statusLogs()->create([
            'tenant_id'  => $user->tenant_id,
            'lead_id'    => $this->lead->id,
            'status'     => LeadStatus::Converted->value,
            'notes'      => 'Converted to work order.',
            'changed_by' => $user->id,
        ]);

        // Redirect to WO create wizard with customer pre-filled
        $params = $customer ? ['customer_id' => $customer->id, 'lead_id' => $this->lead->id] : ['lead_id' => $this->lead->id];
        $this->redirect(route('work-orders.create', $params), navigate: true);
    }

    public function render()
    {
        return view('livewire.leads.lead-show');
    }
}
