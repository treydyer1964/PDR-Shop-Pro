<?php

namespace App\Livewire\StormEvents;

use App\Enums\StormType;
use App\Models\StormEvent;
use Livewire\Attributes\Computed;
use Livewire\Component;

class StormEventList extends Component
{
    // Create form
    public bool   $creating   = false;
    public string $name       = '';
    public string $event_date = '';
    public string $city       = '';
    public string $state      = '';
    public string $storm_type = 'hail';
    public string $notes      = '';

    // Edit form
    public ?int   $editingId  = null;
    public string $editName   = '';
    public string $editDate   = '';
    public string $editCity   = '';
    public string $editState  = '';
    public string $editType   = 'hail';
    public string $editNotes  = '';

    #[Computed]
    public function stormEvents()
    {
        return StormEvent::forTenant(auth()->user()->tenant_id)
            ->withCount('workOrders')
            ->orderByDesc('event_date')
            ->get();
    }

    #[Computed]
    public function stormTypes(): array
    {
        return StormType::cases();
    }

    public function openCreate(): void
    {
        $this->creating  = true;
        $this->editingId = null;
        $this->name = $this->event_date = $this->city = $this->state = $this->notes = '';
        $this->storm_type = 'hail';
        $this->resetValidation();
    }

    public function cancelCreate(): void
    {
        $this->creating = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(auth()->user()->canManageStaff(), 403);

        $this->validate([
            'name'       => 'required|string|max:255',
            'event_date' => 'required|date',
            'city'       => 'nullable|string|max:100',
            'state'      => 'nullable|string|size:2',
            'storm_type' => 'required|in:hail,wind,other',
            'notes'      => 'nullable|string',
        ]);

        StormEvent::create([
            'tenant_id'  => auth()->user()->tenant_id,
            'name'       => $this->name,
            'event_date' => $this->event_date,
            'city'       => $this->city ?: null,
            'state'      => $this->state ?: null,
            'storm_type' => $this->storm_type,
            'notes'      => $this->notes ?: null,
        ]);

        $this->creating = false;
        $this->reset(['name', 'event_date', 'city', 'state', 'storm_type', 'notes']);
        unset($this->stormEvents);
        session()->flash('success', 'Storm event created.');
    }

    public function startEdit(int $id): void
    {
        abort_unless(auth()->user()->canManageStaff(), 403);

        $storm = StormEvent::findOrFail($id);
        abort_unless($storm->tenant_id === auth()->user()->tenant_id, 403);

        $this->creating  = false;
        $this->editingId = $id;
        $this->editName  = $storm->name;
        $this->editDate  = $storm->event_date->format('Y-m-d');
        $this->editCity  = $storm->city ?? '';
        $this->editState = $storm->state ?? '';
        $this->editType  = $storm->storm_type->value;
        $this->editNotes = $storm->notes ?? '';
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetValidation();
    }

    public function update(): void
    {
        abort_unless(auth()->user()->canManageStaff(), 403);

        $storm = StormEvent::findOrFail($this->editingId);
        abort_unless($storm->tenant_id === auth()->user()->tenant_id, 403);

        $this->validate([
            'editName'  => 'required|string|max:255',
            'editDate'  => 'required|date',
            'editCity'  => 'nullable|string|max:100',
            'editState' => 'nullable|string|size:2',
            'editType'  => 'required|in:hail,wind,other',
            'editNotes' => 'nullable|string',
        ]);

        $storm->update([
            'name'       => $this->editName,
            'event_date' => $this->editDate,
            'city'       => $this->editCity ?: null,
            'state'      => $this->editState ?: null,
            'storm_type' => $this->editType,
            'notes'      => $this->editNotes ?: null,
        ]);

        $this->editingId = null;
        unset($this->stormEvents);
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->canManageStaff(), 403);

        $storm = StormEvent::findOrFail($id);
        abort_unless($storm->tenant_id === auth()->user()->tenant_id, 403);

        // Detach WOs before deleting (FK set to null on delete handles this, but be explicit)
        $storm->workOrders()->update(['storm_event_id' => null]);
        $storm->delete();

        unset($this->stormEvents);
        session()->flash('success', 'Storm event deleted.');
    }

    public function render()
    {
        return view('livewire.storm-events.storm-event-list');
    }
}
