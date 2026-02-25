<div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-semibold text-slate-700">Photos</h3>
            @if($this->totalCount > 0)
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">
                    {{ $this->totalCount }}
                </span>
            @endif
        </div>
        @if($this->totalCount > 0)
            <a href="{{ URL::signedRoute('photos.share', $workOrder) }}" target="_blank"
               class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                </svg>
                Share
            </a>
        @endif
    </div>

    {{-- Category tab bar --}}
    <div class="flex border-b border-slate-100 overflow-x-auto scrollbar-none">
        @foreach($this->categories as $cat)
            @php
                $count = $this->countByCategory[$cat->value] ?? 0;
                $isActive = $activeTab === $cat->value;
            @endphp
            <button wire:click="setTab('{{ $cat->value }}')"
                    @class([
                        'flex shrink-0 items-center gap-1.5 border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-colors',
                        'border-transparent text-slate-500 hover:text-slate-700' => !$isActive,
                        $cat->tabActiveClasses() . ' border-current' => $isActive,
                    ])>
                {{ $cat->label() }}
                @if($count > 0)
                    <span @class([
                        'rounded-full px-1.5 py-0.5 text-xs font-semibold',
                        $cat->badgeClasses(),
                    ])>{{ $count }}</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Upload area for active tab --}}
    <div class="border-b border-slate-100 bg-slate-50/50 px-4 py-3">
        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 bg-white px-4 py-5 text-sm font-medium text-slate-600 hover:border-blue-400 hover:text-blue-600 transition-colors active:bg-slate-50">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
            </svg>
            <span>
                Tap to add {{ \App\Enums\PhotoCategory::from($activeTab)->label() }} photos
                <span class="text-slate-400 font-normal text-xs block text-center mt-0.5">Camera or photo library</span>
            </span>
            <input wire:model="uploads"
                   type="file"
                   accept="image/*"
                   multiple
                   class="sr-only" />
        </label>

        {{-- Upload progress / validation errors --}}
        <div wire:loading wire:target="uploads" class="mt-2 text-center text-xs text-blue-600">
            Uploading…
        </div>
        @error('uploads.*')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Photo grid --}}
    @if($this->activePhotos->isNotEmpty())
        <div class="grid grid-cols-3 gap-1 p-2 sm:grid-cols-4">
            @foreach($this->activePhotos as $photo)
                <div class="group relative aspect-square overflow-hidden rounded-lg bg-slate-100"
                     x-data="{ open: false }">

                    {{-- Thumbnail --}}
                    <img src="{{ $photo->url() }}"
                         alt="{{ $photo->original_filename }}"
                         loading="lazy"
                         class="h-full w-full object-cover cursor-pointer"
                         @click="open = true" />

                    {{-- Delete button --}}
                    <button wire:click="deletePhoto({{ $photo->id }})"
                            wire:confirm="Delete this photo?"
                            class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    {{-- Caption if any --}}
                    @if($photo->caption)
                        <div class="absolute bottom-0 inset-x-0 bg-black/50 px-1.5 py-1">
                            <p class="text-xs text-white truncate">{{ $photo->caption }}</p>
                        </div>
                    @endif

                    {{-- Full-size lightbox --}}
                    <div x-show="open"
                         x-transition
                         @click.self="open = false"
                         @keydown.escape.window="open = false"
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                         style="display:none">
                        <button @click="open = false"
                                class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <img src="{{ $photo->url() }}"
                             alt="{{ $photo->original_filename }}"
                             class="max-h-full max-w-full rounded-lg object-contain" />
                        <div class="absolute bottom-4 left-0 right-0 text-center">
                            <span class="text-xs text-white/60">
                                {{ $photo->original_filename }}
                                @if($photo->size) · {{ $photo->formattedSize() }} @endif
                            </span>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <div class="py-8 text-center">
            <p class="text-sm text-slate-400">No {{ \App\Enums\PhotoCategory::from($activeTab)->label() }} photos yet.</p>
        </div>
    @endif

</div>
