<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Left column: Contact info + Status + Convert --}}
    <div class="space-y-5 lg:col-span-2">

        {{-- Converted banner --}}
        @if($lead->isConverted())
        <div class="flex items-center gap-3 rounded-xl border border-purple-200 bg-purple-50 px-4 py-3">
            <svg class="h-5 w-5 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-purple-800">Lead Converted</p>
                @if($lead->convertedWorkOrder)
                <p class="text-xs text-purple-600">
                    Work order
                    <a href="{{ route('work-orders.show', $lead->convertedWorkOrder) }}" wire:navigate
                       class="font-medium underline">{{ $lead->convertedWorkOrder->ro_number }}</a>
                    created for {{ $lead->convertedWorkOrder->customer?->first_name }} {{ $lead->convertedWorkOrder->customer?->last_name }}.
                </p>
                @endif
            </div>
        </div>
        @endif

        {{-- Contact card --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Contact Information</h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @if($lead->phone)
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Phone</p>
                        <a href="tel:{{ $lead->phone }}" class="text-sm text-slate-700 hover:text-blue-600">{{ $lead->phone }}</a>
                    </div>
                    @endif
                    @if($lead->email)
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Email</p>
                        <a href="mailto:{{ $lead->email }}" class="text-sm text-slate-700 hover:text-blue-600">{{ $lead->email }}</a>
                    </div>
                    @endif
                    @if($lead->locationLabel())
                    <div class="sm:col-span-2">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Address</p>
                        <p class="text-sm text-slate-700">
                            {{ $lead->address }}@if($lead->address && $lead->city),@endif
                            {{ trim("{$lead->city}, {$lead->state} {$lead->zip}") }}
                        </p>
                        @if($lead->lat && $lead->lng)
                        <a href="https://maps.google.com/?q={{ $lead->lat }},{{ $lead->lng }}"
                           target="_blank"
                           class="mt-0.5 inline-flex items-center gap-1 text-xs text-blue-500 hover:underline">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            Open in Maps
                        </a>
                        @endif
                    </div>
                    @endif
                </div>

                @if($lead->vehicle_year || $lead->vehicle_make || $lead->vehicle_model)
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Vehicle</p>
                    <p class="text-sm text-slate-700">{{ trim("{$lead->vehicle_year} {$lead->vehicle_make} {$lead->vehicle_model}") }}</p>
                </div>
                @endif

                @if($lead->damage_level)
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Damage Level</p>
                    <p class="text-sm text-slate-700">{{ $lead->damageLevelLabel() }}</p>
                </div>
                @endif

                @if($lead->job_type_interest)
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Interest</p>
                    <p class="text-sm text-slate-700 capitalize">{{ str_replace('_', ' ', $lead->job_type_interest) }}</p>
                </div>
                @endif

                @if($lead->notes)
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Notes</p>
                    <p class="text-sm text-slate-600 whitespace-pre-line">{{ $lead->notes }}</p>
                </div>
                @endif

                @if($lead->stormEvent)
                <div class="border-t border-slate-100 pt-3 flex items-center gap-2">
                    <svg class="h-4 w-4 text-sky-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" />
                    </svg>
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Storm / Event</p>
                        <a href="{{ route('storm-events.show', $lead->stormEvent) }}" wire:navigate
                           class="text-sm font-medium text-sky-600 hover:underline">{{ $lead->stormEvent->name }}</a>
                        @if($lead->stormEvent->locationLabel())
                            <span class="text-xs text-slate-400 ml-1">{{ $lead->stormEvent->locationLabel() }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Status update --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-slate-700">Status</h3>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $lead->status->badgeClasses() }}">
                        {{ $lead->status->label() }}
                    </span>
                </div>
                @if(!$lead->isConverted() && !$showStatusForm)
                <button wire:click="openStatusForm"
                        class="text-xs font-medium text-blue-600 hover:underline">
                    Update
                </button>
                @endif
            </div>
            <div class="p-5">

                @if($showStatusForm)
                <div class="mb-4 space-y-3 rounded-lg border border-blue-200 bg-blue-50 p-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">New Status</label>
                        <select wire:model="newStatus"
                                class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($this->statuses as $s)
                                <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Note (optional)</label>
                        <input wire:model="statusNote" type="text" placeholder="e.g., Left voicemail"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="updateStatus"
                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                            Save
                        </button>
                        <button wire:click="cancelStatusForm"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
                @endif

                {{-- Status timeline --}}
                <div class="space-y-2">
                    @forelse($this->statusLogs as $log)
                    <div class="flex items-start gap-2.5">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $log->statusDotClasses() }}"></span>
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $log->statusLabel() }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $log->created_at->format('M j, Y g:ia') }}
                                @if($log->changedBy) · {{ $log->changedBy->name }} @endif
                            </p>
                            @if($log->notes)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $log->notes }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic">No status history.</p>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- Convert to Work Order --}}
        @if(!$lead->isConverted() && auth()->user()->canCreateWorkOrders())
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-green-800">Ready to convert?</p>
                <p class="text-xs text-green-700 mt-0.5">Creates a customer record and opens the Work Order wizard pre-filled.</p>
            </div>
            <button wire:click="convertToWorkOrder"
                    wire:confirm="Convert this lead to a work order? The lead will be marked Converted."
                    wire:loading.attr="disabled"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-70 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Convert to WO
            </button>
        </div>
        @endif

    </div>

    {{-- Right column: Meta + Follow-ups --}}
    <div class="space-y-5">

        {{-- Meta card --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5 space-y-3">
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Source</p>
                <p class="text-sm text-slate-700">{{ $lead->source->label() }}</p>
            </div>
            @if($lead->assignedUser)
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Assigned Rep</p>
                <p class="text-sm text-slate-700">{{ $lead->assignedUser->name }}</p>
            </div>
            @endif
            @if($lead->territory)
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Territory</p>
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $lead->territory->color }}"></span>
                    <p class="text-sm text-slate-700">{{ $lead->territory->name }}</p>
                </div>
            </div>
            @endif
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Created</p>
                <p class="text-sm text-slate-700">{{ $lead->created_at->format('M j, Y') }}</p>
                @if($lead->creator)
                <p class="text-xs text-slate-400">by {{ $lead->creator->name }}</p>
                @endif
            </div>
        </div>

        {{-- Follow-ups --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
            <livewire:leads.lead-follow-ups :lead="$lead" :key="'followups-'.$lead->id" />
        </div>

    </div>

</div>
