@extends('layouts.site', ['overlay' => true])
@php
    use App\Support\Img;
    $s = fn ($k) => \App\Models\Setting::get($k);
    $l = fn ($k) => \App\Models\Setting::local($k);
    $hero = $s('hero_image') ?: Img::STOCK['hero'];
    // "first line | second line": the second line is set in gold italics.
    [$titleA, $titleB] = array_pad(array_map('trim', preg_split('/\s*[|—]\s*/u', $l('hero_title'), 2)), 2, null);
@endphp

@section('content')
    {{-- HERO --}}
    <section class="relative flex min-h-[100svh] items-end overflow-hidden bg-onyx text-ivory">
        <img src="{{ Img::url($hero, 2400) }}" srcset="{{ Img::srcset($hero) }}" sizes="100vw" alt="" class="ken-burns absolute inset-0 size-full object-cover" fetchpriority="high">
        <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(15,13,12,.72) 0%, rgba(15,13,12,.25) 38%, rgba(15,13,12,.55) 68%, rgba(15,13,12,.96) 100%)"></div>
        <div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(15,13,12,.7) 0%, rgba(15,13,12,.25) 45%, transparent 70%)"></div>

        <div class="container-x relative pt-40 pb-20 lg:pb-28">
            <div class="max-w-4xl">
                <div class="caps hero-in text-gold-soft">{{ __('Toy, nişan və xınayaxdı') }}</div>
                <h1 class="hero-in mt-7 font-display text-[2.7rem] leading-[1.08] sm:text-6xl lg:text-[5.4rem]" style="animation-delay:.15s">
                    {{ $titleA }}@if ($titleB)<br><em class="text-gold-soft">{{ $titleB }}</em>@endif
                </h1>
                <p class="hero-in mt-8 max-w-xl text-[17px] leading-relaxed text-ivory/80" style="animation-delay:.3s">{{ $l('hero_subtitle') }}</p>
                <div class="hero-in mt-12 flex flex-wrap gap-4" style="animation-delay:.45s">
                    <a href="{{ route('catalog') }}" class="btn btn-gold">{{ __('Kolleksiyaya bax') }}</a>
                    <a href="#nece" class="btn btn-line-light">{{ __('Necə işləyir') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- STATEMENT --}}
    <section id="haqqimizda" class="bg-ivory py-28 lg:py-40">
        <div class="container-x">
            <div class="reveal mx-auto max-w-4xl text-center">
                <svg viewBox="0 0 16 16" class="mx-auto h-3 text-gold" fill="none" stroke="currentColor" stroke-width="1"><path d="M8 1l7 7-7 7-7-7z"/></svg>
                <p class="mt-10 font-display text-3xl leading-[1.4] text-ink sm:text-[2.4rem]">
                    {{ __('Hər xonça əl ilə bəzədilir, hər lent sizin seçdiyiniz rəngdə bağlanır.') }}
                    <em class="text-gold-deep">{{ __('Biz ailənizin ən qiymətli gününə zövq gətiririk.') }}</em>
                </p>
            </div>
            <dl class="reveal mx-auto mt-24 grid max-w-5xl gap-y-12 text-center sm:grid-cols-3 sm:gap-x-10">
                @foreach ([[__('Gətiririk və götürürük'), __('Məclisdən bir gün əvvəl çatdırırıq, bir gün sonra özümüz aparırıq.')], [__('İçini siz seçirsiniz'), __('Hazır dolduruş, öz seçiminiz və ya boş xonça. Qərar sizindir.')], [__('Hər dəfə təzə'), __('Hər xonça təmizlənir, lentləri və tülü yenidən bağlanır.')]] as [$t, $d])
                    <div>
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
                    <div class="caps text-gold-soft">{{ __('Məclislər') }}</div>
                    <h2 class="mt-5 font-display text-5xl lg:text-6xl">{{ __('Hər günün') }} <em class="text-gold-soft">{{ __('öz xonçası') }}</em></h2>
                </div>
                <a href="{{ route('catalog') }}" class="link-line text-ivory/80">{{ __('Bütün kolleksiya') }}</a>
            </div>
            <div class="mt-16 grid gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:grid-rows-2 lg:gap-4">
                @foreach ($occasions as $o)
                    @php($img = $o->image ?: (Img::STOCK[$o->slug] ?? Img::STOCK['detail']))
                    <a href="{{ route('catalog.occasion', $o) }}" @class([
                        'img-zoom reveal group relative block overflow-hidden bg-onyx-3',
                        'aspect-[4/5] sm:col-span-2 sm:aspect-[16/10] lg:col-span-6 lg:row-span-2 lg:aspect-auto' => $loop->first,
                        'aspect-[4/5] lg:col-span-3 lg:aspect-[4/4.4]' => ! $loop->first,
                    ]) style="transition-delay: {{ $loop->index * 90 }}ms">
                        <img src="{{ Img::url($img, $loop->first ? 1600 : 900) }}" alt="{{ $o->tr('name') }}" loading="lazy" class="absolute inset-0 size-full object-cover opacity-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-onyx via-onyx/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-7 lg:p-9">
                            <h3 class="font-display {{ $loop->first ? 'text-5xl lg:text-6xl' : 'text-3xl lg:text-4xl' }}">{{ $o->tr('name') }}</h3>
                            <p class="mt-3 max-w-sm text-sm text-ivory/70">{{ $o->tr('tagline') }}</p>
                            <span class="link-line mt-6 text-ivory/80">{{ trans_choice(':count xonça|:count xonça', $o->xoncas_count, ['count' => $o->xoncas_count]) }}</span>
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
                    <div class="caps text-gold-deep">{{ __('Kolleksiya') }}</div>
                    <h2 class="mt-5 font-display text-5xl lg:text-6xl">{{ __('Seçilmiş') }} <em class="text-gold-deep">{{ __('xonçalar') }}</em></h2>
                </div>
                <a href="{{ route('catalog') }}" class="link-line">{{ __('Hamısına bax') }}</a>
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
                    <div class="caps reveal text-gold-soft">{{ __('Necə işləyir') }}</div>
                    <h2 class="reveal mt-5 font-display text-5xl">{{ __('Dörd sakit') }} <em class="text-gold-soft">{{ __('addım') }}</em></h2>
                    <ol class="mt-14 space-y-12">
                        @foreach ([
                            [__('Seçin'), __('Kolleksiyadan məclisinizə uyğun xonçanı seçin və lentin rəngini təyin edin.')],
                            [__('Doldurun'), __('Ustalarımızın hazır dolduruşu, öz seçiminiz və ya evdə özünüz dolduracağınız boş xonça.')],
                            [__('Rezerv edin'), __('Tarixi seçin, sayt xonçanın boş olduğunu dərhal göstərir. Biz zəng edib təsdiqləyirik.')],
                            [__('Rahat olun'), __('Məclisdən bir gün əvvəl gətiririk, bir gün sonra özümüz götürürük.')],
                        ] as $i => [$t, $d])
                            <li class="reveal grid grid-cols-[3.5rem_1fr]" style="transition-delay: {{ $i * 90 }}ms">
                                <span class="font-display text-3xl text-gold-soft italic">{{ $i + 1 }}</span>
                                <div>
                                    <h3 class="caps text-ivory">{{ $t }}</h3>
                                    <p class="mt-3 text-ivory/65">{{ $d }}</p>
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
                <div class="caps text-gold-deep">{{ __('Dolduruş') }}</div>
                <h2 class="mt-5 font-display text-5xl lg:text-6xl">{{ __('Xonçanın içini') }} <em class="text-gold-deep">{{ __('kim dolduracaq?') }}</em></h2>
            </div>
            <div class="mt-20 grid gap-6 md:grid-cols-3">
                @foreach ([
                    [__('Ustalarımız'), __('Kolleksiyadakı kimi: şirniyyat, meyvə, şam və hədiyyələrlə tam hazır, bəzədilmiş halda gəlir.')],
                    [__('Siz seçirsiniz'), __('Kəllə qənddən üzük qutusuna qədər hər şeyi özünüz seçin, qiyməti dərhal görürsünüz.')],
                    [__('Boş xonça'), __('Sini, tül və lentlər bəzədilmiş gəlir, içini evdə öz əlinizlə doldurursunuz. Daha sərfəlidir.')],
                ] as $i => [$t, $d])
                    <div class="reveal bg-ivory px-10 py-14 text-center" style="transition-delay: {{ $i * 90 }}ms">
                        <div class="font-display text-5xl text-gold italic">{{ $i + 1 }}</div>
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
                    <div class="caps text-gold-deep">{{ __('Ailələr deyir') }}</div>
                    <div class="relative mt-12 grid">
                        @foreach ($reviews as $r)
                            <figure data-quote class="col-start-1 row-start-1 transition-opacity duration-1000 {{ $loop->first ? 'opacity-100' : 'pointer-events-none opacity-0' }}">
                                <blockquote class="font-display text-3xl leading-snug text-ink italic sm:text-[2.3rem]">«{{ $r->tr('text') }}»</blockquote>
                                <figcaption class="caps mt-10 text-taupe">{{ $r->name }}, {{ $r->tr('occasion') }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                    <div class="mt-12 flex justify-center gap-3">
                        @foreach ($reviews as $r)
                            <button type="button" data-quote-dot="{{ $loop->index }}" class="size-2 rounded-full bg-ink/20 transition data-[on]:bg-gold" aria-label="{{ $loop->iteration }}"></button>
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
                    <div class="caps text-gold-deep">{{ __('Suallar') }}</div>
                    <h2 class="mt-5 font-display text-5xl">{{ __('Bilmək') }} <em class="text-gold-deep">{{ __('istədikləriniz') }}</em></h2>
                    <p class="mt-8 max-w-sm text-taupe">{{ __('Cavabını tapmadınız? Bizə yazın, şəxsən cavab verəcəyik.') }}</p>
                    <a href="https://wa.me/{{ $s('whatsapp') }}" target="_blank" rel="noopener" class="link-line mt-8">{{ __('WhatsApp ilə yazın') }}</a>
                </div>
                <div class="reveal space-y-3">
                    @foreach ($faqs as $f)
                        <details class="group bg-ivory px-7 [&_summary::-webkit-details-marker]:hidden">
                            <summary class="flex cursor-pointer items-center justify-between gap-6 py-6 font-display text-xl text-ink">
                                {{ $f->tr('question') }}
                                <svg class="size-4 shrink-0 text-gold transition group-open:rotate-180" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M3 6l5 5 5-5"/></svg>
                            </summary>
                            <p class="max-w-2xl pb-7 text-taupe">{{ $f->tr('answer') }}</p>
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
            <div class="caps text-gold-soft">{{ __('Rezervasiya') }}</div>
            <h2 class="mx-auto mt-6 max-w-3xl font-display text-5xl leading-tight lg:text-6xl">{{ __('Tarixiniz yaxınlaşır?') }} <em class="text-gold-soft">{{ __('Xonçanızı indi seçin.') }}</em></h2>
            <p class="mx-auto mt-6 max-w-lg text-ivory/70">{{ __('Mövsümdə ən sevilən xonçalar həftələr əvvəl tutulur.') }}</p>
            <a href="{{ route('catalog') }}" class="btn btn-gold mt-12">{{ __('Kolleksiyaya bax') }}</a>
        </div>
    </section>
@endsection
