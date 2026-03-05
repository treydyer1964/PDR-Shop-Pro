<div>
    {{-- Toolbar --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <input wire:model.live="selectedDate"
               type="date"
               max="{{ now()->toDateString() }}"
               class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />

        <button wire:click="setToday"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition-colors">
            Today
        </button>
        <button wire:click="setYesterday"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition-colors">
            Yesterday
        </button>

        <div class="flex items-center gap-2 sm:ml-auto flex-wrap">
            <span class="text-sm text-slate-500">Min size:</span>
            <select wire:model.live="filterMinSize"
                    class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="0.5">0.5" (Marble)</option>
                <option value="0.75">0.75" (Penny)</option>
                <option value="1.0">1.0" (Quarter)</option>
                <option value="1.75">1.75" (Golf Ball)</option>
                <option value="2.5">2.5" (Baseball)</option>
            </select>

            {{-- Layer toggles --}}
            <div class="flex items-center gap-1.5">
                <button wire:click="toggleRadar"
                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium shadow-sm transition-colors
                               {{ $showRadar ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.75 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    Radar
                </button>
                <button wire:click="toggleWarnings"
                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium shadow-sm transition-colors
                               {{ $showWarnings ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    Warnings
                </button>
                <button wire:click="toggleMesh"
                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium shadow-sm transition-colors
                               {{ $showMesh ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c-.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                    </svg>
                    @if($this->meshSwathUrl)
                        MESH Swath
                    @else
                        Storm Areas
                    @endif
                </button>
            </div>
        </div>

        <div wire:loading class="flex items-center gap-1.5 text-sm text-blue-500">
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Loading...
        </div>
    </div>

    {{-- Main layout: sidebar + map --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[300px_1fr]">

        {{-- Event list sidebar --}}
        <div class="order-last lg:order-first space-y-3">

            {{-- Deploy success banner --}}
            @if($deployedStormEventId)
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-green-700">Storm Event created!</p>
                            <a href="{{ route('storm-events.show', $deployedStormEventId) }}"
                               class="mt-0.5 inline-flex items-center gap-1 text-xs text-green-600 underline hover:text-green-800">
                                View Storm Event &rarr;
                            </a>
                        </div>
                        <button wire:click="dismissDeployBanner"
                                class="text-green-400 hover:text-green-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Event feed --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-slate-700">
                        Events
                        @if($this->eventCount > 0)
                            <span class="ml-1 text-xs font-normal text-slate-400">({{ $this->eventCount }})</span>
                        @endif
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $selectedDate }}</p>
                </div>

                <div class="max-h-72 divide-y divide-slate-50 overflow-y-auto lg:max-h-[400px]">
                    @forelse($this->events as $event)
                        <div class="px-4 py-3">
                            {{-- Top row: size + location + fly-to --}}
                            <div class="flex cursor-pointer items-center justify-between gap-2"
                                 onclick="window.hailMapFlyTo({{ $event['lat'] }}, {{ $event['lng'] }})">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block h-3 w-3 flex-shrink-0 rounded-full"
                                          style="background:{{ $event['color'] }}"></span>
                                    <span class="font-semibold text-slate-800">{{ $event['maxSize'] }}"</span>
                                    <span class="inline-flex rounded-full px-1.5 py-0.5 text-xs font-medium {{ $event['badgeClass'] }}">
                                        {{ $event['sizeLabel'] }}
                                    </span>
                                </div>
                                <span class="text-xs text-slate-400">{{ $event['reportCount'] }} rpts</span>
                            </div>
                            @if($event['location'])
                                <div class="mt-0.5 pl-5 text-xs text-slate-500">{{ $event['location'] }}</div>
                            @endif

                            {{-- Watch action row --}}
                            <div class="mt-2 pl-5">
                                @if($event['watchStatus'] === 'activated')
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Deployed
                                        </span>
                                        @if($event['stormEventId'])
                                            <a href="{{ route('storm-events.show', $event['stormEventId']) }}"
                                               class="text-xs text-blue-500 hover:text-blue-700 hover:underline">
                                                View Storm &rarr;
                                            </a>
                                        @endif
                                    </div>

                                @elseif($event['watchStatus'] === 'watching')
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Watching
                                        </span>
                                        <button wire:click="passEvent({{ $event['id'] }})"
                                                class="rounded px-1.5 py-0.5 text-xs text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                            Pass
                                        </button>
                                        <button wire:click="activateEvent({{ $event['id'] }})"
                                                wire:confirm="Deploy this event and create a new Storm Event?"
                                                class="inline-flex items-center gap-1 rounded-md bg-green-600 px-2 py-0.5 text-xs font-semibold text-white hover:bg-green-700 transition-colors">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.82m5.84-2.56a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.82m2.56-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                            </svg>
                                            Deploy
                                        </button>
                                    </div>

                                @elseif($event['watchStatus'] === 'passed')
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">
                                            Passed
                                        </span>
                                        <button wire:click="watchEvent({{ $event['id'] }})"
                                                class="text-xs text-blue-500 hover:underline">
                                            Re-watch
                                        </button>
                                    </div>

                                @else
                                    {{-- No watch yet --}}
                                    <button wire:click="watchEvent({{ $event['id'] }})"
                                            class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-0.5 text-xs text-slate-500 shadow-sm hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Watch
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center text-sm text-slate-400">
                            No hail events for this date
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Size legend --}}
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Hail Size Key</p>
                <div class="space-y-1.5">
                    @foreach([
                        ['#ef4444', '2.5"+', 'Baseball+'],
                        ['#f97316', '1.75"–2.5"', 'Golf Ball'],
                        ['#eab308', '1.0"–1.75"', 'Quarter'],
                        ['#22c55e', '< 1.0"', 'Penny / Marble'],
                    ] as [$color, $range, $label])
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-3 w-3 flex-shrink-0 rounded-full" style="background:{{ $color }}"></span>
                            <span class="text-xs font-medium text-slate-700">{{ $range }}</span>
                            <span class="text-xs text-slate-400">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Home base info --}}
            @if($this->subscription)
                <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 shadow-sm">
                    <p class="text-xs font-semibold text-blue-700">Home Base</p>
                    <p class="mt-0.5 text-xs text-blue-600">{{ $this->subscription['address'] ?? 'Location set' }}</p>
                    <p class="mt-0.5 text-xs text-blue-500">{{ $this->subscription['radiusMiles'] }} mi radius shown on map</p>
                </div>
            @endif
        </div>

        {{-- Map --}}
        <div wire:key="hail-map-{{ $selectedDate }}-{{ $filterMinSize }}-{{ (int)$showRadar }}-{{ (int)$showWarnings }}-{{ (int)$showMesh }}"
             data-reports="{{ json_encode($this->reports) }}"
             data-events="{{ json_encode($this->events) }}"
             data-subscription="{{ json_encode($this->subscription) }}"
             data-selected-date="{{ $selectedDate }}"
             data-show-radar="{{ $showRadar ? '1' : '0' }}"
             data-show-warnings="{{ $showWarnings ? '1' : '0' }}"
             data-show-mesh="{{ $showMesh ? '1' : '0' }}"
             data-mesh-url="{{ $this->meshSwathUrl ?? '' }}"
             x-init="$nextTick(() => initHailMap($el))"
             id="hail-map-root">

            <div wire:ignore
                 class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
                 style="height: 65vh; min-height: 400px;">
                <div id="hail-map-container" class="h-full w-full"></div>
            </div>

            <p class="mt-2 text-xs text-slate-400">
                {{ $this->reportCount }} report{{ $this->reportCount !== 1 ? 's' : '' }} shown
                &middot; Hail: NOAA SPC
                @if($showRadar)
                    &middot; Radar: Iowa Environmental Mesonet / NEXRAD
                    @if($selectedDate !== now()->toDateString())
                        <span class="text-slate-300">(~23:00 UTC frame)</span>
                    @else
                        <span class="text-slate-300">(live)</span>
                    @endif
                @endif
                @if($showWarnings)
                    &middot; Warnings: NWS
                    @if($selectedDate !== now()->toDateString())
                        <span class="text-slate-300">(historical)</span>
                    @endif
                @endif
                @if($showMesh)
                    @if($this->meshSwathUrl)
                        &middot; MESH Swath: NOAA MRMS
                        <span class="text-slate-300">(daily max accumulation)</span>
                    @else
                        &middot; Storm Areas: estimated coverage from SPC clusters
                    @endif
                @endif
            </p>
        </div>
    </div>
</div>
