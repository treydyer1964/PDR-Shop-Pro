<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('staff.index') }}" wire:navigate
               class="text-slate-400 hover:text-slate-600 transition-colors">Staff</a>
            <span class="text-slate-300">/</span>
            <span>New Staff Member</span>
        </div>
    </x-slot>

    <livewire:staff.staff-form />
</x-app-layout>
