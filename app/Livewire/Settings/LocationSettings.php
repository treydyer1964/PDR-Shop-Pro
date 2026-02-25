<?php

namespace App\Livewire\Settings;

use App\Models\Location;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LocationSettings extends Component
{
    // Add form
    public bool   $showAddForm = false;
    public string $addName     = '';
    public string $addAddress  = '';
    public string $addCity     = '';
    public string $addState    = '';
    public string $addZip      = '';
    public string $addPhone    = '';

    // Edit state
    public ?int   $editingId      = null;
    public string $editName       = '';
    public string $editAddress    = '';
    public string $editCity       = '';
    public string $editState      = '';
    public string $editZip        = '';
    public string $editPhone      = '';

    #[Computed]
    public function locations()
    {
        return Location::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('active', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function startEdit(int $id): void
    {
        $loc = Location::findOrFail($id);
        abort_unless($loc->tenant_id === auth()->user()->tenant_id, 403);

        $this->editingId   = $id;
        $this->editName    = $loc->name;
        $this->editAddress = $loc->address ?? '';
        $this->editCity    = $loc->city ?? '';
        $this->editState   = $loc->state ?? '';
        $this->editZip     = $loc->zip ?? '';
        $this->editPhone   = $loc->phone ?? '';
        $this->showAddForm = false;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editName'  => 'required|string|max:255',
            'editPhone' => 'nullable|string|max:30',
        ]);

        $loc = Location::findOrFail($this->editingId);
        abort_unless($loc->tenant_id === auth()->user()->tenant_id, 403);

        $loc->update([
            'name'    => $this->editName,
            'address' => $this->editAddress ?: null,
            'city'    => $this->editCity ?: null,
            'state'   => $this->editState ?: null,
            'zip'     => $this->editZip ?: null,
            'phone'   => $this->editPhone ?: null,
        ]);

        $this->editingId = null;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function toggleActive(int $id): void
    {
        $loc = Location::findOrFail($id);
        abort_unless($loc->tenant_id === auth()->user()->tenant_id, 403);
        $loc->update(['active' => !$loc->active]);
    }

    public function addLocation(): void
    {
        $this->validate([
            'addName'  => 'required|string|max:255',
            'addPhone' => 'nullable|string|max:30',
        ], [
            'addName.required' => 'Location name is required.',
        ]);

        Location::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name'      => $this->addName,
            'address'   => $this->addAddress ?: null,
            'city'      => $this->addCity ?: null,
            'state'     => $this->addState ?: null,
            'zip'       => $this->addZip ?: null,
            'phone'     => $this->addPhone ?: null,
            'active'    => true,
        ]);

        $this->reset(['addName', 'addAddress', 'addCity', 'addState', 'addZip', 'addPhone']);
        $this->showAddForm = false;
    }

    public function render()
    {
        return view('livewire.settings.location-settings');
    }
}
