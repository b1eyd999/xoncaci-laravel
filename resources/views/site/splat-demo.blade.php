{{-- Hidden page: what a 3D capture from a phone video looks like. The object is a public
     research sample (a sneaker, from the antimatter15/splat demo data), shown only to the
     owner until his own xonça has been filmed and processed. --}}
@php
    [$title, $sub, $intro, $hint] = [
        'az' => ['Videodan 3D', 'nümunə', 'Bu, telefon videosundan süni intellektlə qurulmuş real əşyadır (nümunə üçün idman ayaqqabısı). Xonçanızı eyni cür çəksəniz, saytda o da belə görünəcək.', 'Barmaqla fırladın, iki barmaqla yaxınlaşdırın'],
        'ru' => ['3D из видео', 'пример', 'Это настоящий предмет, собранный нейросетью из видео с телефона (для примера кроссовок). Если снять вашу хончу так же, на сайте она будет выглядеть так же.', 'Крутите пальцем, приближайте двумя пальцами'],
        'en' => ['3D from video', 'a sample', 'This is a real object rebuilt by AI from a phone video (a sneaker, as an example). Film your khoncha the same way and it will look like this on the site.', 'Turn with a finger, zoom with two'],
    ][app()->getLocale()];
@endphp
@extends('layouts.site', ['title' => $title])

@push('head')
    <meta name="robots" content="noindex, nofollow">
    @vite('resources/js/splat-demo.js')
@endpush

@section('content')
    <section class="bg-onyx pb-16 text-ivory">
        <div class="container-x pt-10 text-center lg:pt-14">
            <div class="caps text-gold-soft">Gaussian Splatting</div>
            <h1 class="mt-4 font-display text-4xl lg:text-6xl">{{ $title }} <em class="text-gold-soft">{{ $sub }}</em></h1>
            <p class="mx-auto mt-5 max-w-xl text-sm text-ivory/65">{{ $intro }}</p>
        </div>
        <div class="container-x mt-8">
            <div data-splat="https://huggingface.co/cakewalk/splat-data/resolve/main/nike.splat" data-flip="1" data-distance="0.72"
                 class="relative mx-auto aspect-square w-full max-w-3xl overflow-hidden bg-onyx-2 lg:aspect-[16/10]">
                <div data-splat-loading class="caps absolute inset-0 grid place-items-center !text-[10px] text-ivory/40">3D…</div>
            </div>
            <p class="caps mt-5 text-center !text-[9px] text-ivory/45">{{ $hint }}</p>
        </div>
    </section>
@endsection
