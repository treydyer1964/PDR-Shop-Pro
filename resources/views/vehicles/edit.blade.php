<x-app-layout>
    <x-slot name="header">Edit Vehicle — {{ $vehicle->short_description }}</x-slot>

    <div class="mx-auto max-w-2xl">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <livewire:vehicles.vehicle-form :customer="$customer" :vehicle="$vehicle" />
        </div>
    </div>
</x-app-layout>
