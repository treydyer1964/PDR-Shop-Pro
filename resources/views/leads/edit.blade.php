<x-app-layout>
    <x-slot name="header">Edit Pin</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('leads.show', $lead) }}" wire:navigate
           class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Lead</a>
    </x-slot>

    <livewire:leads.lead-form :lead="$lead" />
</x-app-layout>
