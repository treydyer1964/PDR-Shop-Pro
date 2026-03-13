<div class="space-y-4">
    {{-- Filters bar --}}
    <div class="space-y-2">
        {{-- Search --}}
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" />
            </svg>
            <input wire:model.live.debounce.300ms="search"
                   type="search" placeholder="Search RO#, customer, VIN, claim…"
                   class="w-full rounded-lg border-slate-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        </div>

        {{-- Filter selects: 2-per-row on mobile, single row on desktop --}}
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-2">
            <select wire:model.live="filterView"
                    class="w-full sm:w-auto rounded-lg border-slate-300 py-2 pl-3 pr-8 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 font-medium">
                <option value="in_shop">In Shop</option>
                <option value="to_be_acquired">To Be Acquired</option>
                <option value="balance_due">Balance Due</option>
                <option value="unpaid_commissions">Unpaid Commissions</option>
                <option value="unbilled_rentals">Unbilled Rentals</option>
                <option value="unpaid_rental">Unpaid Rental</option>
                <option value="">All</option>
            </select>

            <select wire:model.live="filterStatus"
                    class="w-full sm:w-auto rounded-lg border-slate-300 py-2 pl-3 pr-8 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Statuses</option>
                @foreach($this->statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterType"
                    class="w-full sm:w-auto rounded-lg border-slate-300 py-2 pl-3 pr-8 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Types</option>
                @foreach($this->jobTypes as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </select>

            @if($this->stormEvents->isNotEmpty())
                <select wire:model.live="filterStorm"
                        class="w-full sm:w-auto rounded-lg border-slate-300 py-2 pl-3 pr-8 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Storms</option>
                    @foreach($this->stormEvents as $storm)
                        <option value="{{ $storm->id }}">{{ $storm->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    {{-- Work Order Cards --}}
    @forelse($this->workOrders as $wo)
        <a href="{{ route('work-orders.show', $wo) }}"
           wire:navigate
           class="block rounded-xl border bg-white shadow-sm hover:shadow-md transition-shadow overflow-hidden
                  {{ $wo->on_hold ? 'border-yellow-300' : ($wo->kicked ? 'border-red-200 opacity-75' : 'border-slate-200') }}">

            <div class="p-4">
                {{-- Top row: RO, badges, days --}}
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-sm font-semibold text-slate-700">{{ $wo->ro_number }}</span>

                        {{-- Job type badge --}}
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $wo->job_type->badgeClasses() }}">
                            {{ $wo->job_type->label() }}
                        </span>

                        {{-- Status badge --}}
                        @if($wo->kicked)
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700">Kicked</span>
                        @elseif($wo->on_hold)
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800">On Hold</span>
                        @else
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $wo->status->badgeClasses() }}">
                                {{ $wo->status->label() }}
                            </span>
                        @endif
                    </div>

                    <span class="shrink-0 text-xs text-slate-400">{{ $wo->daysInShop() }}d in shop</span>
                </div>

                {{-- Customer + Vehicle --}}
                <div class="mt-2">
                    <p class="text-sm font-medium text-slate-800">
                        {{ $wo->customer->first_name }} {{ $wo->customer->last_name }}
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ $wo->vehicle->year }} {{ $wo->vehicle->make }} {{ $wo->vehicle->model }}
                        @if($wo->vehicle->color) — {{ $wo->vehicle->color }} @endif
                    </p>
                </div>

                {{-- Insurance info (if applicable) --}}
                @if($wo->job_type->isInsurance() && $wo->claim_number)
                    <p class="mt-1 text-xs text-slate-400">
                        Claim: {{ $wo->claim_number }}
                        @if($wo->insuranceCompany) · {{ $wo->insuranceCompany->short_name ?? $wo->insuranceCompany->name }} @endif
                    </p>
                @endif

                {{-- Mini pipeline dots --}}
                <div class="mt-3 flex items-center gap-1">
                    @foreach(\App\Enums\WorkOrderStatus::cases() as $s)
                        @php $isActive = $wo->status->position() >= $s->position(); @endphp
                        <div class="h-1.5 flex-1 rounded-full {{ $isActive ? $s->dotClasses() : 'bg-slate-200' }}"></div>
                    @endforeach
                </div>
            </div>
        </a>
    @empty
        <div class="rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center">
            <p class="text-sm text-slate-500">No work orders found.</p>
            @if(auth()->user()->canCreateWorkOrders())
            <a href="{{ route('work-orders.create') }}" wire:navigate
               class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                Create Work Order
            </a>
            @endif
        </div>
    @endforelse

    {{-- Pagination --}}
    @if($this->workOrders->hasPages())
        <div class="mt-4">
            {{ $this->workOrders->links() }}
        </div>
    @endif
</div>
