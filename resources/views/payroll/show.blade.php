<x-app-layout>
    <x-slot name="header">{{ $payRun->name }}</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('payroll.index') }}" wire:navigate
           class="text-sm text-slate-500 hover:text-slate-700 font-medium">← Pay Runs</a>
    </x-slot>

    <livewire:payroll.pay-run-show :pay-run="$payRun" />
</x-app-layout>
