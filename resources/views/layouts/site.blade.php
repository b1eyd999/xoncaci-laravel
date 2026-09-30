@php
    $s = fn ($k) => \App\Models\Setting::get($k);
    $tel = preg_replace('/\s+/', '', $s('phone'));
    $overlay = $overlay ?? false; // the header floats over a dark photograph
    $nav = [route('catalog') => 'Kolleksiya', route('home') . '#bayramlar' => 'Məclislər', route('home') . '#nece' => 'Necə işləyir', route('home') . '#suallar' => 'Suallar'];
@endphp
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f0d0c">
    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ $s('site_name') }}</title>
    <meta name="description" content="{{ $description ?? 'Toy, nişan, xınayaxdı və söz kəsmə üçün əl işi xonçaların icarəsi. İçini özünüz seçin. Bakı üzrə çatdırılma və geri alma.' }}">
    <meta property="og:title" content="{{ $title ?? $s('site_name') }}">
    <meta property="og:description" content="{{ $description ?? $s('hero_subtitle') }}">
    <meta property="og:image" content="{{ \App\Support\Img::url($s('hero_image') ?: \App\Support\Img::STOCK['hero'], 1200) }}">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.XONCA = Object.assign(window.XONCA || {}, {
            routes: { quote: @json(route('cart.quote')), store: @json(route('booking.store')), cart: @json(route('cart')), availability: @json(route('availability')) },
            freeDeliveryFrom: {{ (float) $s('free_delivery_from') }},
        });
    </script>
    @stack('head')
</head>
<body class="min-h-screen overflow-x-hidden">
    <header data-header data-overlay="{{ $overlay ? 1 : 0 }}"
        class="group/h fixed inset-x-0 top-0 z-40 transition-colors duration-700 {{ $overlay ? 'text-ivory' : 'bg-ivory/95 text-ink backdrop-blur-md' }} data-[solid]:bg-ivory/95 data-[solid]:text-ink data-[solid]:backdrop-blur-md">
        <div class="container-x grid h-20 grid-cols-[1fr_auto_1fr] items-center lg:h-24">
            <div class="flex items-center gap-8">
                <button type="button" data-menu-toggle class="-ml-2 p-2 lg:hidden" aria-label="Menyu">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M3 8h18M3 16h12"/></svg>
                </button>
                <nav class="hidden items-center gap-9 lg:flex">
                    @foreach (array_slice($nav, 0, 2, true) as $href => $label)
                        <a href="{{ $href }}" class="caps opacity-80 transition hover:opacity-100">{{ $label }}</a>
                    @endforeach
                </nav>
            </div>

            <a href="{{ route('home') }}" class="text-center leading-none">
                <span class="block font-display text-[26px] tracking-[0.34em] uppercase lg:text-[30px]" style="margin-right:-.34em">{{ $s('site_name') }}</span>
                <span class="caps mt-1.5 block !text-[9px] !tracking-[0.5em] opacity-60" style="margin-right:-.5em">Bakı · xonça icarəsi</span>
            </a>

            <div class="flex items-center justify-end gap-8">
                <nav class="hidden items-center gap-9 lg:flex">
                    @foreach (array_slice($nav, 2, 2, true) as $href => $label)
                        <a href="{{ $href }}" class="caps opacity-80 transition hover:opacity-100">{{ $label }}</a>
                    @endforeach
                </nav>
                <a href="{{ route('cart') }}" class="caps flex items-center gap-2 opacity-90 hover:opacity-100" aria-label="Səbət">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M5 8h14l-1.2 12H6.2z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/></svg>
                    <span class="hidden sm:inline">Səbət</span>
                    <span data-cart-count class="hidden min-w-5 text-gold">0</span>
                </a>
            </div>
        </div>
        <div class="hairline opacity-0 transition-opacity duration-700 group-data-[solid]/h:opacity-40 {{ $overlay ? '' : '!opacity-40' }}"></div>
    </header>

    {{-- Mobile menu --}}
    <div data-menu class="fixed inset-0 z-50 hidden bg-onyx text-ivory">
        <div class="container-x flex h-20 items-center justify-between">
            <span class="font-display text-2xl tracking-[0.34em] uppercase">{{ $s('site_name') }}</span>
            <button type="button" data-menu-toggle class="-mr-2 p-2" aria-label="Bağla">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M5 5l14 14M19 5L5 19"/></svg>
            </button>
        </div>
        <nav class="container-x mt-10 flex flex-col">
            @foreach ($nav + [route('cart') => 'Səbət'] as $href => $label)
                <a href="{{ $href }}" class="border-b border-ivory/10 py-5 font-display text-4xl">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="container-x mt-12 space-y-2 text-ivory/60">
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
                    <div class="font-display text-4xl tracking-[0.3em] text-ivory uppercase">{{ $s('site_name') }}</div>
                    <p class="mt-6 max-w-sm text-ivory/55">Toy, nişan, xınayaxdı və söz kəsmə üçün əl ilə bəzədilmiş xonçalar. Gətiririk, qururuq, məclisdən sonra özümüz götürürük.</p>
                </div>
                <div>
                    <div class="caps text-gold-soft">Əlaqə</div>
                    <ul class="mt-6 space-y-3">
                        <li><a class="hover:text-ivory" href="tel:{{ $tel }}">{{ $s('phone') }}</a></li>
                        <li><a class="hover:text-ivory" href="https://wa.me/{{ $s('whatsapp') }}" target="_blank" rel="noopener">WhatsApp</a></li>
                        <li><a class="hover:text-ivory" href="https://instagram.com/{{ ltrim($s('instagram'), '@') }}" target="_blank" rel="noopener">Instagram</a></li>
                    </ul>
                </div>
                <div>
                    <div class="caps text-gold-soft">Kolleksiya</div>
                    <ul class="mt-6 space-y-3">
                        @foreach (\App\Models\Occasion::active()->take(5)->get() as $o)
                            <li><a class="hover:text-ivory" href="{{ route('catalog.occasion', $o) }}">{{ $o->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <div class="caps text-gold-soft">Ünvan</div>
                    <p class="mt-6">{{ $s('address') }}<br>{{ $s('working_hours') }}</p>
                </div>
            </div>
            <div class="mt-20 h-px bg-ivory/10"></div>
            <div class="caps mt-8 flex flex-col justify-between gap-3 !text-[10px] text-ivory/35 sm:flex-row">
                <span>© {{ date('Y') }} {{ $s('site_name') }}</span>
                <span>Bakı, Azərbaycan</span>
            </div>
        </div>
    </footer>

    <div data-toast class="pointer-events-none fixed inset-x-0 bottom-6 z-50 flex justify-center px-4"></div>
    @stack('scripts')
</body>
</html>
