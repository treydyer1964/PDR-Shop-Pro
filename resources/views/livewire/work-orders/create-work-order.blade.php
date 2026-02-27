<div class="mx-auto max-w-2xl">

    {{-- Step indicator --}}
    <div class="mb-8 flex items-center justify-between">
        @foreach([1 => 'Job Type', 2 => 'Customer', 3 => 'Vehicle', 4 => 'Details'] as $num => $label)
            <div class="flex flex-1 flex-col items-center">
                <div @class([
                    'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition-colors',
                    'bg-blue-600 text-white'   => $step >= $num,
                    'bg-slate-200 text-slate-500' => $step < $num,
                ])>
                    @if($step > $num)
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    @else
                        {{ $num }}
                    @endif
                </div>
                <span class="mt-1 text-xs font-medium {{ $step >= $num ? 'text-blue-600' : 'text-slate-400' }}">{{ $label }}</span>
            </div>
            @if($num < 4)
                <div class="mb-5 h-px flex-1 {{ $step > $num ? 'bg-blue-600' : 'bg-slate-200' }}"></div>
            @endif
        @endforeach
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="p-6">

            {{-- ── Step 1: Job Type ─────────────────────────────────────────── --}}
            @if($step === 1)
                <h2 class="mb-4 text-lg font-semibold text-slate-800">What type of job is this?</h2>

                <p class="mb-4 text-sm text-slate-500">Select one to continue.</p>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    @foreach($this->jobTypes as $type)
                        <button wire:click="selectJobType('{{ $type->value }}')"
                                type="button"
                                class="flex flex-col items-center gap-2 rounded-xl border-2 border-slate-200 p-5 text-center transition-all hover:border-blue-400 hover:bg-blue-50 hover:shadow-md">
                            <span class="text-sm font-semibold text-slate-800">{{ $type->label() }}</span>
                            <span class="text-xs text-slate-500">
                                @switch($type->value)
                                    @case('insurance') Insurance company pays @break
                                    @case('customer_pay') Customer pays out of pocket @break
                                    @case('wholesale') Body shop / dealership @break
                                @endswitch
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- ── Step 2: Customer ─────────────────────────────────────────── --}}
            @if($step === 2)
                <h2 class="mb-4 text-lg font-semibold text-slate-800">Who is the customer?</h2>

                @if($customer_id && !$creatingNewCustomer)
                    {{-- Customer selected --}}
                    <div class="flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4">
                        <div>
                            <p class="font-medium text-slate-800">{{ $this->selectedCustomer?->first_name }} {{ $this->selectedCustomer?->last_name }}</p>
                            <p class="text-sm text-slate-500">{{ $this->selectedCustomer?->phone }}</p>
                        </div>
                        <button wire:click="clearCustomer" type="button" class="text-sm text-slate-500 hover:text-slate-700">Change</button>
                    </div>
                @elseif($creatingNewCustomer)
                    {{-- Create new customer inline --}}
                    <div class="space-y-3 rounded-lg border border-blue-200 bg-blue-50/50 p-4">
                        <p class="text-sm font-medium text-slate-700">New Customer</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600">First Name *</label>
                                <input wire:model="cFirst" type="text" placeholder="John"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('cFirst') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Last Name *</label>
                                <input wire:model="cLast" type="text" placeholder="Smith"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('cLast') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Phone</label>
                                <input wire:model="cPhone" type="tel" inputmode="tel" placeholder="(555) 555-5555"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Email</label>
                                <input wire:model="cEmail" type="email" inputmode="email" placeholder="john@example.com"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>
                        <button wire:click="$set('creatingNewCustomer', false)" type="button"
                                class="text-xs text-slate-500 hover:text-slate-700">← Back to search</button>
                    </div>
                @else
                    {{-- Search --}}
                    <div class="space-y-3">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" />
                            </svg>
                            <input wire:model.live.debounce.200ms="customerSearch" type="search"
                                   placeholder="Search by name or phone…"
                                   class="w-full rounded-lg border-slate-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>

                        @if(strlen($customerSearch) >= 2)
                            <div class="rounded-lg border border-slate-200 divide-y divide-slate-100 bg-white shadow-sm max-h-56 overflow-y-auto">
                                @forelse($this->customerResults as $c)
                                    <button wire:click="selectCustomer({{ $c->id }})" type="button"
                                            class="w-full px-4 py-2.5 text-left hover:bg-slate-50 transition-colors">
                                        <p class="text-sm font-medium text-slate-800">{{ $c->first_name }} {{ $c->last_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $c->phone }}</p>
                                    </button>
                                @empty
                                    <p class="px-4 py-3 text-sm text-slate-500">No customers found.</p>
                                @endforelse
                            </div>
                        @endif

                        <button wire:click="startNewCustomer" type="button"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            + Create new customer
                        </button>
                    </div>
                    @error('customer_id') <p class="mt-2 text-sm text-red-600">Please select a customer.</p> @enderror
                @endif
            @endif

            {{-- ── Step 3: Vehicle ──────────────────────────────────────────── --}}
            @if($step === 3)
                <h2 class="mb-4 text-lg font-semibold text-slate-800">Which vehicle?</h2>

                {{-- Existing vehicles --}}
                @if(!$creatingNewVehicle && $this->customerVehicles->isNotEmpty())
                    <div class="mb-4 space-y-2">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Previous vehicles</p>
                        @foreach($this->customerVehicles as $v)
                            <button wire:click="selectVehicle({{ $v->id }})" type="button"
                                    @class([
                                        'w-full flex items-center justify-between rounded-lg border-2 px-4 py-3 text-left transition-all',
                                        'border-blue-500 bg-blue-50' => $vehicle_id === $v->id,
                                        'border-slate-200 hover:border-slate-300' => $vehicle_id !== $v->id,
                                    ])>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">{{ $v->year }} {{ $v->make }} {{ $v->model }}</p>
                                    <p class="text-xs text-slate-500">
                                        @if($v->vin) VIN: {{ $v->vin }} @endif
                                        @if($v->color) · {{ $v->color }} @endif
                                    </p>
                                </div>
                                @if($vehicle_id === $v->id)
                                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                @if($creatingNewVehicle)
                    {{-- New vehicle form --}}
                    <div class="space-y-3 rounded-lg border border-blue-200 bg-blue-50/50 p-4"
                         x-data="Object.assign(vinScanner(), {
                            decoding: false,
                            async decodeVin(vin) {
                                if (vin.length !== 17) return;
                                this.decoding = true;
                                try {
                                    const r = await fetch(`https://vpic.nhtsa.dot.gov/api/vehicles/decodevin/${vin}?format=json`);
                                    const data = await r.json();
                                    const get = (key) => data.Results?.find(x => x.Variable === key)?.Value || '';
                                    $wire.vinDecoded({
                                        year: get('Model Year'),
                                        make: get('Make'),
                                        model: get('Model'),
                                        trim: get('Trim'),
                                    });
                                } finally {
                                    this.decoding = false;
                                }
                            }
                         })"
                         @vin-scanned.window="$wire.set('vVin', $event.detail.vin); decodeVin($event.detail.vin)">
                        <p class="text-sm font-medium text-slate-700">New Vehicle</p>

                        {{-- VIN --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600">VIN</label>

                            {{-- Hidden photo capture input (fallback for non-HTTPS / iOS) --}}
                            <input x-ref="photoInput" type="file" accept="image/*" capture="environment"
                                   class="hidden" @change="scanFromPhoto($event)" />

                            {{-- Scan button (shown when not live-scanning) --}}
                            <div x-show="!scanning" class="mt-1 mb-2">
                                <button @click="startScan()" type="button"
                                        :disabled="photoScanning"
                                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white hover:bg-blue-700 active:bg-blue-800 disabled:opacity-60">
                                    <template x-if="photoScanning">
                                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!photoScanning">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                                        </svg>
                                    </template>
                                    <span x-text="photoScanning ? 'Reading barcode…' : 'Scan VIN Barcode'"></span>
                                </button>
                            </div>

                            {{-- Camera preview (shown while scanning) --}}
                            <div x-show="scanning" x-cloak class="mt-1 mb-2 relative overflow-hidden rounded-lg border border-slate-300 bg-black">
                                <video x-ref="videoEl" class="w-full" autoplay muted playsinline></video>
                                {{-- Red targeting line --}}
                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    <div class="h-0.5 w-4/5 bg-red-400 opacity-80 shadow-lg"></div>
                                </div>
                                <p class="absolute top-2 left-0 right-0 text-center text-xs font-medium text-white drop-shadow">
                                    Point at VIN barcode — windshield or door jamb
                                </p>
                                <button @click="stopScan(); $refs.photoInput.click()" type="button"
                                        class="absolute bottom-2 left-2 rounded-full bg-blue-600/80 px-3 py-1.5 text-xs font-semibold text-white">
                                    Take Photo Instead
                                </button>
                                <button @click="stopScan()" type="button"
                                        class="absolute bottom-2 right-2 rounded-full bg-black/60 px-3 py-1.5 text-xs font-semibold text-white">
                                    Cancel
                                </button>
                            </div>

                            {{-- Camera error --}}
                            <p x-show="error" x-cloak x-text="error" class="mb-2 text-xs text-red-600"></p>

                            {{-- Manual VIN input (always visible as fallback) --}}
                            <div class="flex gap-2">
                                <input wire:model.blur="vVin" x-on:change="decodeVin($event.target.value)"
                                       type="text" maxlength="17" placeholder="Or type VIN manually…"
                                       class="flex-1 rounded-lg border-slate-300 font-mono text-sm shadow-sm uppercase focus:border-blue-500 focus:ring-blue-500" />
                                <span x-show="decoding" class="flex items-center px-2 text-xs text-slate-500">
                                    <svg class="h-4 w-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Decoding…
                                </span>
                            </div>
                            @error('vVin') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Year *</label>
                                <input wire:model="vYear" type="number" min="1990" max="2030" placeholder="2020"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('vYear') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Make *</label>
                                <input wire:model="vMake" type="text" placeholder="Toyota"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('vMake') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Model *</label>
                                <input wire:model="vModel" type="text" placeholder="Camry"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('vModel') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Color</label>
                                <input wire:model="vColor" type="text" placeholder="Silver"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Plate</label>
                                <input wire:model="vPlate" type="text" placeholder="ABC-1234"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm uppercase shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>

                        @if($this->customerVehicles->isNotEmpty())
                            <button wire:click="$set('creatingNewVehicle', false)" type="button"
                                    class="text-xs text-slate-500 hover:text-slate-700">← Back to existing vehicles</button>
                        @endif
                    </div>
                @else
                    <button wire:click="startNewVehicle" type="button"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + Add new vehicle
                    </button>
                @endif

                @error('vehicle_id') <p class="mt-2 text-sm text-red-600">Please select or add a vehicle.</p> @enderror
            @endif

            {{-- ── Step 4: Job Details ──────────────────────────────────────── --}}
            @if($step === 4)
                <h2 class="mb-4 text-lg font-semibold text-slate-800">Job Details</h2>

                <div class="space-y-4">
                    {{-- Location --}}
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

                    @if($this->isInsurance)
                        <div class="rounded-lg border border-blue-100 bg-blue-50/50 p-4 space-y-3">
                            <p class="text-sm font-semibold text-slate-700">Insurance Information</p>

                            <div>
                                <label class="block text-xs font-medium text-slate-600">Insurance Company</label>
                                <select wire:model="insurance_company_id"
                                        class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select company…</option>
                                    @foreach($this->insuranceCompanies as $ic)
                                        <option value="{{ $ic->id }}">{{ $ic->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Claim Number</label>
                                    <input wire:model="claim_number" type="text" placeholder="SF-123456"
                                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Policy Number</label>
                                    <input wire:model="policy_number" type="text"
                                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Adjuster Name</label>
                                    <input wire:model="adjuster_name" type="text"
                                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Adjuster Phone</label>
                                    <input wire:model="adjuster_phone" type="tel" inputmode="tel"
                                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Deductible</label>
                                    <input wire:model="deductible" type="number" step="0.01" placeholder="500.00"
                                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 pt-1">
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input wire:model="insurance_pre_inspected" type="checkbox"
                                           class="rounded border-slate-300 text-blue-600" />
                                    Insurance has already inspected the vehicle
                                    <span class="text-xs text-slate-400">(we'll submit a supplement)</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input wire:model="has_rental_coverage" type="checkbox"
                                           class="rounded border-slate-300 text-blue-600" />
                                    Customer has rental coverage
                                </label>
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea wire:model="notes" rows="3" placeholder="Any notes about this job…"
                                  class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </div>
            @endif

        </div>

        {{-- Footer navigation (hidden on step 1 — card click advances) --}}
        @if($step > 1)
        <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
            <button wire:click="prevStep" type="button"
                    class="text-sm text-slate-500 hover:text-slate-700 font-medium">← Back</button>

            @if($step < $totalSteps)
                <button wire:click="nextStep" type="button"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    Continue →
                </button>
            @else
                <button wire:click="create" type="button"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-70 transition-colors">
                    <span wire:loading wire:target="create">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                    Create Work Order
                </button>
            @endif
        </div>
        @endif
    </div>
</div>
