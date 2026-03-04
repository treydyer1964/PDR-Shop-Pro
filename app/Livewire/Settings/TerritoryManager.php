<?php

namespace App\Livewire\Settings;

use App\Models\Territory;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TerritoryManager extends Component
{
    public bool   $showForm       = false;
    public string $name           = '';
    public string $color          = '#3b82f6';
    public ?int   $assignedUserId = null;
    public bool   $active         = true;
    public string $boundary       = ''; // JSON string from Leaflet.draw
    public ?int   $editingId      = null;

    #[Computed]
    public function territories()
    {
        return Territory::forTenant(auth()->user()->tenant_id)
            ->with('assignedUser')
            ->orderBy('name')
            ->get();
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
    public function existingTerritories(): array
    {
        return Territory::forTenant(auth()->user()->tenant_id)
            ->whereNotNull('boundary')
            ->get()
            ->map(fn($t) => [
                'id'       => $t->id,
                'name'     => $t->name,
                'color'    => $t->color,
                'boundary' => $t->boundary,
            ])->toArray();
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'color', 'assignedUserId', 'active', 'boundary', 'editingId']);
        $this->color    = '#3b82f6';
        $this->active   = true;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $territory = Territory::forTenant(auth()->user()->tenant_id)->findOrFail($id);

        $this->editingId      = $id;
        $this->name           = $territory->name;
        $this->color          = $territory->color;
        $this->assignedUserId = $territory->assigned_user_id;
        $this->active         = $territory->active;
        $this->boundary       = $territory->boundary ? json_encode($territory->boundary) : '';
        $this->showForm       = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|string|max:20',
        ]);

        $data = [
            'tenant_id'        => auth()->user()->tenant_id,
            'name'             => $this->name,
            'color'            => $this->color,
            'assigned_user_id' => $this->assignedUserId ?: null,
            'active'           => $this->active,
            'boundary'         => $this->boundary ? json_decode($this->boundary, true) : null,
        ];

        if ($this->editingId) {
            Territory::where('id', $this->editingId)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->update($data);
        } else {
            Territory::create($data);
        }

        $this->reset(['name', 'color', 'assignedUserId', 'active', 'boundary', 'editingId', 'showForm']);
        $this->color = '#3b82f6';
        session()->flash('success', 'Territory saved.');
    }

    public function delete(int $id): void
    {
        Territory::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->delete();
    }

    public function cancel(): void
    {
        $this->reset(['name', 'color', 'assignedUserId', 'active', 'boundary', 'editingId', 'showForm']);
        $this->color = '#3b82f6';
    }

    public function render()
    {
        return view('livewire.settings.territory-manager');
    }
}
