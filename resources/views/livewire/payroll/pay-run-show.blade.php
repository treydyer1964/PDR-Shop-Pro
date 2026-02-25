<div class="space-y-6">

    {{-- Header card --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $payRun->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Created {{ $payRun->created_at->format('M j, Y') }} by {{ $payRun->createdBy->name }}
                    @if($payRun->period_start || $payRun->period_end)
                        · Period:
                        {{ $payRun->period_start?->format('M j, Y') ?? '—' }}
                        to
                        {{ $payRun->period_end?->format('M j, Y') ?? '—' }}
                    @endif
                </p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-slate-900">${{ number_format((float) $payRun->total_amount, 2) }}</p>
                @if($payRun->isApproved())
                    <p class="text-xs text-green-600 font-medium mt-0.5">
                        Approved {{ $payRun->approved_at->format('M j, Y') }} by {{ $payRun->approvedBy?->name }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Staff breakdown --}}
    @foreach($this->staffSummary as $row)
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            {{-- Staff header --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/60 px-5 py-3">
                <span class="text-sm font-semibold text-slate-800">{{ $row['user']->name }}</span>
                <span class="text-sm font-bold text-slate-900">${{ number_format($row['total'], 2) }}</span>
            </div>

            {{-- Commission line items --}}
            <div class="divide-y divide-slate-100">
                @foreach($row['commissions'] as $commission)
                    <div class="flex items-start justify-between gap-3 px-5 py-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-medium px-1.5 py-0.5 rounded {{ $commission->role->badgeClasses() }}">
                                    {{ $commission->role->label() }}
                                </span>
                                <a href="{{ route('work-orders.show', $commission->work_order_id) }}" wire:navigate
                                   class="text-xs font-mono text-blue-600 hover:underline">
                                    {{ $commission->workOrder->ro_number }}
                                </a>
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
                        <div class="text-right shrink-0">
                            <p class="text-sm font-semibold text-slate-800">${{ number_format((float) $commission->amount, 2) }}</p>
                            @if($commission->paid_at)
                                <p class="text-xs text-green-600">Paid {{ $commission->paid_at->format('M j, Y') }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

</div>
