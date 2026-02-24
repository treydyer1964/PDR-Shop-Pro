<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('staff.index') }}" wire:navigate
               class="text-slate-400 hover:text-slate-600 transition-colors">Staff</a>
            <span class="text-slate-300">/</span>
            <span>{{ $staff->name }}</span>
        </div>
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('staff.edit', $staff) }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
            </svg>
            Edit
        </a>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 ring-1 ring-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="mx-auto max-w-2xl space-y-4">

        {{-- Profile Card --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Profile</h3>
                @if(! $staff->active)
                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-600">Inactive</span>
                @endif
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Name</span>
                    <span class="text-sm font-medium text-slate-800">{{ $staff->name }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Email</span>
                    <a href="mailto:{{ $staff->email }}" class="text-sm text-blue-600 hover:underline">{{ $staff->email }}</a>
                </div>
                @if($staff->phone)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Phone</span>
                    <a href="tel:{{ $staff->phone }}" class="text-sm text-blue-600 hover:underline">{{ $staff->phone }}</a>
                </div>
                @endif
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Roles</span>
                    <div class="flex flex-wrap gap-1.5 justify-end">
                        @forelse($staff->roles as $role)
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ \App\Enums\Role::from($role->name)->badgeClasses() }}">
                                {{ $role->label() }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-400">No roles assigned</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Commission Card --}}
        @if($staff->commission_rate || $staff->sales_manager_override_rate || $staff->per_car_bonus || $staff->subject_to_manager_override)
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Commission Settings</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @if($staff->commission_rate)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Commission Rate</span>
                    <span class="text-sm font-medium text-slate-800">{{ $staff->commission_rate }}%</span>
                </div>
                @endif
                @if($staff->sales_manager_override_rate)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Manager Override Rate</span>
                    <span class="text-sm font-medium text-slate-800">{{ $staff->sales_manager_override_rate }}%</span>
                </div>
                @endif
                @if($staff->per_car_bonus !== null)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Per-Car Bonus</span>
                    <span class="text-sm font-medium text-slate-800">${{ number_format($staff->per_car_bonus, 2) }}</span>
                </div>
                @endif
                @if($staff->subject_to_manager_override)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-xs text-slate-400 uppercase tracking-wide">Manager Override</span>
                    <span class="text-xs font-medium text-amber-600 bg-amber-50 rounded-full px-2 py-0.5">Subject to override</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Delete --}}
        @if($staff->id !== auth()->id())
        <div class="rounded-xl border border-red-100 bg-white shadow-sm">
            <div class="border-b border-red-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-red-600">Danger Zone</h3>
            </div>
            <div class="px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-700">Delete staff member</p>
                    <p class="text-xs text-slate-400 mt-0.5">Removes this person and all their work order assignments. Cannot be undone.</p>
                </div>
                <form method="POST" action="{{ route('staff.destroy', $staff) }}"
                      onsubmit="return confirm('Delete {{ $staff->name }}? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
