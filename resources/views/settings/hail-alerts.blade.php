<x-app-layout>
    <x-slot name="header">Settings</x-slot>

    <div class="flex gap-6 lg:flex-row flex-col">
        @include('settings.partials.subnav')

        <div class="flex-1 min-w-0">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-slate-800">Hail Alerts</h2>
                <p class="text-sm text-slate-500">Get notified when significant hail is detected near your home base.</p>
            </div>
            <livewire:settings.hail-alert-settings />
        </div>
    </div>
</x-app-layout>
