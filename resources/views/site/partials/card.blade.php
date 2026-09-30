@php($photo = $xonca->imageUrls()[0] ?? null)
<a href="{{ route('xonca', $xonca) }}" class="card group reveal relative flex flex-col overflow-hidden transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_60px_-20px_rgba(90,16,27,0.35)]">
    <div class="stage relative aspect-[4/3.4] overflow-hidden">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $xonca->name }}" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
        @else
            <div class="shimmer absolute inset-0" data-thumb-skeleton></div>
            <img data-thumb='@json($xonca->toViewer())' alt="{{ $xonca->name }} — 3D görünüş" class="absolute inset-0 size-full object-contain opacity-0 transition duration-700 group-hover:scale-105">
        @endif
        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
            @if ($xonca->is_featured)
                <span class="rounded-full bg-wine-600 px-2.5 py-1 text-[11px] font-bold text-white">Seçilmiş</span>
            @endif
            <span class="rounded-full bg-white/85 px-2.5 py-1 text-[11px] font-bold text-wine-700 backdrop-blur">3D</span>
        </div>
        <span class="absolute right-3 bottom-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-ink/70 backdrop-blur">{{ \App\Models\Xonca::SHAPES[$xonca->shape] ?? '' }} · {{ $xonca->size_cm }} sm</span>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-start justify-between gap-3">
            <h3 class="font-display text-xl font-semibold text-ink">{{ $xonca->name }}</h3>
            <div class="text-right">
                <div class="text-lg font-extrabold whitespace-nowrap text-wine-700">{{ \App\Support\Money::format($xonca->price) }}</div>
                <div class="text-[11px] text-ink/45">icarə</div>
            </div>
        </div>
        <p class="mt-1.5 line-clamp-2 text-sm text-ink/60">{{ $xonca->short_description }}</p>
        <div class="mt-auto flex flex-wrap gap-1.5 pt-4">
            @foreach ($xonca->occasions as $o)
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold" style="background: {{ $o->accent }}14; color: {{ $o->accent }}">{{ $o->name }}</span>
            @endforeach
        </div>
    </div>
</a>
