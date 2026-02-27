{{-- Parts sub-task: flag toggle + ordered/received dates --}}
<div class="px-5 py-3">
    <div class="flex items-center gap-3">
        {{-- Toggle flag --}}
        <button wire:click="toggleSubTask('{{ $needsFlag }}')"
                @class([
                    'flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition-colors',
                    'border-blue-500 bg-blue-500 text-white' => $needsValue,
                    'border-slate-300 bg-white hover:border-blue-400' => !$needsValue,
                ])>
            @if($needsValue)
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            @endif
        </button>
        <span @class(['text-sm flex-1 font-medium', 'text-slate-800' => $needsValue, 'text-slate-400' => !$needsValue])>
            {{ $label }}
        </span>
    </div>

    @if($needsValue)
        <div class="mt-2 ml-8 grid grid-cols-2 gap-3">
            {{-- Ordered --}}
            <div>
                <p class="text-xs text-slate-500 mb-1">Ordered</p>
                @if($editingSubTask === $orderedField)
                    <div class="flex items-center gap-1">
                        <input wire:model="subTaskDate" type="date"
                               class="rounded border-slate-300 text-xs py-0.5 focus:border-blue-500 focus:ring-blue-500" />
                        <button wire:click="updateSubTaskDate('{{ $orderedField }}')"
                                class="text-xs text-blue-600 font-medium">Set</button>
                        <button wire:click="$set('editingSubTask', null)"
                                class="text-xs text-slate-400">✕</button>
                    </div>
                @elseif($orderedAt)
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-medium text-slate-700">{{ $orderedAt->format('M j, Y') }}</span>
                        <button wire:click="startEditSubTaskDate('{{ $orderedField }}', '{{ $orderedAt->toDateString() }}')"
                                class="text-slate-300 hover:text-blue-500 transition-colors" title="Edit date">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                            </svg>
                        </button>
                        <button wire:click="clearSubTaskDate('{{ $orderedField }}')"
                                class="text-slate-300 hover:text-slate-500 transition-colors" title="Clear date">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @else
                    <button wire:click="startEditSubTaskDate('{{ $orderedField }}')"
                            class="text-xs text-blue-600 hover:text-blue-700">Set date</button>
                @endif
            </div>

            {{-- Received --}}
            <div>
                <p class="text-xs text-slate-500 mb-1">Received</p>
                @if($editingSubTask === $receivedField)
                    <div class="flex items-center gap-1">
                        <input wire:model="subTaskDate" type="date"
                               class="rounded border-slate-300 text-xs py-0.5 focus:border-blue-500 focus:ring-blue-500" />
                        <button wire:click="updateSubTaskDate('{{ $receivedField }}')"
                                class="text-xs text-blue-600 font-medium">Set</button>
                        <button wire:click="$set('editingSubTask', null)"
                                class="text-xs text-slate-400">✕</button>
                    </div>
                @elseif($receivedAt)
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-medium text-slate-700">{{ $receivedAt->format('M j, Y') }}</span>
                        <button wire:click="startEditSubTaskDate('{{ $receivedField }}', '{{ $receivedAt->toDateString() }}')"
                                class="text-slate-300 hover:text-blue-500 transition-colors" title="Edit date">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                            </svg>
                        </button>
                        <button wire:click="clearSubTaskDate('{{ $receivedField }}')"
                                class="text-slate-300 hover:text-slate-500 transition-colors" title="Clear date">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @else
                    <button wire:click="startEditSubTaskDate('{{ $receivedField }}')"
                            class="text-xs text-blue-600 hover:text-blue-700">Set date</button>
                @endif
            </div>
        </div>
    @endif
</div>
