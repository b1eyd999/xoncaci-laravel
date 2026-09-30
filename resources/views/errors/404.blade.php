@extends('layouts.site', ['title' => 'Səhifə tapılmadı'])

@section('content')
    <section class="bg-ivory py-32 text-center lg:py-44">
        <div class="container-x">
            <div class="font-display text-[9rem] leading-none text-gold/60 italic">404</div>
            <h1 class="mt-6 font-display text-5xl">Bu səhifə tapılmadı</h1>
            <p class="mx-auto mt-5 max-w-md text-taupe">Bəlkə xonça kolleksiyadan çıxarılıb və ya link səhv yazılıb.</p>
            <div class="mt-12 flex flex-wrap justify-center gap-4">
                <a href="{{ route('catalog') }}" class="btn btn-dark">Kolleksiya</a>
                <a href="{{ route('home') }}" class="btn btn-line">Ana səhifə</a>
            </div>
        </div>
    </section>
@endsection
