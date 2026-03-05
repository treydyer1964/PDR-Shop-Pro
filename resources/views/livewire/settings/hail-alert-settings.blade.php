<div class="space-y-6 max-w-2xl">

    {{-- Saved banner --}}
    @if($savedMessage)
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ $savedMessage }}
        </div>
    @endif
    @if($testSent)
        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
            Test alert sent to <strong>{{ auth()->user()->email }}</strong>. Check your inbox.
        </div>
    @endif

    {{-- Home Base --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-1 text-sm font-semibold text-slate-800">Home Base</h3>
        <p class="mb-4 text-xs text-slate-500">Hail events within your radius will trigger alerts.</p>

        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Shop Address</label>
                <div class="flex gap-2">
                    <input wire:model="homeAddress"
                           type="text"
                           placeholder="123 Main St, Abilene, TX 79601"
                           class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    <button wire:click="geocodeAddress"
                            wire:loading.attr="disabled"
                            class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 transition-colors whitespace-nowrap">
                        <span wire:loading.remove wire:target="geocodeAddress">Locate</span>
                        <span wire:loading wire:target="geocodeAddress">...</span>
                    </button>
                </div>
                @if($geocodeError)
                    <p class="mt-1 text-xs text-red-600">{{ $geocodeError }}</p>
                @endif
            </div>

            @if($this->hasHomeBase)
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Latitude</label>
                        <input wire:model="homeLat" type="text"
                               class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm text-slate-600" readonly />
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Longitude</label>
                        <input wire:model="homeLng" type="text"
                               class="w-full rounded-lg border-slate-200 bg-slate-50 text-sm text-slate-600" readonly />
                    </div>
                </div>
            @else
                <p class="text-xs text-amber-600">
                    ⚠ No location set — enter your shop address and click Locate.
                </p>
            @endif
        </div>
    </div>

    {{-- Alert Thresholds --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-slate-800">Alert Thresholds</h3>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Radius</label>
                <select wire:model="radiusMiles"
                        class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="50">50 miles</option>
                    <option value="75">75 miles</option>
                    <option value="100">100 miles</option>
                    <option value="150">150 miles</option>
                    <option value="200">200 miles</option>
                    <option value="250">250 miles</option>
                    <option value="300">300 miles</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Min Hail Size</label>
                <select wire:model="minSizeInches"
                        class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="0.75">0.75" (Penny)</option>
                    <option value="1.00">1.00" (Quarter)</option>
                    <option value="1.50">1.50" (Ping Pong)</option>
                    <option value="1.75">1.75" (Golf Ball)</option>
                    <option value="2.00">2.00" (Egg)</option>
                    <option value="2.50">2.50" (Baseball)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Alert Cooldown</label>
                <select wire:model="alertCooldownHours"
                        class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="1">1 hour</option>
                    <option value="2">2 hours</option>
                    <option value="4">4 hours</option>
                    <option value="6">6 hours</option>
                    <option value="12">12 hours</option>
                    <option value="24">24 hours</option>
                </select>
                <p class="mt-1 text-xs text-slate-400">Won't re-alert the same event within this window.</p>
            </div>
        </div>
    </div>

    {{-- Delivery Channels --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-slate-800">Delivery Channels</h3>
        <p class="mb-3 text-xs text-slate-500">Alerts sent to all Owners, Bookkeepers, and Sales Managers on your account.</p>

        <div class="space-y-3">
            {{-- Email --}}
            <label class="flex cursor-pointer items-center justify-between gap-4">
                <div>
                    <span class="text-sm font-medium text-slate-700">Email alerts</span>
                    <p class="text-xs text-slate-400">Sends to all analytics-level users on your account</p>
                </div>
                <button wire:click="$toggle('emailAlerts')"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $emailAlerts ? 'bg-blue-600' : 'bg-slate-200' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $emailAlerts ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </label>

            {{-- SMS (disabled until Phase 15) --}}
            <label class="flex items-center justify-between gap-4 opacity-50">
                <div>
                    <span class="text-sm font-medium text-slate-700">SMS alerts</span>
                    <p class="text-xs text-slate-400">Coming soon — requires Twilio setup (Phase 15)</p>
                </div>
                <button disabled
                        class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-200 cursor-not-allowed">
                    <span class="inline-block h-4 w-4 transform translate-x-1 rounded-full bg-white shadow"></span>
                </button>
            </label>
        </div>
    </div>

    {{-- Active toggle + actions --}}
    <div class="flex flex-wrap items-center gap-3">
        <label class="flex items-center gap-2 cursor-pointer">
            <button wire:click="$toggle('active')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $active ? 'bg-green-500' : 'bg-slate-200' }}">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $active ? 'translate-x-6' : 'translate-x-1' }}"></span>
            </button>
            <span class="text-sm font-medium text-slate-700">Alerts {{ $active ? 'enabled' : 'paused' }}</span>
        </label>

        <button wire:click="save"
                class="ml-auto rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">
            Save Settings
        </button>

        <button wire:click="sendTestAlert"
                wire:confirm="Send a test hail alert to your email?"
                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
            Send Test Alert
        </button>
    </div>

    {{-- Alert Log --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-slate-700">Recent Alerts</h3>
        </div>

        @if(empty($this->recentAlerts))
            <div class="px-5 py-8 text-center text-sm text-slate-400">
                No alerts sent yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Triggered</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Size</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Location</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Method</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Recipient</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($this->recentAlerts as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5 text-slate-600 whitespace-nowrap">{{ $log['triggered_at'] }}</td>
                                <td class="px-4 py-2.5 font-medium text-slate-800">
                                    {{ $log['size'] !== null ? $log['size'] . '"' : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $log['location'] ?: '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                        {{ $log['delivery_method'] === 'email' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $log['delivery_method'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-500 text-xs">{{ $log['recipient'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
