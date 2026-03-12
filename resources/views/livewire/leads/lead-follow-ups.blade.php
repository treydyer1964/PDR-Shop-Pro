<div>
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-slate-700">Follow-Ups / Appointments</h4>
        @if(!$adding)
        <button wire:click="openAdd"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Schedule
        </button>
        @endif
    </div>

    @if($adding)
    <div class="mb-3 rounded-lg border border-blue-200 bg-blue-50 p-3 space-y-3">
        <div>
            <label class="block text-xs font-medium text-slate-700">Appointment Type *</label>
            <select wire:model="appointment_type_id"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="0">Select type…</option>
                @foreach($this->appointmentTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            @error('appointment_type_id') <p class="mt-1 text-xs text-red-600">Please select an appointment type.</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-700">Date &amp; Time *</label>
            <input wire:model="scheduled_at" type="datetime-local"
                   class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            @error('scheduled_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-700">Notes</label>
            <input wire:model="notes" type="text" placeholder="What to follow up on…"
                   class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="saveFollowUp"
                    class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                Save
            </button>
            <button wire:click="cancelAdd"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                Cancel
            </button>
        </div>
    </div>
    @endif

    @forelse($this->followUps as $appt)
    @php
        $isCompleted = $appt->status === \App\Enums\AppointmentStatus::Completed;
        $isOverdue   = !$isCompleted && $appt->scheduled_at->isPast();
    @endphp
    <div @class([
        'flex items-start gap-3 rounded-lg border p-3 mb-2',
        'border-slate-200 bg-white'     => $isCompleted,
        'border-amber-200 bg-amber-50'  => $isOverdue,
        'border-blue-200 bg-white'      => !$isCompleted && !$isOverdue,
    ])>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="text-sm font-medium text-slate-700">
                    {{ $appt->scheduled_at->format('M j, Y g:ia') }}
                </p>
                @if($appt->type)
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $appt->type->badgeClasses() }}">
                        {{ $appt->type->name }}
                    </span>
                @endif
                @if($isOverdue)
                    <span class="text-xs text-amber-600">Overdue</span>
                @endif
            </div>
            @if($appt->notes)
                <p class="mt-0.5 text-sm text-slate-500">{{ $appt->notes }}</p>
            @endif
            @if($isCompleted)
                <p class="mt-0.5 text-xs text-slate-400">Completed {{ $appt->completed_at->diffForHumans() }}</p>
            @endif
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            @if(!$isCompleted)
            <button wire:click="complete({{ $appt->id }})"
                    class="rounded px-2 py-1 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 transition-colors">
                Done
            </button>
            @endif
            <button wire:click="delete({{ $appt->id }})"
                    wire:confirm="Delete this appointment?"
                    class="rounded p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    @empty
    <p class="text-xs text-slate-400 italic">No follow-ups scheduled.</p>
    @endforelse
</div>
