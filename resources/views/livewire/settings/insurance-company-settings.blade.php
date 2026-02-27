<div class="space-y-4">

    {{-- Add form --}}
    @if($showAddForm)
        <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 space-y-3">
            <p class="text-sm font-medium text-slate-700">Add Insurance Company</p>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Company Name *</label>
                    <input wire:model="addName" type="text" placeholder="e.g. USAA"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('addName') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Short Name</label>
                    <input wire:model="addShortName" type="text" placeholder="e.g. USAA"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Phone</label>
                    <input wire:model="addPhone" type="text" placeholder="e.g. 1-800-555-1234"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button wire:click="addCompany"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    Add Company
                </button>
                <button wire:click="$set('showAddForm', false)"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    @else
        <button wire:click="$set('showAddForm', true)"
                class="rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:border-blue-400 hover:text-blue-600 transition-colors">
            + Add Insurance Company
        </button>
    @endif

    {{-- Companies list --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm divide-y divide-slate-100">
        @forelse($this->companies as $co)
            <div class="px-5 py-3">
                @if($editingId === $co->id)
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Name *</label>
                            <input wire:model="editName" type="text"
                                   class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('editName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Short Name</label>
                            <input wire:model="editShortName" type="text"
                                   class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Phone</label>
                            <input wire:model="editPhone" type="text"
                                   class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="saveEdit"
                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                            Save
                        </button>
                        <button wire:click="cancelEdit"
                                class="text-xs text-slate-400 hover:text-slate-600">Cancel</button>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p @class(['text-sm font-medium truncate', 'text-slate-800' => $co->is_active, 'text-slate-400 line-through' => !$co->is_active])>
                                {{ $co->name }}
                            </p>
                            @if($co->phone)
                                <p class="text-xs text-slate-400">{{ $co->phone }}</p>
                            @endif
                        </div>

                        <button wire:click="startEdit({{ $co->id }})"
                                class="shrink-0 text-slate-400 hover:text-blue-500 transition-colors" title="Edit">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                            </svg>
                        </button>

                        <button wire:click="toggleActive({{ $co->id }})"
                                class="shrink-0 text-xs font-medium {{ $co->is_active ? 'text-green-600 hover:text-slate-400' : 'text-slate-300 hover:text-green-500' }} transition-colors"
                                title="{{ $co->is_active ? 'Deactivate' : 'Activate' }}">
                            {{ $co->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <p class="px-5 py-4 text-sm text-slate-400">No insurance companies yet.</p>
        @endforelse
    </div>

</div>
