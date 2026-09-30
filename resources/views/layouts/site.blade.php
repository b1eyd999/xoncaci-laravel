@php($s = fn ($k) => \App\Models\Setting::get($k))
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#5a101b">
    <title>{{ isset($title) ? $title . ' · ' : '' }}{{ $s('site_name') }} — toy, nişan və xınayaxdı xonçaları</title>
    <meta name="description" content="{{ $description ?? 'Toy, nişan, xınayaxdı və söz kəsmə üçün xonçaların icarəsi. Xonçanın içini özünüz seçin və 3D-də baxın. Bakı üzrə çatdırılma.' }}">
    <meta property="og:title" content="{{ $title ?? $s('site_name') }}">
    <meta property="og:description" content="{{ $description ?? $s('hero_subtitle') }}">
    <meta property="og:type" content="website">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.XONCA = Object.assign(window.XONCA || {}, {
            routes: { quote: @json(route('cart.quote')), store: @json(route('booking.store')), cart: @json(route('cart')), availability: @json(route('availability')) },
            freeDeliveryFrom: {{ (float) $s('free_delivery_from') }},
            deliveryFee: {{ (float) $s('delivery_fee') }},
        });
    </script>
    @stack('head')
</head>
<body class="min-h-screen overflow-x-hidden">
    <header id="top" class="sticky top-0 z-40 border-b border-wine-900/5 bg-cream/80 backdrop-blur-xl">
        <div class="container-x flex h-16 items-center justify-between gap-4 sm:h-20">
            <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
                <span class="grid size-10 place-items-center rounded-full bg-wine-600 text-gold-300 shadow-md shadow-wine-600/30 transition group-hover:rotate-12">
                    <svg viewBox="0 0 32 32" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 22h24M6 22c0-6 4.5-11 10-11s10 5 10 11M16 11V7m-2 0h4"/><path d="M9 25h14" stroke-linecap="round"/></svg>
                </span>
                <span class="leading-none">
                    <span class="block font-display text-xl font-semibold text-wine-700 sm:text-2xl">{{ $s('site_name') }}</span>
                    <span class="hidden text-[11px] tracking-widest text-ink/50 uppercase sm:block">xonça icarəsi</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex">
                @foreach ([route('catalog') => 'Bütün xonçalar', route('home') . '#bayramlar' => 'Bayramlar', route('home') . '#nece' => 'Necə işləyir', route('home') . '#suallar' => 'Suallar', '#elaqe' => 'Əlaqə'] as $href => $label)
                    <a href="{{ $href }}" class="rounded-full px-4 py-2 text-sm font-semibold text-ink/70 transition hover:bg-wine-50 hover:text-wine-700">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <a href="tel:{{ preg_replace('/\s+/', '', $s('phone')) }}" class="hidden items-center gap-2 rounded-full px-3 py-2 text-sm font-bold text-wine-700 md:flex">
                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/></svg>
                    {{ $s('phone') }}
                </a>
                <a href="{{ route('cart') }}" class="relative grid size-11 place-items-center rounded-full bg-white text-wine-700 shadow-sm ring-1 ring-wine-900/10 transition hover:ring-wine-600/40" aria-label="Səbət">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h18M5 12c0-4 3.1-7 7-7s7 3 7 7M12 5V3M6 16h12l-1 4H7z" stroke-linejoin="round"/></svg>
                    <span data-cart-count class="absolute -top-1 -right-1 hidden min-w-5 rounded-full bg-gold-500 px-1.5 text-center text-[11px] leading-5 font-bold text-wine-900"></span>
                </a>
                <button type="button" data-menu-toggle class="grid size-11 place-items-center rounded-full bg-white ring-1 ring-wine-900/10 lg:hidden" aria-label="Menyu">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h10" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
        <div data-menu class="hidden border-t border-wine-900/5 bg-cream lg:hidden">
            <nav class="container-x flex flex-col py-3">
                @foreach ([route('catalog') => 'Bütün xonçalar', route('home') . '#bayramlar' => 'Bayramlar', route('home') . '#nece' => 'Necə işləyir', route('home') . '#suallar' => 'Suallar', '#elaqe' => 'Əlaqə'] as $href => $label)
                    <a href="{{ $href }}" class="rounded-xl px-3 py-3 text-base font-semibold text-ink/80 hover:bg-wine-50">{{ $label }}</a>
                @endforeach
                <a href="tel:{{ preg_replace('/\s+/', '', $s('phone')) }}" class="mt-2 btn btn-primary">📞 {{ $s('phone') }}</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer id="elaqe" class="relative mt-24 overflow-hidden bg-wine-900 text-white/80">
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]" style="background-image:radial-gradient(circle at 1px 1px,#e3c47f 1px,transparent 0);background-size:26px 26px"></div>
        <div class="container-x relative grid gap-10 py-16 md:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <div class="font-display text-3xl text-white">{{ $s('site_name') }}</div>
                <p class="mt-3 max-w-md text-white/60">Toy, nişan, xınayaxdı və söz kəsmə üçün əl işi xonçaların icarəsi. Hər xonça təmizlənir, yenidən bəzədilir və vaxtında çatdırılır.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="https://wa.me/{{ $s('whatsapp') }}" target="_blank" rel="noopener" class="btn bg-[#25D366] text-white hover:brightness-110">WhatsApp</a>
                    <a href="https://instagram.com/{{ ltrim($s('instagram'), '@') }}" target="_blank" rel="noopener" class="btn border border-white/20 text-white hover:bg-white/10">Instagram</a>
                </div>
            </div>
            <div>
                <div class="eyebrow text-gold-300">Əlaqə</div>
                <ul class="mt-4 space-y-2">
                    <li><a class="hover:text-white" href="tel:{{ preg_replace('/\s+/', '', $s('phone')) }}">{{ $s('phone') }}</a></li>
                    <li>{{ $s('address') }}</li>
                    <li>{{ $s('working_hours') }}</li>
                </ul>
            </div>
            <div>
                <div class="eyebrow text-gold-300">Səhifələr</div>
                <ul class="mt-4 space-y-2">
                    <li><a class="hover:text-white" href="{{ route('catalog') }}">Bütün xonçalar</a></li>
                    @foreach (\App\Models\Occasion::active()->take(5)->get() as $o)
                        <li><a class="hover:text-white" href="{{ route('catalog.occasion', $o) }}">{{ $o->name }} xonçaları</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10 py-6 text-center text-sm text-white/40">© {{ date('Y') }} {{ $s('site_name') }}. Bütün hüquqlar qorunur.</div>
    </footer>

    <a href="https://wa.me/{{ $s('whatsapp') }}" target="_blank" rel="noopener" aria-label="WhatsApp"
       class="fixed right-4 bottom-4 z-40 grid size-14 place-items-center rounded-full bg-[#25D366] text-white shadow-xl shadow-black/20 transition hover:scale-105 sm:right-6 sm:bottom-6" style="margin-bottom:env(safe-area-inset-bottom)">
        <svg class="size-7" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1s-.5-.1-.7.1-.8 1-1 1.2-.4.2-.7.1a8.2 8.2 0 0 1-4-3.5c-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6a1.1 1.1 0 0 0-.8.4 3.4 3.4 0 0 0-1.1 2.5 5.9 5.9 0 0 0 1.2 3.1 13.5 13.5 0 0 0 5.2 4.6c1.9.8 2.7.9 3.6.7a3.1 3.1 0 0 0 2-1.4 2.5 2.5 0 0 0 .2-1.4c-.1-.2-.3-.3-.6-.4zM12 21.8a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.9 9.9 0 1 1 12 21.8zm8.4-18.3A11.8 11.8 0 0 0 1.8 17.7L.1 24l6.4-1.7a11.8 11.8 0 0 0 5.6 1.4A11.9 11.9 0 0 0 20.4 3.5z"/></svg>
    </a>

    <div data-toast class="pointer-events-none fixed inset-x-0 bottom-24 z-50 flex justify-center px-4"></div>
    @stack('scripts')
</body>
</html>
