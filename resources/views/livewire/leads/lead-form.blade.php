<div class="mx-auto max-w-2xl space-y-6">
    <form wire:submit="save" class="space-y-6">

        {{-- Address (with GPS) — top priority --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm"
             x-data="{ gpsLoading: false, gpsError: '' }"
             x-init="
                @if($lat && $lng && !$address)
                    $nextTick(async () => {
                        gpsLoading = true;
                        try {
                            const r = await fetch('https://nominatim.openstreetmap.org/reverse?lat={{ $lat }}&lon={{ $lng }}&format=json');
                            const d = await r.json();
                            const addr = d.address || {};
                            const streetNum = addr.house_number ?? '';
                            const street    = addr.road ?? '';
                            $wire.set('address', (streetNum + ' ' + street).trim());
                            $wire.set('city',    addr.city ?? addr.town ?? addr.village ?? '');
                            $wire.set('state',   (addr.state_code ?? addr.ISO3166_2_lvl4 ?? '').replace(/^US-/, '').substring(0, 2).toUpperCase());
                            $wire.set('zip',     addr.postcode ?? '');
                        } catch(e) {}
                        gpsLoading = false;
                    });
                @endif
             ">
            <div class="border-b border-slate-100 px-5 py-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Address</h3>
                <div>
                    <button type="button"
                            @click="
                                gpsError = '';
                                if (!navigator.geolocation) { gpsError = 'Geolocation not supported.'; return; }
                                gpsLoading = true;
                                navigator.geolocation.getCurrentPosition(
                                    async (pos) => {
                                        try {
                                            const r = await fetch('https://nominatim.openstreetmap.org/reverse?lat=' + pos.coords.latitude + '&lon=' + pos.coords.longitude + '&format=json');
                                            const d = await r.json();
                                            const addr = d.address || {};
                                            const streetNum = addr.house_number ?? '';
                                            const street    = addr.road ?? '';
                                            $wire.set('address', (streetNum + ' ' + street).trim());
                                            $wire.set('city',    addr.city ?? addr.town ?? addr.village ?? '');
                                            $wire.set('state',   (addr.state_code ?? addr.ISO3166_2_lvl4 ?? '').replace(/^US-/, '').substring(0, 2).toUpperCase());
                                            $wire.set('zip',     addr.postcode ?? '');
                                            $wire.set('lat',     String(pos.coords.latitude));
                                            $wire.set('lng',     String(pos.coords.longitude));
                                        } catch(e) {
                                            gpsError = 'Could not get address.';
                                        }
                                        gpsLoading = false;
                                    },
                                    () => { gpsError = 'Location denied.'; gpsLoading = false; },
                                    { enableHighAccuracy: true, timeout: 10000 }
                                );
                            "
                            :disabled="gpsLoading"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition-colors">
                        <svg x-show="!gpsLoading" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <svg x-show="gpsLoading" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="gpsLoading ? 'Getting location…' : 'Use My Location'"></span>
                    </button>
                    <p x-show="gpsError" x-text="gpsError" class="mt-1 text-xs text-red-600"></p>
                </div>
            </div>
            <div class="p-5 space-y-4">

                @if($lat && $lng)
                    <p class="text-xs text-blue-600 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        Location pinned ({{ $lat }}, {{ $lng }})
                        <span x-show="gpsLoading" class="text-slate-400">— looking up address…</span>
                    </p>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700">Street Address</label>
                    <input wire:model="address" type="text"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-slate-700">City</label>
                        <input wire:model="city" type="text"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">State</label>
                        <input wire:model="state" type="text" maxlength="2" placeholder="TX"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm uppercase shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">ZIP</label>
                        <input wire:model="zip" type="text" inputmode="numeric"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>

            </div>
        </div>

        {{-- Status + Damage Level --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Assessment</h3>
            </div>
            <div class="p-5 space-y-4">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Status</label>
                        <select wire:model="status"
                                class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($this->statuses as $s)
                                <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Damage Level</label>
                        <select wire:model="damage_level"
                                class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Unknown</option>
                            <option value="no_damage">No Damage</option>
                            <option value="light">Light</option>
                            <option value="medium">Medium</option>
                            <option value="severe">Severe</option>
                            <option value="smoked">Smoked</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Notes</label>
                    <textarea wire:model="notes" rows="3" placeholder="Damage notes, vehicle condition, customer concerns…"
                              class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

            </div>
        </div>

        {{-- Vehicle & Job Details --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Vehicle & Job</h3>
            </div>
            <div class="p-5 space-y-4">

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Year</label>
                        <input wire:model="vehicle_year" type="text" inputmode="numeric" maxlength="4" placeholder="2021"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Make</label>
                        <input wire:model="vehicle_make" type="text" placeholder="Toyota"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Model</label>
                        <input wire:model="vehicle_model" type="text" placeholder="Camry"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Job Type Interest</label>
                        <select wire:model="job_type_interest"
                                class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="insurance">Insurance Claim</option>
                            <option value="customer_pay">Customer Pay</option>
                            <option value="wholesale">Wholesale</option>
                            <option value="">Unknown / Not discussed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Source</label>
                        <select wire:model="source"
                                class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($this->sources as $s)
                                <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Assigned Rep --}}
                @if(auth()->user()->isFieldStaff())
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Assigned Rep</label>
                        <p class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                            {{ auth()->user()->name }}
                            <span class="ml-1 text-xs text-slate-400">(you)</span>
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Assigned Rep</label>
                            <select wire:model="assigned_to"
                                    class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Unassigned</option>
                                @foreach($this->reps as $rep)
                                    <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if(auth()->user()->canManageTerritories() && $this->territories->isNotEmpty())
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Territory</label>
                            <select wire:model="territory_id"
                                    class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">No territory</option>
                                @foreach($this->territories as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                @endif

                @if($this->stormEvents->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-slate-700">Storm / Event</label>
                    <select wire:model="storm_event_id"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">None</option>
                        @foreach($this->stormEvents as $storm)
                            <option value="{{ $storm->id }}">
                                {{ $storm->name }}{{ $storm->city ? ' — ' . $storm->city . ($storm->state ? ', ' . $storm->state : '') : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

            </div>
        </div>

        {{-- Contact Info (optional) --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Contact Info <span class="ml-1 text-xs font-normal text-slate-400">(optional — fill in later)</span></h3>
            </div>
            <div class="p-5 space-y-4">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">First Name</label>
                        <input wire:model="first_name" type="text" placeholder="First name"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Last Name</label>
                        <input wire:model="last_name" type="text" placeholder="Last name"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Phone</label>
                        <input wire:model="phone" type="tel" inputmode="tel" placeholder="555-000-0000"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email</label>
                        <input wire:model="email" type="email" inputmode="email"
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>

            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ $lead && $lead->exists ? route('leads.show', $lead) : route('leads.index') }}" wire:navigate
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
                {{ $lead && $lead->exists ? 'Save Changes' : 'Create Lead' }}
            </button>
        </div>

    </form>
</div>
