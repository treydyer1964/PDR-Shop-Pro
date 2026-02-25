<x-app-layout>
    <x-slot name="header">Payroll</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('payroll.create') }}" wire:navigate
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
            New Pay Run
        </a>
    </x-slot>

    <livewire:payroll.pay-run-list />
</x-app-layout>
