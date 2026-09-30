@extends('layouts.site', ['title' => $xonca->name, 'description' => $xonca->short_description])

@push('head')
    <script>
        window.XONCA = Object.assign(window.XONCA || {}, {
            items: @json($items),
            xonca: @json($xonca->toViewer()),
            busy: @json($busy),
            earliest: @json($earliest),
        });
    </script>
@endpush

@section('content')
    <div data-builder class="container-x pt-4 pb-32 lg:pt-8 lg:pb-16">
        <nav class="mb-4 hidden text-sm text-ink/50 sm:block">
            <a href="{{ route('home') }}" class="hover:text-wine-700">Ana səhifə</a> /
            <a href="{{ route('catalog') }}" class="hover:text-wine-700">Xonçalar</a> /
            <span class="text-ink/70">{{ $xonca->name }}</span>
        </nav>

        <div class="grid gap-6 lg:grid-cols-[1.2fr_1fr] lg:gap-10">
            {{-- 3D STAGE --}}
            {{-- On a phone the xonça stays pinned on top while the filling is chosen below it. --}}
            <div class="sticky top-16 z-20 -mx-4 sm:top-20 sm:mx-0 lg:top-24 lg:self-start">
                <div class="stage relative overflow-hidden shadow-[0_12px_30px_-18px_rgba(90,16,27,.4)] sm:rounded-[2rem] sm:ring-1 sm:ring-wine-900/5 lg:shadow-none">
                    <div class="h-[40vh] w-full sm:h-[48vh] lg:aspect-square lg:h-auto lg:max-h-[calc(100vh-8rem)]" data-viewer></div>
                    <div class="absolute top-3 left-3 flex gap-2">
                        <button type="button" data-toggle-cover class="chip !bg-white/85 !py-1.5 text-xs backdrop-blur">Örtüyü gizlət</button>
                        <button type="button" data-reset-view class="chip !bg-white/85 !py-1.5 text-xs backdrop-blur" aria-label="Görünüşü sıfırla">↺</button>
                    </div>
                    <button type="button" data-fullscreen class="chip absolute top-3 right-3 !bg-white/85 !py-1.5 text-xs backdrop-blur" aria-label="Tam ekran">⤢</button>
                    <div class="pointer-events-none absolute inset-x-0 bottom-2 hidden text-center text-xs font-semibold text-ink/40 sm:block">Barmaqla fırladın · iki barmaqla yaxınlaşdırın</div>
                </div>
                @if ($xonca->imageUrls())
                    <div class="scrollbar-none mt-3 flex gap-2 overflow-x-auto">
                        @foreach ($xonca->imageUrls() as $img)
                            <a href="{{ $img }}" target="_blank" class="shrink-0"><img src="{{ $img }}" alt="{{ $xonca->name }}" class="size-20 rounded-2xl object-cover ring-1 ring-wine-900/10 sm:size-24"></a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- PANEL --}}
            <div id="qurucu" class="scroll-mt-24">
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($xonca->occasions as $o)
                        <a href="{{ route('catalog.occasion', $o) }}" class="rounded-full px-3 py-1 text-xs font-bold" style="background: {{ $o->accent }}14; color: {{ $o->accent }}">{{ $o->name }}</a>
                    @endforeach
                    <span class="rounded-full bg-ink/5 px-3 py-1 text-xs font-bold text-ink/50">{{ $xonca->code }}</span>
                </div>
                <h1 class="mt-3 text-4xl font-semibold sm:text-5xl">{{ $xonca->name }}</h1>
                <p class="mt-3 text-ink/65">{{ $xonca->description }}</p>

                <dl class="mt-5 grid grid-cols-3 gap-2 text-center text-sm">
                    <div class="rounded-2xl bg-white p-3 ring-1 ring-wine-900/5"><dt class="text-xs text-ink/45">Forma</dt><dd class="font-bold">{{ \App\Models\Xonca::SHAPES[$xonca->shape] ?? '' }}</dd></div>
                    <div class="rounded-2xl bg-white p-3 ring-1 ring-wine-900/5"><dt class="text-xs text-ink/45">Ölçü</dt><dd class="font-bold">{{ $xonca->size_cm }} sm</dd></div>
                    <div class="rounded-2xl bg-white p-3 ring-1 ring-wine-900/5"><dt class="text-xs text-ink/45">Örtük</dt><dd class="font-bold">{{ \App\Models\Xonca::COVERS[$xonca->cover_type] ?? '' }}</dd></div>
                </dl>

                {{-- Ribbon --}}
                <section class="mt-8">
                    <h2 class="flex items-center justify-between font-sans text-sm font-bold tracking-wide text-ink/80 uppercase">
                        <span><span class="text-gold-600">01</span> · Lent rəngi</span>
                        <span data-ribbon-name class="text-xs font-semibold text-ink/45 normal-case"></span>
                    </h2>
                    <div class="mt-3 flex flex-wrap gap-2.5" data-ribbons>
                        @foreach ($xonca->ribbonChoices() as $c)
                            <button type="button" data-ribbon="{{ $c }}" aria-label="Lent rəngi {{ $c }}"
                                class="size-10 rounded-full ring-2 ring-transparent ring-offset-2 ring-offset-cream transition hover:scale-110 data-[on]:ring-wine-600"
                                style="background: radial-gradient(circle at 35% 30%, #ffffff66, transparent 45%), {{ $c }}; box-shadow: inset 0 0 0 1px #0000001a"></button>
                        @endforeach
                    </div>
                </section>

                {{-- Fill --}}
                <section class="mt-8">
                    <h2 class="font-sans text-sm font-bold tracking-wide text-ink/80 uppercase"><span class="text-gold-600">02</span> · İçini kim dolduracaq?</h2>
                    <div class="mt-3 grid grid-cols-3 gap-2 rounded-2xl bg-white p-1.5 ring-1 ring-wine-900/5" data-modes>
                        <button type="button" data-mode="ours" class="rounded-xl px-2 py-3 text-center text-sm font-bold transition data-[on]:bg-wine-600 data-[on]:text-white">🎁<span class="block text-xs sm:text-sm">Hazır</span></button>
                        @if ($xonca->fill_allowed)
                            <button type="button" data-mode="custom" class="rounded-xl px-2 py-3 text-center text-sm font-bold transition data-[on]:bg-wine-600 data-[on]:text-white">✨<span class="block text-xs sm:text-sm">Özüm seçim</span></button>
                        @endif
                        <button type="button" data-mode="empty" class="rounded-xl px-2 py-3 text-center text-sm font-bold transition data-[on]:bg-wine-600 data-[on]:text-white">🏠<span class="block text-xs sm:text-sm">Boş</span></button>
                    </div>

                    <div data-pane="ours" class="mt-4">
                        <p class="text-sm text-ink/60">Ustalarımızın bu xonça üçün hazırladığı dolduruş:</p>
                        <ul class="mt-3 divide-y divide-wine-900/5 rounded-2xl bg-white ring-1 ring-wine-900/5" data-ours-list></ul>
                    </div>

                    <div data-pane="custom" class="mt-4 hidden">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm text-ink/60">Toxunun — xonçaya əlavə olunsun.</p>
                            <div class="flex gap-2 text-xs font-bold">
                                <button type="button" data-fill-reset class="text-wine-600 hover:underline">Hazırdan başla</button>
                                <span class="text-ink/20">|</span>
                                <button type="button" data-fill-clear class="text-ink/50 hover:underline">Təmizlə</button>
                            </div>
                        </div>
                        <div class="scrollbar-none -mx-4 mt-3 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0" data-cats>
                            <button type="button" data-cat="all" class="chip shrink-0 !py-1 text-xs">Hamısı</button>
                            @foreach ($categories as $c)
                                <button type="button" data-cat="{{ $c->id }}" class="chip shrink-0 !py-1 text-xs">{{ $c->name }}</button>
                            @endforeach
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-x-2 gap-y-3 pt-1 xl:grid-cols-3" data-item-grid></div>
                    </div>

                    <div data-pane="empty" class="mt-4 hidden">
                        <div class="rounded-2xl bg-gold-100/70 p-5 text-sm text-ink/75 ring-1 ring-gold-400/30">
                            <b>Xonçanı boş götürürsünüz</b> — sini, örtük və lentlər bəzədilmiş gəlir, içini evdə özünüz doldurursunuz.
                            @if ($xonca->empty_discount > 0)
                                <div class="mt-2 font-bold text-emerald-700">Boş xonça {{ \App\Support\Money::format($xonca->empty_discount) }} ucuzdur.</div>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- Date --}}
                <section class="mt-8">
                    <h2 class="font-sans text-sm font-bold tracking-wide text-ink/80 uppercase"><span class="text-gold-600">03</span> · Məclisin tarixi</h2>
                    <input type="date" data-date min="{{ $earliest }}" class="input mt-3">
                    <p data-date-note class="mt-2 text-sm text-ink/50">Tarixi seçin — boş olub-olmadığını dərhal göstərəcəyik. Tarixi səbətdə də seçə bilərsiniz.</p>
                </section>

                {{-- Summary --}}
                <section class="card mt-8 p-5">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink/60">Xonça icarəsi</dt><dd data-sum-xonca class="font-semibold"></dd></div>
                        <div class="flex justify-between"><dt class="text-ink/60">Dolduruş</dt><dd data-sum-fill class="font-semibold"></dd></div>
                        @if ($xonca->deposit > 0)
                            <div class="flex justify-between text-ink/50"><dt>Depozit (geri qaytarılır)</dt><dd>{{ \App\Support\Money::format($xonca->deposit) }}</dd></div>
                        @endif
                        <div class="flex items-end justify-between border-t border-wine-900/5 pt-3"><dt class="font-bold">Cəmi</dt><dd data-sum-total class="font-display text-3xl font-semibold text-wine-700"></dd></div>
                    </dl>
                    <button type="button" data-add class="btn btn-primary mt-5 hidden w-full py-4 text-base lg:flex">Səbətə əlavə et</button>
                </section>
            </div>
        </div>

        @if ($similar->isNotEmpty())
            <section class="mt-20">
                <h2 class="text-3xl font-semibold">Bunlar da xoşunuza gələ bilər</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similar as $x)
                        @include('site.partials.card', ['xonca' => $x])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Mobile action bar --}}
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-wine-900/10 bg-white/95 px-4 pt-3 backdrop-blur lg:hidden" style="padding-bottom: calc(.75rem + env(safe-area-inset-bottom))">
        <div class="flex items-center gap-3 pr-16">
            <div class="min-w-0">
                <div class="text-xs text-ink/50">Cəmi</div>
                <div data-sum-total class="font-display text-2xl leading-none font-semibold text-wine-700"></div>
            </div>
            <button type="button" data-add class="btn btn-primary flex-1 py-3.5">Səbətə əlavə et</button>
        </div>
    </div>
@endsection
