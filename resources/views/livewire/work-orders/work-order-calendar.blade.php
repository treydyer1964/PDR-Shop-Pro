<div>
    {{-- Calendar toolbar --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

        {{-- Navigation --}}
        <div class="flex items-center gap-2">
            <button wire:click="prev"
                    class="rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
            <button wire:click="next"
                    class="rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
            <h2 class="min-w-[160px] text-center text-base font-semibold text-slate-800">
                {{ $this->periodLabel }}
            </h2>
            <button wire:click="goToday"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                Today
            </button>
        </div>

        {{-- View toggle --}}
        <div class="flex overflow-hidden rounded-lg border border-slate-200 bg-white text-sm">
            <button wire:click="setCalView('month')"
                    @class([
                        'px-4 py-1.5 font-medium transition-colors',
                        'bg-slate-900 text-white' => $calView === 'month',
                        'text-slate-500 hover:bg-slate-50' => $calView !== 'month',
                    ])>
                Month
            </button>
            <button wire:click="setCalView('week')"
                    @class([
                        'px-4 py-1.5 font-medium transition-colors border-l border-slate-200',
                        'bg-slate-900 text-white' => $calView === 'week',
                        'text-slate-500 hover:bg-slate-50' => $calView !== 'week',
                    ])>
                Week
            </button>
        </div>
    </div>

    {{-- ── MONTH VIEW ──────────────────────────────────────────────────────────── --}}
    @if($calView === 'month')
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">

            {{-- Day-of-week header --}}
            <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                    <div class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ $day }}
                    </div>
                @endforeach
            </div>

            {{-- Calendar grid --}}
            <div class="grid grid-cols-7 divide-x divide-y divide-slate-100">
                @foreach($this->calendarWeeks as $week)
                    @foreach($week as $day)
                        @php
                            $dateStr   = $day->format('Y-m-d');
                            $isToday   = $day->isToday();
                            $inMonth   = $day->month === $month;
                            $dayEvents = $this->eventsByDate[$dateStr] ?? [];
                        @endphp
                        <div @class([
                            'min-h-[90px] p-1.5 flex flex-col gap-1',
                            'bg-white' => $inMonth,
                            'bg-slate-50/60' => ! $inMonth,
                        ])>
                            {{-- Date number --}}
                            <div class="flex justify-end">
                                <span @class([
                                    'inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium',
                                    'bg-blue-600 text-white' => $isToday,
                                    'text-slate-700' => ! $isToday && $inMonth,
                                    'text-slate-400' => ! $inMonth,
                                ])>
                                    {{ $day->day }}
                                </span>
                            </div>

                            {{-- Events (max 3 visible, then overflow) --}}
                            @foreach(array_slice($dayEvents, 0, 3) as $event)
                                <a href="{{ $event['url'] }}" wire:navigate
                                   class="block truncate rounded px-1.5 py-0.5 text-xs leading-tight hover:opacity-80 transition-opacity
                                          {{ $event['type'] === 'appointment' ? $event['badge_classes'] : 'bg-slate-100 text-slate-700' }}">
                                    @if($event['type'] === 'appointment')
                                        <span class="font-medium">{{ $event['time'] }}</span>
                                        {{ $event['label'] }}
                                        @if($event['sub']) · {{ $event['sub'] }}@endif
                                    @else
                                        <span class="font-mono font-medium">{{ $event['label'] }}</span>
                                        @if($event['sub']) {{ $event['sub'] }}@endif
                                    @endif
                                </a>
                            @endforeach

                            @if(count($dayEvents) > 3)
                                <span class="pl-1.5 text-xs text-slate-400">+{{ count($dayEvents) - 3 }} more</span>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── WEEK VIEW ───────────────────────────────────────────────────────────── --}}
    @if($calView === 'week')
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <div class="min-w-[560px]">

                {{-- Day headers --}}
                <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
                    @foreach($this->weekDays as $day)
                        @php $isToday = $day->isToday(); @endphp
                        <div @class([
                            'px-2 py-3 text-center border-r border-slate-100 last:border-r-0',
                            'bg-blue-50' => $isToday,
                        ])>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                {{ $day->format('D') }}
                            </p>
                            <p @class([
                                'mt-0.5 inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold mx-auto',
                                'bg-blue-600 text-white' => $isToday,
                                'text-slate-700' => ! $isToday,
                            ])>
                                {{ $day->day }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- Day columns --}}
                <div class="grid grid-cols-7 divide-x divide-slate-100">
                    @foreach($this->weekDays as $day)
                        @php
                            $dateStr   = $day->format('Y-m-d');
                            $isToday   = $day->isToday();
                            $dayEvents = $this->eventsByDate[$dateStr] ?? [];
                        @endphp
                        <div @class([
                            'min-h-[120px] p-2 flex flex-col gap-1.5',
                            'bg-blue-50/30' => $isToday,
                        ])>
                            @forelse($dayEvents as $event)
                                <a href="{{ $event['url'] }}" wire:navigate
                                   class="block rounded-lg p-2 text-xs hover:opacity-80 transition-opacity
                                          {{ $event['type'] === 'appointment' ? $event['badge_classes'] : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    @if($event['type'] === 'appointment')
                                        <p class="font-semibold">{{ $event['time'] }} · {{ $event['label'] }}</p>
                                        @if($event['sub'])
                                            <p class="mt-0.5 opacity-75">{{ $event['sub'] }}</p>
                                        @endif
                                    @else
                                        <p class="font-mono font-semibold">{{ $event['label'] }}</p>
                                        @if($event['sub'])
                                            <p class="mt-0.5 font-sans">{{ $event['sub'] }}</p>
                                        @endif
                                        @if($event['vehicle'])
                                            <p class="mt-0.5 opacity-60">{{ $event['vehicle'] }}</p>
                                        @endif
                                        <span class="mt-1 inline-flex rounded-full px-1.5 py-0.5 text-xs font-medium {{ $event['status_classes'] }}">
                                            {{ $event['status_label'] }}
                                        </span>
                                    @endif
                                </a>
                            @empty
                                <div class="flex-1"></div>
                            @endforelse
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- Legend --}}
        <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <span class="h-3 w-3 rounded bg-slate-200"></span> Expected Delivery (Work Order)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3 w-3 rounded bg-blue-200"></span> Drop-Off / Pick-Up
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3 w-3 rounded bg-green-200"></span> Inspection
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3 w-3 rounded bg-purple-200"></span> Supplement
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3 w-3 rounded bg-orange-200"></span> Delivery
            </span>
        </div>
    @endif

    {{-- Empty state --}}
    @if(collect($this->eventsByDate)->flatten(1)->isEmpty())
        <p class="mt-6 text-center text-sm text-slate-400">
            No work orders with expected delivery dates or appointments in this period.
        </p>
    @endif
</div>
