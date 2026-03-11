<x-app-layout>
    <x-slot name="header">New Pin</x-slot>
    <x-slot name="headerActions">
        @php
            $backUrl = route('leads.map');
            if (request('zoom') && request('mapLat') && request('mapLng')) {
                $backUrl .= '?zoom=' . request('zoom') . '&clat=' . request('mapLat') . '&clng=' . request('mapLng');
            }
        @endphp
        <a href="{{ $backUrl }}"
           class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Map</a>
    </x-slot>

    <livewire:leads.lead-form />
</x-app-layout>
