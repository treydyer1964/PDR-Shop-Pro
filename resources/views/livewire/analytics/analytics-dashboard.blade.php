<div class="space-y-4" x-data="{ tab: 'overview' }">

    {{-- ── Filter Bar ──────────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Preset buttons --}}
            <div class="flex rounded-lg border border-slate-200 overflow-hidden text-xs font-medium">
                @foreach(['this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'ytd' => 'YTD', 'custom' => 'Custom'] as $value => $label)
                    <button wire:click="$set('preset', '{{ $value }}')"
                            @class([
                                'px-3 py-2 transition-colors',
                                'bg-blue-600 text-white' => $preset === $value,
                                'text-slate-600 hover:bg-slate-50' => $preset !== $value,
                            ])>
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Custom date inputs --}}
            @if($preset === 'custom')
                <div class="flex items-center gap-2">
                    <input wire:model.live="dateFrom" type="date"
                           class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2">
                    <span class="text-xs text-slate-400">to</span>
                    <input wire:model.live="dateTo" type="date"
                           class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2">
                </div>
            @endif

            {{-- Location filter --}}
            @if($this->locations->count() > 1)
                <select wire:model.live="filterLocation"
                        class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2">
                    <option value="">All Locations</option>
                    @foreach($this->locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                    @endforeach
                </select>
            @endif

            {{-- Loading indicator --}}
            <div wire:loading class="text-xs text-slate-400 flex items-center gap-1">
                <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Loading…
            </div>
        </div>
    </div>

    {{-- ── Tab Navigation ───────────────────────────────────────────────────────── --}}
    <div class="flex gap-1 overflow-x-auto border-b border-slate-200 pb-0">
        @foreach(['overview' => 'Overview', 'cycle' => 'Cycle Time', 'insurance' => 'Insurance', 'staff' => 'Staff', 'rentals' => 'Rentals', 'workorders' => 'Work Orders'] as $t => $label)
            <button @click="tab = '{{ $t }}'"
                    :class="tab === '{{ $t }}' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                    class="whitespace-nowrap px-4 py-2.5 text-sm transition-colors shrink-0">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: OVERVIEW                                                               --}}
    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'overview'" style="display:none">

        {{-- Live KPI cards (no date filter) --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-4">
            @php $kpis = $this->kpis; @endphp

            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Open Jobs</p>
                <p class="mt-1.5 text-3xl font-bold text-slate-900">{{ $kpis['open_jobs'] }}</p>
                <p class="mt-0.5 text-xs text-slate-400">Active shop-wide</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">To Be Acquired</p>
                <p class="mt-1.5 text-3xl font-bold text-slate-900">{{ $kpis['to_be_acquired'] }}</p>
                <p class="mt-0.5 text-xs text-slate-400">Pending acquisition</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">On Hold</p>
                <p class="mt-1.5 text-3xl font-bold {{ $kpis['on_hold'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $kpis['on_hold'] }}</p>
                <p class="mt-0.5 text-xs text-slate-400">Paused, not progressing</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Unpaid Commissions</p>
                <p class="mt-1.5 text-3xl font-bold {{ $kpis['unpaid_commissions'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">${{ number_format($kpis['unpaid_commissions'], 0) }}</p>
                <p class="mt-0.5 text-xs text-slate-400">Pending pay run</p>
            </div>
        </div>

        {{-- Profit summary for date range --}}
        @php $profit = $this->profitOverview; @endphp
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 mb-4">
            <div class="px-5 py-3 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Profit Summary
                    <span class="font-normal text-slate-400 ml-1">({{ $profit['woCount'] }} vehicles in period)</span>
                </h3>
            </div>
            <div class="grid grid-cols-2 divide-y divide-slate-100 lg:grid-cols-5 lg:divide-y-0 lg:divide-x">
                @php
                    $profitStats = [
                        ['label' => 'Gross Revenue',  'value' => $profit['gross'],       'color' => 'text-slate-900'],
                        ['label' => 'Expenses',        'value' => $profit['expenses'],    'color' => 'text-red-600'],
                        ['label' => 'Net Profit',      'value' => $profit['net'],         'color' => 'text-emerald-600'],
                        ['label' => 'Collected',       'value' => $profit['paid'],        'color' => 'text-blue-600'],
                        ['label' => 'Outstanding',     'value' => $profit['outstanding'], 'color' => $profit['outstanding'] > 0 ? 'text-amber-600' : 'text-slate-900'],
                    ];
                @endphp
                @foreach($profitStats as $stat)
                    <div class="px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold {{ $stat['color'] }}">
                            ${{ number_format($stat['value'], 0) }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- By Job Type --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="px-5 py-3 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">By Job Type</h3>
            </div>
            <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-3 sm:divide-y-0 sm:divide-x">
                @foreach($profit['byType'] as $typeData)
                    <div class="px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-3">{{ $typeData['label'] }}</p>
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Vehicles</span>
                                <span class="font-semibold text-slate-800">{{ $typeData['count'] }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Gross</span>
                                <span class="font-semibold text-slate-800">${{ number_format($typeData['gross'], 0) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Expenses</span>
                                <span class="font-semibold text-red-600">(${{ number_format($typeData['expenses'], 0) }})</span>
                            </div>
                            <div class="flex justify-between text-sm border-t border-slate-100 pt-1.5">
                                <span class="text-slate-600 font-medium">Net</span>
                                <span class="font-bold text-emerald-600">${{ number_format($typeData['net'], 0) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: CYCLE TIME                                                             --}}
    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'cycle'" style="display:none">
        @php $ct = $this->cycleTime; @endphp

        @if($ct['avg_days'] === null)
            <div class="rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
                <p class="text-slate-500 text-sm">No delivered vehicles in this period yet.</p>
                <p class="text-slate-400 text-xs mt-1">Cycle time calculates once vehicles have been acquired and delivered.</p>
            </div>
        @else
            {{-- Hero stat --}}
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 mb-4 p-6 flex flex-wrap items-center gap-6">
                <div>
                    @php
                        $avgDays = $ct['avg_days'];
                        $ctColor = $avgDays <= 14 ? 'text-emerald-600' : ($avgDays <= 21 ? 'text-amber-500' : 'text-red-500');
                    @endphp
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-1">Avg Shop-to-Delivery</p>
                    <p class="text-5xl font-bold {{ $ctColor }}">{{ number_format($avgDays, 1) }}<span class="text-2xl font-normal text-slate-400 ml-1">days</span></p>
                    <p class="text-xs text-slate-400 mt-1">Based on {{ $ct['delivered_count'] }} delivered vehicle{{ $ct['delivered_count'] !== 1 ? 's' : '' }}</p>
                </div>
                @if($ct['avg_insurance_wait_days'] !== null)
                    <div class="border-l border-slate-200 pl-6">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-1">Avg Insurance Approval Wait</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ number_format($ct['avg_insurance_wait_days'], 1) }}<span class="text-lg font-normal text-slate-400 ml-1">days</span></p>
                        <p class="text-xs text-slate-400 mt-1">From estimate submitted to approved</p>
                    </div>
                @endif
            </div>

            {{-- Stage breakdown --}}
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="px-5 py-3 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-700">Time Spent Per Stage</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($ct['stages'] as $stage)
                        <div class="flex items-center gap-4 px-5 py-3">
                            <div class="w-40 shrink-0">
                                <span class="text-xs font-medium px-2 py-0.5 rounded {{ $stage['status']->badgeClasses() }}">
                                    {{ $stage['status']->label() }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-blue-400 transition-all"
                                         style="width: {{ $stage['bar_pct'] }}%"></div>
                                </div>
                            </div>
                            <div class="w-20 text-right shrink-0">
                                <span class="text-sm font-semibold text-slate-800">
                                    @if($stage['avg_days'] !== null && $stage['avg_days'] >= 1)
                                        {{ number_format($stage['avg_days'], 1) }}d
                                    @elseif($stage['avg_hours'] !== null)
                                        {{ number_format($stage['avg_hours'], 0) }}h
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                            <div class="w-12 text-right shrink-0">
                                <span class="text-xs text-slate-400">×{{ $stage['count'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: INSURANCE COMPANIES                                                    --}}
    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'insurance'" style="display:none">
        @php $insCompanies = $this->insuranceCompanies; @endphp

        @if($insCompanies->isEmpty())
            <div class="rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
                <p class="text-slate-500 text-sm">No insurance jobs in this period.</p>
            </div>
        @else
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700">Insurance Company Performance</h3>
                    <p class="text-xs text-slate-400">Sort by Approval Wait ↑ to prioritize intake during heavy periods</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                <th class="px-5 py-3 text-left">Company</th>
                                <th class="px-4 py-3 text-center">Jobs</th>
                                <th class="px-4 py-3 text-right">Avg Invoice</th>
                                <th class="px-4 py-3 text-right">Avg Net</th>
                                <th class="px-4 py-3 text-right">Avg Cycle</th>
                                <th class="px-4 py-3 text-right">Approval Wait</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($insCompanies as $row)
                                @php
                                    $wait = $row['avg_approval_wait_days'];
                                    $waitColor = $wait === null ? 'text-slate-400'
                                        : ($wait <= 5 ? 'text-emerald-600' : ($wait <= 10 ? 'text-amber-600' : 'text-red-600'));
                                    $waitBg = $wait === null ? ''
                                        : ($wait <= 5 ? 'bg-emerald-50' : ($wait <= 10 ? 'bg-amber-50' : 'bg-red-50'));
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-3 font-medium text-slate-800">
                                        {{ $row['company']?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $row['count'] }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600">
                                        {{ $row['avg_invoice'] !== null ? '$' . number_format($row['avg_invoice'], 0) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-emerald-700">
                                        {{ $row['avg_net'] !== null ? '$' . number_format($row['avg_net'], 0) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600">
                                        {{ $row['avg_cycle_days'] !== null ? number_format($row['avg_cycle_days'], 0) . 'd' : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $waitColor }} {{ $waitBg }}">
                                            {{ $wait !== null ? number_format($wait, 0) . 'd' : '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-2 border-t border-slate-100 bg-slate-50/50">
                    <p class="text-xs text-slate-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>≤5 days fast
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500 ml-3 mr-1"></span>6–10 days moderate
                        <span class="inline-block w-2 h-2 rounded-full bg-red-500 ml-3 mr-1"></span>&gt;10 days slow
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: STAFF PERFORMANCE                                                      --}}
    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'staff'" style="display:none">
        @php $staff = $this->staffPerformance; @endphp

        {{-- Sales Advisors --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-4">
            <div class="px-5 py-3 border-b border-slate-100 bg-violet-50">
                <h3 class="text-sm font-semibold text-violet-800">Sales Advisors</h3>
            </div>
            @if($staff['advisors']->isEmpty())
                <p class="px-5 py-4 text-sm text-slate-400">No advisor commissions in this period.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                <th class="px-5 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-center">Jobs</th>
                                <th class="px-4 py-3 text-right">Gross Revenue</th>
                                <th class="px-4 py-3 text-right">Net Revenue</th>
                                <th class="px-4 py-3 text-right">Commission</th>
                                <th class="px-4 py-3 text-right">Avg/Car</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($staff['advisors'] as $row)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $row['user']->name }}</td>
                                    <td class="px-4 py-3 text-center text-slate-700">{{ $row['job_count'] }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600">${{ number_format($row['gross_revenue'], 0) }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-700 font-semibold">${{ number_format($row['net_revenue'], 0) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800">${{ number_format($row['total_commission'], 0) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-500 text-xs">
                                        ${{ $row['job_count'] > 0 ? number_format($row['total_commission'] / $row['job_count'], 0) : '0' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- PDR Techs --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden mb-4">
            <div class="px-5 py-3 border-b border-slate-100 bg-cyan-50">
                <h3 class="text-sm font-semibold text-cyan-800">PDR Technicians</h3>
            </div>
            @if($staff['techs']->isEmpty())
                <p class="px-5 py-4 text-sm text-slate-400">No tech commissions in this period.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                <th class="px-5 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-center">Jobs</th>
                                <th class="px-4 py-3 text-right">Net Revenue Handled</th>
                                <th class="px-4 py-3 text-right">Commission</th>
                                <th class="px-4 py-3 text-right">Avg/Car</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($staff['techs'] as $row)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $row['user']->name }}</td>
                                    <td class="px-4 py-3 text-center text-slate-700">{{ $row['job_count'] }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-700 font-semibold">${{ number_format($row['net_revenue'], 0) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800">${{ number_format($row['total_commission'], 0) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-500 text-xs">
                                        ${{ $row['job_count'] > 0 ? number_format($row['total_commission'] / $row['job_count'], 0) : '0' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Other roles (R&I, Porter, etc.) --}}
        @if($staff['others']->isNotEmpty())
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-sm font-semibold text-slate-700">Other Roles</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                <th class="px-5 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Role</th>
                                <th class="px-4 py-3 text-center">Jobs</th>
                                <th class="px-4 py-3 text-right">Total Earnings</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($staff['others'] as $row)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $row['user']->name }}</td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs font-medium px-2 py-0.5 rounded {{ $row['role']->badgeClasses() }}">{{ $row['role']->label() }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-700">{{ $row['job_count'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800">${{ number_format($row['total_commission'], 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: RENTAL UTILIZATION                                                     --}}
    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'rentals'" style="display:none">
        @php $rentals = $this->rentalUtilization; @endphp

        @if($rentals->isEmpty())
            <div class="rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
                <p class="text-slate-500 text-sm">No active rental vehicles in fleet.</p>
            </div>
        @else
            @php $periodDays = $rentals->first()['period_days'] ?? 0; @endphp
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700">Fleet Utilization</h3>
                    <p class="text-xs text-slate-400">Period: {{ $periodDays }} days</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                <th class="px-5 py-3 text-left">Vehicle</th>
                                <th class="px-4 py-3 text-center">Rentals</th>
                                <th class="px-4 py-3 text-center">Days Out</th>
                                <th class="px-4 py-3 text-center">Utilization</th>
                                <th class="px-4 py-3 text-right">Internal Cost</th>
                                <th class="px-4 py-3 text-right">Ins. Billed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rentals as $row)
                                @php
                                    $pct = $row['utilization_pct'];
                                    $utilColor = $pct >= 60 ? 'text-emerald-700 bg-emerald-50'
                                        : ($pct >= 30 ? 'text-amber-700 bg-amber-50'
                                        : 'text-red-700 bg-red-50');
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-slate-800">{{ $row['vehicle']->displayName() }}</p>
                                        @if($row['vehicle']->name)
                                            <p class="text-xs text-slate-400">{{ $row['vehicle']->year }} {{ $row['vehicle']->make }} {{ $row['vehicle']->model }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-700">{{ $row['rental_count'] }}</td>
                                    <td class="px-4 py-3 text-center text-slate-700">{{ $row['total_days_out'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $utilColor }}">
                                            {{ $pct }}%
                                        </span>
                                        <div class="mt-1 h-1.5 rounded-full bg-slate-100 overflow-hidden max-w-16 mx-auto">
                                            <div class="h-full rounded-full {{ $pct >= 60 ? 'bg-emerald-500' : ($pct >= 30 ? 'bg-amber-500' : 'bg-red-500') }}"
                                                 style="width:{{ $pct }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-red-600">
                                        {{ $row['internal_cost'] > 0 ? '$' . number_format($row['internal_cost'], 0) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-emerald-600 font-semibold">
                                        {{ $row['insurance_billed'] > 0 ? '$' . number_format($row['insurance_billed'], 0) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-2 border-t border-slate-100 bg-slate-50/50">
                    <p class="text-xs text-slate-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>≥60% good utilization
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500 ml-3 mr-1"></span>30–59% underutilized
                        <span class="inline-block w-2 h-2 rounded-full bg-red-500 ml-3 mr-1"></span>&lt;30% sitting idle
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: WORK ORDERS (drill-down)                                               --}}
    {{-- ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'workorders'" style="display:none">

        {{-- Sub-filter bar --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-3 mb-3 flex flex-wrap items-center gap-2">
            <select wire:model.live="woFilterStatus"
                    class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1.5">
                <option value="">All Statuses</option>
                @foreach(\App\Enums\WorkOrderStatus::cases() as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="woFilterJobType"
                    class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1.5">
                <option value="">All Job Types</option>
                @foreach(\App\Enums\WorkOrderJobType::cases() as $t)
                    <option value="{{ $t->value }}">{{ $t->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="woFilterAdvisor"
                    class="rounded-lg border-slate-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1.5">
                <option value="">All Advisors</option>
                @foreach($this->staffList as $person)
                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                @endforeach
            </select>
            @if($woFilterStatus || $woFilterJobType || $woFilterAdvisor)
                <button wire:click="$set('woFilterStatus', ''); $set('woFilterJobType', ''); $set('woFilterAdvisor', '')"
                        class="text-xs text-slate-400 hover:text-slate-600 transition-colors">
                    Clear filters
                </button>
            @endif
        </div>

        @php $wos = $this->workOrders; @endphp

        @if($wos->isEmpty())
            <div class="rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
                <p class="text-slate-500 text-sm">No work orders match the current filters.</p>
            </div>
        @else
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                <th class="px-4 py-3 text-left">RO #</th>
                                <th class="px-4 py-3 text-left">Customer</th>
                                <th class="px-4 py-3 text-left">Vehicle</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Advisor</th>
                                <th class="px-4 py-3 text-right">Invoice</th>
                                <th class="px-4 py-3 text-right">Net</th>
                                <th class="px-4 py-3 text-right">Days</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($wos as $wo)
                                @php
                                    $advisor = $wo->assignments->first(fn($a) => $a->role === \App\Enums\Role::SalesAdvisor);
                                    $net = $wo->netTotal();
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('work-orders.show', $wo) }}" wire:navigate
                                           class="font-mono text-xs text-blue-600 hover:underline">{{ $wo->ro_number }}</a>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $wo->customer?->last_name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 text-xs">
                                        {{ trim($wo->vehicle?->year . ' ' . $wo->vehicle?->make . ' ' . $wo->vehicle?->model) ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs font-medium px-1.5 py-0.5 rounded {{ $wo->job_type->badgeClasses() }}">
                                            {{ $wo->job_type->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs font-medium px-1.5 py-0.5 rounded {{ $wo->status->badgeClasses() }}">
                                            {{ $wo->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 text-xs">
                                        {{ $advisor?->user?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-700">
                                        {{ $wo->invoice_total !== null ? '$' . number_format((float)$wo->invoice_total, 0) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold {{ $net !== null && $net >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                        {{ $net !== null ? '$' . number_format($net, 0) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-500 text-xs">
                                        {{ $wo->daysInShop() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($wos->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100">
                        {{ $wos->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>

</div>
