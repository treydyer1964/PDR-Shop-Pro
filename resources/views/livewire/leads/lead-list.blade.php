<div>
    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
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
