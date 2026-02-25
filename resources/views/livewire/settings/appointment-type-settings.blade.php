<div class="space-y-4">

    {{-- Existing types --}}
    <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white shadow-sm">
        @forelse($this->types as $type)
            <div @class(['flex items-center gap-3 px-5 py-3', 'opacity-50' => ! $type->active])>
                <span class="inline-flex h-2.5 w-2.5 rounded-full shrink-0 {{ str_replace('text-', 'bg-', explode(' ', $type->badgeClasses())[1]) }}"></span>

                @if($editingId === $type->id)
                    <input wire:model="editName" type="text" autofocus
                           class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('editName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <button wire:click="saveEdit" class="text-xs font-medium text-blue-600 hover:text-blue-700">Save</button>
                    <button wire:click="cancelEdit" class="text-xs text-slate-500 hover:text-slate-700">Cancel</button>
                @else
                    <span class="flex-1 text-sm text-slate-800">{{ $type->name }}</span>
                    <button wire:click="startEdit({{ $type->id }})"
                            class="text-xs text-slate-400 hover:text-slate-600">Rename</button>
                    <button wire:click="toggleActive({{ $type->id }})"
                            class="text-xs text-slate-400 hover:text-slate-600">
                        {{ $type->active ? 'Deactivate' : 'Activate' }}
                    </button>
                @endif
            </div>
        @empty
            <p class="px-5 py-4 text-sm text-slate-400">No appointment types yet.</p>
        @endforelse
    </div>

    {{-- Add new --}}
    <div class="flex gap-2">
        <input wire:model="newName" wire:keydown.enter="addType" type="text"
               placeholder="New appointment type…"
               class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        <button wire:click="addType"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
            Add
        </button>
    </div>
    @error('newName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

</div>
