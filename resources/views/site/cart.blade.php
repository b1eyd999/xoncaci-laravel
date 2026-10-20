@extends('layouts.site', ['title' => __('Səbət')])

@push('head')
    <script>window.XONCA = Object.assign(window.XONCA || {}, { items: @json($items), earliest: @json($earliest) });</script>
@endpush

@section('content')
    <div data-cart-page class="bg-ivory pb-32">
        <div class="container-x pt-16 text-center lg:pt-20">
            <div class="caps text-gold-deep">{{ __('Sifariş') }}</div>
            <h1 class="mt-5 font-display text-5xl lg:text-6xl">{{ __('Səbət') }}</h1>
        </div>

        <div data-cart-empty class="container-x hidden py-24 text-center">
            <p class="font-display text-3xl text-taupe italic">{{ __('Səbətiniz hələ boşdur.') }}</p>
            <a href="{{ route('catalog') }}" class="btn btn-dark mt-10">{{ __('Kolleksiyaya bax') }}</a>
        </div>

        <form data-cart-form class="container-x mt-16 hidden gap-16 lg:grid-cols-[1.35fr_1fr] xl:gap-24" novalidate>
            <div>
                <section>
                    <h2 class="caps pb-2 text-ink">{{ __('Xonçalarınız') }}</h2>
                    <ul data-cart-lines class="mt-5 space-y-4"></ul>
                    <a href="{{ route('catalog') }}" class="link-line mt-8 text-taupe">{{ __('Daha bir xonça əlavə et') }}</a>
                </section>

                <section class="mt-20">
                    <h2 class="caps text-ink"><span class="mr-3 font-display text-base text-gold italic normal-case">1</span>{{ __('Məclis') }}</h2>
                    <div class="mt-8 grid gap-x-10 gap-y-8 sm:grid-cols-2">
                        <label class="block">
                            <span class="field-label">{{ __('Tarix') }} *</span>
                            <input name="event_date" type="date" min="{{ $earliest }}" required class="field">
                        </label>
                        <label class="block">
                            <span class="field-label">{{ __('Saat') }}</span>
                            <input name="event_time" type="time" class="field">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="field-label">{{ __('Məclis') }}</span>
                            <select name="occasion_id" class="field cursor-pointer">
                                <option value="">{{ __('Seçin') }}</option>
                                @foreach ($occasions as $o)
                                    <option value="{{ $o->id }}">{{ $o->tr('name') }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <p data-avail-note class="mt-4 hidden text-sm"></p>
                </section>

                <section class="mt-20">
                    <h2 class="caps text-ink"><span class="mr-3 font-display text-base text-gold italic normal-case">2</span>{{ __('Əlaqə və çatdırılma') }}</h2>
                    <div class="mt-8 grid gap-x-10 gap-y-8 sm:grid-cols-2">
                        <label class="block">
                            <span class="field-label">{{ __('Ad, soyad') }} *</span>
                            <input name="customer_name" required autocomplete="name" class="field">
                        </label>
                        <label class="block">
                            <span class="field-label">{{ __('Telefon') }} *</span>
                            <input name="phone" type="tel" required autocomplete="tel" inputmode="tel" class="field" placeholder="+994 50 123 45 67">
                        </label>
                        <div class="sm:col-span-2">
                            <span class="field-label">{{ __('Çatdırılma') }}</span>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <label class="caps cursor-pointer bg-pearl py-4 text-center !text-[10px] text-taupe transition has-checked:bg-onyx has-checked:text-ivory">
                                    <input type="radio" name="delivery_type" value="delivery" checked class="sr-only"> {{ __('Ünvana çatdırılma') }}
                                </label>
                                <label class="caps cursor-pointer bg-pearl py-4 text-center !text-[10px] text-taupe transition has-checked:bg-onyx has-checked:text-ivory">
                                    <input type="radio" name="delivery_type" value="pickup" class="sr-only"> {{ __('Özüm götürərəm') }}
                                </label>
                            </div>
                        </div>
                        <label class="block" data-delivery-only>
                            <span class="field-label">{{ __('Rayon') }} *</span>
                            <select name="district" class="field cursor-pointer">
                                <option value="">{{ __('Seçin') }}</option>
                                @foreach ($districts as $d)
                                    <option value="{{ $d }}">{{ __($d) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block" data-delivery-only>
                            <span class="field-label">{{ __('Ünvan') }} *</span>
                            <input name="address" class="field" placeholder="{{ __('Küçə, ev, mənzil') }}" autocomplete="street-address">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="field-label">{{ __('Qeyd') }}</span>
                            <textarea name="note" rows="2" class="field resize-none" placeholder="{{ __('Xüsusi istəkləriniz') }}"></textarea>
                        </label>
                    </div>
                </section>
            </div>

            <aside>
                <div class="bg-onyx p-8 text-ivory lg:sticky lg:top-32 lg:p-10">
                    <h2 class="caps text-gold-soft">{{ __('Hesab') }}</h2>
                    <dl class="mt-8 space-y-4 text-sm">
                        <div class="flex justify-between"><dt class="text-ivory/60">{{ __('Xonçalar') }}</dt><dd data-q="xonca_total"></dd></div>
                        <div class="flex justify-between"><dt class="text-ivory/60">{{ __('Dolduruş') }}</dt><dd data-q="fill_total"></dd></div>
                        <div class="flex justify-between"><dt class="text-ivory/60">{{ __('Çatdırılma') }}</dt><dd data-q="delivery_fee"></dd></div>
                    </dl>
                    <div class="mt-8 flex items-baseline justify-between pt-4">
                        <span class="caps">{{ __('Ödəniləcək') }}</span>
                        <span data-q="total" class="font-display text-4xl text-gold-soft"></span>
                    </div>
                    <div class="mt-3 flex justify-between text-xs text-ivory/45"><span>{{ __('Depozit, geri qaytarılır') }}</span><span data-q="deposit_total"></span></div>
                    <p data-free-note class="mt-6 hidden bg-onyx-3 px-4 py-3 text-xs text-ivory/70"></p>
                    <div data-errors class="mt-6 hidden space-y-1 bg-wine/40 p-4 text-sm text-ivory"></div>
                    <button type="submit" class="btn btn-gold mt-10 w-full">{{ __('Sifarişi göndər') }}</button>
                    <p class="mt-5 text-center text-xs text-ivory/45">{{ __('İndi ödəniş yoxdur. Sifarişdən sonra zəng edib təsdiqləyirik.') }}</p>
                </div>
            </aside>
        </form>
    </div>
@endsection
