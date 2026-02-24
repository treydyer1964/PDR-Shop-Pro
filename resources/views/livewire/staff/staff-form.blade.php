<div class="mx-auto max-w-2xl space-y-6">

    @if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 ring-1 ring-green-200">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">

        {{-- Basic Info --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Basic Information</h3>
            </div>
            <div class="p-5 space-y-4">

                <div>
                    <label class="block text-sm font-medium text-slate-700">Full Name *</label>
                    <input wire:model="name" type="text" autocomplete="off"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email *</label>
                        <input wire:model="email" type="email" autocomplete="off"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Phone</label>
                        <input wire:model="phone" type="tel" inputmode="tel"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">
                        {{ $staff?->exists ? 'New Password' : 'Password *' }}
                    </label>
                    <input wire:model="password" type="password" autocomplete="new-password"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @if($staff?->exists)
                        <p class="mt-1 text-xs text-slate-400">Leave blank to keep current password.</p>
                    @endif
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Roles --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Roles</h3>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach($this->allRoles as $role)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 hover:bg-slate-50">
                            <input type="checkbox"
                                   wire:model="selectedRoles"
                                   value="{{ $role->value }}"
                                   class="rounded border-slate-300 text-blue-600" />
                            <span class="text-sm text-slate-700">{{ $role->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Commission Settings --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Commission Settings</h3>
            </div>
            <div class="p-5 space-y-4">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Commission Rate (%)</label>
                        <div class="relative mt-1">
                            <input wire:model="commission_rate" type="number" step="0.01" min="0" max="100" placeholder="0.00"
                                   class="w-full rounded-lg border-slate-300 pr-7 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400 text-sm">%</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Used for PDR Techs and Sales Advisors (% of Net).</p>
                        @error('commission_rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Manager Override Rate (%)</label>
                        <div class="relative mt-1">
                            <input wire:model="sales_manager_override_rate" type="number" step="0.01" min="0" max="100" placeholder="0.00"
                                   class="w-full rounded-lg border-slate-300 pr-7 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400 text-sm">%</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Only for Sales Managers — override % on advisor jobs.</p>
                        @error('sales_manager_override_rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Per-Car Bonus ($)</label>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">$</span>
                        <input wire:model="per_car_bonus" type="number" step="0.01" min="0" placeholder="100.00"
                               class="w-full rounded-lg border-slate-300 pl-7 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Flat bonus per vehicle sold — for Sales Advisors only. Leave blank for no bonus.</p>
                    @error('per_car_bonus') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model="subject_to_manager_override" type="checkbox"
                           class="rounded border-slate-300 text-blue-600" />
                    <span class="text-sm text-slate-700">Subject to Sales Manager override</span>
                    <span class="text-xs text-slate-400">(check for Sales Advisors)</span>
                </label>

            </div>
        </div>

        {{-- Status --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="p-5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model="active" type="checkbox"
                           class="rounded border-slate-300 text-blue-600" />
                    <span class="text-sm font-medium text-slate-700">Active</span>
                    <span class="text-xs text-slate-400">(inactive staff won't appear in assignment dropdowns)</span>
                </label>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ $staff?->exists ? route('staff.show', $staff) : route('staff.index') }}" wire:navigate
               class="text-sm text-slate-500 hover:text-slate-700 font-medium">← Cancel</a>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-70 transition-colors">
                <span wire:loading wire:target="save">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </span>
                {{ $staff?->exists ? 'Save Changes' : 'Create Staff Member' }}
            </button>
        </div>

    </form>
</div>
