<div class="space-y-6">

    <div>
        <h2 class="text-lg font-semibold text-slate-800">Lead Statuses</h2>
        <p class="mt-1 text-sm text-slate-500">Customize the display names for each lead status used in your workflow.</p>
    </div>

    @if($saved)
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            Status labels saved.
        </div>
    @endif

    <form wire:submit="save" class="space-y-4">

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm divide-y divide-slate-100">
            @foreach($statuses as $status)
                <div class="flex items-center gap-4 px-5 py-3.5">
                    {{-- Color dot --}}
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $status->dotClasses() }}"></span>

                    {{-- System slug (read-only reference) --}}
                    <span class="w-36 shrink-0 text-xs font-mono text-slate-400">{{ $status->value }}</span>

                    {{-- Editable label --}}
                    <div class="flex-1">
                        <input wire:model="labels.{{ $status->value }}"
                               type="text"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error("labels.{$status->value}")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Default label hint --}}
                    <span class="shrink-0 text-xs text-slate-400">default: {{ $status->label() }}</span>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between">
            <button type="button" wire:click="resetToDefaults"
                    wire:confirm="Reset all status labels back to defaults?"
                    class="text-sm text-slate-400 hover:text-red-500 transition-colors">
                Reset to defaults
            </button>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-70 transition-colors">
                Save Labels
            </button>
        </div>

    </form>
</div>
