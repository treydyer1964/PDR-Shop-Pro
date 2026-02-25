<div class="max-w-2xl space-y-6">

    {{-- Step indicator --}}
    <div class="flex items-center gap-2">
        @foreach(['Filters', 'Preview', 'Confirm'] as $i => $label)
            @php $n = $i + 1; @endphp
            <div class="flex items-center gap-1.5">
                <div @class([
                    'flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold',
                    'bg-blue-600 text-white' => $step >= $n,
                    'bg-slate-200 text-slate-400' => $step < $n,
                ])>{{ $n }}</div>
                <span @class([
                    'text-sm font-medium',
                    'text-blue-600' => $step === $n,
                    'text-slate-500' => $step !== $n,
                ])>{{ $label }}</span>
            </div>
            @if(!$loop->last)
                <div class="flex-1 h-px {{ $step > $n ? 'bg-blue-300' : 'bg-slate-200' }}"></div>
            @endif
        @endforeach
    </div>

    {{-- ── Step 1: Filters ─────────────────────────────────────────────────── --}}
    @if($step === 1)
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-6 space-y-5">
            <h2 class="text-base font-semibold text-slate-800">Pay Run Details</h2>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Pay Run Name *</label>
                <input wire:model="name" type="text"
                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="e.g. February 2026 Pay Run" />
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Date range (optional) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Work Order Date Range
                    <span class="text-slate-400 font-normal">(optional — leave blank for all time)</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-500 mb-1 block">From</label>
                        <input wire:model.live="periodStart" type="date"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 mb-1 block">To</label>
                        <input wire:model.live="periodEnd" type="date"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>
            </div>

            {{-- Staff filter (optional) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Limit to Specific Staff
                    <span class="text-slate-400 font-normal">(optional — leave blank for all staff)</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($this->availableStaff as $person)
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" wire:model.live="staffIds" value="{{ $person->id }}"
                                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-slate-700">{{ $person->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Matching count preview --}}
            @php $ids = $this->commissionIds; @endphp
            <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-600">
                @if(count($ids) > 0)
                    <strong class="text-slate-800">{{ count($ids) }}</strong> locked commission line item(s) match your filters
                    — <strong class="text-slate-800">${{ number_format($this->grandTotal, 2) }}</strong> total.
                @else
                    <span class="text-slate-400">No locked, unpaid commissions match your filters.</span>
                @endif
            </div>
        </div>

        <div class="flex justify-end">
            <button wire:click="nextStep"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                Preview →
            </button>
        </div>
    @endif

    {{-- ── Step 2: Preview ─────────────────────────────────────────────────── --}}
    @if($step === 2)
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-slate-700">Commission Preview</h2>
                <p class="text-xs text-slate-400 mt-0.5">Review all line items before creating the pay run.</p>
            </div>

            @foreach($this->preview as $row)
                {{-- Staff member header --}}
                <div class="flex items-center justify-between bg-slate-50 px-5 py-2.5 border-b border-slate-100">
                    <span class="text-sm font-semibold text-slate-800">{{ $row['user']->name }}</span>
                    <span class="text-sm font-bold text-slate-800">${{ number_format($row['total'], 2) }}</span>
                </div>

                {{-- Their commission line items --}}
                @foreach($row['commissions'] as $commission)
                    <div class="flex items-start justify-between gap-3 px-5 py-2.5 border-b border-slate-100 last:border-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium px-1.5 py-0.5 rounded {{ $commission->role->badgeClasses() }}">
                                    {{ $commission->role->label() }}
                                </span>
                                <span class="text-xs text-slate-500 font-mono">
                                    {{ $commission->workOrder->ro_number }}
                                </span>
                            </div>
                            @if($commission->workOrder->customer)
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $commission->workOrder->customer->first_name }} {{ $commission->workOrder->customer->last_name }}
                                    · {{ $commission->workOrder->vehicle?->year }} {{ $commission->workOrder->vehicle?->make }} {{ $commission->workOrder->vehicle?->model }}
                                </p>
                            @endif
                            @if($commission->notes)
                                <p class="text-xs text-slate-400 mt-0.5 italic">{{ $commission->notes }}</p>
                            @endif
                        </div>
                        <span class="text-sm font-semibold text-slate-800 shrink-0">
                            ${{ number_format((float) $commission->amount, 2) }}
                        </span>
                    </div>
                @endforeach
            @endforeach

            {{-- Grand total --}}
            <div class="flex justify-between border-t-2 border-slate-200 bg-slate-50 px-5 py-3">
                <span class="text-sm font-bold text-slate-700">Grand Total</span>
                <span class="text-base font-bold text-slate-900">${{ number_format($this->grandTotal, 2) }}</span>
            </div>
        </div>

        <div class="flex justify-between">
            <button wire:click="prevStep"
                    class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                ← Back
            </button>
            <button wire:click="nextStep"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                Confirm & Pay →
            </button>
        </div>
    @endif

    {{-- ── Step 3: Confirm ─────────────────────────────────────────────────── --}}
    @if($step === 3)
        <div class="rounded-xl border border-green-200 bg-green-50 shadow-sm p-6 space-y-4">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-green-900">Ready to create pay run</h2>
                    <p class="text-sm text-green-700 mt-1">
                        <strong>{{ $name }}</strong><br>
                        {{ count($this->commissionIds) }} commission line items
                        for {{ $this->preview->count() }} staff member(s)<br>
                        Total: <strong>${{ number_format($this->grandTotal, 2) }}</strong>
                    </p>
                    <p class="text-xs text-green-600 mt-2">
                        Clicking "Create Pay Run" will mark all included commissions as <strong>paid</strong>. This cannot be undone.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-between">
            <button wire:click="prevStep"
                    class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                ← Back
            </button>
            <button wire:click="confirm"
                    wire:loading.attr="disabled"
                    class="rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700 transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="confirm">Create Pay Run</span>
                <span wire:loading wire:target="confirm">Processing…</span>
            </button>
        </div>
    @endif

</div>
