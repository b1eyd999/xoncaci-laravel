@props(['xonca', 'dark' => false])
@php
    /*
     * A champagne line drawing of the tray in its own shape, standing in
     * until the owner photographs the xonça. Drawn in a 400×500 box: the
     * tray's top face is a flattened outline around (200, 370).
     */
    $shape = $xonca->shape;
    $cx = 200; $cy = 372;
    $pts = function (float $squash = 0.27, float $grow = 1.0) use ($shape, $cx, $cy) {
        $out = [];
        $n = 96;
        for ($i = 0; $i < $n; $i++) {
            $t = $i / $n * 2 * M_PI;
            switch ($shape) {
                case 'oval':
                    [$x, $y] = [cos($t) * 165, sin($t) * 150];
                    break;
                case 'rect':
                    $c = cos($t); $s = sin($t);
                    $k = 1 / max(pow(abs($c) ** 6 + abs($s) ** 6, 1 / 6), 1e-6);
                    [$x, $y] = [$c * $k * 160, $s * $k * 150];
                    break;
                case 'octagon':
                    $seg = M_PI / 4;
                    $a = fmod($t + $seg / 2, $seg) - $seg / 2;
                    $r = 148 * cos($seg / 2) / cos($a);
                    [$x, $y] = [cos($t) * $r, sin($t) * $r];
                    break;
                case 'heart':
                    $hx = 16 * sin($t) ** 3;
                    $hy = -(13 * cos($t) - 5 * cos(2 * $t) - 2 * cos(3 * $t) - cos(4 * $t));
                    [$x, $y] = [$hx * 9.2, $hy * 9.2 + 20];
                    break;
                default:
                    [$x, $y] = [cos($t) * 145, sin($t) * 145];
            }
            $out[] = [$cx + $x * $grow, $cy + $y * $squash * $grow];
        }
        return $out;
    };
    $path = fn (array $p, float $dy = 0) => 'M' . collect($p)->map(fn ($q) => round($q[0], 1) . ' ' . round($q[1] + $dy, 1))->implode(' L') . ' Z';
    $rim = $pts();
    $inner = $pts(0.27, 0.9);
    $left = collect($rim)->min(fn ($q) => $q[0]);
    $right = collect($rim)->max(fn ($q) => $q[0]);
    $w = ($right - $left) / 2;
    $top = $cover = $xonca->cover_type;
    $domeTop = $cy - ($shape === 'heart' ? 250 : 235);
    $stroke = $dark ? '#d6c39c' : '#b39359';
    $bg = $dark ? 'radial-gradient(ellipse at 50% 38%, #2a2420 0%, #151210 70%)' : 'radial-gradient(ellipse at 50% 38%, #fffdf9 0%, #f3ece1 62%, #ebe2d4 100%)';
