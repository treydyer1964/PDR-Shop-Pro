<div class="bg-white rounded-xl shadow-sm border border-gray-200">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <div class="flex items-center gap-2">
            <h3 class="font-semibold text-gray-900">Payments</h3>
            @if($workOrder->isClosed())
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                    Closed
                </span>
            @endif
        </div>
        @if(!$workOrder->isClosed())
            <button wire:click="openAdd"
                    class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Payment
            </button>
        @endif
    </div>

    {{-- Financial Summary Bar --}}
    <div class="grid grid-cols-3 divide-x divide-gray-100 border-b border-gray-100">
        <div class="px-4 py-3 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Invoice</p>
            <p class="mt-0.5 text-sm font-semibold text-gray-900">
                {{ $workOrder->invoice_total !== null ? '$'.number_format($workOrder->invoice_total, 2) : '—' }}
            </p>
        </div>
        <div class="px-4 py-3 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Paid</p>
            <p class="mt-0.5 text-sm font-semibold text-green-700">
                ${{ number_format($this->totalPaid, 2) }}
            </p>
        </div>
        <div class="px-4 py-3 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Balance</p>
            @if($this->balanceOwed === null)
                <p class="mt-0.5 text-sm font-semibold text-gray-400">—</p>
            @elseif($this->balanceOwed <= 0)
                <p class="mt-0.5 text-sm font-semibold text-green-700">
                    {{ $this->balanceOwed < 0 ? '-' : '' }}${{ number_format(abs($this->balanceOwed), 2) }}
                    @if($this->balanceOwed < 0)
                        <span class="text-xs font-normal text-green-600">(overpaid)</span>
                    @endif
                </p>
            @else
                <p class="mt-0.5 text-sm font-semibold text-red-600">
                    ${{ number_format($this->balanceOwed, 2) }}
                </p>
            @endif
        </div>
    </div>

    {{-- Deductible row (insurance jobs only) --}}
    @if($workOrder->isInsuranceJob() && $workOrder->deductible)
        <div class="flex items-center justify-between px-4 py-2 bg-gray-50 border-b border-gray-100 text-sm">
            <span class="text-gray-500">Deductible on file</span>
            <span class="font-medium text-gray-700">${{ number_format($workOrder->deductible, 2) }}</span>
        </div>
    @endif

    {{-- Add / Edit Form --}}
    @if($showForm)
        <div class="px-4 py-4 bg-gray-50 border-b border-gray-200">
            <p class="text-sm font-medium text-gray-700 mb-3">
                {{ $editingId ? 'Edit Payment' : 'Record Payment' }}
            </p>
            <div class="grid grid-cols-2 gap-3">
                {{-- Source --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Source</label>
                    <select wire:model="source"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($this->sources as $src)
                            <option value="{{ $src->value }}">{{ $src->label() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Method --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Method</label>
                    <select wire:model="method"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($this->methods as $m)
                            <option value="{{ $m->value }}">{{ $m->label() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Amount --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input wire:model="amount" type="number" step="0.01" min="0" placeholder="0.00"
                               class="w-full pl-7 rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                    </div>
                    @error('amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Received On --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date Received</label>
                    <input wire:model="receivedOn" type="date"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>

                {{-- Reference --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Check # / Reference</label>
                    <input wire:model="reference" type="text" placeholder="Optional"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <input wire:model="notes" type="text" placeholder="Optional"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
            </div>

            <div class="flex gap-2 mt-4">
                <button wire:click="save"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    {{ $editingId ? 'Update' : 'Save Payment' }}
                </button>
                <button wire:click="cancel"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Payment List --}}
    <div class="divide-y divide-gray-50">
        @forelse($this->payments as $payment)
            <div class="flex items-start justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                <div class="flex items-start gap-3">
                    {{-- Source badge --}}
                    <span class="mt-0.5 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $payment->source->badgeClasses() }}">
                        {{ $payment->source->label() }}
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-900">${{ number_format($payment->amount, 2) }}</span>
                            <span class="text-xs text-gray-500">via {{ $payment->method->label() }}</span>
                            @if($payment->reference)
                                <span class="text-xs text-gray-400">#{{ $payment->reference }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            @if($payment->received_on)
                                <span class="text-xs text-gray-500">{{ $payment->received_on->format('M j, Y') }}</span>
                            @endif
                            @if($payment->notes)
                                <span class="text-xs text-gray-400">· {{ $payment->notes }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$workOrder->isClosed())
                    <div class="flex items-center gap-2 ml-2 shrink-0">
                        <button wire:click="openEdit({{ $payment->id }})"
                                class="text-xs text-indigo-600 hover:text-indigo-800">Edit</button>
                        <button wire:click="delete({{ $payment->id }})"
                                wire:confirm="Delete this payment?"
                                class="text-xs text-red-500 hover:text-red-700">Delete</button>
                    </div>
                @endif
            </div>
        @empty
            <div class="px-4 py-6 text-center text-sm text-gray-400">
                No payments recorded yet.
            </div>
        @endforelse
    </div>

    {{-- Close / Reopen Work Order --}}
    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-xl">
        @if($workOrder->isClosed())
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">
                    Closed {{ $workOrder->closed_at?->format('M j, Y') }}
                </span>
                <button wire:click="reopenWorkOrder"
                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    Reopen Work Order
                </button>
            </div>
        @else
            @if(!$showCloseConfirm)
                <button wire:click="$set('showCloseConfirm', true)"
                        class="w-full text-center text-sm font-medium text-gray-500 hover:text-gray-700 py-1">
                    Mark Work Order as Closed
                </button>
            @else
                <div class="text-center">
                    <p class="text-sm text-gray-700 mb-2">
                        @if($this->balanceOwed !== null && $this->balanceOwed > 0)
                            <span class="text-amber-700 font-medium">There is still a ${{ number_format($this->balanceOwed, 2) }} balance.</span>
                            Close anyway?
                        @else
                            Close this work order?
                        @endif
                    </p>
                    <div class="flex justify-center gap-3">
                        <button wire:click="closeWorkOrder"
                                class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900">
                            Yes, Close It
                        </button>
                        <button wire:click="$set('showCloseConfirm', false)"
                                class="px-4 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-white">
                            Cancel
                        </button>
                    </div>
                </div>
            @endif
        @endif
    </div>

</div>
