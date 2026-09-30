@extends('layouts.site', ['title' => 'Sifariş ' . $booking->code])

@push('head')
    <script>window.XONCA = Object.assign(window.XONCA || {}, { items: @json($items), clearCart: true });</script>
@endpush

@section('content')
    <section class="container-x py-12 sm:py-20">
        <div class="card fade-up mx-auto max-w-2xl overflow-hidden">
            <div class="stage-dark px-6 py-10 text-center text-white">
                <div class="mx-auto grid size-16 place-items-center rounded-full bg-gold-400 text-3xl text-wine-900">✓</div>
                <h1 class="mt-5 text-3xl font-semibold sm:text-4xl">Təşəkkür edirik, {{ $booking->customer_name }}!</h1>
                <p class="mt-2 text-white/70">Sifarişiniz qəbul olundu. Tezliklə {{ $booking->phone }} nömrəsinə zəng edib təsdiqləyəcəyik.</p>
                <div class="mt-5 inline-block rounded-full bg-white/10 px-5 py-2 font-mono text-lg tracking-widest text-gold-200">{{ $booking->code }}</div>
            </div>
            <div class="p-6 sm:p-8">
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-ink/50">Tarix</dt><dd class="font-bold">{{ $booking->event_date->translatedFormat('j F Y, l') }}{{ $booking->event_time ? ', ' . $booking->event_time : '' }}</dd></div>
                    <div><dt class="text-ink/50">Status</dt><dd class="font-bold">{{ $booking->statusLabel() }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-ink/50">Çatdırılma</dt><dd class="font-bold">{{ $booking->delivery_type === 'pickup' ? 'Özü götürəcək' : trim($booking->district . ', ' . $booking->address, ', ') }}</dd></div>
                </dl>
                <ul class="mt-6 divide-y divide-wine-900/5 rounded-2xl ring-1 ring-wine-900/5">
                    @foreach ($booking->items as $item)
                        <li class="flex items-start justify-between gap-4 p-4">
                            <div>
                                <div class="font-bold">{{ $item->xonca_name }}</div>
                                <div class="text-xs text-ink/55">{{ \App\Models\BookingItem::FILL_MODES[$item->fill_mode] ?? '' }}</div>
                                @if ($item->fill)
                                    <div class="mt-1 text-xs text-ink/55">{{ $item->fillSummary() }}</div>
                                @endif
                            </div>
                            <div class="text-right font-bold whitespace-nowrap">{{ \App\Support\Money::format($item->price + $item->fill_total) }}</div>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-5 space-y-1.5 text-sm">
                    <div class="flex justify-between"><dt class="text-ink/60">Çatdırılma</dt><dd>{{ \App\Support\Money::format($booking->delivery_fee) }}</dd></div>
                    <div class="flex justify-between text-lg font-bold"><dt>Cəmi</dt><dd class="text-wine-700">{{ \App\Support\Money::format($booking->total) }}</dd></div>
                    @if ($booking->deposit_total > 0)
                        <div class="flex justify-between text-ink/50"><dt>+ Depozit (geri qaytarılır)</dt><dd>{{ \App\Support\Money::format($booking->deposit_total) }}</dd></div>
                    @endif
                </dl>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-ghost">Ana səhifə</a>
                    <a href="https://wa.me/{{ \App\Models\Setting::get('whatsapp') }}?text={{ rawurlencode('Salam! Sifarişim: ' . $booking->code) }}" target="_blank" rel="noopener" class="btn bg-[#25D366] text-white">WhatsApp-da yaz</a>
                </div>
            </div>
        </div>
    </section>
@endsection
