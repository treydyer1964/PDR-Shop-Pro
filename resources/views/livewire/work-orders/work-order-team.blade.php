<div class="rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="border-b border-slate-100 px-5 py-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-700">Team</h3>
    </div>

    <div class="divide-y divide-slate-100">

        {{-- Current assignments grouped by role --}}
        @forelse($this->assignments as $roleValue => $roleAssignments)
            @php $role = \App\Enums\Role::from($roleValue); @endphp
            <div class="px-5 py-3">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $role->label() }}</p>
                <div class="space-y-2">
                    @foreach($roleAssignments as $assignment)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                                    {{ strtoupper(substr($assignment->user->name, 0, 1)) }}
                                </span>
                                <span class="text-sm text-slate-800">{{ $assignment->user->name }}</span>
                                @if($assignment->usesSplit())
                                    <div class="flex items-center gap-1">
                                        <input
                                            type="number"
                                            min="0" max="100" step="0.01"
                                            value="{{ $assignment->split_pct }}"
                                            wire:change="updateSplit({{ $assignment->id }}, $event.target.value)"
                                            class="w-16 rounded border-slate-300 py-0.5 text-center text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                        <span class="text-xs text-slate-400">%</span>
                                    </div>
                                @endif
                            </div>
                            <button wire:click="removeAssignment({{ $assignment->id }})"
                                    wire:confirm="Remove {{ $assignment->user->name }} from this job?"
                                    class="rounded p-1 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="px-5 py-4 text-sm text-slate-400 text-center">No team assigned yet.</div>
        @endforelse

        {{-- Add Assignment Form --}}
        <div class="px-5 py-4 bg-slate-50">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Assign Team Member</p>
            <div class="space-y-2">

                {{-- Role picker --}}
                <select wire:model.live="addRole"
                        class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select role…</option>
                    @foreach($this->assignableRoles as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>

                @if($addRole !== '')
                    {{-- Staff picker --}}
                    <select wire:model="addUserId"
                            class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select person…</option>
                        @foreach($this->availableStaff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @error('addUserId') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    @if($this->currentRoleEnum?->usesSplitPct())
                        <div class="flex items-center gap-2">
                            <input wire:model="addSplit" type="number" min="0" max="100" step="0.01"
                                   placeholder="Auto-balance"
                                   class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <span class="shrink-0 text-sm text-slate-400">% split</span>
                        </div>
                        <p class="text-xs text-slate-400">Leave blank to auto-balance equally among assigned {{ $this->currentRoleEnum->label() }}s.</p>
                    @endif

                    <button wire:click="addAssignment"
                            wire:loading.attr="disabled"
                            class="w-full rounded-lg bg-blue-600 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-70 transition-colors">
                        <span wire:loading wire:target="addAssignment">Adding…</span>
                        <span wire:loading.remove wire:target="addAssignment">Add to Team</span>
                    </button>
                @endif

            </div>
        </div>

    </div>
</div>
