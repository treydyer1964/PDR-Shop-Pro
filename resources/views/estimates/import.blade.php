<x-app-layout>
    <x-slot name="header">Import Estimate</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('work-orders.index') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Work Orders
        </a>
    </x-slot>

    <livewire:estimates.estimate-import />
</x-app-layout>
