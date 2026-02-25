<div class="rounded-xl border border-slate-200 bg-white shadow-sm">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-semibold text-slate-700">Commissions</h3>
            @if($workOrder->commissionsLocked())
                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    Locked
                </span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if($workOrder->commissionsLocked())
                <button wire:click="unlock"
                        wire:confirm="Unlock commissions? This will allow recalculation but will remove the lock for pay runs."
                        class="text-xs text-slate-500 hover:text-slate-700 font-medium">
                    Unlock
                </button>
            @else
                @if($this->commissions->isNotEmpty())
                    <button wire:click="lock"
                            class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 transition-colors">
                        Lock for Pay Run
                    </button>
                @endif
                <button wire:click="calculate"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="calculate">
                        {{ $this->commissions->isEmpty() ? 'Calculate' : 'Recalculate' }}
                    </span>
                    <span wire:loading wire:target="calculate">Calculating…</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Errors --}}
    @if(!empty($errors))
        <div class="border-b border-red-100 bg-red-50 px-5 py-3">
            @foreach($errors as $error)
                <p class="text-sm text-red-600">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- No invoice total warning --}}
    @if($workOrder->invoice_total === null && $this->commissions->isEmpty())
        <div class="px-5 py-6 text-center">
            <p class="text-sm text-slate-400">Set the invoice total and expenses first,<br>then calculate commissions.</p>
        </div>
    @elseif($this->commissions->isEmpty())
        <div class="px-5 py-6 text-center">
            <p class="text-sm text-slate-400">No commissions calculated yet.</p>
            @if($workOrder->assignments->isEmpty())
                <p class="mt-1 text-xs text-slate-400">Assign team members first.</p>
            @endif
        </div>
    @else

        {{-- Commission line items --}}
        <div class="divide-y divide-slate-100">
            @php
                $grouped = $this->commissions->groupBy(fn($c) => $c->role->value);
            @endphp

            @foreach($grouped as $roleValue => $items)
                @php $role = \App\Enums\Role::from($roleValue); @endphp

                {{-- Role header --}}
                <div class="bg-slate-50/60 px-5 py-1.5">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $role->label() }}</span>
                </div>

                @foreach($items as $commission)
                    <div class="px-5 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800">{{ $commission->user->name }}</p>
                                @if($commission->notes)
                                    <p class="mt-0.5 text-xs text-slate-400 leading-snug">{{ $commission->notes }}</p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold {{ (float)$commission->amount >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                    ${{ number_format((float) $commission->amount, 2) }}
                                </p>
                                @if($commission->is_paid)
                                    <span class="text-xs text-green-600 font-medium">Paid</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>

        {{-- Total --}}
        <div class="flex justify-between border-t border-slate-200 bg-slate-50 px-5 py-3 rounded-b-xl">
            <span class="text-sm font-semibold text-slate-700">Total Commissions</span>
            <span class="text-sm font-bold text-slate-800">
                ${{ number_format($this->totalCommissions, 2) }}
            </span>
        </div>

        @if($workOrder->commissionsLocked())
            <div class="border-t border-slate-100 px-5 py-2 text-xs text-slate-400 text-center rounded-b-xl">
                Locked {{ $workOrder->commissions_locked_at->format('M j, Y g:i a') }}
            </div>
        @endif

    @endif

</div>
