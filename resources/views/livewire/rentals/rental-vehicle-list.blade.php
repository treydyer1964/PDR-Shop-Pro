<div class="space-y-4">

    {{-- Toolbar --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $this->vehicles->count() }} vehicle{{ $this->vehicles->count() !== 1 ? 's' : '' }} in fleet</p>
        @if(! $showForm)
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                + Add Vehicle
            </button>
        @endif
    </div>

    {{-- Add / Edit form --}}
    @if($showForm)
        <div class="rounded-xl border border-blue-200 bg-blue-50/40 p-5 space-y-4">
            <h3 class="text-sm font-semibold text-slate-700">{{ $editingId ? 'Edit Vehicle' : 'New Fleet Vehicle' }}</h3>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Display Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="e.g. 2022 Chevy Malibu – White"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Internal Daily Cost ($) <span class="text-red-500">*</span></label>
                    <input wire:model="dailyCost" type="number" step="0.01" min="0" placeholder="0.00"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('dailyCost') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">VIN</label>
                    <input wire:model="vin" type="text" maxlength="17" placeholder="17 characters"
                           class="w-full rounded-lg border-slate-300 font-mono text-sm uppercase shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('vin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Year</label>
                    <input wire:model="year" type="number" min="1900" max="2100" placeholder="YYYY"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Make</label>
                    <input wire:model="make" type="text" placeholder="e.g. Chevrolet"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Model</label>
                    <input wire:model="model" type="text" placeholder="e.g. Malibu"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Color</label>
                    <input wire:model="color" type="text" placeholder="e.g. White"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                    <input wire:model="notes" type="text" placeholder="Optional…"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
            </div>

            <div class="flex gap-2">
                <button wire:click="save"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    {{ $editingId ? 'Save Changes' : 'Add Vehicle' }}
                </button>
                <button wire:click="cancel" class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Vehicle list --}}
    @if($this->vehicles->isEmpty() && ! $showForm)
        <div class="rounded-xl border border-dashed border-slate-300 py-12 text-center text-sm text-slate-400">
            No fleet vehicles yet.
        </div>
    @else
        <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white shadow-sm">
            @foreach($this->vehicles as $v)
                <div @class(['flex items-center gap-4 px-5 py-4', 'opacity-50' => ! $v->active])>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-medium text-slate-800">{{ $v->displayName() }}</span>
                            @if(! $v->active)
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">Inactive</span>
                            @endif
                        </div>
                        <div class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-slate-500">
                            @if($v->color) <span>{{ $v->color }}</span> @endif
                            @if($v->vin) <span class="font-mono">{{ $v->vin }}</span> @endif
                            @if($v->notes) <span class="truncate max-w-xs">{{ $v->notes }}</span> @endif
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        <span class="font-semibold text-slate-800">${{ number_format($v->internal_daily_cost, 2) }}</span>
                        <span class="text-xs text-slate-400">/day</span>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button wire:click="openEdit({{ $v->id }})"
                                class="rounded border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            Edit
                        </button>
                        <button wire:click="toggleActive({{ $v->id }})"
                                class="rounded border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            {{ $v->active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
