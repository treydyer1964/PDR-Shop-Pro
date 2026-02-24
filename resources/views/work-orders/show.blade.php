<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('work-orders.index') }}" wire:navigate
               class="text-slate-400 hover:text-slate-600 transition-colors">Work Orders</a>
            <span class="text-slate-300">/</span>
            <span class="font-mono">{{ $workOrder->ro_number }}</span>
        </div>
    </x-slot>

    <livewire:work-orders.work-order-show :work-order="$workOrder" />
</x-app-layout>
