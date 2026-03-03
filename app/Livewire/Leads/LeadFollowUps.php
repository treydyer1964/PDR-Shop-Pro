<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LeadFollowUps extends Component
{
    public Lead $lead;

    public bool   $adding        = false;
    public string $scheduled_at  = '';
    public string $notes         = '';

    #[Computed]
    public function followUps()
    {
        return $this->lead->followUps()->with('creator')->get();
    }

    public function openAdd(): void
    {
        $this->adding       = true;
        $this->scheduled_at = '';
        $this->notes        = '';
    }

    public function cancelAdd(): void
    {
        $this->adding = false;
    }

    public function saveFollowUp(): void
    {
        $this->validate([
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        LeadFollowUp::create([
            'tenant_id'    => $user->tenant_id,
            'lead_id'      => $this->lead->id,
            'scheduled_at' => $this->scheduled_at,
            'notes'        => $this->notes ?: null,
            'created_by'   => $user->id,
        ]);

        $this->adding = false;
        unset($this->followUps);
    }

    public function complete(int $id): void
    {
        $followUp = LeadFollowUp::findOrFail($id);
        abort_unless($followUp->lead_id === $this->lead->id, 403);

        $followUp->update([
            'completed_at'  => now(),
            'completed_by'  => auth()->id(),
        ]);

        unset($this->followUps);
    }

    public function delete(int $id): void
    {
        $followUp = LeadFollowUp::findOrFail($id);
        abort_unless($followUp->lead_id === $this->lead->id, 403);
        $followUp->delete();
        unset($this->followUps);
    }

    public function render()
    {
        return view('livewire.leads.lead-follow-ups');
    }
}
