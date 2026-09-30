@extends('layouts.site', ['title' => 'Sifariş ' . $booking->code])
@php($money = fn ($v) => \App\Support\Money::format($v))

@push('head')
    <script>window.XONCA = Object.assign(window.XONCA || {}, { clearCart: true });</script>
@endpush

@section('content')
    <section class="bg-ivory py-20 lg:py-28">
        <div class="container-x max-w-3xl text-center">
            <svg viewBox="0 0 80 16" class="mx-auto h-4 text-gold" fill="none" stroke="currentColor" stroke-width=".8"><path d="M0 8h30M50 8h30"/><path d="M40 2l6 6-6 6-6-6z"/></svg>
            <div class="caps mt-10 text-gold-deep">Sifariş qəbul olundu</div>
            <h1 class="hero-in mt-6 font-display text-5xl leading-tight lg:text-7xl">Təşəkkür edirik, <em class="text-gold-deep">{{ $booking->customer_name }}</em></h1>
            <p class="mx-auto mt-6 max-w-md text-taupe">Tezliklə {{ $booking->phone }} nömrəsinə zəng edib sifarişinizi təsdiqləyəcəyik.</p>
            <div class="caps mt-10 inline-block border border-gold/40 px-8 py-4 !tracking-[0.4em] text-ink">{{ $booking->code }}</div>
        </div>

        <div class="container-x mt-20 max-w-3xl">
            <dl class="grid gap-8 border-y border-ink/10 py-8 sm:grid-cols-3">
                <div><dt class="field-label">Məclis</dt><dd class="font-display text-xl">{{ $booking->event_date->translatedFormat('j F Y') }}{{ $booking->event_time ? ', ' . $booking->event_time : '' }}</dd></div>
                <div><dt class="field-label">Çatdırılma</dt><dd class="font-display text-xl">{{ $booking->delivery_type === 'pickup' ? 'Özünüz götürürsünüz' : ($booking->district ?: 'Ünvana') }}</dd></div>
                <div><dt class="field-label">Status</dt><dd class="font-display text-xl">{{ $booking->statusLabel() }}</dd></div>
            </dl>
            <ul class="mt-4">
                @foreach ($booking->items as $item)
                    <li class="flex items-start justify-between gap-6 border-b border-ink/10 py-6">
                        <div>
                            <div class="font-display text-2xl">{{ $item->xonca_name }}</div>
                            <div class="caps mt-2 !text-[9px] text-taupe">{{ \App\Models\BookingItem::FILL_MODES[$item->fill_mode] ?? '' }}</div>
                            @if ($item->fill)
                                <div class="mt-2 text-sm text-mist">{{ $item->fillSummary() }}</div>
                            @endif
                        </div>
                        <div class="font-display text-xl whitespace-nowrap">{{ $money($item->price + $item->fill_total) }}</div>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-6 ml-auto max-w-xs space-y-2 text-sm">
                <div class="flex justify-between text-taupe"><dt>Çatdırılma</dt><dd>{{ $money($booking->delivery_fee) }}</dd></div>
                <div class="flex items-baseline justify-between pt-2"><dt class="caps">Cəmi</dt><dd class="font-display text-3xl">{{ $money($booking->total) }}</dd></div>
                @if ($booking->deposit_total > 0)
                    <div class="flex justify-between text-mist"><dt>Depozit · qaytarılır</dt><dd>{{ $money($booking->deposit_total) }}</dd></div>
                @endif
            </dl>
            <div class="mt-16 flex flex-wrap justify-center gap-4">
                <a href="{{ route('home') }}" class="btn btn-line">Ana səhifə</a>
                <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp') }}?text={{ rawurlencode('Salam! Sifarişim: ' . $booking->code) }}" target="_blank" rel="noopener" class="btn btn-dark">WhatsApp-da yazın</a>
            </div>
        </div>
    </section>
@endsection
