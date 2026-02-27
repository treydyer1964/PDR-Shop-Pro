<div class="space-y-4">

    {{-- Add form --}}
    @if($showAddForm)
        <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 space-y-3">
            <p class="text-sm font-medium text-slate-700">Add Color</p>
            <div class="flex gap-2">
                <input wire:model="addName" type="text" placeholder="e.g. Midnight Blue"
                       class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       wire:keydown.enter="addColor" autofocus />
                <button wire:click="addColor"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    Add
                </button>
                <button wire:click="$set('showAddForm', false)"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
            </div>
            @error('addName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @else
        <button wire:click="$set('showAddForm', true)"
                class="rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:border-blue-400 hover:text-blue-600 transition-colors">
            + Add Color
        </button>
    @endif

    {{-- Colors list --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm divide-y divide-slate-100">
        @forelse($this->colors as $color)
            <div class="flex items-center gap-3 px-5 py-3">
                @if($editingId === $color->id)
                    <input wire:model="editName" type="text"
                           class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit" autofocus />
                    <button wire:click="saveEdit"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700">Save</button>
                    <button wire:click="cancelEdit"
                            class="text-sm text-slate-400 hover:text-slate-600">Cancel</button>
                @else
                    <span @class(['flex-1 text-sm', 'text-slate-800' => $color->active, 'text-slate-400 line-through' => !$color->active])>
                        {{ $color->name }}
                    </span>

                    <button wire:click="startEdit({{ $color->id }})"
                            class="text-xs text-slate-400 hover:text-blue-500 transition-colors" title="Rename">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                        </svg>
                    </button>

                    <button wire:click="toggleActive({{ $color->id }})"
                            class="text-xs {{ $color->active ? 'text-slate-400 hover:text-amber-500' : 'text-slate-300 hover:text-green-500' }} transition-colors"
                            title="{{ $color->active ? 'Deactivate' : 'Activate' }}">
                        @if($color->active)
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        @else
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @endif
                    </button>

                    <button wire:click="deleteColor({{ $color->id }})"
                            wire:confirm="Delete '{{ $color->name }}'? This cannot be undone."
                            class="text-xs text-slate-300 hover:text-red-500 transition-colors" title="Delete">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        @empty
            <p class="px-5 py-4 text-sm text-slate-400">No colors yet.</p>
        @endforelse
    </div>

</div>
