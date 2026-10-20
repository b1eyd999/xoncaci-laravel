@php
    $s = fn ($k) => \App\Models\Setting::get($k);
    $l = fn ($k) => \App\Models\Setting::local($k);
    $tel = preg_replace('/\s+/', '', $s('phone'));
    $overlay = $overlay ?? false; // the header floats over a dark photograph
    $locale = app()->getLocale();
    $nav = [route('catalog') => __('Kolleksiya'), route('home') . '#bayramlar' => __('Məclislər'), route('home') . '#nece' => __('Necə işləyir'), route('home') . '#suallar' => __('Suallar')];
    $langUrl = fn ($code) => request()->fullUrlWithQuery(['lang' => $code]);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f0d0c">
    <title>{{ isset($title) ? $title . ', ' : '' }}{{ $s('site_name') }}</title>
    <meta name="description" content="{{ $description ?? __('Toy, nişan, xınayaxdı və söz kəsmə üçün əl işi xonçaların icarəsi. İçini özünüz seçin. Bakı üzrə çatdırılma və geri alma.') }}">
    <meta property="og:title" content="{{ $title ?? $s('site_name') }}">
    <meta property="og:description" content="{{ $description ?? $l('hero_subtitle') }}">
    <meta property="og:image" content="{{ \App\Support\Img::url($s('hero_image') ?: \App\Support\Img::STOCK['hero'], 1200) }}">
    @foreach (\App\Http\Middleware\SetLocale::LOCALES as $code => $label)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ $langUrl($code) }}">
    @endforeach
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.XONCA = Object.assign(window.XONCA || {}, {
            locale: @json($locale),
            routes: { quote: @json(route('cart.quote')), store: @json(route('booking.store')), cart: @json(route('cart')), availability: @json(route('availability')) },
            freeDeliveryFrom: {{ (float) $s('free_delivery_from') }},
            t: @json(\App\Support\JsText::all()),
        });
    </script>
    @stack('head')
