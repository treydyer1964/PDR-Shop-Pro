<x-app-layout>
    <x-slot name="header">Settings</x-slot>

    <div class="flex gap-6 lg:flex-row flex-col">

        {{-- Settings sub-nav --}}
        @include('settings.partials.subnav')

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <livewire:settings.shop-settings />
        </div>
    </div>
</x-app-layout>
