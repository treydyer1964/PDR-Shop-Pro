<x-app-layout>
    <x-slot name="header">Settings</x-slot>

    <div class="flex gap-6 lg:flex-row flex-col">
        @include('settings.partials.subnav')
        <div class="flex-1 min-w-0">
            <livewire:settings.insurance-company-settings />
        </div>
    </div>
</x-app-layout>
