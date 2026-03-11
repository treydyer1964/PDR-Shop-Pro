<x-app-layout>
    <x-slot name="header">{{ $customer->full_name }}</x-slot>
    <x-slot name="headerActions">
        @if(auth()->user()->role === 'owner')
        <form method="POST" action="{{ route('customers.destroy', $customer) }}"
              onsubmit="return confirm('Delete {{ $customer->full_name }}? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                Delete
            </button>
        </form>
        @endif
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
            @if($customer->phone || $customer->email || $customer->city || $customer->state)
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
                @if($customer->birthdate)
                <div>
                    <p class="text-xs text-slate-500">Date of Birth</p>
                    <p class="text-sm font-medium text-slate-900">{{ $customer->birthdate->format('m/d/Y') }}</p>
                </div>
                @endif
                @if($customer->drivers_license)
                <div>
                    <p class="text-xs text-slate-500">Driver's License</p>
                    <p class="text-sm font-medium text-slate-900 font-mono">{{ $customer->drivers_license }}{{ $customer->drivers_license_state ? ' · ' . $customer->drivers_license_state : '' }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs text-slate-500">SMS</p>
                    @if($customer->sms_opted_in)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Opted in
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Opted out
                        </span>
                    @endif
                </div>
            </div>
            @else
            <p class="text-sm text-slate-400 italic">No contact information on file.</p>
            @endif
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
                <div class="mb-2 flex items-start justify-between rounded-xl bg-white px-4 py-3.5 shadow-sm ring-1 ring-slate-200">
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
                    <a href="{{ route('customers.vehicles.edit', [$customer, $vehicle]) }}" wire:navigate
                       class="shrink-0 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        Edit
                    </a>
                </div>
            @empty
                <div class="rounded-xl bg-white px-6 py-8 text-center shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">No vehicles yet.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
