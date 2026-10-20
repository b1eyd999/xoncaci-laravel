@php($photos = $xonca->images ?? [])
<a href="{{ route('xonca', $xonca) }}" class="img-zoom reveal group block">
    <div class="relative aspect-[4/5] overflow-hidden bg-sand">
        @if ($photos)
            <img src="{{ \App\Support\Img::url($photos[0], 900) }}" alt="{{ $xonca->name }}" loading="lazy" class="absolute inset-0 size-full object-cover">
            @if (isset($photos[1]))
                <img src="{{ \App\Support\Img::url($photos[1], 900) }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover opacity-0 transition-opacity duration-700 group-hover:opacity-100">
            @endif
        @else
            <x-tray-art :xonca="$xonca" class="transition-transform duration-[1600ms] group-hover:scale-[1.03]" />
        @endif
        @if ($xonca->hasAr())
            <span class="caps absolute top-4 left-4 bg-onyx/80 px-3 py-1.5 !text-[9px] text-gold-soft backdrop-blur">3D · AR</span>
        @endif
        <span class="caps absolute inset-x-0 bottom-0 translate-y-full bg-onyx/85 py-4 text-center text-ivory transition-transform duration-500 group-hover:translate-y-0">Ətraflı</span>
    </div>
    <div class="mt-6 flex items-start justify-between gap-4">
        <div>
            <div class="caps !text-[10px] text-gold-deep">{{ $xonca->occasions->pluck('name')->take(2)->implode(' · ') ?: \App\Models\Xonca::STYLES[$xonca->style] ?? '' }}</div>
            <h3 class="mt-2 font-display text-[1.7rem] leading-tight text-ink">{{ $xonca->name }}</h3>
        </div>
        <div class="shrink-0 pt-5 text-right">
            <div class="font-display text-xl text-ink">{{ \App\Support\Money::format($xonca->price) }}</div>
        </div>
    </div>
</a>
