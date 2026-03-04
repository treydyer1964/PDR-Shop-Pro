<x-app-layout>
    <x-slot name="headScripts">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
        <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" crossorigin="anonymous">
    </x-slot>
    <x-slot name="footerScripts">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/sp38=" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js" crossorigin="anonymous"></script>
    </x-slot>

    <x-slot name="header">Settings</x-slot>

    <div class="flex gap-6 lg:flex-row flex-col">
        @include('settings.partials.subnav')
        <div class="flex-1 min-w-0">
            <livewire:settings.territory-manager />
        </div>
    </div>
</x-app-layout>
