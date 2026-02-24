<div class="mx-auto max-w-2xl space-y-6">

    <form wire:submit="save" class="space-y-6">

        {{-- Basic info --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Work Order Info</h3>
            </div>
            <div class="p-5 space-y-4">

                <div>
                    <label class="block text-sm font-medium text-slate-700">Location *</label>
                    <select wire:model="location_id"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select location…</option>
                        @foreach($this->locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Invoice Total</label>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">$</span>
                        <input wire:model="invoice_total" type="number" step="0.01" min="0" placeholder="0.00"
                               class="w-full rounded-lg border-slate-300 pl-7 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Set this when the estimate is finalized — required before commissions can be calculated.</p>
                    @error('invoice_total') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Notes</label>
                    <textarea wire:model="notes" rows="3"
                              class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Insurance fields --}}
        @if($this->isInsurance)
        <div class="rounded-xl border border-blue-100 bg-white shadow-sm">
            <div class="border-b border-blue-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Insurance Information</h3>
            </div>
            <div class="p-5 space-y-4">

                <div>
                    <label class="block text-sm font-medium text-slate-700">Insurance Company</label>
                    <select wire:model="insurance_company_id"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select company…</option>
                        @foreach($this->insuranceCompanies as $ic)
                            <option value="{{ $ic->id }}">{{ $ic->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Claim Number</label>
                        <input wire:model="claim_number" type="text" placeholder="SF-123456"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('claim_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Policy Number</label>
                        <input wire:model="policy_number" type="text"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Adjuster Name</label>
                        <input wire:model="adjuster_name" type="text"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Adjuster Phone</label>
                        <input wire:model="adjuster_phone" type="tel" inputmode="tel"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Adjuster Email</label>
                        <input wire:model="adjuster_email" type="email" inputmode="email"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Deductible</label>
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">$</span>
                            <input wire:model="deductible" type="number" step="0.01" min="0" placeholder="500.00"
                                   class="w-full rounded-lg border-slate-300 pl-7 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 pt-1">
                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input wire:model="insurance_pre_inspected" type="checkbox"
                               class="rounded border-slate-300 text-blue-600" />
                        Insurance has already inspected the vehicle
                        <span class="text-xs text-slate-400">(supplement, not initial estimate)</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input wire:model="has_rental_coverage" type="checkbox"
                               class="rounded border-slate-300 text-blue-600" />
                        Customer has rental coverage
                    </label>
                </div>

            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('work-orders.show', $workOrder) }}" wire:navigate
               class="text-sm text-slate-500 hover:text-slate-700 font-medium">← Cancel</a>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-70 transition-colors">
                <span wire:loading wire:target="save">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </span>
                Save Changes
            </button>
        </div>

    </form>
</div>
