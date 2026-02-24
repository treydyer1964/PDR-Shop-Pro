<div>
    <form wire:submit="save" class="space-y-6">

        {{-- ── VIN SCANNER SECTION ──────────────────────────────────────────── --}}
        <div
            x-data="vinScanner()"
            x-on:vin-scanned.window="
                $wire.receiveScanResult($event.detail.vin);
                stopScan();
            "
            class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200"
        >
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Vehicle Identification Number (VIN)
            </label>

            {{-- VIN text input + scan button row --}}
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input
                        wire:model="vin"
                        wire:change="decodeVin"
                        id="vin"
                        type="text"
                        inputmode="text"
                        maxlength="17"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        placeholder="17-character VIN"
                        class="block w-full rounded-lg border-slate-300 pr-10 font-mono tracking-widest shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm uppercase"
                        x-bind:disabled="scanning"
                    />
                    {{-- Checkmark when decoded --}}
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

                {{-- Camera scan button --}}
                <button
                    type="button"
                    @click="scanning ? stopScan() : startScan()"
                    :class="scanning ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
                    class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-2.5 text-sm font-semibold text-white transition-colors"
                    title="Scan VIN barcode with camera"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
                    {{-- Scan guide overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-4/5 border-2 border-blue-400 rounded opacity-70" style="height: 60px">
                            <div class="absolute top-0 left-0 w-4 h-4 border-t-4 border-l-4 border-blue-400 rounded-tl"></div>
                            <div class="absolute top-0 right-0 w-4 h-4 border-t-4 border-r-4 border-blue-400 rounded-tr"></div>
                            <div class="absolute bottom-0 left-0 w-4 h-4 border-b-4 border-l-4 border-blue-400 rounded-bl"></div>
                            <div class="absolute bottom-0 right-0 w-4 h-4 border-b-4 border-r-4 border-blue-400 rounded-br"></div>
                        </div>
                    </div>
                </div>
                <p class="py-2 text-center text-xs text-slate-300">
                    Point camera at the VIN barcode on the windshield or door jamb
                </p>
            </div>

            {{-- Camera error --}}
            <div x-show="error" x-transition class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700" style="display:none">
                <span x-text="error"></span>
            </div>

            {{-- Server-side VIN error --}}
            @if($vinError)
                <p class="mt-2 text-xs text-red-600">{{ $vinError }}</p>
            @endif
            @error('vin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            {{-- Photo fallback (OpenAI Vision) --}}
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-slate-400">Can't scan?</span>
                <label
                    wire:loading.class="opacity-50 pointer-events-none"
                    wire:target="vinPhoto"
                    class="cursor-pointer text-xs font-medium text-blue-600 hover:text-blue-800 underline"
                >
                    <span wire:loading wire:target="vinPhoto">Analyzing photo…</span>
                    <span wire:loading.remove wire:target="vinPhoto">Take/upload a photo</span>
                    <input wire:model="vinPhoto" type="file" accept="image/*" capture="environment" class="sr-only" />
                </label>
                @if($vinPhotoProcessing)
                    <svg class="h-4 w-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                @endif
            </div>
        </div>

        {{-- ── DECODED VEHICLE INFO ──────────────────────────────────────────── --}}
        @if($vinDecoded || $vehicle?->exists)
        <div
            x-data
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="rounded-xl bg-green-50 ring-1 ring-green-200 p-4"
        >
            <p class="text-xs font-semibold text-green-700 mb-3 flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                VIN Decoded — verify and edit if needed
            </p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Year</label>
                    <input wire:model="year" type="number" inputmode="numeric"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Make</label>
                    <input wire:model="make" type="text"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Model</label>
                    <input wire:model="model" type="text"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Trim</label>
                    <input wire:model="trim" type="text"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Body Style</label>
                    <input wire:model="body_style" type="text"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Drive Type</label>
                    <input wire:model="drive_type" type="text"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Engine</label>
                    <input wire:model="engine" type="text"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Color</label>
                    <input wire:model="color" type="text" placeholder="e.g. Silver"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">License Plate</label>
                    <input wire:model="plate" type="text" class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase" />
                </div>
            </div>
        </div>
        @else
        {{-- Manual entry fields shown when VIN not decoded --}}
        <div class="rounded-xl bg-white p-4 ring-1 ring-slate-200">
            <p class="text-xs font-medium text-slate-500 mb-3">Or enter vehicle details manually:</p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Year</label>
                    <input wire:model="year" type="number" inputmode="numeric" placeholder="{{ date('Y') }}"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Make</label>
                    <input wire:model="make" type="text" placeholder="Ford"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Model</label>
                    <input wire:model="model" type="text" placeholder="F-150"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Color</label>
                    <input wire:model="color" type="text" placeholder="Silver"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">License Plate</label>
                    <input wire:model="plate" type="text"
                        class="mt-0.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase" />
                </div>
            </div>
        </div>
        @endif

        {{-- Notes --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
            <textarea wire:model="notes" id="notes" rows="2"
                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                placeholder="Any notes about this vehicle…"></textarea>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between gap-3 pt-1">
            <a href="{{ route('customers.show', $customer) }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
            <button type="submit"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-70"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-70 transition-colors">
                <span wire:loading wire:target="save">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </span>
                {{ $vehicle?->exists ? 'Save Changes' : 'Add Vehicle' }}
            </button>
        </div>
    </form>
</div>
