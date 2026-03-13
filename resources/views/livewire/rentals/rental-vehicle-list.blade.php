<div class="space-y-4">

    {{-- Toolbar --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $this->vehicles->count() }} vehicle{{ $this->vehicles->count() !== 1 ? 's' : '' }} in fleet</p>
        @if(! $showForm)
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                + Add Vehicle
            </button>
        @endif
    </div>

    {{-- Add / Edit form --}}
    @if($showForm)
        <div
            x-data="vinScanner()"
            x-on:vin-scanned.window="
                $wire.receiveScanResult($event.detail.vin);
                stopScan();
            "
            class="rounded-xl border border-blue-200 bg-blue-50/40 p-5 space-y-4"
        >
            <h3 class="text-sm font-semibold text-slate-700">{{ $editingId ? 'Edit Vehicle' : 'New Fleet Vehicle' }}</h3>

            {{-- ── VIN SCANNER ──────────────────────────────────────────────────── --}}
            <div class="rounded-lg bg-white p-4 ring-1 ring-slate-200">
                <label class="block text-xs font-semibold text-slate-700 mb-2">VIN</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input
                            wire:model="vin"
                            wire:change="decodeVin"
                            type="text"
                            inputmode="text"
                            maxlength="17"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="characters"
                            spellcheck="false"
                            placeholder="17-character VIN"
                            class="block w-full rounded-lg border-slate-300 pr-10 font-mono tracking-widest text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase"
                            x-bind:disabled="scanning"
                        />
                        @if($vinDecoded)
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        @elseif($vinDecoding)
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-4 w-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <button type="button"
                        @click="scanning ? stopScan() : startScan()"
                        :class="scanning ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
                        class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-white transition-colors"
                        title="Scan VIN barcode with camera">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                        </svg>
                        <span x-text="scanning ? 'Stop' : 'Scan'"></span>
                    </button>
                </div>

                {{-- Camera viewfinder --}}
                <div x-show="scanning" x-transition class="mt-3 overflow-hidden rounded-lg bg-black" style="display:none">
                    <div class="relative aspect-video w-full">
                        <video x-ref="videoEl" autoplay playsinline muted class="h-full w-full object-cover"></video>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-4/5 border-2 border-blue-400 rounded opacity-70" style="height: 60px">
                                <div class="absolute top-0 left-0 w-4 h-4 border-t-4 border-l-4 border-blue-400 rounded-tl"></div>
                                <div class="absolute top-0 right-0 w-4 h-4 border-t-4 border-r-4 border-blue-400 rounded-tr"></div>
                                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-4 border-l-4 border-blue-400 rounded-bl"></div>
                                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-4 border-r-4 border-blue-400 rounded-br"></div>
                            </div>
                        </div>
                    </div>
                    <p class="py-2 text-center text-xs text-slate-300">Point camera at the VIN barcode</p>
                </div>

                <div x-show="error" x-transition class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700" style="display:none">
                    <span x-text="error"></span>
                </div>

                @if($vinError)
                    <p class="mt-2 text-xs text-red-600">{{ $vinError }}</p>
                @endif
                @error('vin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-2 flex items-center gap-2">
                    <span class="text-xs text-slate-400">Can't scan?</span>
                    <label wire:loading.class="opacity-50 pointer-events-none" wire:target="vinPhoto"
                           class="cursor-pointer text-xs font-medium text-blue-600 hover:text-blue-800 underline">
                        <span wire:loading wire:target="vinPhoto">Analyzing photo…</span>
                        <span wire:loading.remove wire:target="vinPhoto">Take/upload a photo</span>
                        <input wire:model="vinPhoto" type="file" accept="image/*" capture="environment" class="sr-only" />
                    </label>
                </div>
            </div>

            {{-- ── VEHICLE FIELDS ──────────────────────────────────────────────── --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Display Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="e.g. 2022 Chevy Malibu – White"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Internal Daily Cost ($) <span class="text-red-500">*</span></label>
                    <input wire:model="dailyCost" type="number" step="0.01" min="0" placeholder="0.00"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('dailyCost') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Year</label>
                    <input wire:model="year" type="number" min="1900" max="2100" placeholder="YYYY"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Make</label>
                    <input wire:model="make" type="text" placeholder="e.g. Chevrolet"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Model</label>
                    <input wire:model="model" type="text" placeholder="e.g. Malibu"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Color</label>
                    <input wire:model="color" type="text" placeholder="e.g. White"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">License Plate</label>
                    <input wire:model="plateNumber" type="text" placeholder="e.g. ABC-1234"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase" />
                    @error('plateNumber') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Current Odometer (mi)</label>
                    <input wire:model="currentOdometer" type="number" min="0" placeholder="e.g. 45000"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('currentOdometer') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Oil Change Interval (mi)</label>
                    <input wire:model="serviceIntervalMiles" type="number" min="100" placeholder="3000"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('serviceIntervalMiles') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Warn When (mi before due)</label>
                    <input wire:model="serviceAlertThresholdMiles" type="number" min="0" placeholder="500"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('serviceAlertThresholdMiles') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                    <input wire:model="notes" type="text" placeholder="Optional…"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
            </div>

            <div class="flex gap-2">
                <button wire:click="save"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    {{ $editingId ? 'Save Changes' : 'Add Vehicle' }}
                </button>
                <button type="button" wire:click="cancel" class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Mark Serviced inline form --}}
    @if($showServiceForm)
        <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 space-y-3">
            <p class="text-sm font-semibold text-amber-800">Mark Oil Change Completed</p>
            <div class="max-w-xs">
                <label class="block text-xs font-medium text-slate-600 mb-1">Odometer at Service (mi) <span class="text-red-500">*</span></label>
                <input wire:model="servicedOdometer" type="number" min="0" placeholder="e.g. 47500"
                       class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                @error('servicedOdometer') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="markServiced"
                        class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition-colors">
                    Confirm Service
                </button>
                <button wire:click="cancelService" class="text-sm text-slate-500 hover:text-slate-700">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Vehicle list --}}
    @if($this->vehicles->isEmpty() && ! $showForm)
        <div class="rounded-xl border border-dashed border-slate-300 py-12 text-center text-sm text-slate-400">
            No fleet vehicles yet.
        </div>
    @else
        <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white shadow-sm">
            @foreach($this->vehicles as $v)
                @php
                    $svcStatus    = $v->serviceStatus();
                    $available    = $v->isAvailable();
                    $activeRental = $available ? null : $v->activeRental();
                @endphp
                <div @class(['px-5 py-4', 'opacity-60' => ! $v->active])>
                    <div class="flex items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-slate-800">{{ $v->displayName() }}</span>
                                {{-- Availability badge --}}
                                @if($v->active)
                                    @if($available)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                                            <svg class="h-2 w-2 fill-green-500" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3"/></svg>
                                            Available
                                        </span>
                                    @else
                                        @php
                                            $wo       = $activeRental?->workOrder;
                                            $customer = $wo?->customer;
                                            $label    = $customer ? $customer->full_name : ($wo ? 'WO #' . $wo->ro_number : 'Out');
                                        @endphp
                                        <a href="{{ $wo ? route('work-orders.show', $wo) : '#' }}"
                                           class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 hover:bg-red-200 transition-colors">
                                            <svg class="h-2 w-2 fill-red-500" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3"/></svg>
                                            Out — {{ $label }}
                                        </a>
                                    @endif
                                @endif
                                @if(! $v->active)
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">Inactive</span>
                                @endif
                                {{-- Service status badge --}}
                                @if($svcStatus === 'overdue')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                        Oil Change Overdue
                                    </span>
                                @elseif($svcStatus === 'due_soon')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                        Service Due Soon
                                    </span>
                                @elseif($svcStatus === 'ok')
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Oil OK</span>
                                @endif
                            </div>
                            <div class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-slate-500">
                                @if($v->plate_number) <span class="font-mono uppercase">{{ $v->plate_number }}</span> @endif
                                @if($v->color) <span>{{ $v->color }}</span> @endif
                                @if($v->vin) <span class="font-mono">{{ $v->vin }}</span> @endif
                                @if($v->current_odometer !== null)
                                    <span>{{ number_format($v->current_odometer) }} mi</span>
                                @endif
                                @if($svcStatus !== null && $v->milesToNextService() !== null)
                                    @php $miles = $v->milesToNextService(); @endphp
                                    <span @class(['text-red-600 font-medium' => $svcStatus === 'overdue', 'text-amber-600' => $svcStatus === 'due_soon'])>
                                        @if($miles <= 0)
                                            {{ number_format(abs($miles)) }} mi past due
                                        @else
                                            {{ number_format($miles) }} mi until service
                                        @endif
                                    </span>
                                @endif
                                @if($v->notes) <span class="truncate max-w-xs">{{ $v->notes }}</span> @endif
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            <span class="font-semibold text-slate-800">${{ number_format($v->internal_daily_cost, 2) }}</span>
                            <span class="text-xs text-slate-400">/day</span>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
                            @if(in_array($svcStatus, ['overdue', 'due_soon']))
                                <button wire:click="openServiceForm({{ $v->id }})"
                                        class="rounded border border-amber-400 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 hover:bg-amber-100 transition-colors">
                                    Mark Serviced
                                </button>
                            @endif
                            <button wire:click="openEdit({{ $v->id }})"
                                    class="rounded border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                Edit
                            </button>
                            <button wire:click="toggleActive({{ $v->id }})"
                                    class="rounded border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                {{ $v->active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
