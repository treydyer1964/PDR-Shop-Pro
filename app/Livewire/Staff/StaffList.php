<?php

namespace App\Livewire\Staff;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class StaffList extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    public function updatedSearch(): void
    {
        // no pagination to reset, but keep pattern consistent
    }

    #[Computed]
    public function staff()
    {
        $tenantId = auth()->user()->tenant_id;

        return User::where('tenant_id', $tenantId)
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name',  'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->with('roles')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.staff.staff-list');
    }
}
