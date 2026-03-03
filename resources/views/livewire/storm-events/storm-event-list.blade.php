<div>
    {{-- Header row --}}
    @if(auth()->user()->canManageStaff() && ! $creating && ! $editingId)
        <div class="mb-5 flex justify-end">
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Storm Event
            </button>
        </div>
    @endif

    {{-- Create form --}}
    @if($creating)
        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50/50 p-5">
            <h3 class="mb-4 text-sm font-semibold text-slate-800">New Storm Event</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-600">Event Name *</label>
                    <input wire:model="name" type="text" placeholder="e.g. Abilene Hail — March 2026"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Event Date *</label>
                    <input wire:model="event_date" type="date"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('event_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Storm Type *</label>
                    <select wire:model="storm_type"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($this->stormTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">City</label>
                    <input wire:model="city" type="text" placeholder="Abilene"
                           class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">State</label>
                    <select wire:model="state"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">— Select —</option>
                        @foreach(['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'] as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-600">Notes</label>
                    <textarea wire:model="notes" rows="2" placeholder="Optional notes about this event"
                              class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <button wire:click="save"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    Save Event
                </button>
                <button wire:click="cancelCreate"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Storm events list --}}
    @if($this->stormEvents->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center">
            <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" />
            </svg>
            <p class="mt-3 text-sm font-medium text-slate-500">No storm events yet</p>
            <p class="mt-1 text-xs text-slate-400">Add your first storm event to start grouping work orders.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">WOs</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($this->stormEvents as $storm)
                        @if($editingId === $storm->id)
                            {{-- Inline edit row --}}
                            <tr class="bg-blue-50/40">
                                <td class="px-4 py-3" colspan="6">
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                        <div class="sm:col-span-3">
                                            <input wire:model="editName" type="text" placeholder="Event name"
                                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                            @error('editName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <input wire:model="editDate" type="date"
                                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                        </div>
                                        <div>
                                            <select wire:model="editType"
                                                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach($this->stormTypes as $type)
                                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex gap-2">
                                            <input wire:model="editCity" type="text" placeholder="City"
                                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                            <select wire:model="editState"
                                                    class="w-24 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">ST</option>
                                                @foreach(['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'] as $s)
                                                    <option value="{{ $s }}">{{ $s }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="sm:col-span-3">
                                            <textarea wire:model="editNotes" rows="2" placeholder="Notes (optional)"
                                                      class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <button wire:click="update"
                                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition-colors">
                                            Save
                                        </button>
                                        <button wire:click="cancelEdit"
                                                class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    <a href="{{ route('storm-events.show', $storm) }}"
                                       class="font-medium text-blue-600 hover:text-blue-800 text-sm">
                                        {{ $storm->name }}
                                    </a>
                                    @if($storm->notes)
                                        <p class="mt-0.5 text-xs text-slate-400 truncate max-w-xs">{{ $storm->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">
                                    {{ $storm->event_date->format('M j, Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ $storm->locationLabel() ?: '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $storm->storm_type->badgeClasses() }}">
                                        {{ $storm->storm_type->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('storm-events.show', $storm) }}"
                                       class="text-sm font-semibold text-slate-700 hover:text-blue-600">
                                        {{ $storm->work_orders_count }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if(auth()->user()->canManageStaff())
                                        <button wire:click="startEdit({{ $storm->id }})"
                                                class="text-xs text-slate-400 hover:text-blue-600 transition-colors mr-3">
                                            Edit
                                        </button>
                                        <button wire:click="delete({{ $storm->id }})"
                                                wire:confirm="Delete '{{ $storm->name }}'? Work orders will be detached but not deleted."
                                                class="text-xs text-slate-400 hover:text-red-600 transition-colors">
                                            Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
