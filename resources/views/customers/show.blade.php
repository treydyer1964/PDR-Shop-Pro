<x-app-layout>
    <x-slot name="header">{{ $customer->full_name }}</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('customers.edit', $customer) }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-slate-300 hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            Edit
        </a>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-4">

        {{-- Contact info card --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($customer->phone)
                <div>
                    <p class="text-xs text-slate-500">Phone</p>
                    <a href="tel:{{ $customer->phone }}" class="text-sm font-medium text-blue-600">{{ $customer->display_phone }}</a>
                </div>
                @endif
                @if($customer->email)
                <div>
                    <p class="text-xs text-slate-500">Email</p>
                    <a href="mailto:{{ $customer->email }}" class="text-sm font-medium text-blue-600">{{ $customer->email }}</a>
                </div>
                @endif
                @if($customer->city || $customer->state)
                <div>
                    <p class="text-xs text-slate-500">Location</p>
                    <p class="text-sm font-medium text-slate-900">{{ collect([$customer->city, $customer->state])->filter()->implode(', ') }}</p>
                </div>
                @endif
            </div>
            @if($customer->notes)
            <div class="mt-3 border-t border-slate-100 pt-3">
                <p class="text-xs text-slate-500">Notes</p>
                <p class="text-sm text-slate-700">{{ $customer->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Vehicles --}}
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Vehicles</h2>
                <a href="{{ route('customers.vehicles.create', $customer) }}"
                   class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Vehicle
                </a>
            </div>

            @forelse($customer->vehicles as $vehicle)
                <a href="{{ route('customers.vehicles.edit', [$customer, $vehicle]) }}"
                   class="mb-2 flex items-start justify-between rounded-xl bg-white px-4 py-3.5 shadow-sm ring-1 ring-slate-200 hover:ring-blue-400 transition-all">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $vehicle->description ?: 'Unknown Vehicle' }}</p>
                        <div class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-slate-500">
                            @if($vehicle->vin)
                                <span class="font-mono">{{ $vehicle->vin }}</span>
                            @endif
                            @if($vehicle->color)
                                <span>{{ $vehicle->color }}</span>
                            @endif
                            @if($vehicle->plate)
                                <span>{{ $vehicle->plate }}</span>
                            @endif
                        </div>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @empty
                <div class="rounded-xl bg-white px-6 py-8 text-center shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">No vehicles yet.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
