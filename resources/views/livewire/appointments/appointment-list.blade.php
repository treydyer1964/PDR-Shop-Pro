<div x-on:open-create-appointment.window="$wire.openCreate()">

    {{-- Create form --}}
    @if($creating)
    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-4 space-y-3">
        <p class="text-sm font-semibold text-slate-700">New Appointment</p>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="block text-xs font-medium text-slate-600">Type *</label>
                <select wire:model="newTypeId"
                        class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="0">Select type…</option>
                    @foreach($this->types as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                @error('newTypeId') <p class="mt-1 text-xs text-red-600">Please select a type.</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Date &amp; Time *</label>
                <input wire:model="newScheduledAt" type="datetime-local"
                       class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                @error('newScheduledAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Work Order link (optional) --}}
        <div>
            <label class="block text-xs font-medium text-slate-600">Link to Work Order <span class="text-slate-400">(optional)</span></label>
            @if($newWorkOrderId && $this->selectedWorkOrder)
                <div class="mt-1 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 px-3 py-2">
                    <span class="text-sm font-medium text-slate-800">
                        {{ $this->selectedWorkOrder->ro_number }}
                        — {{ $this->selectedWorkOrder->customer?->first_name }} {{ $this->selectedWorkOrder->customer?->last_name }}
                    </span>
                    <button wire:click="clearWorkOrder" type="button" class="text-xs text-slate-500 hover:text-red-500">✕</button>
                </div>
            @else
                <div class="relative mt-1">
                    <input wire:model.live.debounce.200ms="woSearch" type="search"
                           placeholder="Search by RO# or customer name…"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @if(strlen($woSearch) >= 2)
                        <div class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg divide-y divide-slate-100 max-h-48 overflow-y-auto">
                            @forelse($this->woResults as $wo)
                                <button wire:click="selectWorkOrder({{ $wo->id }})" type="button"
                                        class="w-full px-4 py-2.5 text-left hover:bg-slate-50 transition-colors">
                                    <p class="text-sm font-medium text-slate-800">{{ $wo->ro_number }} — {{ $wo->customer?->first_name }} {{ $wo->customer?->last_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $wo->vehicle?->year }} {{ $wo->vehicle?->make }} {{ $wo->vehicle?->model }}</p>
                                </button>
                            @empty
                                <p class="px-4 py-3 text-sm text-slate-500">No work orders found.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600">Notes</label>
            <input wire:model="newNotes" type="text" placeholder="Optional notes…"
                   class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="saveAppointment"
                    class="rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                Save
            </button>
            <button wire:click="cancelCreate"
                    class="rounded-lg border border-slate-200 bg-white px-4 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                Cancel
            </button>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <select wire:model.live="filterStatus"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="upcoming">Upcoming</option>
            <option value="all">All</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled / No-Show</option>
        </select>

        <select wire:model.live="filterTypeId"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Types</option>
            @foreach($this->types as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
            @endforeach
        </select>

        <input wire:model.live="filterDate" type="date"
               class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />

        @if($filterDate)
            <button wire:click="$set('filterDate', '')" class="text-xs text-slate-500 hover:text-slate-700">Clear date</button>
        @endif
    </div>

    {{-- Results --}}
    @if($this->appointments->isEmpty())
        <div class="mt-4 rounded-xl border border-dashed border-slate-300 py-12 text-center text-sm text-slate-400">
            No appointments found.
        </div>
    @else
        <div class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white shadow-sm">
            @foreach($this->appointments as $appt)
                <div @class(['flex items-start gap-4 px-5 py-4', 'opacity-60' => $appt->status->isTerminal()])>

                    {{-- Date block --}}
                    <div class="w-14 shrink-0 text-center">
                        <p class="text-xs font-semibold uppercase text-slate-400">{{ $appt->scheduled_at->format('M') }}</p>
                        <p class="text-2xl font-bold leading-none text-slate-800">{{ $appt->scheduled_at->format('j') }}</p>
                        <p class="text-xs text-slate-400">{{ $appt->scheduled_at->format('g:i A') }}</p>
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $appt->type?->badgeClasses() ?? 'bg-blue-100 text-blue-700' }}">
                                {{ $appt->type?->name ?? 'Appointment' }}
                            </span>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $appt->status->badgeClasses() }}">
                                {{ $appt->status->label() }}
                            </span>
                        </div>
                        @if($appt->workOrder)
                            <a href="{{ route('work-orders.show', $appt->workOrder) }}" wire:navigate
                               class="mt-1 block text-sm font-semibold text-blue-600 hover:underline">
                                {{ $appt->workOrder->ro_number }}
                                — {{ $appt->workOrder->customer?->first_name }} {{ $appt->workOrder->customer?->last_name }}
                            </a>
                            <p class="text-xs text-slate-500">
                                {{ $appt->workOrder->vehicle?->year }}
                                {{ $appt->workOrder->vehicle?->make }}
                                {{ $appt->workOrder->vehicle?->model }}
                            </p>
                        @elseif($appt->lead)
                            <a href="{{ route('leads.show', $appt->lead) }}" wire:navigate
                               class="mt-1 block text-sm font-semibold text-blue-600 hover:underline">
                                {{ $appt->lead->hasName() ? $appt->lead->fullName() : 'No name yet' }}
                            </a>
                            <p class="text-xs text-slate-500">{{ $appt->lead->address ?? 'Pin follow-up' }}</p>
                        @else
                            <p class="mt-1 text-sm text-slate-400 italic">Standalone appointment</p>
                        @endif
                        @if($appt->notes)
                            <p class="mt-0.5 text-xs text-slate-400">{{ $appt->notes }}</p>
                        @endif
                    </div>

                    {{-- Quick status --}}
                    @if(! $appt->status->isTerminal())
                        <div x-data="{ open: false }" class="relative shrink-0">
                            <button @click="open = !open"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                Update ▾
                            </button>
                            <div x-show="open" @click.away="open = false"
                                 class="absolute right-0 z-10 mt-1 w-36 rounded-lg border border-slate-200 bg-white shadow-lg divide-y divide-slate-100 text-xs">
                                <button @click="open=false" wire:click="updateStatus({{ $appt->id }}, 'completed')"
                                        class="w-full px-3 py-2 text-left text-green-700 hover:bg-green-50">Completed</button>
                                <button @click="open=false" wire:click="updateStatus({{ $appt->id }}, 'no_show')"
                                        class="w-full px-3 py-2 text-left text-red-600 hover:bg-red-50">No-Show</button>
                                <button @click="open=false" wire:click="updateStatus({{ $appt->id }}, 'cancelled')"
                                        class="w-full px-3 py-2 text-left text-slate-600 hover:bg-slate-50">Cancel</button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>
