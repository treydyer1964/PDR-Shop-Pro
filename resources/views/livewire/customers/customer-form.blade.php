<div>
    <form wire:submit="save" class="space-y-5">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {{-- First Name --}}
            <div>
                <label for="first_name" class="block text-sm font-medium text-slate-700">First Name <span class="text-red-500">*</span></label>
                <input wire:model="first_name" id="first_name" type="text" autocomplete="given-name"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="John" />
                @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Last Name --}}
            <div>
                <label for="last_name" class="block text-sm font-medium text-slate-700">Last Name <span class="text-red-500">*</span></label>
                <input wire:model="last_name" id="last_name" type="text" autocomplete="family-name"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="Smith" />
                @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
            <input wire:model="phone" id="phone" type="tel" autocomplete="tel" inputmode="tel"
                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="(555) 555-5555" />
            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" inputmode="email"
                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="john@example.com" />
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Address --}}
        <div>
            <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
            <input wire:model="address" id="address" type="text" autocomplete="street-address"
                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="123 Main St" />
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="col-span-2 sm:col-span-2">
                <label for="city" class="block text-sm font-medium text-slate-700">City</label>
                <input wire:model="city" id="city" type="text" autocomplete="address-level2"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
            </div>
            <div>
                <label for="state" class="block text-sm font-medium text-slate-700">State</label>
                <input wire:model="state" id="state" type="text" autocomplete="address-level1" maxlength="2"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm uppercase"
                    placeholder="TX" />
            </div>
            <div>
                <label for="zip" class="block text-sm font-medium text-slate-700">ZIP</label>
                <input wire:model="zip" id="zip" type="text" autocomplete="postal-code" inputmode="numeric"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="79601" />
            </div>
        </div>

        {{-- ID / License --}}
        <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4 space-y-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">ID &amp; License <span class="normal-case text-slate-400 font-normal">(needed for rental agreements)</span></p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="birthdate" class="block text-sm font-medium text-slate-700">Date of Birth</label>
                    <input wire:model="birthdate" id="birthdate" type="date"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                    @error('birthdate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="drivers_license" class="block text-sm font-medium text-slate-700">Driver's License #</label>
                    <input wire:model="drivers_license" id="drivers_license" type="text" autocomplete="off"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm uppercase"
                        placeholder="e.g. 12345678" />
                    @error('drivers_license') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="drivers_license_state" class="block text-sm font-medium text-slate-700">DL State</label>
                    <select wire:model="drivers_license_state" id="drivers_license_state"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">—</option>
                        @foreach(['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'] as $abbr)
                            <option value="{{ $abbr }}">{{ $abbr }}</option>
                        @endforeach
                    </select>
                    @error('drivers_license_state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
            <textarea wire:model="notes" id="notes" rows="3"
                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="Any relevant notes about this customer…"></textarea>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between gap-3 pt-2">
            <a href="{{ route('customers.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
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
                {{ $customer?->exists ? 'Save Changes' : 'Add Customer' }}
            </button>
        </div>
    </form>
</div>
