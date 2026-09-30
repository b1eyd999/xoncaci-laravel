@extends('layouts.site')
@php($s = fn ($k) => \App\Models\Setting::get($k))

@push('head')
    <script>window.XONCA = Object.assign(window.XONCA || {}, { items: @json($items) });</script>
@endpush

@section('content')
    {{-- HERO --}}
    <section class="ornament relative overflow-hidden">
        <div class="pointer-events-none absolute -top-40 -right-40 size-[38rem] rounded-full bg-gold-200/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-40 size-[30rem] rounded-full bg-wine-100/60 blur-3xl"></div>
        <div class="container-x relative grid items-center gap-6 pt-6 pb-14 lg:grid-cols-[1.05fr_1fr] lg:gap-10 lg:pt-12 lg:pb-24">
            <div class="order-2 lg:order-1">
                <div class="eyebrow fade-up"><span class="h-px w-8 bg-gold-500"></span> Toy · Nişan · Xınayaxdı</div>
                <h1 class="fade-up mt-4 text-[2.4rem] leading-[1.08] font-semibold text-ink sm:text-5xl lg:text-[4.1rem]" style="animation-delay:.08s">
                    {!! preg_replace('/—\s*(.+)$/u', '— <span class="gold-text italic">$1</span>', e($s('hero_title'))) !!}
                </h1>
                <p class="fade-up mt-5 max-w-xl text-lg text-ink/65" style="animation-delay:.16s">{{ $s('hero_subtitle') }}</p>
                <div class="fade-up mt-8 flex flex-wrap gap-3" style="animation-delay:.24s">
                    <a href="{{ route('catalog') }}" class="btn btn-primary px-8 py-4 text-base">Xonçaları seç</a>
                    @if ($hero)
                        <a href="{{ route('xonca', $hero) }}#qurucu" class="btn btn-ghost px-7 py-4 text-base">✨ İçini özün doldur</a>
                    @endif
                </div>
                <dl class="fade-up mt-10 grid max-w-lg grid-cols-3 gap-3" style="animation-delay:.32s">
                    @foreach ([[$xoncaCount . '+', 'xonça modeli'], [$itemCount . '+', 'dolduruş seçimi'], ['3D', 'canlı görünüş']] as [$n, $l])
                        <div class="rounded-2xl bg-white/70 p-3 text-center ring-1 ring-wine-900/5 backdrop-blur sm:p-4">
                            <dt class="font-display text-2xl font-semibold text-wine-700 sm:text-3xl">{{ $n }}</dt>
                            <dd class="text-xs text-ink/55 sm:text-sm">{{ $l }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            @if ($hero)
                <div class="order-1 lg:order-2">
                    <div class="relative mx-auto aspect-square w-full max-w-[640px]">
                        <div class="absolute inset-[6%] rounded-full bg-gradient-to-b from-white to-gold-100 shadow-[inset_0_-30px_80px_rgba(201,161,74,.25)]"></div>
                        <div class="absolute inset-[6%] rounded-full ring-1 ring-gold-400/30"></div>
                        <div class="absolute inset-0" data-hero-viewer='@json($hero->toViewer())'></div>
                        <div class="pointer-events-none absolute inset-x-0 bottom-2 text-center text-xs font-semibold tracking-wide text-ink/40">↻ fırladın · yaxınlaşdırın</div>
                    </div>
                    <div class="scrollbar-none -mx-4 -mt-2 flex gap-2 overflow-x-auto px-4 sm:justify-center" data-hero-switch>
                        @foreach ($featured->take(5) as $x)
                            <button type="button" data-config='@json($x->toViewer())' data-href="{{ route('xonca', $x) }}"
                                class="chip {{ $loop->first ? 'chip-active' : '' }} shrink-0 !px-3 !py-1 text-xs whitespace-nowrap">{{ $x->name }}</button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- OCCASIONS --}}
    <section id="bayramlar" class="container-x scroll-mt-24 py-16 sm:py-20">
        <div class="reveal mx-auto max-w-2xl text-center">
            <div class="eyebrow justify-center">Hər məclisə öz xonçası</div>
            <h2 class="mt-3 text-3xl font-semibold sm:text-5xl">Hansı gününüz üçün?</h2>
        </div>
        <div class="mt-10 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-5">
            @foreach ($occasions as $o)
                <a href="{{ route('catalog.occasion', $o) }}" class="reveal group relative overflow-hidden rounded-3xl p-5 text-white shadow-lg transition hover:-translate-y-1 sm:p-6 {{ $loop->first ? 'col-span-2 lg:col-span-1' : '' }}"
                   style="background: linear-gradient(150deg, {{ $o->accent }}, {{ $o->accent }}cc 60%, #2b1b20); transition-delay: {{ $loop->index * 60 }}ms">
                    <div class="pointer-events-none absolute -right-8 -bottom-10 size-36 rounded-full border-[18px] border-white/10 transition group-hover:scale-110"></div>
                    <div class="font-display text-2xl font-semibold sm:text-3xl">{{ $o->name }}</div>
                    <p class="mt-2 text-sm text-white/75">{{ $o->tagline }}</p>
                    <div class="mt-6 inline-flex items-center gap-1 text-sm font-bold text-gold-200">{{ $o->xoncas_count }} xonça <span class="transition group-hover:translate-x-1">→</span></div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section id="nece" class="scroll-mt-24 bg-white/60 py-16 sm:py-24">
        <div class="container-x">
            <div class="reveal mx-auto max-w-2xl text-center">
                <div class="eyebrow justify-center">4 addım</div>
                <h2 class="mt-3 text-3xl font-semibold sm:text-5xl">Necə işləyir?</h2>
            </div>
            <ol class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Xonçanı seçin', 'Formasına, rənginə və bayrama görə — hər birini 3D-də fırladıb baxın.', 'M4 20h16M6 20c0-5 2.7-9 6-9s6 4 6 9M12 11V7'],
                    ['İçini doldurun', 'Hazır dolduruş, 3D qurucuda öz seçiminiz, ya da boş götürüb evdə özünüz.', 'M12 4v16m-8-8h16'],
                    ['Tarixi seçin', 'Sayt boş günləri özü göstərir — xonça sizin günə rezerv olunur.', 'M5 7h14v12H5zM8 4v4m8-4v4M5 11h14'],
                    ['Gətirib-aparırıq', 'Məclisdən bir gün əvvəl çatdırırıq, bir gün sonra özümüz götürürük.', 'M3 16V7h11v9M14 10h4l3 3v3h-7M7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z'],
                ] as $i => [$t, $d, $icon])
                    <li class="card reveal relative p-6" style="transition-delay: {{ $i * 80 }}ms">
                        <div class="flex items-center justify-between">
                            <span class="grid size-12 place-items-center rounded-2xl bg-wine-50 text-wine-600">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                            </span>
                            <span class="font-display text-5xl text-gold-200">{{ $i + 1 }}</span>
                        </div>
                        <h3 class="mt-5 text-xl font-semibold">{{ $t }}</h3>
                        <p class="mt-2 text-sm text-ink/60">{{ $d }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- FEATURED --}}
    <section class="container-x py-16 sm:py-24">
        <div class="reveal flex flex-wrap items-end justify-between gap-4">
            <div>
                <div class="eyebrow">Kolleksiya</div>
                <h2 class="mt-3 text-3xl font-semibold sm:text-5xl">Ən sevilən xonçalar</h2>
            </div>
            <a href="{{ route('catalog') }}" class="btn btn-ghost">Hamısına bax →</a>
        </div>
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($featured as $xonca)
                @include('site.partials.card', ['xonca' => $xonca])
            @endforeach
        </div>
    </section>

    {{-- FILL MODES --}}
    <section class="stage-dark relative overflow-hidden py-16 text-white sm:py-24">
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image:radial-gradient(circle at 1px 1px,#e3c47f 1px,transparent 0);background-size:26px 26px"></div>
        <div class="container-x relative">
            <div class="reveal mx-auto max-w-2xl text-center">
                <div class="eyebrow justify-center text-gold-300">Sizin seçiminiz</div>
                <h2 class="mt-3 text-3xl font-semibold sm:text-5xl">Xonçanın içini <span class="gold-text italic">kim dolduracaq?</span></h2>
            </div>
            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['🎁', 'Biz dolduraq', 'Ustalarımızın hazırladığı dolduruşla — tam hazır, bəzədilmiş gəlir.'],
                    ['✨', 'Saytda özünüz seçin', '3D qurucuda şirniyyat, meyvə, şam, hədiyyə əlavə edin — necə olacağını dərhal görün.'],
                    ['🏠', 'Boş götürün', 'Xonçanı boş icarəyə götürün, evdə öz əlinizlə doldurun — daha sərfəli.'],
                ] as $i => [$e, $t, $d])
                    <div class="reveal rounded-3xl border border-white/10 bg-white/5 p-7 backdrop-blur transition hover:bg-white/10" style="transition-delay: {{ $i * 80 }}ms">
                        <div class="text-4xl">{{ $e }}</div>
                        <h3 class="mt-4 text-2xl font-semibold text-gold-200">{{ $t }}</h3>
                        <p class="mt-2 text-white/70">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- REVIEWS --}}
    @if ($reviews->isNotEmpty())
        <section class="container-x py-16 sm:py-24">
            <div class="reveal mx-auto max-w-2xl text-center">
                <div class="eyebrow justify-center">Rəylər</div>
                <h2 class="mt-3 text-3xl font-semibold sm:text-5xl">Xoşbəxt ailələr deyir</h2>
            </div>
            <div class="scrollbar-none -mx-4 mt-10 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 md:mx-0 md:grid md:grid-cols-3 md:overflow-visible md:px-0">
                @foreach ($reviews as $r)
                    <figure class="card reveal w-[82%] shrink-0 snap-center p-6 md:w-auto">
                        <div class="text-gold-500">{{ str_repeat('★', $r->rating) }}<span class="text-ink/15">{{ str_repeat('★', 5 - $r->rating) }}</span></div>
                        <blockquote class="mt-3 text-ink/75">“{{ $r->text }}”</blockquote>
                        <figcaption class="mt-5 flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-full bg-wine-50 font-bold text-wine-700">{{ mb_substr($r->name, 0, 1) }}</span>
                            <span><span class="block font-bold">{{ $r->name }}</span><span class="text-xs text-ink/50">{{ $r->occasion }}</span></span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section id="suallar" class="scroll-mt-24 bg-white/60 py-16 sm:py-24">
            <div class="container-x grid gap-10 lg:grid-cols-[1fr_1.4fr]">
                <div class="reveal">
                    <div class="eyebrow">Suallar</div>
                    <h2 class="mt-3 text-3xl font-semibold sm:text-5xl">Tez-tez soruşulanlar</h2>
                    <p class="mt-4 text-ink/60">Cavabını tapmadınız? Bizə yazın — dərhal cavab verəcəyik.</p>
                    <a href="https://wa.me/{{ $s('whatsapp') }}" target="_blank" rel="noopener" class="btn btn-primary mt-6">WhatsApp-da yazın</a>
                </div>
                <div class="space-y-3">
                    @foreach ($faqs as $f)
                        <details class="card reveal group p-0 [&_summary::-webkit-details-marker]:hidden" @if($loop->first) open @endif>
                            <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 text-lg font-semibold">
                                {{ $f->question }}
                                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-wine-50 text-wine-600 transition group-open:rotate-45">+</span>
                            </summary>
                            <div class="px-5 pb-5 text-ink/65">{{ $f->answer }}</div>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="container-x pt-16 sm:pt-24">
        <div class="reveal relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-wine-600 to-wine-900 px-6 py-12 text-center text-white sm:px-12 sm:py-16">
            <div class="pointer-events-none absolute -top-20 -left-20 size-72 rounded-full bg-gold-400/20 blur-3xl"></div>
            <h2 class="relative text-3xl font-semibold sm:text-5xl">Tarixiniz yaxındır?</h2>
            <p class="relative mx-auto mt-3 max-w-xl text-white/75">Məşhur xonçalar mövsümdə tez tutulur. Bu gün seçin — tarixinizə rezerv edək.</p>
            <a href="{{ route('catalog') }}" class="btn btn-gold relative mt-8 px-9 py-4 text-base">Xonçanı seç</a>
        </div>
    </section>
@endsection
