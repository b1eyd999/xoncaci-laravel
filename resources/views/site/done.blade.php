@extends('layouts.site', ['title' => __('Sifariş') . ' ' . $booking->code])
@php($money = fn ($v) => \App\Support\Money::format($v))

@push('head')
    <script>window.XONCA = Object.assign(window.XONCA || {}, { clearCart: true });</script>
@endpush

@section('content')
    <section class="bg-ivory py-20 lg:py-28">
        <div class="container-x max-w-3xl text-center">
            <svg viewBox="0 0 16 16" class="mx-auto h-3 text-gold" fill="none" stroke="currentColor" stroke-width="1"><path d="M8 1l7 7-7 7-7-7z"/></svg>
            <div class="caps mt-10 text-gold-deep">{{ __('Sifariş qəbul olundu') }}</div>
            <h1 class="hero-in mt-6 font-display text-4xl leading-tight lg:text-6xl">{{ __('Təşəkkür edirik') }}, <em class="text-gold-deep">{{ $booking->customer_name }}</em></h1>
            <p class="mx-auto mt-6 max-w-md text-taupe">{{ __('Tezliklə :phone nömrəsinə zəng edib sifarişinizi təsdiqləyəcəyik.', ['phone' => $booking->phone]) }}</p>
            <div class="caps mt-10 inline-block bg-pearl px-8 py-4 !tracking-[0.35em] text-ink">{{ $booking->code }}</div>
        </div>

        <div class="container-x mt-20 max-w-3xl">
            <dl class="grid gap-6 sm:grid-cols-3">
                <div class="bg-pearl p-6"><dt class="field-label">{{ __('Məclis') }}</dt><dd class="font-display text-lg">{{ $booking->event_date->translatedFormat('j F Y') }}{{ $booking->event_time ? ', ' . $booking->event_time : '' }}</dd></div>
                <div class="bg-pearl p-6"><dt class="field-label">{{ __('Çatdırılma') }}</dt><dd class="font-display text-lg">{{ $booking->delivery_type === 'pickup' ? __('Özünüz götürürsünüz') : ($booking->district ? __($booking->district) : __('Ünvana')) }}</dd></div>
                <div class="bg-pearl p-6"><dt class="field-label">{{ __('Status') }}</dt><dd class="font-display text-lg">{{ __($booking->statusLabel()) }}</dd></div>
            </dl>
            <ul class="mt-6 space-y-3">
                @foreach ($booking->items as $item)
                    <li class="flex items-start justify-between gap-6 bg-pearl p-6">
                        <div>
                            <div class="font-display text-xl">{{ $item->xonca?->tr('name') ?? $item->xonca_name }}</div>
                            <div class="caps mt-2 !text-[9px] text-taupe">{{ __(\App\Models\BookingItem::FILL_MODES[$item->fill_mode] ?? '') }}</div>
                        </div>
                        <div class="font-display text-lg whitespace-nowrap">{{ $money($item->price + $item->fill_total) }}</div>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-8 ml-auto max-w-xs space-y-2 text-sm">
                <div class="flex justify-between text-taupe"><dt>{{ __('Çatdırılma') }}</dt><dd>{{ $money($booking->delivery_fee) }}</dd></div>
                <div class="flex items-baseline justify-between pt-2"><dt class="caps">{{ __('Cəmi') }}</dt><dd class="font-display text-3xl">{{ $money($booking->total) }}</dd></div>
                @if ($booking->deposit_total > 0)
                    <div class="flex justify-between text-mist"><dt>{{ __('Depozit, geri qaytarılır') }}</dt><dd>{{ $money($booking->deposit_total) }}</dd></div>
                @endif
            </dl>
            <div class="mt-16 flex flex-wrap justify-center gap-4">
                <a href="{{ route('home') }}" class="btn btn-line">{{ __('Ana səhifə') }}</a>
                <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp') }}?text={{ rawurlencode(__('Salam! Sifarişim: :code', ['code' => $booking->code])) }}" target="_blank" rel="noopener" class="btn btn-dark">{{ __('WhatsApp ilə yazın') }}</a>
            </div>
        </div>
    </section>
@endsection
