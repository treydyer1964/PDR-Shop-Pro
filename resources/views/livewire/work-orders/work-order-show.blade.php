<div class="space-y-6" x-data="{ confirmAdvance: false, confirmRevert: false }">

    {{-- Flash message --}}
    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Header card ───────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-xl font-bold text-slate-800">{{ $workOrder->ro_number }}</span>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $workOrder->job_type->badgeClasses() }}">
                            {{ $workOrder->job_type->label() }}
                        </span>
                        @if($workOrder->kicked)
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-700">Kicked</span>
                        @elseif($workOrder->on_hold)
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                                </svg>
                                On Hold
                            </span>
                        @else
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $workOrder->status->badgeClasses() }}">
                                {{ $workOrder->status->label() }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $workOrder->daysInShop() }} days in shop · Created {{ $workOrder->created_at->format('M j, Y') }}
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-wrap">
                    @if($workOrder->kicked)
                        {{-- No actions for kicked --}}
                    @elseif($workOrder->on_hold)
                        <button wire:click="releaseHold"
                                class="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-600 transition-colors">
                            Release Hold
                        </button>
                    @else
                        {{-- Log supplement (only while waiting on insurance) --}}
                        @if($workOrder->status === \App\Enums\WorkOrderStatus::WaitingOnInsurance)
                            <button wire:click="$set('showSupplementForm', true)"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                Log Supplement
                            </button>
                        @endif

                        {{-- Advance status --}}
                        @if($this->nextStatus)
                            <div class="flex items-center gap-1.5">
                                <input wire:model="transitionDate"
                                       type="date"
                                       title="Date for this status change"
                                       class="rounded-lg border-slate-300 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 w-36" />
                                <button wire:click="advanceStatus"
                                        wire:loading.attr="disabled"
                                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors whitespace-nowrap">
                                    → {{ $this->nextStatus->label() }}
                                </button>
                            </div>
                        @endif

                        {{-- Hold / Kick --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                    class="rounded-lg border border-slate-300 bg-white p-2 text-slate-500 hover:bg-slate-50 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                 class="absolute right-0 z-10 mt-1 w-44 rounded-lg border border-slate-200 bg-white shadow-lg divide-y divide-slate-100">
                                <button @click="open = false" wire:click="$set('showNoteForm', true)"
                                        class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50">
                                    Add Note
                                </button>
                                @if($workOrder->status !== \App\Enums\WorkOrderStatus::Acquired)
                                    <button @click="open = false" wire:click="revertStatus"
                                            class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50">
                                        Revert Status
                                    </button>
                                @endif
                                <button @click="open = false" wire:click="$set('showHoldModal', true)"
                                        class="w-full px-4 py-2.5 text-left text-sm text-yellow-700 hover:bg-yellow-50">
                                    Put On Hold
                                </button>
                                <button @click="open = false" wire:click="$set('showKickModal', true)"
                                        class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">
                                    Kick Vehicle
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Status Pipeline ──────────────────────────────────────────────── --}}
        <div class="border-t border-slate-100 px-5 py-4">
            <div class="flex items-center">
                @foreach(\App\Enums\WorkOrderStatus::cases() as $i => $s)
                    @php
                        $pos = $s->position();
                        $currentPos = $workOrder->status->position();
                        $isCurrent = $s === $workOrder->status;
                        $isPast    = $pos < $currentPos;
                        $isFuture  = $pos > $currentPos;
                    @endphp

                    {{-- Step --}}
                    <div class="flex flex-col items-center flex-1 min-w-0">
                        <button
                            @if(!$workOrder->kicked && !$workOrder->on_hold)
                                wire:click="jumpToStatus('{{ $s->value }}')"
                                title="Jump to {{ $s->label() }}"
                            @endif
                            @class([
                                'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition-all shrink-0',
                                'ring-2 ring-offset-2 ' . $s->dotClasses() . ' text-white ring-current' => $isCurrent,
                                $s->dotClasses() . ' text-white'   => $isPast,
                                'bg-slate-200 text-slate-400'      => $isFuture,
                                'cursor-pointer hover:opacity-80'  => !$workOrder->kicked && !$workOrder->on_hold,
                                'cursor-default'                   => $workOrder->kicked || $workOrder->on_hold,
                            ])>
                            @if($isPast)
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            @else
                                {{ $pos }}
                            @endif
                        </button>
                        <span @class([
                            'mt-1 text-center text-xs font-medium leading-tight max-w-[4rem] hidden sm:block truncate',
                            'text-slate-700' => $isCurrent,
                            'text-slate-500' => $isPast,
                            'text-slate-300' => $isFuture,
                        ])>
                            @if($s === \App\Enums\WorkOrderStatus::Inspected)
                                {{ $workOrder->inspectedLabel() }}
                            @else
                                {{ $s->label() }}
                            @endif
                        </span>
                    </div>

                    {{-- Connector line --}}
                    @if(!$loop->last)
                        <div @class([
                            'h-0.5 flex-1 mb-4 mx-0.5 transition-colors',
                            $s->dotClasses() => $pos < $currentPos,
                            'bg-slate-200'   => $pos >= $currentPos,
                        ])></div>
                    @endif
                @endforeach
            </div>

            {{-- On Hold / Kicked reason --}}
            @if($workOrder->on_hold && $workOrder->hold_reason)
                <p class="mt-2 text-xs text-yellow-700">
                    <strong>Hold reason:</strong> {{ $workOrder->hold_reason }}
                </p>
            @endif
            @if($workOrder->kicked && $workOrder->kicked_reason)
                <p class="mt-2 text-xs text-red-600">
                    <strong>Kicked:</strong> {{ $workOrder->kicked_reason }}
                </p>
            @endif
        </div>
    </div>

    {{-- ── Two-column layout ─────────────────────────────────────────────────── --}}
    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Left col (2/3) --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Sub-Tasks card --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-3">
                    <h3 class="text-sm font-semibold text-slate-700">Sub-Tasks</h3>
                </div>
                <div class="divide-y divide-slate-100">

                    {{-- Teardown --}}
                    <div class="flex items-center gap-3 px-5 py-3">
                        <button wire:click="toggleSubTask('teardown')"
                                @class([
                                    'flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition-colors',
                                    'border-blue-500 bg-blue-500 text-white' => $workOrder->teardown_completed_at,
                                    'border-slate-300 bg-white hover:border-blue-400' => !$workOrder->teardown_completed_at,
                                ])>
                            @if($workOrder->teardown_completed_at)
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            @endif
                        </button>
                        <span @class(['text-sm flex-1', 'text-slate-800' => $workOrder->teardown_completed_at, 'text-slate-400' => !$workOrder->teardown_completed_at])>
                            Teardown
                        </span>
                        @if($workOrder->teardown_completed_at)
                            <span class="text-xs text-slate-400">{{ $workOrder->teardown_completed_at->format('M j, Y') }}</span>
                        @endif
                    </div>

                    {{-- Parts — Pre-Repair --}}
                    @include('livewire.work-orders.partials.sub-task-parts', [
                        'label'       => 'Parts — Pre-Repair',
                        'needsFlag'   => 'needs_parts_pre_repair',
                        'needsValue'  => $workOrder->needs_parts_pre_repair,
                        'orderedAt'   => $workOrder->parts_pre_repair_ordered_at,
                        'receivedAt'  => $workOrder->parts_pre_repair_received_at,
                        'orderedField'  => 'parts_pre_repair_ordered_at',
                        'receivedField' => 'parts_pre_repair_received_at',
                    ])

                    {{-- Parts — Reassembly --}}
                    @include('livewire.work-orders.partials.sub-task-parts', [
                        'label'       => 'Parts — Reassembly',
                        'needsFlag'   => 'needs_parts_reassembly',
                        'needsValue'  => $workOrder->needs_parts_reassembly,
                        'orderedAt'   => $workOrder->parts_reassembly_ordered_at,
                        'receivedAt'  => $workOrder->parts_reassembly_received_at,
                        'orderedField'  => 'parts_reassembly_ordered_at',
                        'receivedField' => 'parts_reassembly_received_at',
                    ])

                    {{-- Body Shop --}}
                    @include('livewire.work-orders.partials.sub-task-inout', [
                        'label'      => 'Body Shop',
                        'needsFlag'  => 'needs_body_shop',
                        'needsValue' => $workOrder->needs_body_shop,
                        'sentAt'     => $workOrder->body_shop_sent_at,
                        'returnedAt' => $workOrder->body_shop_returned_at,
                        'sentField'     => 'body_shop_sent_at',
                        'returnedField' => 'body_shop_returned_at',
                    ])

                    {{-- Glass --}}
                    @include('livewire.work-orders.partials.sub-task-inout', [
                        'label'      => 'Glass',
                        'needsFlag'  => 'needs_glass',
                        'needsValue' => $workOrder->needs_glass,
                        'sentAt'     => $workOrder->glass_sent_at,
                        'returnedAt' => $workOrder->glass_returned_at,
                        'sentField'     => 'glass_sent_at',
                        'returnedField' => 'glass_returned_at',
                    ])

                </div>
            </div>

            {{-- Events / Timeline --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h3 class="text-sm font-semibold text-slate-700">Timeline</h3>
                    <button wire:click="$set('showNoteForm', true)"
                            class="text-xs text-blue-600 hover:text-blue-700 font-medium">+ Add Note</button>
                </div>

                @if($showNoteForm)
                    <div class="border-b border-slate-100 p-4 space-y-2">
                        <textarea wire:model="noteText" rows="2" placeholder="Add a note…"
                                  class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('noteText') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <div class="flex gap-2">
                            <button wire:click="addNote"
                                    class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                                Save Note
                            </button>
                            <button wire:click="$set('showNoteForm', false)"
                                    class="text-xs text-slate-500 hover:text-slate-700">Cancel</button>
                        </div>
                    </div>
                @endif

                @if($showSupplementForm)
                    <div class="border-b border-slate-100 p-4 space-y-2 bg-orange-50/50">
                        <p class="text-xs font-medium text-orange-700">Logging supplement submission</p>
                        <textarea wire:model="supplementNotes" rows="2" placeholder="Notes about this supplement (optional)…"
                                  class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        <div class="flex gap-2">
                            <button wire:click="logSupplement"
                                    class="rounded-lg bg-orange-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-600 transition-colors">
                                Log Supplement
                            </button>
                            <button wire:click="$set('showSupplementForm', false)"
                                    class="text-xs text-slate-500 hover:text-slate-700">Cancel</button>
                        </div>
                    </div>
                @endif

                <div class="divide-y divide-slate-100">
                    @forelse($this->events as $event)
                        <div class="px-5 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs font-semibold text-slate-600">{{ $event->typeLabel() }}</p>
                                    @if($event->description)
                                        <p class="mt-0.5 text-sm text-slate-700">{{ $event->description }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xs text-slate-400">{{ $event->created_at->format('M j') }}</p>
                                    <p class="text-xs text-slate-400">{{ $event->user->name ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-slate-400">No events yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right col (1/3) --}}
        <div class="space-y-6">

            {{-- Customer & Vehicle --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm divide-y divide-slate-100">
                <div class="px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Customer</p>
                    <a href="{{ route('customers.show', $workOrder->customer) }}" wire:navigate
                       class="font-medium text-blue-600 hover:underline text-sm">
                        {{ $workOrder->customer->first_name }} {{ $workOrder->customer->last_name }}
                    </a>
                    @if($workOrder->customer->phone)
                        <p class="text-sm text-slate-500 mt-0.5">{{ $workOrder->customer->phone }}</p>
                    @endif
                    @if($workOrder->customer->email)
                        <p class="text-sm text-slate-500">{{ $workOrder->customer->email }}</p>
                    @endif
                </div>
                <div class="px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Vehicle</p>
                    <p class="text-sm font-medium text-slate-800">
                        {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                    </p>
                    @if($workOrder->vehicle->vin)
                        <p class="font-mono text-xs text-slate-500 mt-0.5">{{ $workOrder->vehicle->vin }}</p>
                    @endif
                    @if($workOrder->vehicle->color)
                        <p class="text-xs text-slate-500">{{ $workOrder->vehicle->color }}</p>
                    @endif
                </div>
            </div>

            {{-- Insurance details (if applicable) --}}
            @if($workOrder->job_type->isInsurance())
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-semibold text-slate-700">Insurance</h3>
                    </div>
                    <dl class="divide-y divide-slate-100 px-5">
                        @if($workOrder->insuranceCompany)
                            <div class="flex justify-between py-2.5">
                                <dt class="text-xs text-slate-500">Company</dt>
                                <dd class="text-xs font-medium text-slate-800">{{ $workOrder->insuranceCompany->name }}</dd>
                            </div>
                        @endif
                        @if($workOrder->claim_number)
                            <div class="flex justify-between py-2.5">
                                <dt class="text-xs text-slate-500">Claim #</dt>
                                <dd class="text-xs font-mono font-medium text-slate-800">{{ $workOrder->claim_number }}</dd>
                            </div>
                        @endif
                        @if($workOrder->policy_number)
                            <div class="flex justify-between py-2.5">
                                <dt class="text-xs text-slate-500">Policy #</dt>
                                <dd class="text-xs font-medium text-slate-800">{{ $workOrder->policy_number }}</dd>
                            </div>
                        @endif
                        @if($workOrder->adjuster_name)
                            <div class="flex justify-between py-2.5">
                                <dt class="text-xs text-slate-500">Adjuster</dt>
                                <dd class="text-xs font-medium text-slate-800">{{ $workOrder->adjuster_name }}</dd>
                            </div>
                        @endif
                        @if($workOrder->deductible !== null)
                            <div class="flex justify-between py-2.5">
                                <dt class="text-xs text-slate-500">Deductible</dt>
                                <dd class="text-xs font-medium text-slate-800">${{ number_format($workOrder->deductible, 2) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between py-2.5">
                            <dt class="text-xs text-slate-500">Pre-Inspected</dt>
                            <dd class="text-xs font-medium {{ $workOrder->insurance_pre_inspected ? 'text-green-600' : 'text-slate-400' }}">
                                {{ $workOrder->insurance_pre_inspected ? 'Yes' : 'No' }}
                            </dd>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <dt class="text-xs text-slate-500">Rental Coverage</dt>
                            <dd class="text-xs font-medium {{ $workOrder->has_rental_coverage ? 'text-green-600' : 'text-slate-400' }}">
                                {{ $workOrder->has_rental_coverage ? 'Yes' : 'No' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            {{-- Notes --}}
            @if($workOrder->notes)
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Notes</p>
                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $workOrder->notes }}</p>
                </div>
            @endif

        </div>
    </div>

    {{-- ── On Hold modal ─────────────────────────────────────────────────────── --}}
    @if($showHoldModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="font-semibold text-slate-800">Put On Hold</h3>
                </div>
                <div class="p-6 space-y-3">
                    <label class="block text-sm font-medium text-slate-700">Reason *</label>
                    <textarea wire:model="holdReason" rows="3" placeholder="Why is this vehicle going on hold?"
                              class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500"></textarea>
                    @error('holdReason') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                    <button wire:click="$set('showHoldModal', false)"
                            class="flex-1 rounded-lg border border-slate-300 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="hold"
                            class="flex-1 rounded-lg bg-yellow-500 py-2 text-sm font-semibold text-white hover:bg-yellow-600 transition-colors">
                        Hold
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Kick modal ────────────────────────────────────────────────────────── --}}
    @if($showKickModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="font-semibold text-slate-800">Kick Vehicle</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Vehicle will be returned to customer unrepaired.</p>
                </div>
                <div class="p-6 space-y-3">
                    <label class="block text-sm font-medium text-slate-700">Reason *</label>
                    <textarea wire:model="kickReason" rows="3" placeholder="Totaled, uninsured, customer changed mind…"
                              class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                    @error('kickReason') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                    <button wire:click="$set('showKickModal', false)"
                            class="flex-1 rounded-lg border border-slate-300 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="kick"
                            class="flex-1 rounded-lg bg-red-600 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                        Kick
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
