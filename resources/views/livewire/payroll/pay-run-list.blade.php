<div class="space-y-4">

    {{-- Pending commissions banner --}}
    @if($this->pendingCount > 0)
        <div class="flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50 px-5 py-4">
            <div>
                <p class="text-sm font-semibold text-blue-800">
                    {{ $this->pendingCount }} locked commission{{ $this->pendingCount === 1 ? '' : 's' }} ready to pay
                </p>
                <p class="text-xs text-blue-600 mt-0.5">Create a new pay run to batch and mark these as paid.</p>
            </div>
            <a href="{{ route('payroll.create') }}" wire:navigate
               class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors whitespace-nowrap">
                New Pay Run
            </a>
        </div>
    @endif

    {{-- Pay runs table --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Pay Run History</h3>
            @if($this->pendingCount === 0)
                <a href="{{ route('payroll.create') }}" wire:navigate
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium">+ New Pay Run</a>
            @endif
        </div>

        @if($this->payRuns->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-sm text-slate-400">No pay runs yet.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($this->payRuns as $run)
                    <a href="{{ route('payroll.show', $run) }}" wire:navigate
                       class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800">{{ $run->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $run->commissions_count }} line item{{ $run->commissions_count === 1 ? '' : 's' }}
                                · Created by {{ $run->createdBy->name }}
                                · {{ $run->created_at->format('M j, Y') }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-bold text-slate-800">${{ number_format((float) $run->total_amount, 2) }}</p>
                            @if($run->isApproved())
                                <span class="text-xs text-green-600 font-medium">Approved</span>
                            @else
                                <span class="text-xs text-amber-600 font-medium">Pending</span>
                            @endif
                        </div>
                        <svg class="h-4 w-4 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

</div>
