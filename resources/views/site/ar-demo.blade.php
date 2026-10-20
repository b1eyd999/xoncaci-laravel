{{-- A hidden page for trying «Evinizdə baxın» before any xonça has a scan of its own.
     The object is an open sample model (Khronos glTF sample «Lantern», CC0), baked to 27 × 45 cm with tools/fit-model.mjs. --}}
@php
    $copy = [
        'az' => ['Evinizdə baxın', 'necə işləyir', 'Bu səhifə sınaq üçündür. Burada nümunə 3D obyekt (fənər) var. Xonçalarınızın 3D skanı yükləndikdən sonra eyni düymə hər xonçanın səhifəsində olacaq və telefonda xonçanın özü görünəcək.',
            ['Aşağıdakı düyməni basın.', '«Kameranı aç» düyməsini basın.', 'Telefonu yavaşca masaya və ya döşəməyə tutun, bir az sağa sola hərəkət etdirin.', 'Obyekt görünəndə barmağınızla yerini dəyişin, iki barmaqla çevirin. Ətrafında gəzin, o yerində qalır.'],
            'Sınağa başla', 'iPhone: Safari ilə açın. Android: Chrome ilə açın.'],
        'ru' => ['Посмотреть у себя дома', 'как это работает', 'Это тестовая страница. Здесь стоит пример 3D объекта (фонарь). Когда вы загрузите 3D сканы своих хонч, такая же кнопка будет на странице каждой хончи, и в телефоне появится сама хонча.',
            ['Нажмите кнопку ниже.', 'Нажмите «Открыть камеру».', 'Медленно наведите телефон на стол или пол и немного поводите им из стороны в сторону.', 'Когда объект появится, передвиньте его пальцем, поверните двумя пальцами. Обойдите вокруг, он останется на месте.'],
            'Начать проверку', 'iPhone: откройте в Safari. Android: откройте в Chrome.'],
        'en' => ['See it in your home', 'how it works', 'This is a test page with a sample 3D object (a lantern). Once your khonchas are scanned, the same button will be on every khoncha page, and the phone will show the khoncha itself.',
            ['Tap the button below.', 'Tap “Open the camera”.', 'Slowly point your phone at a table or the floor and move it gently from side to side.', 'When the object appears, move it with one finger and turn it with two. Walk around it; it stays in place.'],
            'Start the test', 'iPhone: open in Safari. Android: open in Chrome.'],
    ][app()->getLocale()];
    [$title, $sub, $intro, $steps, $cta, $note] = $copy;
@endphp
@extends('layouts.site', ['title' => $title])

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <section class="bg-ivory pt-14 pb-32 lg:pt-20">
        <div class="container-x max-w-2xl text-center">
            <div class="caps text-gold-deep">3D AR</div>
            <h1 class="mt-5 font-display text-5xl leading-tight lg:text-6xl">{{ $title }} <em class="text-gold-deep">{{ $sub }}</em></h1>
            <p class="mx-auto mt-6 text-taupe">{{ $intro }}</p>
        </div>

        <ol class="container-x mt-14 grid max-w-2xl gap-3">
            @foreach ($steps as $i => $step)
                <li class="flex items-start gap-5 bg-pearl px-6 py-5">
                    <span class="font-display text-2xl leading-none text-gold italic">{{ $i + 1 }}</span>
                    <span class="text-ink">{{ $step }}</span>
                </li>
            @endforeach
        </ol>

        <div class="container-x mt-12 max-w-2xl text-center">
            <button type="button" data-ar-open class="btn btn-dark w-full py-5 text-[12px]">{{ $cta }}</button>
            <p class="mt-5 text-sm text-mist">{{ $note }}</p>
        </div>
    </section>

    @include('site.partials.ar-modal', [
        'glb' => asset('models/demo-lantern.glb'),
        'usdz' => null,
        'size' => 27,
        'name' => ['az' => 'Fənər', 'ru' => 'Фонарь', 'en' => 'The lantern'][app()->getLocale()],
        'url' => route('ar.demo') . '?ar=1&lang=' . app()->getLocale(),
    ])
@endsection
