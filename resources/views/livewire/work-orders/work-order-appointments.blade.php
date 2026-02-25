<div class="rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <h3 class="text-sm font-semibold text-slate-700">Appointments</h3>
        @if(! $showForm)
            <button wire:click="openAdd"
                    class="text-xs font-medium text-blue-600 hover:text-blue-700">+ Add</button>
        @endif
    </div>

    {{-- Add / Edit form --}}
    @if($showForm)
        <div class="border-b border-slate-100 bg-slate-50/50 p-4 space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                <select wire:model="typeId"
                        class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">— Select —</option>
                    @foreach($this->types as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                @error('typeId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Date</label>
                    <input wire:model="scheduledDate" type="date"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('scheduledDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Time</label>
                    <input wire:model="scheduledTime" type="time"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                <input wire:model="notes" type="text" placeholder="Optional…"
                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>
            <div class="flex gap-2">
                <button wire:click="save"
                        class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                    {{ $editingId ? 'Save' : 'Schedule' }}
                </button>
                <button wire:click="cancel" class="text-xs text-slate-500 hover:text-slate-700">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Appointment list --}}
    @if($this->appointments->isEmpty() && ! $showForm)
        <p class="px-5 py-4 text-xs text-slate-400">No appointments scheduled.</p>
    @endif

    <div class="divide-y divide-slate-50">
        @foreach($this->appointments as $appt)
            <div @class(['px-4 py-3', 'opacity-60' => $appt->status->isTerminal()])>
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $appt->type->badgeClasses() }}">
                                {{ $appt->type->name }}
                            </span>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $appt->status->badgeClasses() }}">
                                {{ $appt->status->label() }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ $appt->scheduled_at->format('M j, Y') }}
                            <span class="text-slate-400 font-normal">@ {{ $appt->scheduled_at->format('g:i A') }}</span>
                        </p>
                        @if($appt->notes)
                            <p class="mt-0.5 text-xs text-slate-500">{{ $appt->notes }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div x-data="{ open: false }" class="relative shrink-0">
                        <button @click="open = !open"
                                class="rounded p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             class="absolute right-0 z-10 mt-1 w-36 rounded-lg border border-slate-200 bg-white shadow-lg divide-y divide-slate-100 text-xs">
                            @if(! $appt->status->isTerminal())
                                <button @click="open=false" wire:click="updateStatus({{ $appt->id }}, 'completed')"
                                        class="w-full px-3 py-2 text-left text-green-700 hover:bg-green-50">Completed</button>
                                <button @click="open=false" wire:click="updateStatus({{ $appt->id }}, 'no_show')"
                                        class="w-full px-3 py-2 text-left text-red-600 hover:bg-red-50">No-Show</button>
                                <button @click="open=false" wire:click="updateStatus({{ $appt->id }}, 'cancelled')"
                                        class="w-full px-3 py-2 text-left text-slate-600 hover:bg-slate-50">Cancel</button>
                                <button @click="open=false" wire:click="openEdit({{ $appt->id }})"
                                        class="w-full px-3 py-2 text-left text-slate-600 hover:bg-slate-50">Edit</button>
                            @endif
                            <button @click="open=false" wire:click="delete({{ $appt->id }})"
                                    wire:confirm="Delete this appointment?"
                                    class="w-full px-3 py-2 text-left text-red-500 hover:bg-red-50">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
