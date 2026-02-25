<div class="space-y-4">

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
        <div class="rounded-xl border border-dashed border-slate-300 py-12 text-center text-sm text-slate-400">
            No appointments found.
        </div>
    @else
        <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white shadow-sm">
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
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $appt->type->badgeClasses() }}">
                                {{ $appt->type->name }}
                            </span>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $appt->status->badgeClasses() }}">
                                {{ $appt->status->label() }}
                            </span>
                        </div>
                        <a href="{{ route('work-orders.show', $appt->workOrder) }}" wire:navigate
                           class="mt-1 block text-sm font-semibold text-blue-600 hover:underline">
                            {{ $appt->workOrder->ro_number }}
                            — {{ $appt->workOrder->customer->first_name }} {{ $appt->workOrder->customer->last_name }}
                        </a>
                        <p class="text-xs text-slate-500">
                            {{ $appt->workOrder->vehicle->year }}
                            {{ $appt->workOrder->vehicle->make }}
                            {{ $appt->workOrder->vehicle->model }}
                        </p>
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
