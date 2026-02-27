<?php

namespace App\Livewire\Settings;

use App\Models\VehicleColor;
use Livewire\Attributes\Computed;
use Livewire\Component;

class VehicleColorSettings extends Component
{
    public bool   $showAddForm = false;
    public string $addName     = '';

    public ?int   $editingId   = null;
    public string $editName    = '';

    #[Computed]
    public function colors()
    {
        return VehicleColor::orderBy('sort_order')->orderBy('name')->get();
    }

    public function startEdit(int $id): void
    {
        $color = VehicleColor::findOrFail($id);
        $this->editingId   = $id;
        $this->editName    = $color->name;
        $this->showAddForm = false;
    }

    public function saveEdit(): void
    {
        $this->validate(['editName' => 'required|string|max:100']);
        VehicleColor::findOrFail($this->editingId)->update(['name' => $this->editName]);
        $this->editingId = null;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function toggleActive(int $id): void
    {
        $color = VehicleColor::findOrFail($id);
        $color->update(['active' => ! $color->active]);
    }

    public function addColor(): void
    {
        $this->validate(['addName' => 'required|string|max:100']);

        $maxSort = VehicleColor::max('sort_order') ?? 0;

        VehicleColor::create([
            'name'       => $this->addName,
            'sort_order' => $maxSort + 1,
            'active'     => true,
        ]);

        $this->addName     = '';
        $this->showAddForm = false;
        unset($this->colors);
    }

    public function deleteColor(int $id): void
    {
        VehicleColor::findOrFail($id)->delete();
        unset($this->colors);
    }

    public function render()
    {
        return view('livewire.settings.vehicle-color-settings');
    }
}
