@extends('layouts.site', ['title' => $xonca->name, 'description' => $xonca->short_description])
@php
    use App\Support\Img;
    use App\Support\Money;
    $photos = $xonca->images ?? [];
    $ours = $xonca->defaultFills->filter(fn ($f) => $f->item && $f->item->is_active);
    $oursTotal = $ours->sum(fn ($f) => $f->item->price * $f->qty);
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
                <a href="{{ route('home') }}" class="hover:text-ink">Ana səhifə</a> <span class="mx-2">/</span>
                <a href="{{ route('catalog') }}" class="hover:text-ink">Kolleksiya</a> <span class="mx-2">/</span>
                <span class="text-taupe">{{ $xonca->name }}</span>
            </nav>
        </div>

        <div class="container-x mt-8 grid gap-12 lg:mt-12 lg:grid-cols-[1.15fr_1fr] lg:gap-20 xl:gap-28">
            {{-- GALLERY --}}
            <div class="lg:sticky lg:top-32 lg:self-start">
                <div class="relative -mx-5 aspect-[4/5] overflow-hidden bg-sand sm:mx-0" data-gallery-main>
                    @if ($photos)
                        @foreach ($photos as $p)
                            <img src="{{ Img::url($p, 1400) }}" alt="{{ $xonca->name }}" data-gallery-slide="{{ $loop->index }}" class="absolute inset-0 size-full object-cover transition-opacity duration-700 {{ $loop->first ? '' : 'opacity-0' }}">
                        @endforeach
                    @else
                        <x-tray-art :xonca="$xonca" />
                    @endif
                </div>
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
                <div class="caps !text-[10px] text-gold-deep">N° {{ preg_replace('/\D+/', '', $xonca->code) }} <span class="mx-2 text-mist">·</span> {{ $xonca->occasions->pluck('name')->implode(' · ') }}</div>
                <h1 class="mt-5 font-display text-6xl leading-none lg:text-7xl">{{ $xonca->name }}</h1>
                <div class="mt-6 flex flex-wrap items-baseline gap-x-4 gap-y-1">
                    <span class="font-display text-3xl">{{ Money::format($xonca->price) }}</span>
                    <span class="caps !text-[10px] text-mist">icarə · çatdırılma və geri alma ilə</span>
                </div>
                <p class="mt-8 max-w-lg text-[17px] leading-relaxed text-taupe">{{ $xonca->description }}</p>

                <dl class="mt-10 grid grid-cols-3 border-y border-ink/10 py-6 text-center">
                    @foreach ([['Forma', \App\Models\Xonca::SHAPES[$xonca->shape] ?? ''], ['Ölçü', $xonca->size_cm . ' sm'], ['Örtük', \App\Models\Xonca::COVERS[$xonca->cover_type] ?? '']] as [$k, $v])
                        <div class="border-ink/10 [&:not(:first-child)]:border-l">
                            <dt class="caps !text-[10px] text-mist">{{ $k }}</dt>
                            <dd class="mt-2 font-display text-xl">{{ $v }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- I. Ribbon --}}
                <section class="mt-14">
                    <div class="flex items-baseline justify-between">
                        <h2 class="caps text-ink"><span class="mr-3 font-display text-base text-gold italic normal-case">I</span>Lentin rəngi</h2>
                        <span data-ribbon-name class="font-display text-lg text-taupe italic"></span>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-4" data-ribbons>
                        @foreach ($xonca->ribbonChoices() as $c)
                            <button type="button" data-ribbon="{{ $c }}" aria-label="Lent {{ $c }}"
                                class="grid size-11 place-items-center rounded-full ring-1 ring-transparent transition hover:ring-ink/30 data-[on]:ring-gold">
                                <span class="size-8 rounded-full" style="background: {{ $c }}; box-shadow: inset 0 0 0 1px rgba(0,0,0,.12), inset 0 6px 10px rgba(255,255,255,.25)"></span>
                            </button>
                        @endforeach
                    </div>
                </section>

                {{-- II. Filling --}}
                <section class="mt-14">
                    <h2 class="caps text-ink"><span class="mr-3 font-display text-base text-gold italic normal-case">II</span>Dolduruş</h2>
                    <div class="mt-6 border-t border-ink/10" data-modes>
                        @foreach ([
                            'ours' => ['Ustalarımızın dolduruşu', 'Kolleksiyadakı kimi, tam hazır gəlir', '+ ' . Money::format($oursTotal)],
                            'custom' => ['Öz seçiminiz', 'Hər əşyanı özünüz seçin', 'seçimə görə'],
                            'empty' => ['Boş xonça', 'İçini evdə özünüz doldurursunuz', $xonca->empty_discount > 0 ? '− ' . Money::format($xonca->empty_discount) : ''],
                        ] as $mode => [$t, $d, $p])
                            @continue($mode === 'custom' && ! $xonca->fill_allowed)
                            <button type="button" data-mode="{{ $mode }}" class="group flex w-full items-center gap-5 border-b border-ink/10 py-5 text-left">
                                <span class="grid size-5 shrink-0 place-items-center rounded-full ring-1 ring-ink/30 group-data-[on]:ring-gold">
                                    <span class="size-2.5 scale-0 rounded-full bg-gold transition group-data-[on]:scale-100"></span>
                                </span>
                                <span class="flex-1">
                                    <span class="block font-display text-2xl leading-tight">{{ $t }}</span>
                                    <span class="mt-1 block text-sm text-mist">{{ $d }}</span>
                                </span>
                                <span class="caps !text-[10px] whitespace-nowrap text-taupe">{{ $p }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div data-pane="ours" class="mt-8">
                        <ul class="grid gap-x-8 sm:grid-cols-2">
                            @forelse ($ours as $f)
                                <li class="flex items-center gap-4 border-b border-ink/5 py-3">
                                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-pearl text-gold ring-1 ring-gold/25">{!! \App\Support\Icons::svg($f->item->model, 'size-6') !!}</span>
                                    <span class="flex-1 text-sm">{{ $f->item->name }}</span>
                                    <span class="caps !text-[10px] text-mist">× {{ $f->qty }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-mist">Bu xonça dolduruşsuz təklif olunur.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div data-pane="custom" class="mt-8 hidden">
                        <div class="scrollbar-none -mx-5 flex gap-6 overflow-x-auto px-5 sm:mx-0 sm:px-0" data-cats>
                            <button type="button" data-cat="all" class="caps shrink-0 border-b border-transparent pb-2 !text-[10px] text-mist data-[on]:border-ink data-[on]:text-ink">Hamısı</button>
                            @foreach ($categories as $c)
                                <button type="button" data-cat="{{ $c->id }}" class="caps shrink-0 border-b border-transparent pb-2 !text-[10px] text-mist data-[on]:border-ink data-[on]:text-ink">{{ $c->name }}</button>
                            @endforeach
                        </div>
                        <ul class="mt-4 grid gap-x-8 sm:grid-cols-2" data-item-grid></ul>
                        <div class="mt-6 flex justify-end gap-6">
                            <button type="button" data-fill-reset class="link-line !text-[10px] text-taupe">Hazırdan başla</button>
                            <button type="button" data-fill-clear class="link-line !text-[10px] text-mist">Təmizlə</button>
                        </div>
                    </div>

                    <div data-pane="empty" class="mt-8 hidden">
                        <p class="border-l border-gold pl-6 font-display text-xl leading-relaxed text-taupe italic">Sini, tül və lentlər bəzədilmiş halda gəlir — içini öz əlinizlə, öz zövqünüzlə doldurursunuz.</p>
                    </div>
                </section>

                {{-- III. Date --}}
                <section class="mt-14">
                    <h2 class="caps text-ink"><span class="mr-3 font-display text-base text-gold italic normal-case">III</span>Məclisin tarixi</h2>
                    <input type="date" data-date min="{{ $earliest }}" class="field mt-4 max-w-xs">
                    <p data-date-note class="mt-3 text-sm text-mist">Tarixi seçin — boş olub-olmadığını dərhal göstərəcəyik.</p>
                </section>

                {{-- Summary --}}
                <section class="mt-14 bg-pearl p-8 ring-1 ring-ink/5">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-taupe">Xonça icarəsi</dt><dd data-sum-xonca></dd></div>
                        <div class="flex justify-between"><dt class="text-taupe">Dolduruş</dt><dd data-sum-fill></dd></div>
                        @if ($xonca->deposit > 0)
                            <div class="flex justify-between text-mist"><dt>Depozit · qaytarılır</dt><dd>{{ Money::format($xonca->deposit) }}</dd></div>
                        @endif
                    </dl>
                    <div class="mt-6 flex items-baseline justify-between border-t border-ink/10 pt-6">
                        <span class="caps text-ink">Cəmi</span>
                        <span data-sum-total class="font-display text-4xl"></span>
                    </div>
                    <button type="button" data-add class="btn btn-dark mt-8 hidden w-full lg:flex">Səbətə əlavə et</button>
                    <p class="mt-5 text-center text-xs text-mist">İndi ödəniş yoxdur — sifarişdən sonra zəng edib təsdiqləyirik.</p>
                </section>
            </div>
        </div>

        @if ($similar->isNotEmpty())
            <section class="container-x mt-32 border-t border-ink/10 pt-20">
                <h2 class="text-center font-display text-5xl">Bunlar da <em class="text-gold-deep">xoşunuza gələ bilər</em></h2>
                <div class="mt-14 grid gap-x-6 gap-y-14 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similar as $x)
                        @include('site.partials.card', ['xonca' => $x])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Phone: price and the button stay at hand --}}
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-ink/10 bg-ivory/95 backdrop-blur lg:hidden" style="padding-bottom: env(safe-area-inset-bottom)">
        <div class="container-x flex items-center gap-5 py-3">
            <div>
                <div class="caps !text-[9px] text-mist">Cəmi</div>
                <div data-sum-total class="font-display text-2xl leading-none"></div>
            </div>
            <button type="button" data-add class="btn btn-dark flex-1 !px-4">Səbətə əlavə et</button>
        </div>
    </div>
@endsection
