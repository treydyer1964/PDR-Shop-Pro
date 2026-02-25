<?php

namespace App\Livewire\Rentals;

use App\Models\RentalVehicle;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RentalVehicleList extends Component
{
    // Add / edit form
    public bool   $showForm    = false;
    public ?int   $editingId   = null;
    public string $name        = '';
    public string $vin         = '';
    public string $year        = '';
    public string $make        = '';
    public string $model       = '';
    public string $color       = '';
    public string $dailyCost   = '';
    public string $notes       = '';

    #[Computed]
    public function vehicles()
    {
        return RentalVehicle::forTenant(auth()->user()->tenant_id)
            ->orderBy('active', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'vin', 'year', 'make', 'model', 'color', 'dailyCost', 'notes']);
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $v = RentalVehicle::findOrFail($id);
        abort_unless($v->tenant_id === auth()->user()->tenant_id, 403);

        $this->editingId = $id;
        $this->name      = $v->name;
        $this->vin       = $v->vin ?? '';
        $this->year      = $v->year ? (string) $v->year : '';
        $this->make      = $v->make ?? '';
        $this->model     = $v->model ?? '';
        $this->color     = $v->color ?? '';
        $this->dailyCost = number_format((float) $v->internal_daily_cost, 2, '.', '');
        $this->notes     = $v->notes ?? '';
        $this->showForm  = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name'      => 'required|string|max:100',
            'vin'       => 'nullable|string|size:17',
            'year'      => 'nullable|integer|min:1900|max:2100',
            'make'      => 'nullable|string|max:50',
            'model'     => 'nullable|string|max:50',
            'color'     => 'nullable|string|max:50',
            'dailyCost' => 'required|numeric|min:0',
            'notes'     => 'nullable|string|max:1000',
        ], [
            'vin.size'       => 'VIN must be exactly 17 characters.',
            'dailyCost.required' => 'Enter the internal daily cost.',
            'dailyCost.numeric'  => 'Daily cost must be a number.',
        ]);

        $fields = [
            'name'               => $data['name'],
            'vin'                => $data['vin'] ? strtoupper($data['vin']) : null,
            'year'               => $data['year'] ?: null,
            'make'               => $data['make'] ?: null,
            'model'              => $data['model'] ?: null,
            'color'              => $data['color'] ?: null,
            'internal_daily_cost' => (float) $data['dailyCost'],
            'notes'              => $data['notes'] ?: null,
        ];

        if ($this->editingId) {
            $v = RentalVehicle::findOrFail($this->editingId);
            abort_unless($v->tenant_id === auth()->user()->tenant_id, 403);
            $v->update($fields);
        } else {
            RentalVehicle::create(array_merge($fields, [
                'tenant_id' => auth()->user()->tenant_id,
                'active'    => true,
            ]));
        }

        $this->showForm = false;
        unset($this->vehicles);
    }

    public function toggleActive(int $id): void
    {
        $v = RentalVehicle::findOrFail($id);
        abort_unless($v->tenant_id === auth()->user()->tenant_id, 403);
        $v->update(['active' => ! $v->active]);
        unset($this->vehicles);
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->editingId = null;
    }

    public function render()
    {
        return view('livewire.rentals.rental-vehicle-list');
    }
}
