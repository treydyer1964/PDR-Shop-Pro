<div class="rounded-xl border border-slate-200 bg-white shadow-sm">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-semibold text-slate-700">Rental Vehicle</h3>
            @if($this->rental)
                @php $totalDays = $this->rental->totalDays(); @endphp
                @if($totalDays > 0)
                    <span class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                        {{ $totalDays }} day{{ $totalDays !== 1 ? 's' : '' }}
                    </span>
                @endif
            @endif
        </div>
        @if(! $this->rental)
            <button wire:click="$set('showAssignForm', true)"
                    class="text-xs font-medium text-blue-600 hover:text-blue-700">+ Assign Vehicle</button>
        @endif
    </div>

    {{-- No rental assigned --}}
    @if(! $this->rental && ! $showAssignForm)
        <div class="px-5 py-6 text-center text-sm text-slate-400">
            No rental vehicle assigned.
        </div>
    @endif

    {{-- Assign form --}}
    @if($showAssignForm && ! $this->rental)
        <div class="border-b border-slate-100 p-5 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fleet Vehicle</label>
                    <select wire:model="vehicleId"
                            class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">— None / External —</option>
                        @foreach($this->vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->displayName() }} (${{ number_format($v->internal_daily_cost, 2) }}/day)
                                @if($v->serviceStatus() === 'overdue') ⚠ OIL OVERDUE
                                @elseif($v->serviceStatus() === 'due_soon') ⚠ Service Due Soon
                                @endif
                            </option>
                        @endforeach
                    </select>
                    {{-- Service warning for selected vehicle --}}
                    @if($vehicleId)
                        @php $selectedV = $this->vehicles->firstWhere('id', (int) $vehicleId); @endphp
                        @if($selectedV && in_array($selectedV->serviceStatus(), ['overdue', 'due_soon']))
                            <div @class([
                                'mt-2 flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium',
                                'bg-red-50 text-red-700' => $selectedV->serviceStatus() === 'overdue',
                                'bg-amber-50 text-amber-700' => $selectedV->serviceStatus() === 'due_soon',
                            ])>
                                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                @if($selectedV->serviceStatus() === 'overdue')
                                    This vehicle is past due for an oil change.
                                @else
                                    This vehicle is due for an oil change in {{ number_format($selectedV->milesToNextService()) }} miles.
                                @endif
                            </div>
                        @endif
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Rental Provider</label>
                    <select wire:model="providerId"
                            class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">— None —</option>
                        @foreach($this->providers as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input wire:model="hasInsurance" type="checkbox" id="hasIns"
                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                <label for="hasIns" class="text-sm text-slate-700">Customer has rental coverage (bill insurance)</label>
            </div>

            @if($hasInsurance)
                <div class="max-w-xs">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Insurance Daily Rate ($)</label>
                    <input wire:model="insuranceDailyRate" type="number" step="0.01" min="0" placeholder="0.00"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('insuranceDailyRate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                <input wire:model="assignNotes" type="text" placeholder="Optional notes…"
                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <div class="flex gap-2">
                <button wire:click="assignRental"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    Assign
                </button>
                <button wire:click="$set('showAssignForm', false)"
                        class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Rental detail --}}
    @if($this->rental)
        @php
            $rental    = $this->rental;
            $totalDays = $rental->totalDays();
            $totalCost = $rental->totalInternalCost();
            $billable  = $rental->totalInsuranceBillable();
        @endphp

        {{-- Vehicle & coverage summary --}}
        <div class="px-5 py-4 space-y-3">
            <div class="flex flex-wrap items-start gap-x-6 gap-y-2 text-sm">
                <div>
                    <span class="text-xs text-slate-500">Vehicle</span><br>
                    <span class="font-medium text-slate-800">
                        {{ $rental->vehicle?->displayName() ?? '— External —' }}
                    </span>
                    @if($rental->vehicle)
                        <span class="text-slate-400 text-xs ml-1">${{ number_format($rental->vehicle->internal_daily_cost, 2) }}/day</span>
                    @endif
                </div>
                @if($rental->provider)
                    <div>
                        <span class="text-xs text-slate-500">Provider</span><br>
                        <span class="font-medium text-slate-800">{{ $rental->provider->name }}</span>
                    </div>
                @endif
                <div>
                    <span class="text-xs text-slate-500">Insurance Coverage</span><br>
                    @if($rental->has_insurance_coverage)
                        <span class="text-green-700 font-medium">Yes</span>
                        @if($rental->insurance_daily_rate)
                            <span class="text-slate-400 text-xs ml-1">@ ${{ number_format($rental->insurance_daily_rate, 2) }}/day</span>
                        @endif
                    @else
                        <span class="text-slate-500">No</span>
                    @endif
                    <button wire:click="openCoverageForm" class="ml-2 text-xs text-blue-500 hover:text-blue-700">Edit</button>
                </div>
                @if($totalDays > 0)
                    <div>
                        <span class="text-xs text-slate-500">Internal Cost</span><br>
                        <span class="font-semibold text-slate-800">${{ number_format($totalCost, 2) }}</span>
                        <span class="text-slate-400 text-xs">({{ $totalDays }}d)</span>
                    </div>
                    @if($billable !== null)
                        <div>
                            <span class="text-xs text-slate-500">Billable to Insurance</span><br>
                            <span class="font-semibold text-green-700">${{ number_format($billable, 2) }}</span>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 flex-wrap">
                @if($rental->rental_vehicle_id)
                    <button onclick="openPdf('{{ route('work-orders.rental-agreement-pdf', $workOrder) }}', 'courtesy-vehicle-agreement-{{ $workOrder->ro_number }}.pdf', this)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-blue-300 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        Print Courtesy Vehicle Agreement
                    </button>
                @endif
                <button wire:click="removeRental"
                        wire:confirm="Remove this rental and all its segments? This will also remove the rental expense."
                        class="text-xs text-red-500 hover:text-red-700">Remove Rental</button>
            </div>
        </div>

        {{-- Coverage edit form --}}
        @if($showCoverageForm)
            <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4 space-y-3">
                <p class="text-xs font-semibold text-slate-600">Edit Insurance Coverage</p>
                <div class="flex items-center gap-3">
                    <input wire:model="hasInsurance" type="checkbox" id="editHasIns"
                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                    <label for="editHasIns" class="text-sm text-slate-700">Customer has rental coverage (bill insurance)</label>
                </div>
                @if($hasInsurance)
                    <div class="max-w-xs">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Insurance Daily Rate ($)</label>
                        <input wire:model="insuranceDailyRate" type="number" step="0.01" min="0" placeholder="0.00"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('insuranceDailyRate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div class="flex gap-2">
                    <button wire:click="updateRentalInsurance"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                        Save
                    </button>
                    <button wire:click="$set('showCoverageForm', false)"
                            class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
                </div>
            </div>
        @endif

        {{-- ── Segments ──────────────────────────────────────────────────────── --}}
        <div class="border-t border-slate-100">
            <div class="flex items-center justify-between px-5 py-2.5">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Rental Periods</h4>
                @if(! $showSegmentForm)
                    <button wire:click="openSegmentForm({{ $rental->id }})"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700">+ Add Period</button>
                @endif
            </div>

            @if($rental->segments->isEmpty() && ! $showSegmentForm)
                <p class="px-5 pb-4 text-xs text-slate-400">No rental periods yet. Add one to start tracking days.</p>
            @endif

            @foreach($rental->segments as $seg)
                <div @class(['border-t border-slate-50 px-5 py-3 text-sm', 'bg-slate-50/50' => $showCloseForm && $closingSegmentId === $seg->id])>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <span class="font-medium text-slate-700">{{ $seg->start_date->format('M j') }}</span>
                            <span class="text-slate-400 mx-1">→</span>
                            @if($seg->end_date)
                                <span class="font-medium text-slate-700">{{ $seg->end_date->format('M j, Y') }}</span>
                                <span class="ml-2 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $seg->days }} day{{ $seg->days !== 1 ? 's' : '' }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-700">Out now</span>
                            @endif
                            {{-- Odometer / fuel summary --}}
                            @if($seg->odometer_out !== null || $seg->odometer_in !== null || $seg->fuel_level_out || $seg->fuel_level_in)
                                <div class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-slate-400">
                                    @if($seg->odometer_out !== null)
                                        <span>Out: {{ number_format($seg->odometer_out) }} mi</span>
                                    @endif
                                    @if($seg->odometer_in !== null)
                                        <span>In: {{ number_format($seg->odometer_in) }} mi</span>
                                    @endif
                                    @if($seg->miles_driven !== null)
                                        <span class="text-slate-600 font-medium">{{ number_format($seg->miles_driven) }} mi driven</span>
                                    @endif
                                    @if($seg->fuel_level_out)
                                        <span>Fuel out: {{ $seg->fuel_level_out }}</span>
                                    @endif
                                    @if($seg->fuel_level_in)
                                        <span>Fuel in: {{ $seg->fuel_level_in }}</span>
                                    @endif
                                </div>
                            @endif
                            @if($seg->notes)
                                <p class="mt-0.5 text-xs text-slate-400">{{ $seg->notes }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($seg->isOpen())
                                <button wire:click="openCloseForm({{ $seg->id }})"
                                        class="rounded bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700 transition-colors">
                                    Return
                                </button>
                            @endif
                            <button wire:click="deleteSegment({{ $seg->id }})"
                                    wire:confirm="Delete this rental period?"
                                    class="text-xs text-red-400 hover:text-red-600">✕</button>
                        </div>
                    </div>

                    {{-- Inline close form --}}
                    @if($showCloseForm && $closingSegmentId === $seg->id)
                        <div class="mt-3 rounded-lg border border-green-200 bg-green-50/60 p-3 space-y-3">
                            <p class="text-xs font-semibold text-green-800">Record Return Details</p>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">Return Date <span class="text-red-500">*</span></label>
                                    <input wire:model="segEndDate" type="date"
                                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                    @error('segEndDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">Odometer In (mi)</label>
                                    <input wire:model="segOdometerIn" type="number" min="0" placeholder="e.g. 45890"
                                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">Fuel Level In</label>
                                    <select wire:model="segFuelLevelIn"
                                            class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">—</option>
                                        @foreach(['F', '3/4', '1/2', '1/4', 'E'] as $fl)
                                            <option value="{{ $fl }}">{{ $fl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="closeSegmentWithDetails"
                                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors">
                                    Confirm Return
                                </button>
                                <button wire:click="cancelCloseForm" class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Add segment form --}}
            @if($showSegmentForm)
                <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4 space-y-3">
                    <p class="text-xs font-semibold text-slate-600">New Rental Period</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Pickup Date <span class="text-red-500">*</span></label>
                            <input wire:model="segStartDate" type="date"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('segStartDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Return Date <span class="text-slate-400">(blank if still out)</span></label>
                            <input wire:model="segEndDate" type="date"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('segEndDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Odometer Out (mi)</label>
                            <input wire:model="segOdometerOut" type="number" min="0" placeholder="e.g. 45000"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Fuel Level Out</label>
                            <select wire:model="segFuelLevelOut"
                                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">—</option>
                                @foreach(['F', '3/4', '1/2', '1/4', 'E'] as $fl)
                                    <option value="{{ $fl }}">{{ $fl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <input wire:model="segNotes" type="text" placeholder="Notes (optional)"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="addSegment"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                            Add Period
                        </button>
                        <button wire:click="$set('showSegmentForm', false)"
                                class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Reimbursement ─────────────────────────────────────────────────── --}}
        @if($rental->has_insurance_coverage)
            <div class="border-t border-slate-100">
                <div class="flex items-center justify-between px-5 py-2.5">
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Insurance Reimbursement</h4>
                    @if($billable !== null)
                        <span class="text-xs text-slate-500">Billable: <span class="font-semibold text-slate-700">${{ number_format($billable, 2) }}</span></span>
                    @endif
                </div>

                {{-- STATE 1: Not yet submitted --}}
                @if(! $rental->reimbursement && ! $showReimburseForm)
                    <div class="px-5 pb-4 space-y-3">
                        <p class="text-xs text-slate-400">Claim has not been submitted to insurance yet.</p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button wire:click="markSubmitted({{ $rental->id }})"
                                    wire:confirm="Mark rental claim as submitted to insurance?"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                                Submit to Insurance
                            </button>
                            <button onclick="openPdf('{{ route('work-orders.rental-invoice-pdf', $workOrder) }}', 'rental-invoice-{{ $workOrder->ro_number }}.pdf', this)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Print Rental Invoice
                            </button>
                            <button wire:click="openReimburseForm({{ $rental->id }})"
                                    class="text-xs text-slate-400 hover:text-slate-600">Skip to Record Payment</button>
                        </div>
                    </div>

                {{-- STATE 2: Submitted, awaiting payment --}}
                @elseif($rental->reimbursement && ! $rental->reimbursement->isPaid() && ! $showReimburseForm)
                    <div class="px-5 pb-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Submitted {{ $rental->reimbursement->submitted_at->format('M j, Y') }} — Awaiting Payment
                            </span>
                        </div>
                        @if($rental->reimbursement->notes)
                            <p class="text-xs text-slate-500">{{ $rental->reimbursement->notes }}</p>
                        @endif
                        <div class="flex items-center gap-2 flex-wrap">
                            <button wire:click="openReimburseForm({{ $rental->id }})"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                </svg>
                                Record Payment Received
                            </button>
                            <button onclick="openPdf('{{ route('work-orders.rental-invoice-pdf', $workOrder) }}', 'rental-invoice-{{ $workOrder->ro_number }}.pdf', this)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Print Rental Invoice
                            </button>
                            <button wire:click="deleteReimbursement({{ $rental->reimbursement->id }})"
                                    wire:confirm="Undo submission and reset to unbilled?"
                                    class="text-xs text-red-400 hover:text-red-600">Undo Submission</button>
                        </div>
                    </div>

                {{-- STATE 3: Paid --}}
                @elseif($rental->reimbursement && $rental->reimbursement->isPaid() && ! $showReimburseForm)
                    <div class="px-5 pb-4 space-y-2">
                        <div class="flex items-center gap-6 text-sm">
                            <div>
                                <span class="text-xs text-slate-500">Received from Insurance</span><br>
                                <span class="text-lg font-bold text-green-700">${{ number_format($rental->reimbursement->insurance_amount_received, 2) }}</span>
                            </div>
                            @if($billable !== null)
                                <div>
                                    <span class="text-xs text-slate-500">Billed</span><br>
                                    <span class="font-medium text-slate-700">${{ number_format($billable, 2) }}</span>
                                </div>
                                @php $diff = $rental->reimbursement->insurance_amount_received - $billable; @endphp
                                @if(abs($diff) > 0.01)
                                    <div>
                                        <span class="text-xs text-slate-500">{{ $diff >= 0 ? 'Overpaid' : 'Short' }}</span><br>
                                        <span class="font-medium {{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            ${{ number_format(abs($diff), 2) }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        </div>

                        @if($rental->reimbursement->notes)
                            <p class="text-xs text-slate-500">{{ $rental->reimbursement->notes }}</p>
                        @endif

                        {{-- Staff breakdown --}}
                        @if(! empty($this->reimbursementBreakdown))
                            <div class="mt-2 rounded-lg border border-green-100 bg-green-50 p-3">
                                <p class="mb-2 text-xs font-semibold text-green-800">Staff Reimbursement Breakdown</p>
                                @foreach($this->reimbursementBreakdown as $userId => $amount)
                                    @php $staffUser = \App\Models\User::find($userId); @endphp
                                    @if($staffUser && $amount > 0)
                                        <div class="flex justify-between text-xs text-green-700 py-0.5">
                                            <span>{{ $staffUser->name }}</span>
                                            <span class="font-semibold">${{ number_format($amount, 2) }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center gap-3 pt-1">
                            <button wire:click="openReimburseForm({{ $rental->id }})"
                                    class="text-xs text-blue-600 hover:text-blue-700">Edit Amount</button>
                            <button wire:click="deleteReimbursement({{ $rental->reimbursement->id }})"
                                    wire:confirm="Remove this reimbursement record?"
                                    class="text-xs text-red-400 hover:text-red-600">Remove</button>
                        </div>
                    </div>
                @endif

                {{-- Payment form (States 1 skip-to-pay or State 2 record payment) --}}
                @if($showReimburseForm)
                    <div class="border-t border-slate-100 bg-green-50/40 px-5 py-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-600">Record Payment from Insurance</p>
                        <div class="max-w-xs">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Amount Received ($)</label>
                            <input wire:model="insuranceAmountReceived" type="number" step="0.01" min="0" placeholder="0.00"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('insuranceAmountReceived') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                            <input wire:model="reimburseNotes" type="text" placeholder="Check #, date, etc."
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="recordReimbursement"
                                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors">
                                Save
                            </button>
                            <button wire:click="$set('showReimburseForm', false)"
                                    class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif

</div>