</head>
<body class="min-h-screen overflow-x-hidden">
    <header data-header data-overlay="{{ $overlay ? 1 : 0 }}"
        class="group/h fixed inset-x-0 top-0 z-40 transition-colors duration-700 {{ $overlay ? 'text-ivory' : 'bg-ivory/95 text-ink backdrop-blur-md' }} data-[solid]:bg-ivory/95 data-[solid]:text-ink data-[solid]:backdrop-blur-md">
        <div class="container-x grid h-20 grid-cols-[1fr_auto_1fr] items-center lg:h-24">
            <div class="flex items-center gap-8">
                <button type="button" data-menu-toggle class="-ml-2 p-2 lg:hidden" aria-label="{{ __('Menyu') }}">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></svg>
                </button>
                <nav class="hidden items-center gap-9 lg:flex">
                    @foreach (array_slice($nav, 0, 2, true) as $href => $label)
                        <a href="{{ $href }}" class="caps opacity-80 transition hover:opacity-100">{{ $label }}</a>
                    @endforeach
                </nav>
            </div>

            <a href="{{ route('home') }}" class="text-center leading-none">
                <span class="block font-display text-[24px] tracking-[0.28em] uppercase lg:text-[28px]" style="margin-right:-.28em">{{ $s('site_name') }}</span>
                <span class="caps mt-1.5 block !text-[8.5px] !tracking-[0.42em] opacity-60" style="margin-right:-.42em">{{ __('Bakıda xonça icarəsi') }}</span>
            </a>

            <div class="flex items-center justify-end gap-6 lg:gap-8">
                <nav class="hidden items-center gap-9 xl:flex">
                    @foreach (array_slice($nav, 2, 2, true) as $href => $label)
                        <a href="{{ $href }}" class="caps opacity-80 transition hover:opacity-100">{{ $label }}</a>
                    @endforeach
                </nav>
                <div class="hidden items-center gap-3 sm:flex">
                    @foreach (\App\Http\Middleware\SetLocale::LOCALES as $code => $label)
                        <a href="{{ $langUrl($code) }}" hreflang="{{ $code }}" class="caps !text-[10px] transition {{ $code === $locale ? 'opacity-100' : 'opacity-45 hover:opacity-90' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <a href="{{ route('cart') }}" class="caps flex items-center gap-2 opacity-90 hover:opacity-100" aria-label="{{ __('Səbət') }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M5 8h14l-1.2 12H6.2z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/></svg>
                    <span class="hidden md:inline">{{ __('Səbət') }}</span>
                    <span data-cart-count class="hidden min-w-5 text-gold"></span>
                </a>
            </div>
        </div>
    </header>

    {{-- Mobile menu --}}
    <div data-menu class="fixed inset-0 z-50 hidden overflow-y-auto bg-onyx text-ivory">
        <div class="container-x flex h-20 items-center justify-between">
            <span class="font-display text-2xl tracking-[0.28em] uppercase">{{ $s('site_name') }}</span>
            <button type="button" data-menu-toggle class="-mr-2 p-2" aria-label="{{ __('Bağla') }}">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M5 5l14 14M19 5L5 19"/></svg>
            </button>
        </div>
        <nav class="container-x mt-8 flex flex-col gap-7">
            @foreach ($nav + [route('cart') => __('Səbət')] as $href => $label)
                <a href="{{ $href }}" class="font-display text-4xl">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="container-x mt-12 flex gap-6">
            @foreach (\App\Http\Middleware\SetLocale::LOCALES as $code => $label)
                <a href="{{ $langUrl($code) }}" class="caps {{ $code === $locale ? 'text-gold-soft' : 'text-ivory/50' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="container-x mt-10 space-y-2 pb-12 text-ivory/60">
            <a href="tel:{{ $tel }}" class="block">{{ $s('phone') }}</a>
            <a href="https://wa.me/{{ $s('whatsapp') }}" class="block">WhatsApp</a>
        </div>
    </div>

    <main class="{{ $overlay ? '' : 'pt-20 lg:pt-24' }}">
        @yield('content')
    </main>

    <footer id="elaqe" class="bg-onyx text-ivory/70">
        <div class="container-x pt-24 pb-10">
            <div class="grid gap-14 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div>
                    <div class="font-display text-4xl tracking-[0.24em] text-ivory uppercase">{{ $s('site_name') }}</div>
                    <p class="mt-6 max-w-sm text-ivory/55">{{ __('Toy, nişan, xınayaxdı və söz kəsmə üçün əl ilə bəzədilmiş xonçalar. Gətiririk, qururuq, məclisdən sonra özümüz götürürük.') }}</p>
                </div>
                <div>
                    <div class="caps text-gold-soft">{{ __('Əlaqə') }}</div>
                    <ul class="mt-6 space-y-3">
                        <li><a class="hover:text-ivory" href="tel:{{ $tel }}">{{ $s('phone') }}</a></li>
                        <li><a class="hover:text-ivory" href="https://wa.me/{{ $s('whatsapp') }}" target="_blank" rel="noopener">WhatsApp</a></li>
                        <li><a class="hover:text-ivory" href="https://instagram.com/{{ ltrim($s('instagram'), '@') }}" target="_blank" rel="noopener">Instagram</a></li>
                    </ul>
                </div>
                <div>
                    <div class="caps text-gold-soft">{{ __('Kolleksiya') }}</div>
                    <ul class="mt-6 space-y-3">
                        @foreach (\App\Models\Occasion::active()->take(5)->get() as $o)
                            <li><a class="hover:text-ivory" href="{{ route('catalog.occasion', $o) }}">{{ $o->tr('name') }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <div class="caps text-gold-soft">{{ __('Ünvan') }}</div>
                    <p class="mt-6">{{ $l('address') }}<br>{{ $l('working_hours') }}</p>
                </div>
            </div>
            <div class="mt-20 h-px bg-ivory/10"></div>
            <div class="caps mt-8 flex flex-col justify-between gap-3 !text-[10px] text-ivory/35 sm:flex-row">
                <span>© {{ date('Y') }} {{ $s('site_name') }}</span>
                <span>{{ __('Bakı, Azərbaycan') }}</span>
            </div>
        </div>
    </footer>

    <div data-toast class="pointer-events-none fixed inset-x-0 bottom-6 z-50 flex justify-center px-4"></div>
    @stack('scripts')
</body>
</html>
