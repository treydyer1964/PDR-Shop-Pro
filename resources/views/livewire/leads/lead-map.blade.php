<div>
    {{-- Status filter strip + map --}}
    <div
        x-data="{ filter: '' }"
        data-leads="{{ json_encode($this->allLeads) }}"
        data-territories="{{ json_encode($this->territories) }}"
        x-init="$nextTick(() => initLeadMap($el, filter))"
        id="lead-map-root"
    >
        {{-- Filter buttons --}}
        <div class="mb-3 flex flex-wrap items-center gap-2">
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

        {{-- Lead count --}}
        <p class="mt-2 text-xs text-slate-400">
            <span x-text="window.leadMapCount ? window.leadMapCount(filter) : ''"></span>
            @if($this->unlocatedLeads->isNotEmpty())
                · {{ $this->unlocatedLeads->count() }} without location (listed below)
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
                            <span class="font-medium text-slate-800">{{ $lead->fullName() }}</span>
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
