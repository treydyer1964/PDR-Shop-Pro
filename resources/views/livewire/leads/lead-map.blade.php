<div>
    @php
        $today         = now()->toDateString();
        $weekStart     = now()->startOfWeek()->toDateString();
        $monthStart    = now()->startOfMonth()->toDateString();
        $yearStart     = now()->startOfYear()->toDateString();
        $lastYearStart = now()->subYear()->startOfYear()->toDateString();
        $lastYearEnd   = now()->subYear()->endOfYear()->toDateString();

        $presetActive = fn(string $from, string $to = '') =>
            $filterDateFrom === $from && $filterDateTo === $to;

        $activeDatePreset = match(true) {
            $presetActive('', '')                       => 'all',
            $presetActive($today, '')                   => 'today',
            $presetActive($weekStart, '')               => 'week',
            $presetActive($monthStart, '')              => 'month',
            $presetActive($yearStart, '')               => 'year',
            $presetActive($lastYearStart, $lastYearEnd) => 'lastyear',
            ($filterDateFrom || $filterDateTo)          => 'custom',
            default                                     => 'all',
        };
    @endphp

    {{-- Compact filter bar: all dropdowns in one row --}}
    <div
        x-data="{ datePreset: @js($activeDatePreset), pinFilter: '' }"
        class="mb-3 flex flex-wrap items-center gap-2"
    >
        {{-- Date dropdown --}}
        <select
            x-model="datePreset"
            @change="if (datePreset !== 'custom') $wire.setDatePreset(datePreset)"
            class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1.5">
            <option value="all">All Dates</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
            <option value="lastyear">Last Year</option>
            <option value="custom">Custom…</option>
        </select>

        {{-- Custom date inputs — revealed inline --}}
        <div x-show="datePreset === 'custom'" x-cloak class="flex items-center gap-1.5">
            <input wire:model.live="filterDateFrom" type="date"
                   class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1" />
            <span class="text-xs text-slate-400">–</span>
            <input wire:model.live="filterDateTo" type="date"
                   class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1" />
            @if($filterDateFrom || $filterDateTo)
            <button wire:click="clearDateFilter" @click="datePreset = 'all'"
                    class="text-xs text-slate-400 hover:text-red-500 transition-colors">✕</button>
            @endif
        </div>

        @if(!auth()->user()->isFieldStaff())
        <select wire:model.live="filterRep"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1.5">
            <option value="">All Reps</option>
            @foreach($this->reps as $rep)
                <option value="{{ $rep->id }}">{{ $rep->name }}</option>
            @endforeach
        </select>
        @endif

        <select wire:model.live="filterStorm"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1.5">
            <option value="">All Events</option>
            @forelse($this->stormEvents as $storm)
                <option value="{{ $storm->id }}">
                    {{ $storm->name }}{{ $storm->city ? ' — ' . $storm->city . ($storm->state ? ', ' . $storm->state : '') : '' }}
                </option>
            @empty
                <option value="" disabled>No events yet</option>
            @endforelse
        </select>

        {{-- Pin Type dropdown --}}
        <select
            @change="pinFilter = $event.target.value; window.leadMapSetFilter && window.leadMapSetFilter(pinFilter)"
            class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1.5">
            <option value="">All Pin Types</option>
            @foreach($this->statuses as $s)
            <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>

        <span class="text-xs text-slate-400" x-text="window.leadMapCount ? window.leadMapCount(pinFilter) : ''"></span>
    </div>

    {{-- Map wrapper — wire:key forces Leaflet reinit on server-side filter change --}}
    <div
        wire:key="lead-map-{{ $this->filterStorm }}-{{ $this->filterRep }}-{{ $this->filterDateFrom }}-{{ $this->filterDateTo }}"
        x-data="{}"
        data-leads="{{ json_encode($this->allLeads) }}"
        data-territories="{{ json_encode($this->territories) }}"
        x-init="$nextTick(() => initLeadMap($el))"
        id="lead-map-root"
    >

        {{-- Map container --}}
        <div wire:ignore
             class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
             style="height: 65vh; min-height: 380px;">
            <div id="lead-map-container" class="h-full w-full"></div>
        </div>

        {{-- Tap hint --}}
        @if(auth()->user()->canCreateWorkOrders())
        <p class="mt-2 text-xs text-blue-500">Tap anywhere on the map to create a new lead at that location</p>
        @endif
        @if($this->unlocatedLeads->isNotEmpty())
        <p class="mt-1 text-xs text-slate-400">{{ $this->unlocatedLeads->count() }} lead(s) without location listed below</p>
        @endif
    </div>

    {{-- Unlocated leads --}}
    @if($this->unlocatedLeads->isNotEmpty())
        <div class="mt-5">
            <p class="mb-2 text-sm font-semibold text-slate-500">No Location</p>
            <div class="space-y-2">
                @foreach($this->unlocatedLeads as $lead)
                    <a href="{{ route('leads.show', $lead) }}" wire:navigate
                       class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm hover:border-blue-300 hover:shadow-md transition-all">
                        <div>
                            <span class="font-medium text-slate-800">
                            {{ $lead->hasName() ? $lead->fullName() : 'No name yet' }}
                        </span>
                            <span class="ml-2 inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $lead->status->badgeClasses() }}">
                                {{ $lead->status->label() }}
                            </span>
                            @if($lead->assignedUser)
                                <span class="ml-2 text-sm text-slate-400">{{ $lead->assignedUser->name }}</span>
                            @endif
                        </div>
                        <svg class="h-4 w-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
