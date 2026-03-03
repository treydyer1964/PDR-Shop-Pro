<x-app-layout>
    <x-slot name="header">New Lead</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('leads.index') }}" wire:navigate
           class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Leads</a>
    </x-slot>

    <livewire:leads.lead-form />
</x-app-layout>
