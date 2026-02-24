<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('staff.show', $staff) }}" wire:navigate
               class="text-slate-400 hover:text-slate-600 transition-colors">{{ $staff->name }}</a>
            <span class="text-slate-300">/</span>
            <span>Edit</span>
        </div>
    </x-slot>

    <livewire:staff.staff-form :staff="$staff" />
</x-app-layout>
