<div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-700">Expense Categories</h3>
            <p class="text-xs text-slate-400 mt-0.5">System categories can be renamed or hidden. Custom categories can be added and deleted.</p>
        </div>
        @if(!$showAddForm)
            <button wire:click="$set('showAddForm', true)"
                    class="text-xs text-blue-600 hover:text-blue-700 font-medium shrink-0 ml-3">
                + Add Category
            </button>
        @endif
    </div>

    {{-- Add form --}}
    @if($showAddForm)
        <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-4 space-y-3">
            <div class="flex gap-2">
                <div class="flex-1">
                    <input wire:model="addName" type="text" placeholder="Category name…"
                           wire:keydown.enter="addCategory"
                           wire:keydown.escape="$set('showAddForm', false)"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           autofocus />
                    @error('addName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <button wire:click="addCategory"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    Add
                </button>
                <button wire:click="$set('showAddForm', false)"
                        class="text-sm text-slate-500 hover:text-slate-700 px-1">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Category list --}}
    <div class="divide-y divide-slate-100">
        @forelse($this->categories as $cat)
            <div class="flex items-center gap-3 px-5 py-3">

                {{-- System lock icon --}}
                @if($cat->is_system)
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" title="System category">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                @else
                    <div class="w-3.5 shrink-0"></div>
                @endif

                {{-- Name / inline edit --}}
                @if($editingId === $cat->id)
                    <div class="flex flex-1 items-center gap-2">
                        <input wire:model="editName" type="text"
                               wire:keydown.enter="saveEdit"
                               wire:keydown.escape="cancelEdit"
                               class="flex-1 rounded-lg border-slate-300 py-1.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               autofocus />
                        <button wire:click="saveEdit"
                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                            Save
                        </button>
                        <button wire:click="cancelEdit"
                                class="text-xs text-slate-400 hover:text-slate-600">Cancel</button>
                    </div>
                @else
                    <div class="flex-1 min-w-0">
                        <span @class([
                            'text-sm',
                            'text-slate-800 font-medium' => $cat->active,
                            'text-slate-400 line-through' => !$cat->active,
                        ])>{{ $cat->name }}</span>
                        @if($cat->is_system)
                            <span class="ml-1.5 text-xs text-slate-400">system</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 shrink-0">
                        <button wire:click="startEdit({{ $cat->id }})"
                                class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                            Rename
                        </button>
                        <button wire:click="toggleActive({{ $cat->id }})"
                                class="text-xs {{ $cat->active ? 'text-slate-400 hover:text-amber-600' : 'text-green-600 hover:text-green-700' }} font-medium">
                            {{ $cat->active ? 'Hide' : 'Show' }}
                        </button>
                        @if(!$cat->is_system)
                            <button wire:click="deleteCategory({{ $cat->id }})"
                                    wire:confirm="Delete this category?"
                                    class="text-xs text-red-400 hover:text-red-600 font-medium">
                                Delete
                            </button>
                        @endif
                    </div>
                @endif
            </div>
            @error('delete_' . $cat->id)
                <p class="px-5 pb-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        @empty
            <p class="px-5 py-8 text-center text-sm text-slate-400">No expense categories.</p>
        @endforelse
    </div>

</div>
