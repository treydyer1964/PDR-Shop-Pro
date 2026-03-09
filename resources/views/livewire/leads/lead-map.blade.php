<div>
    @php
        $today         = now()->toDateString();
        $weekStart     = now()->startOfWeek()->toDateString();
        $monthStart    = now()->startOfMonth()->toDateString();
        $yearStart     = now()->startOfYear()->toDateString();
        $lastYearStart = now()->subYear()->startOfYear()->toDateString();
        $lastYearEnd   = now()->subYear()->endOfYear()->toDateString();

        $presetActive  = fn(string $from, string $to = '') =>
            $filterDateFrom === $from && $filterDateTo === $to;
        $activeClass   = 'bg-slate-900 text-white border-slate-900';
        $inactiveClass = 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50';

        // Is a custom date active (no preset matches)?
        $isCustomDate = ($filterDateFrom || $filterDateTo)
            && ! $presetActive($today, '')
            && ! $presetActive($weekStart, '')
            && ! $presetActive($monthStart, '')
            && ! $presetActive($yearStart, '')
            && ! $presetActive($lastYearStart, $lastYearEnd);
    @endphp

    {{-- Server-side filters (trigger Livewire re-render + Leaflet reinit via wire:key) --}}
    <div class="mb-3 space-y-2">

        {{-- Row 1: Date Range presets --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-slate-400">Date:</span>

            <button wire:click="setDatePreset('all')"
                    class="rounded-full border px-3 py-1 text-xs font-medium transition-colors {{ $presetActive('', '') ? $activeClass : $inactiveClass }}">
                All
            </button>
            <button wire:click="setDatePreset('today')"
                    class="rounded-full border px-3 py-1 text-xs font-medium transition-colors {{ $presetActive($today, '') ? $activeClass : $inactiveClass }}">
                Today
            </button>
            <button wire:click="setDatePreset('week')"
                    class="rounded-full border px-3 py-1 text-xs font-medium transition-colors {{ $presetActive($weekStart, '') ? $activeClass : $inactiveClass }}">
                This Week
            </button>
            <button wire:click="setDatePreset('month')"
                    class="rounded-full border px-3 py-1 text-xs font-medium transition-colors {{ $presetActive($monthStart, '') ? $activeClass : $inactiveClass }}">
                This Month
            </button>
            <button wire:click="setDatePreset('year')"
                    class="rounded-full border px-3 py-1 text-xs font-medium transition-colors {{ $presetActive($yearStart, '') ? $activeClass : $inactiveClass }}">
                This Year
            </button>
            <button wire:click="setDatePreset('lastyear')"
                    class="rounded-full border px-3 py-1 text-xs font-medium transition-colors {{ $presetActive($lastYearStart, $lastYearEnd) ? $activeClass : $inactiveClass }}">
                Last Year
            </button>

            {{-- Custom: always visible small inputs --}}
            <div x-data="{ open: @js($isCustomDate) }" class="flex items-center gap-1.5">
                <button @click="open = !open"
                        class="rounded-full border px-3 py-1 text-xs font-medium transition-colors {{ $isCustomDate ? $activeClass : $inactiveClass }}">
                    Custom
                </button>
                <div x-show="open" x-cloak class="flex items-center gap-1.5">
                    <input wire:model.live="filterDateFrom" type="date"
                           class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1" />
                    <span class="text-xs text-slate-400">–</span>
                    <input wire:model.live="filterDateTo" type="date"
                           class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1" />
                    @if($filterDateFrom || $filterDateTo)
                    <button wire:click="clearDateFilter" class="text-xs text-slate-400 hover:text-red-500 transition-colors">✕</button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Row 2: Rep + Storm selects --}}
        <div class="flex flex-wrap items-center gap-3">
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
    </div>

    {{-- Pin Type filter strip + map (wire:key forces Leaflet reinit when server-side filters change) --}}
    <div
        wire:key="lead-map-{{ $this->filterStorm }}-{{ $this->filterRep }}-{{ $this->filterDateFrom }}-{{ $this->filterDateTo }}"
        x-data="{ filter: '' }"
        data-leads="{{ json_encode($this->allLeads) }}"
        data-territories="{{ json_encode($this->territories) }}"
        x-init="$nextTick(() => initLeadMap($el, filter))"
        id="lead-map-root"
    >
        {{-- Pin Type filter buttons --}}
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-slate-400">Pin Type:</span>
            <button
                @click="filter = ''; window.leadMapSetFilter('')"
                :class="filter === '' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 hover:bg-slate-50 border-slate-200'"
                class="rounded-full border px-3 py-1 text-xs font-medium transition-colors">
                All
            </button>
            @foreach($this->statuses as $s)
            <button
                @click="filter = '{{ $s->value }}'; window.leadMapSetFilter('{{ $s->value }}')"
                :class="filter === '{{ $s->value }}' ? 'ring-2 ring-offset-1 ring-slate-400 opacity-100' : 'opacity-70 hover:opacity-100'"
                class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium {{ $s->badgeClasses() }} transition-all">
                {{ $s->label() }}
            </button>
            @endforeach
        </div>

        {{-- Map container --}}
        <div wire:ignore
             class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
             style="height: 65vh; min-height: 380px;">
            <div id="lead-map-container" class="h-full w-full"></div>
        </div>

        {{-- Lead count + tap hint --}}
        <p class="mt-2 text-xs text-slate-400">
            <span x-text="window.leadMapCount ? window.leadMapCount(filter) : ''"></span>
            @if($this->unlocatedLeads->isNotEmpty())
                · {{ $this->unlocatedLeads->count() }} without location (listed below)
            @endif
            @if(auth()->user()->canCreateWorkOrders())
                <span class="ml-2 text-slate-300">·</span>
                <span class="ml-2 text-blue-500">Tap anywhere on the map to create a new lead at that location</span>
            @endif
        </p>
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
