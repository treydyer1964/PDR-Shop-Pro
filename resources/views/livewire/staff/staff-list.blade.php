<div>
    {{-- Search --}}
    <div class="mb-4">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z" />
                </svg>
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Search by name, email, or phone…"
                class="block w-full rounded-xl border-0 bg-white py-3 pl-10 pr-4 text-slate-900 shadow-sm ring-1 ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 sm:text-sm"
                autocomplete="off"
            />
        </div>
    </div>

    @if($this->staff->count())
        <div class="space-y-2">
            @foreach($this->staff as $member)
                <a
                    href="{{ route('staff.show', $member) }}"
                    class="flex items-center justify-between rounded-xl bg-white px-4 py-3.5 shadow-sm ring-1 ring-slate-200 hover:ring-blue-400 transition-all active:scale-[0.99]"
                >
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                            @if(! $member->active)
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-600">Inactive</span>
                            @endif
                        </div>
                        <div class="mt-0.5 flex flex-wrap gap-1.5">
                            @foreach($member->roles as $role)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ \App\Enums\Role::from($role->name)->badgeClasses() }}">
                                    {{ $role->label() }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="ml-3 flex shrink-0 items-center gap-2">
                        @if($member->commission_rate)
                            <span class="hidden text-xs text-slate-400 sm:block">{{ $member->commission_rate }}%</span>
                        @endif
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    @elseif($this->search)
        <div class="rounded-xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-200">
            <p class="text-sm text-slate-500">No staff found for "<strong>{{ $this->search }}</strong>"</p>
        </div>
    @else
        <div class="rounded-xl bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-200">
            <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            <p class="mt-3 text-sm font-medium text-slate-900">No staff members yet</p>
            <a href="{{ route('staff.create') }}" wire:navigate
               class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Add Staff Member
            </a>
        </div>
    @endif
</div>
