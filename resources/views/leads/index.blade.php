<x-app-layout>
    <x-slot name="headScripts">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    </x-slot>
    <x-slot name="footerScripts">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/sp38=" crossorigin="anonymous"></script>
    </x-slot>

    <x-slot name="header">Leads</x-slot>
    <x-slot name="headerActions">
        {{-- List / Map toggle --}}
        <div class="flex overflow-hidden rounded-lg border border-slate-200 bg-white text-sm">
            <a href="{{ route('leads.index') }}" wire:navigate
               @class([
                   'px-3 py-1.5 font-medium transition-colors flex items-center gap-1.5',
                   'bg-slate-900 text-white' => ! request()->query('mapview'),
                   'text-slate-500 hover:bg-slate-50' => request()->query('mapview'),
               ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                </svg>
                List
            </a>
            <a href="{{ route('leads.index', ['mapview' => 1]) }}" wire:navigate
               @class([
                   'px-3 py-1.5 font-medium transition-colors flex items-center gap-1.5 border-l border-slate-200',
                   'bg-slate-900 text-white' => request()->query('mapview'),
                   'text-slate-500 hover:bg-slate-50' => ! request()->query('mapview'),
               ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.159.69.159 1.006 0z" />
                </svg>
                Map
            </a>
        </div>

        @if(auth()->user()->canCreateWorkOrders() && ! request()->query('mapview'))
            <a href="{{ route('leads.create') }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Lead
            </a>
        @endif
    </x-slot>

    @if(request()->query('mapview'))
        <livewire:leads.lead-map />
    @else
        <livewire:leads.lead-list />
    @endif
</x-app-layout>
