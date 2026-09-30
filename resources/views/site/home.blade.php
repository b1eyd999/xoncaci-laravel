@extends('layouts.site', ['overlay' => true])
@php
    use App\Support\Img;
    $s = fn ($k) => \App\Models\Setting::get($k);
    $hero = $s('hero_image') ?: Img::STOCK['hero'];
    [$titleA, $titleB] = array_pad(array_map('trim', explode('—', $s('hero_title'), 2)), 2, null);
@endphp

@section('content')
    {{-- HERO --}}
    <section class="relative flex min-h-[100svh] items-end overflow-hidden bg-onyx text-ivory">
        <img src="{{ Img::url($hero, 2400) }}" srcset="{{ Img::srcset($hero) }}" sizes="100vw" alt="" class="ken-burns absolute inset-0 size-full object-cover" fetchpriority="high">
        <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(15,13,12,.72) 0%, rgba(15,13,12,.25) 38%, rgba(15,13,12,.55) 68%, rgba(15,13,12,.96) 100%)"></div>
        <div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(15,13,12,.7) 0%, rgba(15,13,12,.25) 45%, transparent 70%)"></div>

        <div class="container-x relative pt-40 pb-20 lg:pb-28">
            <div class="max-w-4xl">
                <div class="caps hero-in flex items-center gap-4 text-gold-soft"><span class="h-px w-12 bg-gold-soft/60"></span>Toy · Nişan · Xınayaxdı</div>
                <h1 class="hero-in mt-8 font-display text-[3.1rem] leading-[1.02] sm:text-7xl lg:text-[6.4rem]" style="animation-delay:.15s">
                    {{ $titleA }}@if ($titleB)<br><em class="text-gold-soft">{{ $titleB }}</em>@endif
                </h1>
                <p class="hero-in mt-8 max-w-xl text-lg leading-relaxed text-ivory/75" style="animation-delay:.3s">{{ $s('hero_subtitle') }}</p>
                <div class="hero-in mt-12 flex flex-wrap gap-4" style="animation-delay:.45s">
                    <a href="{{ route('catalog') }}" class="btn btn-gold">Kolleksiyaya bax</a>
                    <a href="#nece" class="btn btn-line-light">Necə işləyir</a>
                </div>
            </div>
        </div>
        <a href="#haqqimizda" class="caps absolute right-6 bottom-10 hidden items-center gap-4 text-ivory/60 lg:right-16 lg:flex" aria-label="Aşağı">
            Kəşf edin <span class="scroll-cue relative block h-14 w-px bg-ivory/15"></span>
        </a>
    </section>

    {{-- STATEMENT --}}
    <section id="haqqimizda" class="bg-ivory py-28 lg:py-40">
        <div class="container-x">
            <div class="reveal mx-auto max-w-4xl text-center">
                <svg viewBox="0 0 80 16" class="mx-auto h-4 text-gold" fill="none" stroke="currentColor" stroke-width=".8"><path d="M0 8h30M50 8h30"/><path d="M40 2l6 6-6 6-6-6z"/></svg>
                <p class="mt-12 font-display text-3xl leading-[1.35] text-ink sm:text-[2.6rem]">
                    Hər xonça əl ilə bəzədilir, hər lent sizin seçdiyiniz rəngdə bağlanır. Biz sadəcə icarəyə vermirik —
                    <em class="text-gold-deep">ailənizin ən qiymətli gününə zövq gətiririk.</em>
                </p>
            </div>
            <dl class="reveal mx-auto mt-24 grid max-w-5xl gap-y-10 text-center sm:grid-cols-3">
                @foreach ([['Gətiririk və götürürük', 'Məclisdən bir gün əvvəl çatdırırıq, bir gün sonra özümüz aparırıq.'], ['İçini siz seçirsiniz', 'Hazır dolduruş, öz seçiminiz və ya boş xonça — qərar sizindir.'], ['Hər dəfə təzə', 'Hər xonça təmizlənir, lentləri və tülü yenidən bağlanır.']] as [$t, $d])
                    <div class="px-8 sm:border-l sm:border-ink/10 sm:first:border-0">
                        <dt class="caps text-ink">{{ $t }}</dt>
                        <dd class="mt-4 text-taupe">{{ $d }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- OCCASIONS --}}
    <section id="bayramlar" class="scroll-mt-24 bg-onyx py-28 text-ivory lg:py-36">
        <div class="container-x">
            <div class="reveal flex flex-wrap items-end justify-between gap-8">
                <div>
                    <div class="caps text-gold-soft">Məclislər</div>
                    <h2 class="mt-5 font-display text-5xl lg:text-7xl">Hər günün <em class="text-gold-soft">öz xonçası</em></h2>
                </div>
                <a href="{{ route('catalog') }}" class="link-line text-ivory/80">Bütün kolleksiya</a>
            </div>
            <div class="mt-16 grid gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:grid-rows-2 lg:gap-4">
                @foreach ($occasions as $o)
                    @php($img = $o->image ?: (Img::STOCK[$o->slug] ?? Img::STOCK['detail']))
                    <a href="{{ route('catalog.occasion', $o) }}" @class([
                        'img-zoom reveal group relative block overflow-hidden bg-onyx-3',
                        'aspect-[4/5] sm:col-span-2 sm:aspect-[16/10] lg:col-span-6 lg:row-span-2 lg:aspect-auto' => $loop->first,
                        'aspect-[4/5] lg:col-span-3 lg:aspect-[4/4.4]' => ! $loop->first,
                    ]) style="transition-delay: {{ $loop->index * 90 }}ms">
                        <img src="{{ Img::url($img, $loop->first ? 1600 : 900) }}" alt="{{ $o->name }}" loading="lazy" class="absolute inset-0 size-full object-cover opacity-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-onyx via-onyx/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-7 lg:p-9">
                            <div class="caps text-gold-soft/80">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                            <h3 class="mt-3 font-display {{ $loop->first ? 'text-5xl lg:text-6xl' : 'text-4xl' }}">{{ $o->name }}</h3>
                            <p class="mt-3 max-w-sm text-sm text-ivory/65">{{ $o->tagline }}</p>
                            <span class="link-line mt-6 text-ivory/80">{{ $o->xoncas_count }} xonça</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- COLLECTION --}}
    <section class="bg-ivory py-28 lg:py-40">
        <div class="container-x">
            <div class="reveal flex flex-wrap items-end justify-between gap-8">
                <div>
                    <div class="caps text-gold-deep">Kolleksiya</div>
                    <h2 class="mt-5 font-display text-5xl lg:text-7xl">Seçilmiş <em class="text-gold-deep">xonçalar</em></h2>
                </div>
                <a href="{{ route('catalog') }}" class="link-line">Hamısına bax</a>
            </div>
            <div class="mt-16 grid gap-x-6 gap-y-16 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featured as $xonca)
                    @include('site.partials.card', ['xonca' => $xonca])
                @endforeach
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section id="nece" class="scroll-mt-20 bg-onyx text-ivory">
        <div class="grid lg:grid-cols-2">
            <div class="img-zoom relative min-h-[60vh] overflow-hidden lg:min-h-[52rem]">
                <img src="{{ Img::url($s('craft_image') ?: Img::STOCK['craft'], 1600) }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover">
                <div class="absolute inset-0 bg-onyx/20"></div>
            </div>
            <div class="flex items-center px-5 py-24 sm:px-12 lg:px-20 xl:px-28">
                <div class="w-full max-w-xl">
                    <div class="caps text-gold-soft reveal">Necə işləyir</div>
                    <h2 class="reveal mt-5 font-display text-5xl lg:text-6xl">Dörd sakit <em class="text-gold-soft">addım</em></h2>
                    <ol class="mt-14">
                        @foreach ([
                            ['Seçin', 'Kolleksiyadan məclisinizə uyğun xonçanı seçin və lentin rəngini təyin edin.'],
                            ['Doldurun', 'Ustalarımızın hazır dolduruşu, öz seçiminiz və ya boş xonça — evdə özünüz doldurursunuz.'],
                            ['Rezerv edin', 'Tarixi seçin — sayt boş olub-olmadığını dərhal göstərir. Biz zəng edib təsdiqləyirik.'],
                            ['Rahat olun', 'Məclisdən bir gün əvvəl gətiririk, bir gün sonra özümüz götürürük.'],
                        ] as $i => [$t, $d])
                            <li class="reveal grid grid-cols-[3.5rem_1fr] border-t border-ivory/10 py-8 last:border-b" style="transition-delay: {{ $i * 90 }}ms">
                                <span class="font-display text-2xl text-gold-soft italic">{{ ['I', 'II', 'III', 'IV'][$i] }}</span>
                                <div>
                                    <h3 class="caps text-ivory">{{ $t }}</h3>
                                    <p class="mt-3 text-ivory/60">{{ $d }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>

    {{-- FILL MODES --}}
    <section class="bg-pearl py-28 lg:py-40">
        <div class="container-x">
            <div class="reveal mx-auto max-w-3xl text-center">
                <div class="caps text-gold-deep">Dolduruş</div>
                <h2 class="mt-5 font-display text-5xl lg:text-7xl">Xonçanın içini <em class="text-gold-deep">kim dolduracaq?</em></h2>
            </div>
            <div class="mt-20 grid gap-px bg-ink/10 md:grid-cols-3">
                @foreach ([
                    ['Ustalarımız', 'Kolleksiyadakı kimi — şirniyyat, meyvə, şam və hədiyyələrlə tam hazır, bəzədilmiş halda gəlir.'],
                    ['Siz seçirsiniz', 'Kəllə qənddən üzük qutusuna qədər hər şeyi özünüz seçin — qiyməti dərhal görürsünüz.'],
                    ['Boş xonça', 'Sini, tül və lentlər bəzədilmiş gəlir, içini evdə öz əlinizlə doldurursunuz. Daha sərfəlidir.'],
                ] as $i => [$t, $d])
                    <div class="reveal bg-pearl px-10 py-14 text-center" style="transition-delay: {{ $i * 90 }}ms">
                        <div class="font-display text-5xl text-gold italic">{{ ['I', 'II', 'III'][$i] }}</div>
                        <h3 class="caps mt-8 text-ink">{{ $t }}</h3>
                        <p class="mx-auto mt-4 max-w-xs text-taupe">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- TESTIMONIALS --}}
    @if ($reviews->isNotEmpty())
        <section class="bg-ivory py-28 lg:py-36" data-quotes>
            <div class="container-x">
                <div class="reveal mx-auto max-w-4xl text-center">
                    <div class="caps text-gold-deep">Ailələr deyir</div>
                    <div class="relative mt-12 grid">
                        @foreach ($reviews as $r)
                            <figure data-quote class="col-start-1 row-start-1 transition-opacity duration-1000 {{ $loop->first ? 'opacity-100' : 'pointer-events-none opacity-0' }}">
                                <blockquote class="font-display text-3xl leading-snug text-ink italic sm:text-[2.6rem]">“{{ $r->text }}”</blockquote>
                                <figcaption class="caps mt-10 text-taupe">{{ $r->name }} <span class="mx-3 text-gold">—</span> {{ $r->occasion }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                    <div class="mt-12 flex justify-center gap-3">
                        @foreach ($reviews as $r)
                            <button type="button" data-quote-dot="{{ $loop->index }}" class="h-px w-8 bg-ink/20 transition-all data-[on]:w-14 data-[on]:bg-gold" aria-label="Rəy {{ $loop->iteration }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section id="suallar" class="scroll-mt-20 bg-pearl py-28 lg:py-36">
            <div class="container-x grid gap-16 lg:grid-cols-[1fr_1.5fr]">
                <div class="reveal">
                    <div class="caps text-gold-deep">Suallar</div>
                    <h2 class="mt-5 font-display text-5xl lg:text-6xl">Bilmək <em class="text-gold-deep">istədikləriniz</em></h2>
                    <p class="mt-8 max-w-sm text-taupe">Cavabını tapmadınız? Bizə yazın — şəxsən cavab verəcəyik.</p>
                    <a href="https://wa.me/{{ $s('whatsapp') }}" target="_blank" rel="noopener" class="link-line mt-8">WhatsApp-da yazın</a>
                </div>
                <div class="reveal border-t border-ink/15">
                    @foreach ($faqs as $f)
                        <details class="group border-b border-ink/15 [&_summary::-webkit-details-marker]:hidden">
                            <summary class="flex cursor-pointer items-center justify-between gap-6 py-7 font-display text-2xl text-ink">
                                {{ $f->question }}
                                <span class="relative size-4 shrink-0 before:absolute before:top-1/2 before:left-0 before:h-px before:w-4 before:bg-gold after:absolute after:top-0 after:left-1/2 after:h-4 after:w-px after:bg-gold after:transition group-open:after:scale-y-0"></span>
                            </summary>
                            <p class="max-w-2xl pb-8 text-taupe">{{ $f->answer }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="relative overflow-hidden bg-onyx py-40 text-center text-ivory lg:py-56">
        <img src="{{ Img::url($s('cta_image') ?: Img::STOCK['cta'], 2000) }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover opacity-45">
        <div class="absolute inset-0 bg-gradient-to-b from-onyx/60 via-onyx/30 to-onyx/80"></div>
        <div class="container-x reveal relative">
            <div class="caps text-gold-soft">Rezervasiya</div>
            <h2 class="mx-auto mt-6 max-w-3xl font-display text-5xl leading-tight lg:text-7xl">Tarixiniz yaxınlaşır? <em class="text-gold-soft">Xonçanızı indi seçin.</em></h2>
            <p class="mx-auto mt-6 max-w-lg text-ivory/65">Mövsümdə ən sevilən xonçalar həftələr əvvəl tutulur.</p>
            <a href="{{ route('catalog') }}" class="btn btn-gold mt-12">Kolleksiyaya bax</a>
        </div>
    </section>
@endsection
