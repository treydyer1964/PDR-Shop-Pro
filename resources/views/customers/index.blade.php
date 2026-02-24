<x-app-layout>
    <x-slot name="header">Customers</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('customers.create') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Customer
        </a>
    </x-slot>

    <livewire:customers.customer-list />
</x-app-layout>
