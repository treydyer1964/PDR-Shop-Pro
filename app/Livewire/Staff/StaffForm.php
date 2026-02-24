<?php

namespace App\Livewire\Staff;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class StaffForm extends Component
{
    public ?User $staff = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|min:8|max:255')]
    public string $password = '';

    #[Validate('nullable|decimal:0,4|min:0|max:100')]
    public string $commission_rate = '';

    #[Validate('nullable|decimal:0,4|min:0|max:100')]
    public string $sales_manager_override_rate = '';

    #[Validate('nullable|decimal:0,2|min:0')]
    public string $per_car_bonus = '';

    public bool $subject_to_manager_override = false;
    public bool $active = true;

    public array $selectedRoles = [];

    public function mount(?User $staff = null): void
    {
        if ($staff?->exists) {
            $this->staff                        = $staff;
            $this->name                         = $staff->name;
            $this->email                        = $staff->email;
            $this->phone                        = $staff->phone ?? '';
            $this->commission_rate              = $staff->commission_rate ?? '';
            $this->sales_manager_override_rate  = $staff->sales_manager_override_rate ?? '';
            $this->per_car_bonus                = $staff->per_car_bonus ?? '';
            $this->subject_to_manager_override  = (bool) $staff->subject_to_manager_override;
            $this->active                       = (bool) $staff->active;
            $this->selectedRoles                = $staff->roles->pluck('name')->toArray();
        }
    }

    #[Computed]
    public function allRoles(): array
    {
        return Role::cases();
    }

    public function save(): void
    {
        $this->validate();

        $tenantId = auth()->user()->tenant_id;

        // Validate email uniqueness separately (exclude self on edit)
        $uniqueRule = $this->staff?->exists
            ? 'unique:users,email,' . $this->staff->id
            : 'unique:users,email';

        $this->validateOnly('email', ['email' => ['required', 'email', 'max:255', $uniqueRule]]);

        $data = [
            'tenant_id'                    => $tenantId,
            'name'                         => $this->name,
            'email'                        => $this->email,
            'phone'                        => $this->phone ?: null,
            'commission_rate'              => $this->commission_rate !== '' ? $this->commission_rate : null,
            'sales_manager_override_rate'  => $this->sales_manager_override_rate !== '' ? $this->sales_manager_override_rate : null,
            'per_car_bonus'               => $this->per_car_bonus !== '' ? $this->per_car_bonus : null,
            'subject_to_manager_override'  => $this->subject_to_manager_override,
            'active'                       => $this->active,
        ];

        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->staff?->exists) {
            $this->staff->update($data);
            $this->staff->roles()->sync($this->selectedRoles);
            session()->flash('success', 'Staff member updated.');
            $this->redirect(route('staff.show', $this->staff), navigate: true);
        } else {
            if ($this->password === '') {
                $this->addError('password', 'Password is required for new staff members.');
                return;
            }
            $user = User::create($data);
            $user->roles()->sync($this->selectedRoles);
            session()->flash('success', 'Staff member created.');
            $this->redirect(route('staff.show', $user), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.staff.staff-form');
    }
}
