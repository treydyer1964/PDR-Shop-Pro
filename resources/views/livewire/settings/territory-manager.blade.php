<div>
    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800">Territories</h2>
        @if(!$showForm)
        <button wire:click="openCreate"
                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New Territory
        </button>
        @endif
    </div>

    {{-- Territory list --}}
    @if(!$showForm)
        @forelse($this->territories as $territory)
            <div class="mb-3 flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="h-4 w-4 shrink-0 rounded-full border border-white shadow-sm"
                          style="background-color: {{ $territory->color }}"></span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-slate-800">{{ $territory->name }}</span>
                            @if(!$territory->active)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">Inactive</span>
                            @endif
                            @if($territory->boundary)
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs text-green-700">Boundary set</span>
                            @else
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700">No boundary</span>
                            @endif
                        </div>
                        @if($territory->assignedUser)
                            <p class="text-sm text-slate-400">{{ $territory->assignedUser->name }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="edit({{ $territory->id }})"
                            class="rounded-md px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                        Edit
                    </button>
                    <button wire:click="delete({{ $territory->id }})"
                            wire:confirm="Delete '{{ $territory->name }}'? This cannot be undone."
                            class="rounded-md px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
                <p class="text-slate-400">No territories yet. Create one to assign reps to geographic areas.</p>
            </div>
        @endforelse
    @endif

    {{-- Add / Edit form + map --}}
    @if($showForm)
        <div>
            <h3 class="mb-4 text-sm font-semibold text-slate-700">
                {{ $editingId ? 'Edit Territory' : 'New Territory' }}
            </h3>

            {{-- Form fields --}}
            <div class="mb-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="e.g. North Abilene"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Color</label>
                    <div class="flex items-center gap-2">
                        <input wire:model.live="color" type="color"
                               class="h-9 w-16 cursor-pointer rounded-lg border-slate-300 shadow-sm">
                        <span class="text-sm text-slate-500">{{ $color }}</span>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Assigned Rep</label>
                    <select wire:model="assignedUserId"
                            class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">None</option>
                        @foreach($this->reps as $rep)
                            <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <label class="flex cursor-pointer items-center gap-2">
                        <input wire:model="active" type="checkbox"
                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-slate-700">Active</span>
                    </label>
                </div>
            </div>

            {{-- Map for boundary drawing --}}
            <div class="mb-4">
                <p class="mb-2 text-sm font-medium text-slate-700">Territory Boundary
                    <span class="ml-1 text-xs font-normal text-slate-400">(optional — use the polygon tool to draw on the map)</span>
                </p>

                @if($boundary)
                    <p class="mb-2 text-xs text-green-700">
                        <svg class="inline h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Boundary set — redraw to replace, or delete the drawn shape to clear.
                    </p>
                @endif

                {{-- Data attributes pass PHP values to JS without embedding JS in HTML attributes --}}
                <div
                    wire:key="territory-map-{{ $editingId ?? 'new' }}"
                    x-data="{}"
                    x-init="$nextTick(() => initTerritoryDrawMap($el, $wire))"
                    data-boundary="{{ $boundary }}"
                    data-existing="{{ json_encode($this->existingTerritories) }}"
                    data-editing="{{ $editingId ?? '' }}"
                >
                    <div wire:ignore
                         id="territory-draw-map"
                         class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
                         style="height: 400px;"></div>
                </div>
            </div>

            {{-- Save / Cancel --}}
            <div class="flex items-center gap-3">
                <button wire:click="save"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    {{ $editingId ? 'Update Territory' : 'Save Territory' }}
                </button>
                <button wire:click="cancel"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    @endif
</div>
