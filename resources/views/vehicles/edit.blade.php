<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('customers.index') }}" wire:navigate
               class="text-slate-400 hover:text-slate-600 transition-colors">Customers</a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('customers.show', $customer) }}" wire:navigate
               class="text-slate-400 hover:text-slate-600 transition-colors">{{ $customer->full_name }}</a>
            <span class="text-slate-300">/</span>
            <span>Edit Vehicle</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-2xl">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <livewire:vehicles.vehicle-form :customer="$customer" :vehicle="$vehicle" />
        </div>
    </div>
</x-app-layout>
