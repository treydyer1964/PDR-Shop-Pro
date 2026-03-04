<div>
    {{-- Filters --}}
    <div class="mb-3 flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="search" type="search"
               placeholder="Search name, phone, address…"
               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-64" />

        <select wire:model.live="filterStatus"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Statuses</option>
            @foreach($this->statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>

        @if(!auth()->user()->isFieldStaff())
        <select wire:model.live="filterRep"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Reps</option>
            @foreach($this->reps as $rep)
                <option value="{{ $rep->id }}">{{ $rep->name }}</option>
            @endforeach
        </select>
        @endif

        <select wire:model.live="filterStorm"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Events</option>
            @forelse($this->stormEvents as $storm)
                <option value="{{ $storm->id }}">
                    {{ $storm->name }}{{ $storm->city ? ' — ' . $storm->city . ($storm->state ? ', ' . $storm->state : '') : '' }}
                </option>
            @empty
                <option value="" disabled>No events yet</option>
            @endforelse
        </select>
    </div>

    {{-- Date filter row --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        {{-- Quick presets --}}
        <span class="text-xs font-medium text-slate-400 mr-1">Date:</span>
        @php
            $today     = now()->toDateString();
            $yesterday = now()->subDay()->toDateString();
            $weekStart = now()->startOfWeek()->toDateString();
            $monthStart= now()->startOfMonth()->toDateString();
        @endphp
        <button @click="$wire.set('filterDateFrom', '{{ $today }}'); $wire.set('filterDateTo', '');"
                class="rounded-full border px-3 py-1 text-xs font-medium transition-colors
                    {{ $filterDateFrom === $today && $filterDateTo === '' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            Today
        </button>
        <button @click="$wire.set('filterDateFrom', '{{ $yesterday }}'); $wire.set('filterDateTo', '{{ $yesterday }}');"
                class="rounded-full border px-3 py-1 text-xs font-medium transition-colors
                    {{ $filterDateFrom === $yesterday && $filterDateTo === $yesterday ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            Yesterday
        </button>
        <button @click="$wire.set('filterDateFrom', '{{ $weekStart }}'); $wire.set('filterDateTo', '');"
                class="rounded-full border px-3 py-1 text-xs font-medium transition-colors
                    {{ $filterDateFrom === $weekStart && $filterDateTo === '' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            This Week
        </button>
        <button @click="$wire.set('filterDateFrom', '{{ $monthStart }}'); $wire.set('filterDateTo', '');"
                class="rounded-full border px-3 py-1 text-xs font-medium transition-colors
                    {{ $filterDateFrom === $monthStart && $filterDateTo === '' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            This Month
        </button>

        {{-- Custom range --}}
        <div class="flex items-center gap-1.5 ml-1">
            <input wire:model.live="filterDateFrom" type="date"
                   class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1" />
            <span class="text-xs text-slate-400">–</span>
            <input wire:model.live="filterDateTo" type="date"
                   class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1" />
        </div>

        @if($filterDateFrom || $filterDateTo)
        <button wire:click="clearDateFilter"
                class="text-xs text-slate-400 hover:text-red-500 transition-colors">
            ✕ Clear date
        </button>
        @endif
    </div>

    {{-- Lead cards --}}
    @forelse($this->leads as $lead)
        <a href="{{ route('leads.show', $lead) }}" wire:navigate
           class="mb-3 flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-300 hover:shadow-md transition-all block">

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-slate-800">{{ $lead->fullName() }}</span>
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $lead->status->badgeClasses() }}">
                        {{ $lead->status->label() }}
                    </span>
                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                        {{ $lead->source->label() }}
                    </span>
                </div>

                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-0.5 text-sm text-slate-500">
                    @if($lead->phone)
                        <span>{{ $lead->phone }}</span>
                    @endif
                    @if($lead->locationLabel())
                        <span>{{ $lead->locationLabel() }}</span>
                    @endif
                    @if($lead->vehicle_year || $lead->vehicle_make)
                        <span>{{ trim("{$lead->vehicle_year} {$lead->vehicle_make} {$lead->vehicle_model}") }}</span>
                    @endif
                </div>

                <div class="mt-1.5 flex flex-wrap gap-3 text-xs text-slate-400">
                    @if($lead->stormEvent)
                        <span class="inline-flex items-center gap-1 text-sky-600 font-medium">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" /></svg>
                            {{ $lead->stormEvent->name }}
                        </span>
                    @endif
                    @if($lead->assignedUser)
                        <span>Rep: {{ $lead->assignedUser->name }}</span>
                    @endif
                    @if($lead->pending_follow_ups_count)
                        <span class="text-amber-600 font-medium">{{ $lead->pending_follow_ups_count }} follow-up{{ $lead->pending_follow_ups_count > 1 ? 's' : '' }} pending</span>
                    @endif
                    @if($lead->convertedWorkOrder)
                        <span class="text-purple-600 font-medium">Converted → WO {{ $lead->convertedWorkOrder->ro_number }}</span>
                    @endif
                    <span>Added {{ $lead->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <svg class="mt-1 h-4 w-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </a>
    @empty
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center">
            <p class="text-slate-400">No leads found.</p>
            @if(auth()->user()->canCreateWorkOrders())
                <a href="{{ route('leads.create') }}" wire:navigate
                   class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:underline">
                    Add your first lead
                </a>
            @endif
        </div>
    @endforelse

    <div class="mt-4">
        {{ $this->leads->links() }}
    </div>
</div>
