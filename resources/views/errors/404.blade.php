@extends('layouts.site', ['title' => 'Səhifə tapılmadı'])

@section('content')
    <section class="container-x py-20 text-center sm:py-28">
        <div class="font-display text-8xl font-semibold text-gold-300">404</div>
        <h1 class="mt-4 text-3xl font-semibold sm:text-4xl">Bu səhifə tapılmadı</h1>
        <p class="mx-auto mt-3 max-w-md text-ink/60">Bəlkə xonça artıq kataloqdan çıxarılıb və ya link səhv yazılıb.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('catalog') }}" class="btn btn-primary">Xonçalara bax</a>
            <a href="{{ route('home') }}" class="btn btn-ghost">Ana səhifə</a>
        </div>
    </section>
@endsection
