@extends('layouts.site', ['title' => $xonca->tr('name'), 'description' => $xonca->tr('short_description')])
@php
    use App\Support\Img;
    use App\Support\Money;
    $photos = $xonca->images ?? [];
    $ours = $xonca->defaultFills->filter(fn ($f) => $f->item && $f->item->is_active);
    $oursTotal = $ours->sum(fn ($f) => $f->item->price * $f->qty);
    $name = $xonca->tr('name');
@endphp

@push('head')
    <script>
        window.XONCA = Object.assign(window.XONCA || {}, {
            items: @json($items),
            xonca: @json($xonca->toViewer() + ['photo' => $photos ? Img::url($photos[0], 600) : null]),
            busy: @json($busy),
            earliest: @json($earliest),
        });
    </script>
@endpush

@section('content')
    <div data-builder class="bg-ivory pb-36 lg:pb-32">
        <div class="container-x pt-8 lg:pt-12">
            <nav class="caps !text-[10px] text-mist">
                <a href="{{ route('home') }}" class="hover:text-ink">{{ __('Ana səhifə') }}</a> <span class="mx-2">/</span>
                <a href="{{ route('catalog') }}" class="hover:text-ink">{{ __('Kolleksiya') }}</a> <span class="mx-2">/</span>
                <span class="text-taupe">{{ $name }}</span>
            </nav>
        </div>

        <div class="container-x mt-8 grid gap-12 lg:mt-12 lg:grid-cols-[1.15fr_1fr] lg:gap-20 xl:gap-28">
            {{-- GALLERY --}}
            <div class="lg:sticky lg:top-32 lg:self-start">
                <div class="relative -mx-5 aspect-[4/5] overflow-hidden bg-sand sm:mx-0">
                    @if ($photos)
                        @foreach ($photos as $p)
                            <img src="{{ Img::url($p, 1400) }}" alt="{{ $name }}" data-gallery-slide="{{ $loop->index }}" class="absolute inset-0 size-full object-cover transition-opacity duration-700 {{ $loop->first ? '' : 'opacity-0' }}">
                        @endforeach
                    @else
                        <x-tray-art :xonca="$xonca" />
                    @endif
                </div>
                @if ($xonca->hasAr())
                    <button type="button" data-ar-open class="group mt-3 flex w-full items-center justify-between gap-6 bg-onyx px-6 py-5 text-left text-ivory transition hover:bg-onyx-3 sm:px-8">
                        <span class="flex items-center gap-5">
                            <svg class="size-9 shrink-0 text-gold-soft" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12V6h6M30 6h6v6M36 28v6h-6M10 34H4v-6"/><path d="M11 25c0-6 4-11 9-11s9 5 9 11"/><ellipse cx="20" cy="26" rx="10" ry="2.6"/></svg>
                            <span>
                                <span class="block font-display text-2xl leading-tight">{{ __('Evinizdə baxın') }}</span>
                                <span class="caps mt-1 block !text-[9px] text-ivory/55">{{ __('Telefonun kamerası ilə, real ölçüdə') }}</span>
                            </span>
                        </span>
                        <span class="caps !text-[10px] text-gold-soft transition group-hover:translate-x-1">3D AR</span>
                    </button>
                @endif
                @if (count($photos) > 1)
                    <div class="mt-3 grid grid-cols-5 gap-3">
                        @foreach ($photos as $p)
                            <button type="button" data-gallery-thumb="{{ $loop->index }}" class="aspect-square overflow-hidden opacity-50 transition hover:opacity-100 data-[on]:opacity-100">
                                <img src="{{ Img::url($p, 300) }}" alt="" class="size-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- DETAILS & BUILDER --}}
            <div>
                <div class="caps !text-[10px] text-gold-deep">N° {{ preg_replace('/\D+/', '', $xonca->code) }}<span class="mx-3"></span>{{ $xonca->occasions->map(fn ($o) => $o->tr('name'))->implode(', ') }}</div>
                <h1 class="mt-5 font-display text-5xl leading-[1.05] lg:text-6xl">{{ $name }}</h1>
                <div class="mt-6 flex flex-wrap items-baseline gap-x-4 gap-y-1">
                    <span class="font-display text-3xl">{{ Money::format($xonca->price) }}</span>
                    <span class="caps !text-[10px] text-mist">{{ __('icarə, çatdırılma və geri alma ilə') }}</span>
                </div>
                <p class="mt-8 max-w-lg text-[16px] leading-relaxed text-taupe">{{ $xonca->tr('description') }}</p>

                <dl class="mt-10 grid grid-cols-3 gap-3 text-center">
                    @foreach ([[__('Forma'), __(\App\Models\Xonca::SHAPES[$xonca->shape] ?? '')], [__('Ölçü'), $xonca->size_cm . ' ' . __('sm')], [__('Örtük'), __(\App\Models\Xonca::COVERS[$xonca->cover_type] ?? '')]] as [$k, $v])
                        <div class="bg-pearl px-2 py-5">
                            <dt class="caps !text-[9px] text-mist">{{ $k }}</dt>
                            <dd class="mt-2 font-display text-lg">{{ $v }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- 1. Ribbon --}}
                <section class="mt-14">
                    <div class="flex items-baseline justify-between">
                        <h2 class="caps text-ink"><span class="mr-3 font-display text-base text-gold italic normal-case">1</span>{{ __('Lentin rəngi') }}</h2>
                        <span data-ribbon-name class="font-display text-lg text-taupe italic"></span>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-4" data-ribbons>
                        @foreach ($xonca->ribbonChoices() as $c)
                            <button type="button" data-ribbon="{{ $c }}" aria-label="{{ __('Lentin rəngi') }} {{ $c }}"
                                class="grid size-11 place-items-center rounded-full ring-1 ring-transparent transition hover:ring-ink/30 data-[on]:ring-gold">
                                <span class="size-8 rounded-full" style="background: {{ $c }}; box-shadow: inset 0 0 0 1px rgba(0,0,0,.12), inset 0 6px 10px rgba(255,255,255,.25)"></span>
                            </button>
                        @endforeach
                    </div>
                </section>

                {{-- 2. Filling --}}
                <section class="mt-14">
                    <h2 class="caps text-ink"><span class="mr-3 font-display text-base text-gold italic normal-case">2</span>{{ __('Dolduruş') }}</h2>
                    <div class="mt-6 space-y-3" data-modes>
                        @foreach ([
                            'ours' => [__('Ustalarımızın dolduruşu'), __('Kolleksiyadakı kimi, tam hazır gəlir'), '+' . Money::format($oursTotal)],
                            'custom' => [__('Öz seçiminiz'), __('Hər əşyanı özünüz seçin'), __('seçimə görə')],
                            'empty' => [__('Boş xonça'), __('İçini evdə özünüz doldurursunuz'), $xonca->empty_discount > 0 ? __(':sum endirim', ['sum' => Money::format($xonca->empty_discount)]) : ''],
                        ] as $mode => [$t, $d, $p])
                            @continue($mode === 'custom' && ! $xonca->fill_allowed)
                            <button type="button" data-mode="{{ $mode }}" class="group flex w-full items-center gap-5 bg-pearl px-5 py-5 text-left ring-1 ring-transparent transition data-[on]:ring-gold">
                                <span class="grid size-5 shrink-0 place-items-center rounded-full ring-1 ring-ink/30 group-data-[on]:ring-gold">
                                    <span class="size-2.5 scale-0 rounded-full bg-gold transition group-data-[on]:scale-100"></span>
                                </span>
                                <span class="flex-1">
                                    <span class="block font-display text-xl leading-tight">{{ $t }}</span>
                                    <span class="mt-1 block text-sm text-mist">{{ $d }}</span>
                                </span>
                                <span class="caps !text-[9px] whitespace-nowrap text-taupe">{{ $p }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div data-pane="ours" class="mt-8">
                        <ul class="grid gap-x-8 gap-y-4 sm:grid-cols-2">
                            @forelse ($ours as $f)
                                <li class="flex items-center gap-4">
                                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-pearl text-gold ring-1 ring-gold/25">{!! \App\Support\Icons::svg($f->item->model, 'size-6') !!}</span>
                                    <span class="flex-1 text-sm">{{ $f->item->tr('name') }}</span>
                                    <span class="caps !text-[10px] text-mist">{{ $f->qty }} {{ __('əd.') }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-mist">{{ __('Bu xonça dolduruşsuz təklif olunur.') }}</li>
                            @endforelse
                        </ul>
                    </div>

                    <div data-pane="custom" class="mt-8 hidden">
                        <div class="scrollbar-none -mx-5 flex gap-2 overflow-x-auto px-5 sm:mx-0 sm:flex-wrap sm:px-0" data-cats>
                            <button type="button" data-cat="all" class="caps shrink-0 bg-pearl px-4 py-2.5 !text-[9px] text-taupe data-[on]:bg-onyx data-[on]:text-ivory">{{ __('Hamısı') }}</button>
                            @foreach ($categories as $c)
                                <button type="button" data-cat="{{ $c->id }}" class="caps shrink-0 bg-pearl px-4 py-2.5 !text-[9px] text-taupe data-[on]:bg-onyx data-[on]:text-ivory">{{ $c->tr('name') }}</button>
                            @endforeach
                        </div>
                        <ul class="mt-6 grid gap-x-8 gap-y-3 sm:grid-cols-2" data-item-grid></ul>
                        <div class="mt-6 flex justify-end gap-6">
                            <button type="button" data-fill-reset class="link-line !text-[10px] text-taupe">{{ __('Hazırdan başla') }}</button>
                            <button type="button" data-fill-clear class="link-line !text-[10px] text-mist">{{ __('Təmizlə') }}</button>
                        </div>
                    </div>

                    <div data-pane="empty" class="mt-8 hidden">
                        <p class="bg-pearl px-6 py-5 font-display text-lg leading-relaxed text-taupe italic">{{ __('Sini, tül və lentlər bəzədilmiş halda gəlir. İçini öz əlinizlə, öz zövqünüzlə doldurursunuz.') }}</p>
                    </div>
                </section>

                {{-- 3. Date --}}
                <section class="mt-14">
                    <h2 class="caps text-ink"><span class="mr-3 font-display text-base text-gold italic normal-case">3</span>{{ __('Məclisin tarixi') }}</h2>
                    <input type="date" data-date min="{{ $earliest }}" class="field mt-4 max-w-xs">
                    <p data-date-note class="mt-3 text-sm text-mist">{{ __('Tarixi seçin, xonçanın həmin gün boş olduğunu dərhal göstərəcəyik.') }}</p>
                </section>

                {{-- Summary --}}
                <section class="mt-14 bg-pearl p-8">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-taupe">{{ __('Xonça icarəsi') }}</dt><dd data-sum-xonca></dd></div>
                        <div class="flex justify-between"><dt class="text-taupe">{{ __('Dolduruş') }}</dt><dd data-sum-fill></dd></div>
                        @if ($xonca->deposit > 0)
                            <div class="flex justify-between text-mist"><dt>{{ __('Depozit, geri qaytarılır') }}</dt><dd>{{ Money::format($xonca->deposit) }}</dd></div>
                        @endif
                    </dl>
                    <div class="mt-6 flex items-baseline justify-between pt-4">
                        <span class="caps text-ink">{{ __('Cəmi') }}</span>
                        <span data-sum-total class="font-display text-4xl"></span>
                    </div>
                    <button type="button" data-add class="btn btn-dark mt-8 hidden w-full lg:flex">{{ __('Səbətə əlavə et') }}</button>
                    <p class="mt-5 text-center text-xs text-mist">{{ __('İndi ödəniş yoxdur. Sifarişdən sonra zəng edib təsdiqləyirik.') }}</p>
                </section>
            </div>
        </div>

        @if ($similar->isNotEmpty())
            <section class="container-x mt-32 pt-8">
                <h2 class="text-center font-display text-4xl lg:text-5xl">{{ __('Bunlar da') }} <em class="text-gold-deep">{{ __('xoşunuza gələ bilər') }}</em></h2>
                <div class="mt-14 grid gap-x-6 gap-y-14 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similar as $x)
                        @include('site.partials.card', ['xonca' => $x])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @if ($xonca->hasAr())
        @include('site.partials.ar-modal', ['glb' => $xonca->glbUrl(), 'usdz' => $xonca->usdzUrl(), 'size' => $xonca->size_cm, 'name' => $name, 'url' => route('xonca', $xonca) . '?ar=1&lang=' . app()->getLocale()])
    @endif

    {{-- Phone: price and the button stay at hand --}}
    <div class="fixed inset-x-0 bottom-0 z-30 bg-ivory/95 shadow-[0_-10px_30px_-20px_rgba(0,0,0,.35)] backdrop-blur lg:hidden" style="padding-bottom: env(safe-area-inset-bottom)">
        <div class="container-x flex items-center gap-5 py-3">
            <div>
                <div class="caps !text-[9px] text-mist">{{ __('Cəmi') }}</div>
                <div data-sum-total class="font-display text-2xl leading-none"></div>
            </div>
            <button type="button" data-add class="btn btn-dark flex-1 !px-4">{{ __('Səbətə əlavə et') }}</button>
        </div>
    </div>
@endsection
