<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('work-orders.index') }}" wire:navigate
               class="text-slate-400 hover:text-slate-600 transition-colors">Work Orders</a>
            <span class="text-slate-300">/</span>
            <span class="font-mono">{{ $workOrder->ro_number }}</span>
        </div>
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('work-orders.edit', $workOrder) }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
            </svg>
            Edit
        </a>
    </x-slot>

    <livewire:work-orders.work-order-show :work-order="$workOrder" />
</x-app-layout>
