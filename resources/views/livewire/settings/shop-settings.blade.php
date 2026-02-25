<div class="space-y-6">

    {{-- Saved banner --}}
    @if($saved)
        <div x-data x-init="setTimeout(() => $wire.set('saved', false), 3000)"
             class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            Settings saved successfully.
        </div>
    @endif

    {{-- Shop Info --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-slate-700">Shop Information</h3>
        </div>
        <div class="p-5 space-y-4">

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Shop Name *</label>
                    <input wire:model="name" type="text"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                    <input wire:model="phone" type="tel"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input wire:model="email" type="email"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Street Address</label>
                    <input wire:model="address" type="text"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">City</label>
                    <input wire:model="city" type="text"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">State</label>
                        <input wire:model="state" type="text" maxlength="2" placeholder="TX"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">ZIP</label>
                        <input wire:model="zip" type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>
            </div>

            {{-- Remit address --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Remit Address
                    <span class="text-slate-400 font-normal">(appears on invoices)</span>
                </label>
                <textarea wire:model="remitAddress" rows="3"
                          placeholder="Full mailing address for payments..."
                          class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
        </div>
    </div>

    {{-- Logo --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-slate-700">Logo</h3>
        </div>
        <div class="p-5 flex items-start gap-5">
            {{-- Current logo / placeholder --}}
            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center">
                @if($this->tenant->logo_path)
                    <img src="{{ Storage::disk('public')->url($this->tenant->logo_path) }}"
                         alt="Logo" class="h-full w-full object-contain" />
                @else
                    <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                @endif
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-700 mb-1">Upload Logo</label>
                <input wire:model="logo" type="file" accept="image/*"
                       class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100" />
                <p class="mt-1 text-xs text-slate-400">PNG, JPG, SVG up to 4 MB</p>
                @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @if($logo)
                    <p class="mt-1 text-xs text-blue-600">New logo ready — save to apply.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Commission Defaults --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-slate-700">Commission Defaults</h3>
        </div>
        <div class="p-5">
            <div class="max-w-xs">
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Sales Advisor Per-Car Bonus
                    <span class="text-slate-400 font-normal">(default $100)</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-2.5 flex items-center text-slate-400 text-sm">$</span>
                    <input wire:model="advisorPerCarBonus" type="number" step="0.01" min="0"
                           class="w-full rounded-lg border-slate-300 pl-6 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <p class="mt-1 text-xs text-slate-400">Applied to each advisor's commission. Can be overridden per-advisor in Staff settings.</p>
                @error('advisorPerCarBonus') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- Save button --}}
    <div class="flex justify-end">
        <button wire:click="save"
                wire:loading.attr="disabled"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors disabled:opacity-50">
            <span wire:loading.remove wire:target="save">Save Settings</span>
            <span wire:loading wire:target="save">Saving…</span>
        </button>
    </div>

</div>