@endphp
<div {{ $attributes->merge(['class' => 'relative size-full']) }} style="background: {{ $bg }}">
    <svg viewBox="0 0 400 500" class="absolute inset-0 size-full" fill="none" stroke="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        {{-- floor shadow --}}
        <ellipse cx="200" cy="{{ $cy + 62 }}" rx="{{ $w * 0.95 }}" ry="14" fill="{{ $stroke }}" fill-opacity=".07" stroke="none"/>
        {{-- feet --}}
        @foreach ([-0.55, -0.2, 0.2, 0.55] as $f)
            <path d="M{{ 200 + $w * $f - 7 }} {{ $cy + 34 }} q7 14 14 0" stroke-width=".9" opacity=".8"/>
        @endforeach
        {{-- tray body: lower edge, upper edge, engraving --}}
        <path d="{{ $path($rim, 26) }}" stroke-width=".8" opacity=".45"/>
        <line x1="{{ $left }}" y1="{{ $cy }}" x2="{{ $left + 2 }}" y2="{{ $cy + 26 }}" stroke-width=".8" opacity=".6"/>
        <line x1="{{ $right }}" y1="{{ $cy }}" x2="{{ $right - 2 }}" y2="{{ $cy + 26 }}" stroke-width=".8" opacity=".6"/>
        <path d="{{ $path($rim) }}" stroke-width="1.1"/>
        <path d="{{ $path($inner) }}" stroke-width=".6" opacity=".7"/>
        <path d="{{ $path($pts(0.27, 0.55)) }}" stroke-width=".5" opacity=".45" stroke-dasharray="2 5"/>

        @if ($cover === 'none')
            {{-- the filling, drawn as quiet silhouettes --}}
            <g stroke-width=".9">
                <path d="M200 {{ $cy - 118 }} L178 {{ $cy - 6 }} H222 Z"/>
                <path d="M183 {{ $cy - 34 }} H217" opacity=".7"/>
                @foreach ([[-95, 0.9], [-60, 1.15], [70, 1], [105, 0.85]] as [$dx, $s])
                    <path d="M{{ 200 + $dx - 6 * $s }} {{ $cy - 8 }} v{{ -58 * $s }} h{{ 12 * $s }} v{{ 58 * $s }}"/>
                    <path d="M{{ 200 + $dx }} {{ $cy - 8 - 58 * $s }} c-4 -6 -3 -12 0 -18 c3 6 4 12 0 18z" opacity=".8"/>
                @endforeach
                @foreach ([[-120, 12], [-25, 20], [30, 18], [128, 10]] as [$dx, $dy])
                    <circle cx="{{ 200 + $dx }}" cy="{{ $cy - $dy }}" r="13"/>
                @endforeach
            </g>
        @else
            {{-- dome: tulle falls in folds from a knot; glass is one clean curve --}}
            @php
                $l = $left + $w * 0.06; $r = $right - $w * 0.06;
                $dome = "M{$l} {$cy} C{$l} " . ($cy - 170) . ' ' . (200 - $w * 0.55) . " {$domeTop} 200 {$domeTop} C" . (200 + $w * 0.55) . " {$domeTop} {$r} " . ($cy - 170) . " {$r} {$cy}";
            @endphp
            <path d="{{ $dome }}" stroke-width="1"/>
            @if ($cover === 'glass')
                <path d="M{{ 200 - $w * 0.62 }} {{ $cy - 120 }} C{{ 200 - $w * 0.6 }} {{ $cy - 190 }} {{ 200 - $w * 0.35 }} {{ $domeTop + 22 }} {{ 200 - $w * 0.12 }} {{ $domeTop + 12 }}" stroke-width=".7" opacity=".55"/>
                <circle cx="200" cy="{{ $domeTop - 12 }}" r="11" stroke-width="1"/>
                <path d="M195 {{ $domeTop - 1 }} h10" stroke-width="1"/>
            @else
                @foreach ([-0.72, -0.42, -0.14, 0.14, 0.42, 0.72] as $k)
                    <path d="M{{ 200 + $w * $k * 0.12 }} {{ $domeTop + 16 }} C{{ 200 + $w * $k * 0.5 }} {{ $domeTop + 90 }} {{ 200 + $w * $k * 0.95 }} {{ $cy - 120 }} {{ 200 + $w * $k * 1.02 }} {{ $cy + 2 }}" stroke-width=".55" opacity=".5"/>
                @endforeach
                {{-- bow on the knot --}}
                <g stroke-width="1" transform="translate(200 {{ $domeTop + 4 }})">
                    <path d="M0 0 C-14 -22 -44 -18 -36 -2 C-30 10 -12 6 0 0z"/>
                    <path d="M0 0 C14 -22 44 -18 36 -2 C30 10 12 6 0 0z"/>
                    <path d="M-3 3 C-10 18 -16 30 -22 40 M3 3 C10 18 16 30 22 40"/>
                    <circle cx="0" cy="0" r="4"/>
                </g>
            @endif
        @endif
    </svg>
    <div class="absolute inset-x-0 top-6 text-center font-display text-sm tracking-[0.3em] italic {{ $dark ? 'text-gold-soft/70' : 'text-gold-deep/70' }}">N° {{ preg_replace('/\D+/', '', $xonca->code) ?: $xonca->id }}</div>
</div>
