<x-app-layout>
    <x-slot name="header">
        {{ $lead->fullName() }}
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('leads.index') }}" wire:navigate
           class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Leads</a>
        @if(!$lead->isConverted())
            <a href="{{ route('leads.edit', $lead) }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                Edit
            </a>
        @endif
    </x-slot>

    <livewire:leads.lead-show :lead="$lead" />
</x-app-layout>
