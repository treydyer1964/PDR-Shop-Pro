<div class="space-y-4">

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-slate-700">Locations</h3>
            @if(!$showAddForm && !$editingId)
                <button wire:click="$set('showAddForm', true)"
                        class="text-xs text-blue-600 hover:text-blue-700 font-medium">+ Add Location</button>
            @endif
        </div>

        {{-- Add form --}}
        @if($showAddForm)
            <div class="border-b border-slate-100 bg-slate-50/60 p-5 space-y-4">
                <p class="text-sm font-semibold text-slate-700">New Location</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Name *</label>
                        <input wire:model="addName" type="text" placeholder="e.g. Abilene Main"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('addName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Address</label>
                        <input wire:model="addAddress" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">City</label>
                        <input wire:model="addCity" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">State</label>
                            <input wire:model="addState" type="text" maxlength="2"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">ZIP</label>
                            <input wire:model="addZip" type="text"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
                        <input wire:model="addPhone" type="tel"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="addLocation"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                        Add Location
                    </button>
                    <button wire:click="$set('showAddForm', false)"
                            class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
                </div>
            </div>
        @endif

        {{-- Location list --}}
        <div class="divide-y divide-slate-100">
            @forelse($this->locations as $loc)
                @if($editingId === $loc->id)
                    {{-- Inline edit form --}}
                    <div class="bg-blue-50/40 p-5 space-y-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Name *</label>
                                <input wire:model="editName" type="text"
                                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('editName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Address</label>
                                <input wire:model="editAddress" type="text"
                                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">City</label>
                                <input wire:model="editCity" type="text"
                                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">State</label>
                                    <input wire:model="editState" type="text" maxlength="2"
                                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">ZIP</label>
                                    <input wire:model="editZip" type="text"
                                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
                                <input wire:model="editPhone" type="tel"
                                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="saveEdit"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                                Save
                            </button>
                            <button wire:click="cancelEdit"
                                    class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-slate-800">{{ $loc->name }}</p>
                                @if(!$loc->active)
                                    <span class="text-xs text-slate-400">(inactive)</span>
                                @endif
                            </div>
                            @if($loc->city || $loc->state)
                                <p class="text-xs text-slate-400">
                                    {{ collect([$loc->address, $loc->city, $loc->state, $loc->zip])->filter()->implode(', ') }}
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button wire:click="startEdit({{ $loc->id }})"
                                    class="text-xs text-blue-600 hover:text-blue-700 font-medium">Edit</button>
                            <button wire:click="toggleActive({{ $loc->id }})"
                                    class="text-xs {{ $loc->active ? 'text-slate-400 hover:text-red-500' : 'text-green-600 hover:text-green-700' }} font-medium">
                                {{ $loc->active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </div>
                    </div>
                @endif
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-400">No locations yet.</p>
            @endforelse
        </div>
    </div>

</div>
