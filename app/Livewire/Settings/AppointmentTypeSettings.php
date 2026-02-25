<?php

namespace App\Livewire\Settings;

use App\Models\AppointmentType;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AppointmentTypeSettings extends Component
{
    public string $newName   = '';
    public ?int   $editingId = null;
    public string $editName  = '';

    #[Computed]
    public function types()
    {
        return AppointmentType::forTenant(auth()->user()->tenant_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function addType(): void
    {
        $this->validate(['newName' => 'required|string|max:100']);

        $maxSort = AppointmentType::forTenant(auth()->user()->tenant_id)->max('sort_order') ?? 0;

        AppointmentType::create([
            'tenant_id'  => auth()->user()->tenant_id,
            'name'       => $this->newName,
            'color'      => 'blue',
            'active'     => true,
            'sort_order' => $maxSort + 1,
        ]);

        $this->newName = '';
        unset($this->types);
    }

    public function startEdit(int $id): void
    {
        $type = AppointmentType::findOrFail($id);
        abort_unless($type->tenant_id === auth()->user()->tenant_id, 403);
        $this->editingId = $id;
        $this->editName  = $type->name;
    }

    public function saveEdit(): void
    {
        $this->validate(['editName' => 'required|string|max:100']);

        $type = AppointmentType::findOrFail($this->editingId);
        abort_unless($type->tenant_id === auth()->user()->tenant_id, 403);
        $type->update(['name' => $this->editName]);

        $this->editingId = null;
        unset($this->types);
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
    }

    public function toggleActive(int $id): void
    {
        $type = AppointmentType::findOrFail($id);
        abort_unless($type->tenant_id === auth()->user()->tenant_id, 403);
        $type->update(['active' => ! $type->active]);
        unset($this->types);
    }

    public function render()
    {
        return view('livewire.settings.appointment-type-settings');
    }
}
