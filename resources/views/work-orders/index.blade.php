<x-app-layout>
    <x-slot name="header">Work Orders</x-slot>
    <x-slot name="headerActions">
        {{-- List / Calendar toggle --}}
        <div class="flex overflow-hidden rounded-lg border border-slate-200 bg-white text-sm">
            <a href="{{ route('work-orders.index') }}" wire:navigate
               @class([
                   'px-3 py-1.5 font-medium transition-colors flex items-center gap-1.5',
                   'bg-slate-900 text-white' => ! request()->query('calview'),
                   'text-slate-500 hover:bg-slate-50' => request()->query('calview'),
               ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                </svg>
                List
            </a>
            <a href="{{ route('work-orders.index', ['calview' => 1]) }}" wire:navigate
               @class([
                   'px-3 py-1.5 font-medium transition-colors flex items-center gap-1.5 border-l border-slate-200',
                   'bg-slate-900 text-white' => request()->query('calview'),
                   'text-slate-500 hover:bg-slate-50' => ! request()->query('calview'),
               ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                </svg>
                Calendar
            </a>
        </div>

        @if(auth()->user()->canCreateWorkOrders() && ! request()->query('calview'))
            <a href="{{ route('work-orders.create') }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Work Order
            </a>
        @endif
    </x-slot>

    @if(request()->query('calview'))
        <livewire:work-orders.work-order-calendar />
    @else
        <livewire:work-orders.work-order-list />
    @endif
</x-app-layout>
