<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Photos — {{ $workOrder->ro_number }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-900 font-sans antialiased" x-data>

    {{-- Header --}}
    <div class="border-b border-slate-800 bg-slate-900 px-4 py-4 sm:px-6">
        <div class="mx-auto max-w-4xl">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">PDR Shop Pro</p>
            <h1 class="mt-1 text-lg font-bold text-white">
                {{ $workOrder->ro_number }}
                @if($workOrder->vehicle)
                    &mdash; {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                @endif
            </h1>
            @if($workOrder->customer)
                <p class="text-sm text-slate-400">
                    {{ $workOrder->customer->first_name }} {{ $workOrder->customer->last_name }}
                </p>
            @endif
        </div>
    </div>

    {{-- Photo sections --}}
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 space-y-8">

        @forelse(\App\Enums\PhotoCategory::cases() as $cat)
            @php $catPhotos = $photos->get($cat->value, collect()); @endphp
            @if($catPhotos->isNotEmpty())
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                            {{ $cat->label() }}
                        </span>
                        <span class="text-xs text-slate-600">{{ $catPhotos->count() }} photo{{ $catPhotos->count() === 1 ? '' : 's' }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
                        @foreach($catPhotos as $photo)
                            <a href="{{ $photo->url() }}" target="_blank"
                               class="group relative aspect-square overflow-hidden rounded-xl bg-slate-800 block">
                                <img src="{{ $photo->url() }}"
                                     alt="{{ $photo->original_filename }}"
                                     loading="lazy"
                                     class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105" />
                                @if($photo->caption)
                                    <div class="absolute bottom-0 inset-x-0 bg-black/60 px-2 py-1.5">
                                        <p class="text-xs text-white truncate">{{ $photo->caption }}</p>
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @empty
            <div class="py-20 text-center">
                <p class="text-slate-500">No photos have been shared for this vehicle.</p>
            </div>
        @endforelse

        @if($photos->isEmpty())
            <div class="py-20 text-center">
                <p class="text-slate-500">No photos have been shared for this vehicle.</p>
            </div>
        @endif

    </div>

    <div class="border-t border-slate-800 px-4 py-4 text-center">
        <p class="text-xs text-slate-600">Shared via PDR Shop Pro</p>
    </div>

</body>
</html>
