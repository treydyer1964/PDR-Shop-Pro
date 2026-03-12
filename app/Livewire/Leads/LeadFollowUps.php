<?php

namespace App\Livewire\Leads;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Lead;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LeadFollowUps extends Component
{
    public Lead $lead;

    public bool   $adding               = false;
    public string $scheduled_at         = '';
    public string $notes                = '';
    public int    $appointment_type_id  = 0;

    #[Computed]
    public function appointmentTypes()
    {
        return AppointmentType::forTenant(auth()->user()->tenant_id)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function followUps()
    {
        return Appointment::where('lead_id', $this->lead->id)
            ->with(['type', 'createdBy'])
            ->orderBy('scheduled_at')
            ->get();
    }

    public function openAdd(): void
    {
        $this->adding              = true;
        $this->scheduled_at        = '';
        $this->notes               = '';
        $this->appointment_type_id = 0;
    }

    public function cancelAdd(): void
    {
        $this->adding = false;
    }

    public function saveFollowUp(): void
    {
        $this->validate([
            'appointment_type_id' => 'required|integer|min:1',
            'scheduled_at'        => 'required|date',
            'notes'               => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        Appointment::create([
            'tenant_id'            => $user->tenant_id,
            'lead_id'              => $this->lead->id,
            'appointment_type_id'  => $this->appointment_type_id,
            'scheduled_at'         => $this->scheduled_at,
            'notes'                => $this->notes ?: null,
            'status'               => AppointmentStatus::Scheduled->value,
            'created_by'           => $user->id,
        ]);

        $this->adding = false;
        unset($this->followUps);
    }

    public function complete(int $id): void
    {
        $appt = Appointment::where('id', $id)
            ->where('lead_id', $this->lead->id)
            ->firstOrFail();

        $appt->update([
            'status'       => AppointmentStatus::Completed->value,
            'completed_at' => now(),
        ]);

        unset($this->followUps);
    }

    public function delete(int $id): void
    {
        $appt = Appointment::where('id', $id)
            ->where('lead_id', $this->lead->id)
            ->firstOrFail();

        $appt->delete();
        unset($this->followUps);
    }

    public function render()
    {
        return view('livewire.leads.lead-follow-ups');
    }
}
