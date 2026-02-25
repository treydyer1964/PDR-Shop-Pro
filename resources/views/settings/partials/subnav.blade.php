<nav class="lg:w-48 shrink-0">
    <ul class="flex lg:flex-col gap-1 overflow-x-auto lg:overflow-x-visible">
        @php
            $links = [
                ['route' => 'settings.shop',                 'label' => 'Shop Info'],
                ['route' => 'settings.locations',            'label' => 'Locations'],
                ['route' => 'settings.expense-categories',   'label' => 'Expense Categories'],
                ['route' => 'settings.appointment-types',    'label' => 'Appointment Types'],
            ];
        @endphp
        @foreach($links as $link)
            <li>
                <a href="{{ route($link['route']) }}" wire:navigate
                   @class([
                       'flex whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                       'bg-slate-900 text-white' => request()->routeIs($link['route']),
                       'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => !request()->routeIs($link['route']),
                   ])>
                    {{ $link['label'] }}
                </a>
            </li>
        @endforeach

        {{-- Staff management link (already built) --}}
        <li class="lg:mt-4">
            <p class="hidden lg:block px-3 pb-1 text-xs font-semibold uppercase tracking-widest text-slate-400">Team</p>
        </li>
        <li>
            <a href="{{ route('staff.index') }}" wire:navigate
               class="flex whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                Staff & Roles
            </a>
        </li>
    </ul>
</nav>
