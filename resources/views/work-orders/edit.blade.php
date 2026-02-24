<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('work-orders.show', $workOrder) }}" wire:navigate
               class="text-slate-400 hover:text-slate-600 transition-colors">{{ $workOrder->ro_number }}</a>
            <span class="text-slate-300">/</span>
            <span>Edit</span>
        </div>
    </x-slot>

    <livewire:work-orders.edit-work-order :work-order="$workOrder" />
</x-app-layout>
