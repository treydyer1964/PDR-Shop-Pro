<div class="rounded-xl border border-slate-200 bg-white shadow-sm">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <h3 class="text-sm font-semibold text-slate-700">Expenses & Net</h3>
        @if(!$showAddForm)
            <button wire:click="$set('showAddForm', true)"
                    class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                + Add Expense
            </button>
        @endif
    </div>

    {{-- Invoice Total --}}
    <div class="border-b border-slate-100 px-5 py-3">
        <div class="flex items-center justify-between gap-3">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Invoice Total</span>

            @if($editingInvoice)
                <div class="flex items-center gap-2">
                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 text-sm">$</span>
                            <input wire:model="invoiceTotal"
                                   wire:keydown.enter="saveInvoiceTotal"
                                   wire:keydown.escape="cancelInvoiceEdit"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   autofocus
                                   class="w-32 rounded-lg border-slate-300 pl-6 py-1.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 text-right" />
                        </div>
                        @error('invoiceTotal')
                            <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button wire:click="saveInvoiceTotal"
                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                        Save
                    </button>
                    <button wire:click="cancelInvoiceEdit"
                            class="text-xs text-slate-500 hover:text-slate-700">
                        Cancel
                    </button>
                </div>
            @else
                <button wire:click="$set('editingInvoice', true)"
                        class="group flex items-center gap-1.5 text-sm font-semibold text-slate-800 hover:text-blue-600 transition-colors">
                    @if($workOrder->invoice_total !== null)
                        ${{ number_format((float) $workOrder->invoice_total, 2) }}
                    @else
                        <span class="text-slate-400 font-normal">Not set</span>
                    @endif
                    <svg class="h-3.5 w-3.5 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Add expense form --}}
    @if($showAddForm)
        <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-4 space-y-3">
            <p class="text-xs font-semibold text-slate-600">Add Expense</p>

            <div class="grid grid-cols-2 gap-3">
                {{-- Category --}}
                <div>
                    <select wire:model="addCategoryId"
                            class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Category…</option>
                        @foreach($this->categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('addCategoryId') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Amount --}}
                <div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 text-sm">$</span>
                        <input wire:model="addAmount"
                               type="number"
                               step="0.01"
                               min="0.01"
                               placeholder="0.00"
                               class="w-full rounded-lg border-slate-300 pl-6 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    @error('addAmount') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <input wire:model="addNotes"
                       type="text"
                       placeholder="Notes (optional)…"
                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                @error('addNotes') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                <button wire:click="addExpense"
                        class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                    Add
                </button>
                <button wire:click="$set('showAddForm', false)"
                        class="text-xs text-slate-500 hover:text-slate-700">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Expense line items --}}
    @if($this->expenses->isNotEmpty())
        <div class="divide-y divide-slate-100">
            @foreach($this->expenses as $expense)
                <div class="flex items-center gap-3 px-5 py-2.5">
                    <div class="flex-1 min-w-0">
                        <span class="text-sm font-medium text-slate-700">{{ $expense->category->name }}</span>
                        @if($expense->notes)
                            <span class="text-xs text-slate-400 ml-1.5">— {{ $expense->notes }}</span>
                        @endif
                    </div>
                    <span class="text-sm font-semibold text-slate-800 shrink-0">
                        ${{ number_format((float) $expense->amount, 2) }}
                    </span>
                    <button wire:click="deleteExpense({{ $expense->id }})"
                            wire:confirm="Remove this expense?"
                            class="shrink-0 text-slate-300 hover:text-red-500 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <p class="px-5 py-4 text-sm text-slate-400">No expenses recorded.</p>
    @endif

    {{-- Totals summary --}}
    <div class="border-t border-slate-200 bg-slate-50 rounded-b-xl divide-y divide-slate-200">

        {{-- Total expenses row --}}
        @if($this->expenses->isNotEmpty())
            <div class="flex justify-between px-5 py-2.5">
                <span class="text-xs text-slate-500">Total Expenses</span>
                <span class="text-xs font-semibold text-red-600">
                    − ${{ number_format($this->totalExpenses, 2) }}
                </span>
            </div>
        @endif

        {{-- Net row --}}
        <div class="flex justify-between px-5 py-3">
            <span class="text-sm font-semibold text-slate-700">Net</span>
            @if($this->netTotal !== null)
                @php $net = $this->netTotal; @endphp
                <span @class([
                    'text-sm font-bold',
                    'text-green-600' => $net >= 0,
                    'text-red-600'   => $net < 0,
                ])>
                    ${{ number_format(abs($net), 2) }}{{ $net < 0 ? ' (negative)' : '' }}
                </span>
            @else
                <span class="text-sm text-slate-400">Set invoice total to calculate</span>
            @endif
        </div>
    </div>

</div>
