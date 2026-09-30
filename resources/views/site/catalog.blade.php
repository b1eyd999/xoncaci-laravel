@extends('layouts.site', ['title' => $occasion ? $occasion->name . ' xonçaları' : 'Kolleksiya'])
@php($q = request()->only(['forma', 'stil', 'sirala', 'tarix']))

@section('content')
    <section class="bg-ivory pt-16 pb-10 lg:pt-24">
        <div class="container-x text-center">
            <nav class="caps !text-[10px] text-mist"><a href="{{ route('home') }}" class="hover:text-ink">Ana səhifə</a> <span class="mx-2">/</span> Kolleksiya</nav>
            <h1 class="mt-8 font-display text-6xl lg:text-8xl">{{ $occasion ? $occasion->name : 'Kolleksiya' }}</h1>
            <p class="mx-auto mt-6 max-w-xl text-taupe">{{ $occasion?->tagline ?? 'Hər xonça əl ilə bəzədilir. Seçin, içini özünüz müəyyənləşdirin və tarixinizə rezerv edin.' }}</p>
        </div>

        <div class="container-x mt-16">
            <nav class="scrollbar-none -mx-5 flex justify-start gap-8 overflow-x-auto border-b border-ink/10 px-5 sm:mx-0 sm:justify-center sm:px-0">
                <a href="{{ route('catalog', $q) }}" class="caps shrink-0 border-b pb-4 {{ ! $occasion ? 'border-ink text-ink' : 'border-transparent text-mist hover:text-ink' }}">Hamısı</a>
                @foreach ($occasions as $o)
                    <a href="{{ route('catalog.occasion', ['occasion' => $o] + $q) }}" class="caps shrink-0 border-b pb-4 {{ $occasion?->id === $o->id ? 'border-ink text-ink' : 'border-transparent text-mist hover:text-ink' }}">{{ $o->name }}</a>
                @endforeach
            </nav>

            <form method="get" data-autosubmit class="mt-8 grid grid-cols-2 gap-x-8 gap-y-6 md:flex md:items-end md:justify-center">
                @foreach ([['forma', 'Forma', \App\Models\Xonca::SHAPES], ['stil', 'Stil', \App\Models\Xonca::STYLES], ['sirala', 'Sıralama', ['ucuz' => 'Əvvəl ucuz', 'baha' => 'Əvvəl bahalı']]] as [$name, $label, $options])
                    <label class="block md:w-44">
                        <span class="field-label !text-[10px]">{{ $label }}</span>
                        <select name="{{ $name }}" class="field cursor-pointer !py-2">
                            <option value="">Hamısı</option>
                            @foreach ($options as $k => $v)
                                <option value="{{ $k }}" @selected(($q[$name] ?? '') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </label>
                @endforeach
                <label class="block md:w-44">
                    <span class="field-label !text-[10px]">Məclis tarixi</span>
                    <input type="date" name="tarix" value="{{ $date }}" min="{{ now()->toDateString() }}" class="field !py-2">
                </label>
                @if ($q)
                    <a href="{{ $occasion ? route('catalog.occasion', $occasion) : route('catalog') }}" class="link-line col-span-2 justify-self-center text-taupe md:mb-2">Təmizlə</a>
                @endif
            </form>
            @if ($date)
                <p class="caps mt-8 text-center !text-[10px] text-gold-deep">{{ \Carbon\Carbon::parse($date)->translatedFormat('j F Y') }} tarixinə boş olan xonçalar</p>
            @endif
        </div>
    </section>

    <section class="bg-ivory pb-32">
        <div class="container-x">
            @if ($xoncas->isEmpty())
                <div class="mx-auto max-w-lg py-24 text-center">
                    <h2 class="font-display text-4xl">Uyğun xonça tapılmadı</h2>
                    <p class="mt-4 text-taupe">Seçimləri dəyişin və ya bizə yazın — sizin üçün xüsusi xonça hazırlayaq.</p>
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
