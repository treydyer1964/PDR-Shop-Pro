<x-app-layout>
    <x-slot name="header">Add Vehicle — {{ $customer->full_name }}</x-slot>

    <div class="mx-auto max-w-2xl">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <livewire:vehicles.vehicle-form :customer="$customer" />
        </div>
    </div>
</x-app-layout>
