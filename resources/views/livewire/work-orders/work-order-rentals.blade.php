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
                            <option value="{{ $v->id }}">{{ $v->displayName() }} (${{ number_format($v->internal_daily_cost, 2) }}/day)</option>
                        @endforeach
                    </select>
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

            {{-- Remove link --}}
            <div class="flex items-center gap-3">
                <button wire:click="removeRental"
                        wire:confirm="Remove this rental and all its segments? This will also remove the rental expense."
                        class="text-xs text-red-500 hover:text-red-700">Remove Rental</button>
            </div>
        </div>

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
                <div class="flex items-center gap-4 border-t border-slate-50 px-5 py-3 text-sm">
                    <div class="flex-1">
                        <span class="font-medium text-slate-700">{{ $seg->start_date->format('M j') }}</span>
                        <span class="text-slate-400 mx-1">→</span>
                        @if($seg->end_date)
                            <span class="font-medium text-slate-700">{{ $seg->end_date->format('M j, Y') }}</span>
                            <span class="ml-2 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $seg->days }} day{{ $seg->days !== 1 ? 's' : '' }}</span>
                        @else
                            <span class="inline-flex rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-700">Out now</span>
                        @endif
                        @if($seg->notes)
                            <p class="mt-0.5 text-xs text-slate-400">{{ $seg->notes }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($seg->isOpen())
                            <button wire:click="closeSegment({{ $seg->id }})"
                                    class="rounded bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700 transition-colors">
                                Return
                            </button>
                        @endif
                        <button wire:click="deleteSegment({{ $seg->id }})"
                                wire:confirm="Delete this rental period?"
                                class="text-xs text-red-400 hover:text-red-600">✕</button>
                    </div>
                </div>
            @endforeach

            {{-- Add segment form --}}
            @if($showSegmentForm)
                <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4 space-y-3">
                    <p class="text-xs font-semibold text-slate-600">New Rental Period</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Pickup Date</label>
                            <input wire:model="segStartDate" type="date"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('segStartDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Return Date <span class="text-slate-400">(leave blank if still out)</span></label>
                            <input wire:model="segEndDate" type="date"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('segEndDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                    @if(! $rental->reimbursement && ! $showReimburseForm)
                        <button wire:click="openReimburseForm({{ $rental->id }})"
                                class="text-xs font-medium text-green-600 hover:text-green-700">+ Record Payment</button>
                    @endif
                </div>

                @if($rental->reimbursement)
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
                                    @php $user = \App\Models\User::find($userId); @endphp
                                    @if($user && $amount > 0)
                                        <div class="flex justify-between text-xs text-green-700 py-0.5">
                                            <span>{{ $user->name }}</span>
                                            <span class="font-semibold">${{ number_format($amount, 2) }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center gap-3 pt-1">
                            <button wire:click="openReimburseForm({{ $rental->id }})"
                                    class="text-xs text-blue-600 hover:text-blue-700">Edit</button>
                            <button wire:click="deleteReimbursement({{ $rental->reimbursement->id }})"
                                    wire:confirm="Remove this reimbursement record?"
                                    class="text-xs text-red-400 hover:text-red-600">Remove</button>
                        </div>
                    </div>

                @elseif(! $showReimburseForm)
                    <p class="px-5 pb-4 text-xs text-slate-400">No reimbursement received yet.</p>
                @endif

                {{-- Reimbursement form --}}
                @if($showReimburseForm)
                    <div class="border-t border-slate-100 bg-green-50/40 px-5 py-4 space-y-3">
                        <div class="max-w-xs">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Amount Received from Insurance ($)</label>
                            <input wire:model="insuranceAmountReceived" type="number" step="0.01" min="0" placeholder="0.00"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            @error('insuranceAmountReceived') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                            <input wire:model="reimburseNotes" type="text" placeholder="Optional…"
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
