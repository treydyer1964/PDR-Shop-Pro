<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Rental Fleet</span>
            <a href="{{ route('rental-agreement.blank') }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-400 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Print Blank Agreement
            </a>
        </div>
    </x-slot>

    <livewire:rentals.rental-vehicle-list />
</x-app-layout>
