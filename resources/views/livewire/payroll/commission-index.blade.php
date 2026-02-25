<div class="space-y-4">

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        {{-- Status filter --}}
        <div class="flex rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm">
            @foreach(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'all' => 'All'] as $value => $label)
                <button wire:click="$set('filterStatus', '{{ $value }}')"
                        @class([
                            'px-4 py-2 text-sm font-medium transition-colors',
                            'bg-blue-600 text-white' => $filterStatus === $value,
                            'text-slate-600 hover:bg-slate-50' => $filterStatus !== $value,
                        ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Staff filter --}}
        <select wire:model.live="filterStaff"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Staff</option>
            @foreach($this->staff as $person)
                <option value="{{ $person->id }}">{{ $person->name }}</option>
            @endforeach
        </select>

        @if($this->grandTotal > 0)
            <span class="ml-auto text-sm font-semibold text-slate-700">
                Total: ${{ number_format($this->grandTotal, 2) }}
            </span>
        @endif
    </div>

    {{-- Results --}}
    @if($this->commissions->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm px-5 py-12 text-center">
            <p class="text-sm text-slate-400">No commissions found.</p>
        </div>
    @else
        @foreach($this->commissions as $row)
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                {{-- Staff header --}}
                <div class="flex items-center justify-between bg-slate-50/60 border-b border-slate-100 px-5 py-2.5">
                    <span class="text-sm font-semibold text-slate-800">{{ $row['user']->name }}</span>
                    <div class="flex items-center gap-4">
                        @if($filterStatus === 'all' && ($row['unpaid'] > 0 || $row['paid'] > 0))
                            <span class="text-xs text-slate-500">
                                Unpaid: ${{ number_format($row['unpaid'], 2) }}
                                · Paid: ${{ number_format($row['paid'], 2) }}
                            </span>
                        @endif
                        <span class="text-sm font-bold text-slate-800">${{ number_format($row['total'], 2) }}</span>
                    </div>
                </div>

                {{-- Line items --}}
                <div class="divide-y divide-slate-100">
                    @foreach($row['commissions'] as $commission)
                        <div class="flex items-start justify-between gap-3 px-5 py-2.5">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-medium px-1.5 py-0.5 rounded {{ $commission->role->badgeClasses() }}">
                                        {{ $commission->role->label() }}
                                    </span>
                                    <a href="{{ route('work-orders.show', $commission->work_order_id) }}" wire:navigate
                                       class="text-xs font-mono text-blue-600 hover:underline">
                                        {{ $commission->workOrder->ro_number }}
                                    </a>
                                    @if($commission->workOrder->customer)
                                        <span class="text-xs text-slate-400">
                                            {{ $commission->workOrder->customer->first_name }} {{ $commission->workOrder->customer->last_name }}
                                        </span>
                                    @endif
                                </div>
                                @if($commission->notes)
                                    <p class="text-xs text-slate-400 mt-0.5 italic">{{ $commission->notes }}</p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold text-slate-800">${{ number_format((float) $commission->amount, 2) }}</p>
                                @if($commission->is_paid)
                                    <p class="text-xs text-green-600">Paid {{ $commission->paid_at?->format('M j') }}</p>
                                @else
                                    @if($commission->workOrder->commissionsLocked())
                                        <p class="text-xs text-amber-600">Locked</p>
                                    @else
                                        <p class="text-xs text-slate-400">Unlocked</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

</div>
