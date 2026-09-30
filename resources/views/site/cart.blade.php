@extends('layouts.site', ['title' => 'Səbət'])

@push('head')
    <script>window.XONCA = Object.assign(window.XONCA || {}, { items: @json($items), earliest: @json($earliest) });</script>
@endpush

@section('content')
    <div data-cart-page class="container-x py-8 sm:py-12">
        <h1 class="text-4xl font-semibold sm:text-5xl">Səbət</h1>

        <div data-cart-empty class="card mx-auto mt-10 hidden max-w-lg p-10 text-center">
            <div class="text-5xl">🎀</div>
            <h2 class="mt-4 text-2xl font-semibold">Səbətiniz boşdur</h2>
            <p class="mt-2 text-ink/60">Xonçanı seçin, içini doldurun və buraya əlavə edin.</p>
            <a href="{{ route('catalog') }}" class="btn btn-primary mt-6">Xonçalara bax</a>
        </div>

        <form data-cart-form class="mt-8 hidden gap-8 lg:grid lg:grid-cols-[1.4fr_1fr]" novalidate>
            <div class="space-y-8">
                <section>
                    <h2 class="font-sans text-sm font-bold tracking-wide text-ink/70 uppercase">Xonçalarınız</h2>
                    <ul data-cart-lines class="mt-3 space-y-3"></ul>
                    <a href="{{ route('catalog') }}" class="mt-3 inline-block text-sm font-bold text-wine-600 hover:underline">+ Daha bir xonça əlavə et</a>
                </section>

                <section class="card p-5 sm:p-6">
                    <h2 class="font-sans text-sm font-bold tracking-wide text-ink/70 uppercase">Məclis</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="event_date">Tarix *</label>
                            <input id="event_date" name="event_date" type="date" min="{{ $earliest }}" required class="input">
                        </div>
                        <div>
                            <label class="label" for="event_time">Saat</label>
                            <input id="event_time" name="event_time" type="time" class="input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label" for="occasion_id">Məclis növü</label>
                            <select id="occasion_id" name="occasion_id" class="input">
                                <option value="">Seçin</option>
                                @foreach ($occasions as $o)
                                    <option value="{{ $o->id }}">{{ $o->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <p data-avail-note class="mt-3 hidden text-sm font-semibold"></p>
                </section>

                <section class="card p-5 sm:p-6">
                    <h2 class="font-sans text-sm font-bold tracking-wide text-ink/70 uppercase">Əlaqə və çatdırılma</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="customer_name">Adınız *</label>
                            <input id="customer_name" name="customer_name" required autocomplete="name" class="input" placeholder="Ad Soyad">
                        </div>
                        <div>
                            <label class="label" for="phone">Telefon *</label>
                            <input id="phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" class="input" placeholder="+994 50 123 45 67">
                        </div>
                        <div class="sm:col-span-2">
                            <span class="label">Çatdırılma</span>
                            <div class="grid grid-cols-2 gap-2 rounded-2xl bg-cream p-1.5 ring-1 ring-wine-900/5">
                                <label class="cursor-pointer rounded-xl px-3 py-3 text-center text-sm font-bold transition has-checked:bg-wine-600 has-checked:text-white">
                                    <input type="radio" name="delivery_type" value="delivery" checked class="sr-only"> 🚚 Ünvana çatdırılma
                                </label>
                                <label class="cursor-pointer rounded-xl px-3 py-3 text-center text-sm font-bold transition has-checked:bg-wine-600 has-checked:text-white">
                                    <input type="radio" name="delivery_type" value="pickup" class="sr-only"> 🏠 Özüm götürərəm
                                </label>
                            </div>
                        </div>
                        <div data-delivery-only>
                            <label class="label" for="district">Rayon *</label>
                            <select id="district" name="district" class="input">
                                <option value="">Seçin</option>
                                @foreach ($districts as $d)
                                    <option>{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div data-delivery-only>
                            <label class="label" for="address">Ünvan *</label>
                            <input id="address" name="address" class="input" placeholder="Küçə, ev, mənzil" autocomplete="street-address">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label" for="note">Qeyd</label>
                            <textarea id="note" name="note" rows="3" class="input" placeholder="Xüsusi istəkləriniz, məsələn: lentə ad yazılsın"></textarea>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="mt-8 lg:mt-0">
                <div class="card p-5 sm:p-6 lg:sticky lg:top-28">
                    <h2 class="font-sans text-sm font-bold tracking-wide text-ink/70 uppercase">Hesab</h2>
                    <dl class="mt-4 space-y-2.5 text-sm">
                        <div class="flex justify-between"><dt class="text-ink/60">Xonçalar</dt><dd data-q="xonca_total" class="font-semibold">—</dd></div>
                        <div class="flex justify-between"><dt class="text-ink/60">Dolduruş</dt><dd data-q="fill_total" class="font-semibold">—</dd></div>
                        <div class="flex justify-between"><dt class="text-ink/60">Çatdırılma</dt><dd data-q="delivery_fee" class="font-semibold">—</dd></div>
                        <div class="flex items-end justify-between border-t border-wine-900/5 pt-3"><dt class="font-bold">Ödəniləcək</dt><dd data-q="total" class="font-display text-3xl font-semibold text-wine-700">—</dd></div>
                        <div class="flex justify-between text-xs text-ink/50"><dt>+ Depozit (geri qaytarılır)</dt><dd data-q="deposit_total">—</dd></div>
                    </dl>
                    <p data-free-note class="mt-3 hidden rounded-xl bg-emerald-50 p-3 text-xs font-semibold text-emerald-800"></p>
                    <div data-errors class="mt-4 hidden rounded-2xl bg-red-50 p-4 text-sm text-red-700"></div>
                    <button type="submit" class="btn btn-primary mt-5 w-full py-4 text-base">Sifarişi göndər</button>
                    <p class="mt-3 text-center text-xs text-ink/45">İndi ödəniş yoxdur. Sifarişdən sonra sizə zəng edib təsdiqləyəcəyik.</p>
                </div>
            </aside>
        </form>
    </div>
@endsection
