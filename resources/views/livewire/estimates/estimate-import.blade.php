<div class="max-w-3xl mx-auto space-y-6">

    {{-- ── STEP 1: Upload ────────────────────────────────────────────────────── --}}
    @if($step === 1)
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-1">Upload Estimate</h2>
            <p class="text-sm text-slate-500 mb-5">Upload a PDF or photo of the insurance estimate. We'll extract the customer, vehicle, and insurance info automatically.</p>

            @if($extractError)
                <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 mb-4">
                    {{ $extractError }}
                </div>
            @endif

            <form wire:submit="extract">
                {{-- Drop zone --}}
                <label for="estimate-file"
                       class="flex flex-col items-center justify-center gap-3 w-full rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors">
                    <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-slate-700">Click to choose a file</p>
                        <p class="text-xs text-slate-400 mt-1">PDF, JPG, or PNG — max 10 MB</p>
                    </div>
                    <input id="estimate-file" wire:model="file" type="file"
                           accept=".pdf,.jpg,.jpeg,.png" class="sr-only" />
                </label>

                @error('file')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                @if($file)
                    <p class="mt-3 text-sm text-slate-600 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        {{ $file->getClientOriginalName() }}
                    </p>
                @endif

                <div class="mt-5 flex items-center gap-3">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60 transition-colors">
                        <span wire:loading.remove wire:target="extract">
                            Extract Data
                        </span>
                        <span wire:loading wire:target="extract" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Extracting...
                        </span>
                    </button>
                    <a href="{{ route('work-orders.index') }}" wire:navigate
                       class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    @endif

    {{-- ── STEP 2: Review ─────────────────────────────────────────────────────── --}}
    @if($step === 2)

        @if($supplementNumber)
            <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                <strong>Supplement #{{ $supplementNumber }} detected.</strong> Review the fields below, then create the work order.
            </div>
        @endif

        {{-- Customer match notice --}}
        @if($matchedCustomerId && $this->matchedCustomer)
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-green-800">Existing customer found</p>
                        <p class="text-sm text-green-700 mt-0.5">
                            {{ $this->matchedCustomer->full_name }}
                            @if($this->matchedCustomer->phone)
                                · {{ $this->matchedCustomer->display_phone }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <label class="flex items-center gap-1.5 text-sm text-green-800 cursor-pointer">
                            <input wire:model.live="useMatchedCustomer" type="radio" :value="true"
                                   value="1" class="text-green-600 border-green-400" />
                            Use this customer
                        </label>
                        <label class="flex items-center gap-1.5 text-sm text-slate-600 cursor-pointer">
                            <input wire:model.live="useMatchedCustomer" type="radio" :value="false"
                                   value="0" class="text-slate-500" />
                            Create new
                        </label>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit="createWorkOrder" class="space-y-5">

            {{-- ── Customer ────────────────────────────────────────────────── --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</p>
                </div>
                <div class="p-5 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">First Name</label>
                        <input wire:model="customerFirstName" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Last Name <span class="text-red-500">*</span></label>
                        <input wire:model="customerLastName" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('customerLastName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
                        <input wire:model="customerPhone" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                        <input wire:model="customerEmail" type="email"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Address</label>
                        <input wire:model="customerAddress" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">City</label>
                        <input wire:model="customerCity" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">State</label>
                            <input wire:model="customerState" type="text" maxlength="2"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">ZIP</label>
                            <input wire:model="customerZip" type="text"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Vehicle ─────────────────────────────────────────────────── --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Vehicle</p>
                </div>
                <div class="p-5 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Year <span class="text-red-500">*</span></label>
                        <input wire:model="vehicleYear" type="text" maxlength="4"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('vehicleYear') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Make <span class="text-red-500">*</span></label>
                        <input wire:model="vehicleMake" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('vehicleMake') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Model <span class="text-red-500">*</span></label>
                        <input wire:model="vehicleModel" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('vehicleModel') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Color</label>
                        <input wire:model="vehicleColor" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">VIN</label>
                        <input wire:model="vehicleVin" type="text" maxlength="17"
                               class="w-full rounded-lg border-slate-300 text-sm font-mono shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Odometer</label>
                        <input wire:model="vehicleOdometer" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>
            </div>

            {{-- ── Insurance ───────────────────────────────────────────────── --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Insurance</p>
                </div>
                <div class="p-5 grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Insurance Company</label>
                        <input wire:model="insuranceCompany" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Claim Number</label>
                        <input wire:model="claimNumber" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Policy Number</label>
                        <input wire:model="policyNumber" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Adjuster Name</label>
                        <input wire:model="adjusterName" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Adjuster Phone</label>
                        <input wire:model="adjusterPhone" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Adjuster Email</label>
                        <input wire:model="adjusterEmail" type="email"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>
            </div>

            {{-- ── Actions ──────────────────────────────────────────────────── --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60 transition-colors">
                    <span wire:loading.remove wire:target="createWorkOrder">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="createWorkOrder">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                    Create Work Order
                </button>
                <button type="button" wire:click="startOver"
                        class="text-sm text-slate-500 hover:text-slate-700">
                    Start Over
                </button>
            </div>

        </form>
    @endif

</div>
