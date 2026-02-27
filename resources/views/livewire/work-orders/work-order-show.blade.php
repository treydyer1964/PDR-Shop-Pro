<div class="space-y-3">

    {{-- Flash message --}}
    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── HEADER CARD ───────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">

        {{-- Vehicle + customer identity line --}}
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                {{-- Vehicle (bold, large) --}}
                <h1 class="text-lg font-bold text-slate-800 leading-snug">
                    @if($workOrder->vehicle->color){{ $workOrder->vehicle->color }} · @endif{{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                </h1>

                {{-- Customer name + insurance summary (smaller) --}}
                <p class="text-sm text-slate-500 mt-0.5 leading-snug">
                    {{ $workOrder->customer->last_name }}
                    @if($workOrder->job_type->isInsurance() && $workOrder->insuranceCompany)
                        <span class="text-slate-300 mx-1">·</span>{{ $workOrder->insuranceCompany->short_name ?? $workOrder->insuranceCompany->name }}
                        @if($workOrder->claim_number)<span class="text-slate-300 mx-1">·</span>{{ $workOrder->claim_number }}@endif
                    @endif
                </p>

                {{-- Badges --}}
                <div class="flex items-center gap-1.5 flex-wrap mt-2">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $workOrder->job_type->badgeClasses() }}">
                        {{ $workOrder->job_type->label() }}
                    </span>
                    @if($workOrder->kicked)
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700">Kicked</span>
                    @elseif($workOrder->on_hold)
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                            </svg>
                            On Hold
                        </span>
                    @elseif($workOrder->is_closed)
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-600">Closed</span>
                    @else
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $workOrder->status->badgeClasses() }}">
                            {{ $workOrder->status->label() }}
                        </span>
                    @endif
                    <span class="text-xs text-slate-400">· {{ $workOrder->daysInShop() }}d · {{ $workOrder->ro_number }}</span>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-start gap-1.5 shrink-0">
                @if($workOrder->kicked)
                    {{-- No actions --}}
                @elseif($workOrder->on_hold)
                    <button wire:click="releaseHold"
                            class="rounded-lg bg-yellow-500 px-3 py-2 text-xs font-semibold text-white hover:bg-yellow-600 transition-colors whitespace-nowrap">
                        Release Hold
                    </button>
                @else
                    {{-- Overflow menu: supplement, revert, hold, kick --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="rounded-lg border border-slate-300 bg-white p-2 text-slate-500 hover:bg-slate-50 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             class="absolute right-0 z-10 mt-1 w-48 rounded-lg border border-slate-200 bg-white shadow-lg divide-y divide-slate-100">
                            <button @click="open = false" wire:click="$set('showNoteForm', true)"
                                    class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50">
                                Add Note
                            </button>
                            <button @click="open = false" wire:click="$set('showSupplementForm', true)"
                                    class="w-full px-4 py-2.5 text-left text-sm text-orange-700 hover:bg-orange-50">
                                Log Supplement
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

        {{-- On Hold / Kicked reason --}}
        @if($workOrder->on_hold && $workOrder->hold_reason)
            <p class="mt-2 text-xs text-yellow-700 bg-yellow-50 rounded-lg px-3 py-2">
                <strong>Hold:</strong> {{ $workOrder->hold_reason }}
            </p>
        @endif
        @if($workOrder->kicked && $workOrder->kicked_reason)
            <p class="mt-2 text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2">
                <strong>Kicked:</strong> {{ $workOrder->kicked_reason }}
            </p>
        @endif
    </div>

    {{-- ── STATUS PIPELINE ───────────────────────────────────────────────────── --}}
    @php
        $allStatuses = \App\Enums\WorkOrderStatus::cases();
        $totalSteps  = count($allStatuses);
        $currentPos  = $workOrder->status->position();
    @endphp
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">

        {{-- Collapsed: progress bar + current phase + advance controls --}}
        <div class="px-4 pt-3 pb-4">

            {{-- Tappable progress bar row --}}
            <button @click="open = !open" type="button"
                    class="w-full text-left mb-3 group">
                <div class="flex items-center gap-0.5 mb-2">
                    @foreach($allStatuses as $s)
                        @php $sPos = $s->position(); @endphp
                        <div @class([
                            'h-2 flex-1 rounded-full transition-colors',
                            $s->dotClasses()  => $sPos <= $currentPos,
                            'bg-slate-200'    => $sPos > $currentPos,
                        ])></div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $workOrder->status->badgeClasses() }}">
                            @if($workOrder->status === \App\Enums\WorkOrderStatus::Inspected)
                                {{ $workOrder->inspectedLabel() }}
                            @else
                                {{ $workOrder->status->label() }}
                            @endif
                        </span>
                        @php $currentLog = $this->statusLogs->where('status', $workOrder->status->value)->last(); @endphp
                        @if($currentLog)
                            <span class="text-xs text-slate-400">since {{ $currentLog->entered_at->format('M j, Y') }}</span>
                        @endif
                    </div>
                    <span class="flex items-center gap-1 text-xs text-slate-400 group-hover:text-slate-600 transition-colors">
                        <span>Step {{ $currentPos }}/{{ $totalSteps }}</span>
                        <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="{'rotate-180': open}"
                             fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </div>
            </button>

            {{-- Advance status controls --}}
            @if(!$workOrder->kicked && !$workOrder->on_hold && $this->nextStatus)
                <div class="flex items-center gap-2">
                    <input wire:model.live="transitionDate" type="date"
                           class="rounded-lg border-slate-300 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 flex-1 min-w-0" />
                    <button wire:click="advanceStatus" wire:loading.attr="disabled"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors whitespace-nowrap">
                        → {{ $this->nextStatus->label() }}
                    </button>
                </div>
            @elseif(!$workOrder->kicked && !$workOrder->on_hold)
                <p class="text-xs text-green-600 font-medium">✓ Delivered — workflow complete</p>
            @endif
        </div>

        {{-- Expanded: all phases with dates and edit controls --}}
        <div x-show="open" style="display:none" class="border-t border-slate-100">
            <div class="divide-y divide-slate-100">
                @foreach($allStatuses as $s)
                    @php
                        $sPos   = $s->position();
                        $isPast = $sPos < $currentPos;
                        $isCur  = $s === $workOrder->status;
                        $log    = $this->statusLogs->where('status', $s->value)->last();
                    @endphp
                    <div class="flex items-center gap-3 px-5 py-3">
                        {{-- Status dot --}}
                        <div @class([
                            'flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                            $s->dotClasses() . ' text-white' => $isPast || $isCur,
                            'bg-slate-100 text-slate-400'   => !$isPast && !$isCur,
                        ])>
                            @if($isPast)
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            @else
                                {{ $sPos }}
                            @endif
                        </div>

                        {{-- Phase label --}}
                        <span @class([
                            'text-sm flex-1',
                            'font-semibold text-slate-800' => $isCur,
                            'text-slate-500'               => $isPast,
                            'text-slate-300'               => !$isPast && !$isCur,
                        ])>
                            @if($s === \App\Enums\WorkOrderStatus::Inspected)
                                {{ $workOrder->inspectedLabel() }}
                            @else
                                {{ $s->label() }}
                            @endif
                        </span>

                        {{-- Date or edit input --}}
                        @if($log)
                            @if($editingLogId === $log->id)
                                <div class="flex items-center gap-1">
                                    <input wire:model.live="editLogDate" type="date"
                                           class="rounded border-slate-300 text-xs py-0.5 focus:border-blue-500 focus:ring-blue-500" />
                                    <button wire:click="saveLogDate"
                                            class="text-xs font-medium text-blue-600 hover:text-blue-700">Save</button>
                                    <button wire:click="cancelEditLogDate"
                                            class="text-xs text-slate-400">✕</button>
                                </div>
                            @else
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-xs text-slate-400">{{ $log->entered_at->format('M j, Y') }}</span>
                                    <button wire:click="startEditLogDate({{ $log->id }})"
                                            class="text-slate-300 hover:text-blue-500 transition-colors" title="Edit date">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        @else
                            <span class="text-xs text-slate-300">—</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── ACCORDION SECTIONS ────────────────────────────────────────────────── --}}

    @php
        $chevron = '<svg class="h-4 w-4 text-slate-400 transition-transform duration-200 shrink-0" :class="{\'rotate-180\': open}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>';
    @endphp

    {{-- ── 1. Sub-Tasks (Phases) ─────────────────────────────────────────────── --}}
    @php
        $activeTasks = collect([
            $workOrder->teardown_completed_at ? null : ($workOrder->teardown_completed_at !== null ? null : 'Teardown pending'),
            $workOrder->needs_parts_pre_repair && !$workOrder->parts_pre_repair_received_at ? 'Parts (Pre-Repair)' : null,
            $workOrder->needs_parts_reassembly && !$workOrder->parts_reassembly_received_at ? 'Parts (Reassembly)' : null,
            $workOrder->needs_body_shop && !$workOrder->body_shop_returned_at ? 'Body Shop' : null,
            $workOrder->needs_glass && !$workOrder->glass_returned_at ? 'Glass' : null,
        ])->filter()->values();
        $completedCount = collect([
            $workOrder->teardown_completed_at,
            $workOrder->needs_parts_pre_repair && $workOrder->parts_pre_repair_received_at,
            $workOrder->needs_parts_reassembly && $workOrder->parts_reassembly_received_at,
            $workOrder->needs_body_shop && $workOrder->body_shop_returned_at,
            $workOrder->needs_glass && $workOrder->glass_returned_at,
        ])->filter()->count();
    @endphp
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sub-Tasks</p>
                @if($activeTasks->isNotEmpty())
                    <p class="text-sm font-medium text-amber-700 mt-0.5">{{ $activeTasks->implode(' · ') }}</p>
                @elseif($completedCount > 0)
                    <p class="text-sm text-slate-500 mt-0.5">{{ $completedCount }} task(s) complete</p>
                @else
                    <p class="text-sm text-slate-400 mt-0.5">None active</p>
                @endif
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100 divide-y divide-slate-100">

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
                @if($editingSubTask === 'teardown_completed_at')
                    <div class="flex items-center gap-1">
                        <input wire:model.live="subTaskDate" type="date"
                               class="rounded border-slate-300 text-xs py-0.5 focus:border-blue-500 focus:ring-blue-500" />
                        <button wire:click="updateSubTaskDate('teardown_completed_at')"
                                class="text-xs text-blue-600 font-medium">Set</button>
                        <button wire:click="$set('editingSubTask', null)"
                                class="text-xs text-slate-400">✕</button>
                    </div>
                @elseif($workOrder->teardown_completed_at)
                    <div class="flex items-center gap-1">
                        <span class="text-xs text-slate-400">{{ $workOrder->teardown_completed_at->format('M j, Y') }}</span>
                        <button wire:click="startEditSubTaskDate('teardown_completed_at', '{{ $workOrder->teardown_completed_at->toDateString() }}')"
                                class="text-slate-300 hover:text-blue-500 transition-colors">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                            </svg>
                        </button>
                    </div>
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

    {{-- ── 2. Customer ───────────────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Customer</p>
                <p class="text-sm font-medium text-slate-800 truncate mt-0.5">{{ $workOrder->customer->full_name }}</p>
                @if($workOrder->customer->phone)
                    <p class="text-xs text-slate-500">{{ $workOrder->customer->display_phone }}</p>
                @endif
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100 px-5 py-4 space-y-4">

            {{-- Phone --}}
            @if($workOrder->customer->phone)
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Phone</p>
                        <p class="text-sm text-slate-800">{{ $workOrder->customer->display_phone }}</p>
                    </div>
                    <a href="tel:{{ preg_replace('/\D/', '', $workOrder->customer->phone) }}"
                       class="flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.338c0-1.142.888-2.088 2.064-2.088h.512c.48 0 .9.323 1.025.788l.826 3.085a1.05 1.05 0 01-.31 1.054l-.87.763a11.25 11.25 0 005.46 5.46l.763-.87a1.05 1.05 0 011.054-.31l3.085.826c.465.125.788.545.788 1.025v.512c0 1.176-.946 2.064-2.088 2.064a16.5 16.5 0 01-15.662-15.662z" />
                        </svg>
                        Call
                    </a>
                </div>
            @endif

            {{-- Email --}}
            @if($workOrder->customer->email)
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Email</p>
                    <a href="mailto:{{ $workOrder->customer->email }}"
                       class="text-sm text-blue-600 hover:underline">{{ $workOrder->customer->email }}</a>
                </div>
            @endif

            {{-- Address --}}
            @php
                $addressParts = array_filter([
                    $workOrder->customer->address,
                    $workOrder->customer->city,
                    trim(($workOrder->customer->state ?? '') . ' ' . ($workOrder->customer->zip ?? '')),
                ]);
                $fullAddress = implode(', ', $addressParts);
            @endphp
            @if($fullAddress)
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs text-slate-400 mb-0.5">Address</p>
                        <p class="text-sm text-slate-800">{{ $fullAddress }}</p>
                    </div>
                    <a href="https://maps.google.com/?q={{ urlencode($fullAddress) }}" target="_blank" rel="noopener"
                       class="flex items-center gap-1.5 shrink-0 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        Maps
                    </a>
                </div>
            @endif

            {{-- Vehicle --}}
            <div>
                <p class="text-xs text-slate-400 mb-0.5">Vehicle</p>
                <p class="text-sm font-medium text-slate-800">
                    {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                    @if($workOrder->vehicle->color) · {{ $workOrder->vehicle->color }} @endif
                </p>
                @if($workOrder->vehicle->vin)
                    <p class="font-mono text-xs text-slate-400 mt-0.5">{{ $workOrder->vehicle->vin }}</p>
                @endif
            </div>

            <a href="{{ route('customers.show', $workOrder->customer) }}" wire:navigate
               class="inline-flex text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
                View Customer Profile →
            </a>
        </div>
    </div>

    {{-- ── 3. Notes ──────────────────────────────────────────────────────────── --}}
    <div x-data="{ open: @js($showNoteForm || $showSupplementForm) }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Notes</p>
                @if($this->recentNotes->isNotEmpty())
                    <p class="text-sm text-slate-600 truncate mt-0.5 italic">
                        "{{ Str::limit($this->recentNotes->first()->description, 80) }}"
                    </p>
                    @if($this->recentNotes->count() > 1)
                        <p class="text-xs text-slate-400">+ {{ $this->recentNotes->count() - 1 }} more</p>
                    @endif
                @else
                    <p class="text-sm text-slate-400 mt-0.5">No notes yet</p>
                @endif
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100">

            {{-- Add Note form --}}
            @if($showNoteForm)
                <div class="border-b border-slate-100 p-4 space-y-2">
                    <textarea wire:model="noteText" rows="3" placeholder="Add a note…"
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
            @else
                <div class="border-b border-slate-100 px-5 py-2.5">
                    <button wire:click="$set('showNoteForm', true)"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700">+ Add Note</button>
                </div>
            @endif

            {{-- Supplement form --}}
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

            {{-- Note list --}}
            <div class="divide-y divide-slate-100">
                @forelse($this->events->where('type', \App\Models\WorkOrderEvent::TYPE_NOTE) as $note)
                    <div class="px-5 py-3">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm text-slate-700 flex-1">{{ $note->description }}</p>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-slate-400">{{ $note->created_at->format('M j') }}</p>
                                <p class="text-xs text-slate-400">{{ $note->user->name ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-3 text-sm text-slate-400">No notes yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── 4. Expenses & Net ─────────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Expenses & Net</p>
                <div class="flex items-baseline gap-4 mt-0.5 flex-wrap">
                    <span class="text-sm text-slate-600">
                        Invoice: <strong class="text-slate-800">${{ number_format($workOrder->invoice_total ?? 0, 2) }}</strong>
                    </span>
                    <span class="text-sm text-slate-600">
                        Expenses: <strong class="text-slate-700">${{ number_format($this->expenseTotal, 2) }}</strong>
                    </span>
                    <span class="text-sm {{ $this->netAmount >= 0 ? 'text-green-700' : 'text-red-600' }}">
                        Net: <strong>${{ number_format($this->netAmount, 2) }}</strong>
                    </span>
                </div>
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100">
            <livewire:work-orders.work-order-expenses :work-order="$workOrder" />
        </div>
    </div>

    {{-- ── 5. Rental ─────────────────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Rental</p>
                @if($this->rentalSummary && $this->rentalSummary->vehicle)
                    <p class="text-sm font-medium text-slate-800 mt-0.5">
                        {{ $this->rentalSummary->vehicle->name ?? ($this->rentalSummary->vehicle->year . ' ' . $this->rentalSummary->vehicle->make . ' ' . $this->rentalSummary->vehicle->model) }}
                    </p>
                    @php
                        $hasOpenSegment = $this->rentalSummary->segments
                            ->where('end_date', null)
                            ->isNotEmpty();
                    @endphp
                    @if($hasOpenSegment)
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-800 mt-0.5">Out</span>
                    @else
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 mt-0.5">Returned</span>
                    @endif
                @else
                    <p class="text-sm text-slate-400 mt-0.5">No rental assigned</p>
                @endif
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100">
            <livewire:work-orders.work-order-rentals :work-order="$workOrder" />
        </div>
    </div>

    {{-- ── 6. Insurance (if applicable) ─────────────────────────────────────── --}}
    @if($workOrder->job_type->isInsurance())
        <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <button @click="open = !open"
                    class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Insurance</p>
                    <p class="text-sm font-medium text-slate-800 mt-0.5">
                        {{ $workOrder->insuranceCompany?->name ?? 'Unknown' }}
                        @if($workOrder->claim_number)
                            <span class="font-mono font-normal text-slate-500"> · {{ $workOrder->claim_number }}</span>
                        @endif
                    </p>
                    <div class="flex gap-3 mt-0.5">
                        <span class="text-xs {{ $workOrder->has_rental_coverage ? 'text-green-600' : 'text-slate-400' }}">
                            Rental: {{ $workOrder->has_rental_coverage ? 'Yes' : 'No' }}
                        </span>
                        <span class="text-xs {{ $workOrder->insurance_pre_inspected ? 'text-green-600' : 'text-slate-400' }}">
                            Pre-Inspected: {{ $workOrder->insurance_pre_inspected ? 'Yes' : 'No' }}
                        </span>
                    </div>
                </div>
                {!! $chevron !!}
            </button>
            <div x-show="open" style="display:none" class="border-t border-slate-100">
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
                    @if($workOrder->adjuster_phone)
                        <div class="flex justify-between py-2.5">
                            <dt class="text-xs text-slate-500">Adj. Phone</dt>
                            <dd class="text-xs font-medium text-slate-800">
                                <a href="tel:{{ preg_replace('/\D/', '', $workOrder->adjuster_phone) }}" class="text-blue-600">
                                    {{ $workOrder->adjuster_phone }}
                                </a>
                            </dd>
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
        </div>
    @endif

    {{-- ── 7. Team ────────────────────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Team</p>
                <p class="text-sm text-slate-600 mt-0.5">
                    {{ $this->teamCount }} {{ Str::plural('member', $this->teamCount) }} assigned
                </p>
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100">
            <livewire:work-orders.work-order-team :work-order="$workOrder" />
        </div>
    </div>

    {{-- ── 8. Appointments ────────────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Appointments</p>
                @php
                    $upcomingApt = $workOrder->appointments()
                        ->where('status', 'scheduled')
                        ->where('scheduled_at', '>=', now())
                        ->orderBy('scheduled_at')
                        ->first();
                @endphp
                @if($upcomingApt)
                    <p class="text-sm font-medium text-slate-800 mt-0.5">
                        {{ $upcomingApt->type?->name }} — {{ $upcomingApt->scheduled_at->format('M j, g:i A') }}
                    </p>
                @else
                    <p class="text-sm text-slate-400 mt-0.5">No upcoming appointments</p>
                @endif
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100">
            <livewire:work-orders.work-order-appointments :work-order="$workOrder" />
        </div>
    </div>

    {{-- ── 9. Payments & Invoice ───────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Payments</p>
                <div class="flex items-baseline gap-3 mt-0.5 flex-wrap">
                    <span class="text-sm text-slate-600">
                        Invoice: <strong class="text-slate-800">${{ number_format($workOrder->invoice_total ?? 0, 2) }}</strong>
                    </span>
                    <span class="text-sm text-slate-600">
                        Paid: <strong class="text-green-700">${{ number_format($workOrder->totalPaid(), 2) }}</strong>
                    </span>
                    @php $balance = $workOrder->balanceOwed(); @endphp
                    @if($balance > 0)
                        <span class="text-sm text-red-600">
                            Owed: <strong>${{ number_format($balance, 2) }}</strong>
                        </span>
                    @elseif($balance <= 0 && ($workOrder->invoice_total ?? 0) > 0)
                        <span class="text-xs font-medium text-green-600">Paid in full</span>
                    @endif
                </div>
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100">
            <livewire:work-orders.work-order-payments :work-order="$workOrder" />
        </div>
    </div>

    {{-- ── 10. Commissions ────────────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Commissions</p>
                @if($workOrder->commissions_locked_at)
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 mt-0.5">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        Locked
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 mt-0.5">
                        Unlocked
                    </span>
                @endif
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100">
            <livewire:work-orders.work-order-commissions :work-order="$workOrder" />
        </div>
    </div>

    {{-- ── 11. Photos ──────────────────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Photos</p>
                @php $photoCount = $workOrder->photos()->count(); @endphp
                <p class="text-sm text-slate-600 mt-0.5">{{ $photoCount }} {{ Str::plural('photo', $photoCount) }}</p>
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100">
            <livewire:work-orders.work-order-photos :work-order="$workOrder" />
        </div>
    </div>

    {{-- ── 12. Timeline (Log) ─────────────────────────────────────────────────── --}}
    <div x-data="{ open: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Timeline</p>
                @php $lastEvent = $this->events->first(); @endphp
                @if($lastEvent)
                    <p class="text-sm text-slate-600 mt-0.5 truncate">
                        {{ $lastEvent->typeLabel() }} — {{ $lastEvent->created_at->format('M j, Y') }}
                    </p>
                @else
                    <p class="text-sm text-slate-400 mt-0.5">No events yet</p>
                @endif
            </div>
            {!! $chevron !!}
        </button>
        <div x-show="open" style="display:none" class="border-t border-slate-100 divide-y divide-slate-100">
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
