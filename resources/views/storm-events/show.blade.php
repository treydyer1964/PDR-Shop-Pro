<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('storm-events.index') }}" wire:navigate
               class="hover:text-slate-700 transition-colors">Storm Events</a>
            <span class="text-slate-300">/</span>
            <span class="font-semibold text-slate-800">{{ $stormEvent->name }}</span>
        </div>
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('storm-events.index') }}" wire:navigate
           class="text-sm font-medium text-slate-500 hover:text-slate-700">← All Storms</a>
    </x-slot>

    {{-- Storm header card --}}
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $stormEvent->storm_type->badgeClasses() }}">
                {{ $stormEvent->storm_type->label() }}
            </span>
            <span class="text-sm text-slate-500">{{ $stormEvent->event_date->format('F j, Y') }}</span>
            @if($stormEvent->locationLabel())
                <span class="text-sm text-slate-500">{{ $stormEvent->locationLabel() }}</span>
            @endif
        </div>
        @if($stormEvent->notes)
            <p class="mt-2 text-sm text-slate-500">{{ $stormEvent->notes }}</p>
        @endif
    </div>

    {{-- KPI cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-slate-900">{{ $totalCars }}</p>
            <p class="mt-0.5 text-xs font-medium text-slate-500 uppercase tracking-wide">Total Cars</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-slate-900">${{ number_format($totalRevenue, 0) }}</p>
            <p class="mt-0.5 text-xs font-medium text-slate-500 uppercase tracking-wide">Gross Revenue</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-slate-900">${{ number_format($avgPerCar, 0) }}</p>
            <p class="mt-0.5 text-xs font-medium text-slate-500 uppercase tracking-wide">Avg / Car</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-red-600">${{ number_format($totalExpenses, 0) }}</p>
            <p class="mt-0.5 text-xs font-medium text-slate-500 uppercase tracking-wide">Total Expenses</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">${{ number_format($totalNet, 0) }}</p>
            <p class="mt-0.5 text-xs font-medium text-slate-500 uppercase tracking-wide">Total Net</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-slate-900">
                {{ $totalRevenue > 0 ? number_format(($totalNet / $totalRevenue) * 100, 0) : 0 }}%
            </p>
            <p class="mt-0.5 text-xs font-medium text-slate-500 uppercase tracking-wide">Net Margin</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Status breakdown --}}
        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">Cars by Status</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($byStatus as $row)
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $row['status']->badgeClasses() }}">
                            {{ $row['status']->label() }}
                        </span>
                        <span class="text-sm font-semibold text-slate-700">{{ $row['count'] }}</span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400">No work orders yet.</p>
                @endforelse
            </div>
        </div>

        {{-- By job type --}}
        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">By Job Type</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($byJobType as $row)
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm text-slate-700">{{ $row['label'] }}</span>
                        <div class="text-right">
                            <span class="block text-sm font-semibold text-slate-700">{{ $row['count'] }} cars</span>
                            <span class="text-xs text-slate-400">${{ number_format($row['revenue'], 0) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400">No work orders yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Insurance company breakdown --}}
        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-slate-700">By Insurance Company</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($byInsurance as $row)
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm text-slate-700 truncate max-w-[140px]">{{ $row['company'] }}</span>
                        <div class="text-right">
                            <span class="block text-sm font-semibold text-slate-700">{{ $row['count'] }} cars</span>
                            <span class="text-xs text-slate-400">${{ number_format($row['revenue'], 0) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400">No insurance jobs.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Work orders table --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-slate-700">Work Orders ({{ $totalCars }})</h3>
        </div>
        @if($workOrders->isEmpty())
            <p class="px-5 py-6 text-sm text-slate-400">No work orders tagged to this storm yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">RO #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Vehicle</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Insurance Co.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Invoice</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($workOrders as $wo)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    <a href="{{ route('work-orders.show', $wo) }}" wire:navigate
                                       class="font-mono text-sm text-blue-600 hover:text-blue-800">
                                        {{ $wo->ro_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">
                                    {{ $wo->customer->full_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ $wo->vehicle ? "{$wo->vehicle->year} {$wo->vehicle->make} {$wo->vehicle->model}" : '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ $wo->insuranceCompany?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($wo->kicked)
                                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">Kicked</span>
                                    @else
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $wo->status->badgeClasses() }}">
                                            {{ $wo->status->label() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-slate-700">
                                    {{ $wo->invoice_total ? '$' . number_format($wo->invoice_total, 0) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium {{ $wo->netTotal() !== null && $wo->netTotal() >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $wo->netTotal() !== null ? '$' . number_format($wo->netTotal(), 0) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-app-layout>
