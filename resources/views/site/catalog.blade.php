@extends('layouts.site', ['title' => $occasion ? $occasion->name . ' xonçaları' : 'Bütün xonçalar'])

@push('head')
    <script>window.XONCA = Object.assign(window.XONCA || {}, { items: @json($items) });</script>
@endpush

@section('content')
    @php($q = request()->only(['forma', 'stil', 'sirala', 'tarix']))
    <section class="ornament border-b border-wine-900/5">
        <div class="container-x py-10 sm:py-14">
            <nav class="text-sm text-ink/50"><a href="{{ route('home') }}" class="hover:text-wine-700">Ana səhifə</a> / Xonçalar</nav>
            <h1 class="mt-3 text-4xl font-semibold sm:text-5xl">{{ $occasion ? $occasion->name . ' xonçaları' : 'Bütün xonçalar' }}</h1>
            <p class="mt-3 max-w-2xl text-ink/60">{{ $occasion?->tagline ?? 'Hər xonçanı 3D-də fırladıb baxın, içini özünüz seçin və tarixinizə rezerv edin.' }}</p>

            <div class="scrollbar-none -mx-4 mt-8 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0">
                <a href="{{ route('catalog', $q) }}" class="chip shrink-0 {{ ! $occasion ? 'chip-active' : '' }}">Hamısı</a>
                @foreach ($occasions as $o)
                    <a href="{{ route('catalog.occasion', ['occasion' => $o] + $q) }}" class="chip shrink-0 {{ $occasion?->id === $o->id ? 'chip-active' : '' }}">{{ $o->name }}</a>
                @endforeach
            </div>

            <form method="get" class="mt-4 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center" data-autosubmit>
                <select name="forma" class="input !w-auto !rounded-full !py-2 text-sm">
                    <option value="">Forma: hamısı</option>
                    @foreach (\App\Models\Xonca::SHAPES as $k => $v)
                        <option value="{{ $k }}" @selected(($q['forma'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
                <select name="stil" class="input !w-auto !rounded-full !py-2 text-sm">
                    <option value="">Stil: hamısı</option>
                    @foreach (\App\Models\Xonca::STYLES as $k => $v)
                        <option value="{{ $k }}" @selected(($q['stil'] ?? '') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
                <select name="sirala" class="input !w-auto !rounded-full !py-2 text-sm">
                    <option value="">Sırala: tövsiyə</option>
                    <option value="ucuz" @selected(($q['sirala'] ?? '') === 'ucuz')>Əvvəl ucuz</option>
                    <option value="baha" @selected(($q['sirala'] ?? '') === 'baha')>Əvvəl bahalı</option>
                </select>
                <label class="relative">
                    <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-xs font-semibold text-ink/45">{{ $date ? '' : 'Tarix:' }}</span>
                    <input type="date" name="tarix" value="{{ $date }}" min="{{ now()->toDateString() }}" class="input !rounded-full !py-2 text-sm {{ $date ? '' : '!pl-14' }}">
                </label>
                @if ($q)
                    <a href="{{ $occasion ? route('catalog.occasion', $occasion) : route('catalog') }}" class="col-span-2 text-center text-sm font-semibold text-wine-600 sm:col-span-1">Təmizlə ✕</a>
                @endif
            </form>
            @if ($date)
                <p class="mt-3 text-sm font-semibold text-emerald-700">✓ {{ \Carbon\Carbon::parse($date)->translatedFormat('j F Y') }} tarixinə boş olan xonçalar göstərilir</p>
            @endif
        </div>
    </section>

    <section class="container-x py-10">
        @if ($xoncas->isEmpty())
            <div class="card mx-auto max-w-lg p-10 text-center">
                <div class="text-5xl">🕊️</div>
                <h2 class="mt-4 text-2xl font-semibold">Uyğun xonça tapılmadı</h2>
                <p class="mt-2 text-ink/60">Filtrləri dəyişin və ya bizə yazın — sizin üçün xüsusi xonça hazırlayaq.</p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($xoncas as $xonca)
                    @include('site.partials.card', ['xonca' => $xonca])
                @endforeach
            </div>
        @endif
    </section>
@endsection
