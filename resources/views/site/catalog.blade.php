@extends('layouts.site', ['title' => $occasion ? $occasion->tr('name') : __('Kolleksiya')])
@php($q = request()->only(['forma', 'stil', 'sirala', 'tarix']))

@section('content')
    <section class="bg-ivory pt-16 pb-10 lg:pt-24">
        <div class="container-x text-center">
            <nav class="caps !text-[10px] text-mist"><a href="{{ route('home') }}" class="hover:text-ink">{{ __('Ana səhifə') }}</a> <span class="mx-2">/</span> {{ __('Kolleksiya') }}</nav>
            <h1 class="mt-8 font-display text-5xl lg:text-7xl">{{ $occasion ? $occasion->tr('name') : __('Kolleksiya') }}</h1>
            <p class="mx-auto mt-6 max-w-xl text-taupe">{{ $occasion?->tr('tagline') ?? __('Hər xonça əl ilə bəzədilir. Seçin, içini özünüz müəyyənləşdirin və tarixinizə rezerv edin.') }}</p>
        </div>

        <div class="container-x mt-16">
            <nav class="scrollbar-none -mx-5 flex justify-start gap-3 overflow-x-auto px-5 sm:mx-0 sm:flex-wrap sm:justify-center sm:px-0">
                <a href="{{ route('catalog', $q) }}" class="caps shrink-0 px-5 py-3 !text-[10px] {{ ! $occasion ? 'bg-onyx text-ivory' : 'bg-pearl text-taupe hover:text-ink' }}">{{ __('Hamısı') }}</a>
                @foreach ($occasions as $o)
                    <a href="{{ route('catalog.occasion', ['occasion' => $o] + $q) }}" class="caps shrink-0 px-5 py-3 !text-[10px] {{ $occasion?->id === $o->id ? 'bg-onyx text-ivory' : 'bg-pearl text-taupe hover:text-ink' }}">{{ $o->tr('name') }}</a>
                @endforeach
            </nav>

            <form method="get" data-autosubmit class="mt-10 grid grid-cols-2 gap-x-8 gap-y-6 md:flex md:items-end md:justify-center">
                @foreach ([['forma', __('Forma'), \App\Models\Xonca::SHAPES], ['stil', __('Stil'), \App\Models\Xonca::STYLES], ['sirala', __('Sıralama'), ['ucuz' => 'Əvvəl ucuz', 'baha' => 'Əvvəl bahalı']]] as [$name, $label, $options])
                    <label class="block md:w-44">
                        <span class="field-label !text-[10px]">{{ $label }}</span>
                        <select name="{{ $name }}" class="field cursor-pointer !py-2">
                            <option value="">{{ __('Hamısı') }}</option>
                            @foreach ($options as $k => $v)
                                <option value="{{ $k }}" @selected(($q[$name] ?? '') === $k)>{{ __($v) }}</option>
                            @endforeach
                        </select>
                    </label>
                @endforeach
                <label class="block md:w-44">
                    <span class="field-label !text-[10px]">{{ __('Məclis tarixi') }}</span>
                    <input type="date" name="tarix" value="{{ $date }}" min="{{ now()->toDateString() }}" class="field !py-2">
                </label>
                @if ($q)
                    <a href="{{ $occasion ? route('catalog.occasion', $occasion) : route('catalog') }}" class="link-line col-span-2 justify-self-center text-taupe md:mb-2">{{ __('Təmizlə') }}</a>
                @endif
            </form>
            @if ($date)
                <p class="caps mt-8 text-center !text-[10px] text-gold-deep">{{ __(':date tarixinə boş olan xonçalar', ['date' => \Carbon\Carbon::parse($date)->translatedFormat('j F Y')]) }}</p>
            @endif
        </div>
    </section>

    <section class="bg-ivory pb-32">
        <div class="container-x">
            @if ($xoncas->isEmpty())
                <div class="mx-auto max-w-lg py-24 text-center">
                    <h2 class="font-display text-4xl">{{ __('Uyğun xonça tapılmadı') }}</h2>
                    <p class="mt-4 text-taupe">{{ __('Seçimləri dəyişin və ya bizə yazın, sizin üçün xüsusi xonça hazırlayaq.') }}</p>
                </div>
            @else
                <div class="mt-8 grid gap-x-6 gap-y-16 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($xoncas as $xonca)
                        @include('site.partials.card', ['xonca' => $xonca])
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
