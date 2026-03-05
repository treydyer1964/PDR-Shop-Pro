<?php

namespace App\Livewire\Settings;

use App\Enums\LeadStatus;
use Livewire\Component;

class LeadStatusSettings extends Component
{
    public array $labels = [];

    public bool $saved = false;

    public function mount(): void
    {
        $overrides = auth()->user()->tenant->lead_status_labels ?? [];

        foreach (LeadStatus::cases() as $status) {
            $this->labels[$status->value] = $overrides[$status->value] ?? $status->label();
        }
    }

    public function save(): void
    {
        $rules = [];
        foreach (LeadStatus::cases() as $status) {
            $rules["labels.{$status->value}"] = 'required|string|max:50';
        }

        $this->validate($rules);

        auth()->user()->tenant->update([
            'lead_status_labels' => $this->labels,
        ]);

        $this->saved = true;
    }

    public function resetToDefaults(): void
    {
        auth()->user()->tenant->update(['lead_status_labels' => null]);

        foreach (LeadStatus::cases() as $status) {
            $this->labels[$status->value] = $status->label();
        }

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.settings.lead-status-settings', [
            'statuses' => LeadStatus::cases(),
        ]);
    }
}
