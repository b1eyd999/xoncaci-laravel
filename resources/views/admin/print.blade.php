@php($money = fn ($v) => \App\Support\Money::format($v))
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <title>{{ $booking->code }} — kuryer vərəqi</title>
    <style>
        * { box-sizing: border-box; }
        body { font: 14px/1.45 system-ui, 'Segoe UI', sans-serif; color: #222; margin: 0; padding: 28px; }
        h1 { margin: 0; font-size: 22px; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #8a1c2b; padding-bottom: 12px; }
        .code { font: 700 22px monospace; color: #8a1c2b; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 24px; margin: 18px 0; }
        .grid div b { display: block; font-size: 11px; text-transform: uppercase; color: #888; letter-spacing: .05em; }
        .big { font-size: 18px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; vertical-align: top; }
        th { font-size: 11px; text-transform: uppercase; color: #888; }
        .dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; border: 1px solid #0002; vertical-align: -1px; }
        .tot td { border: 0; padding: 3px 8px; }
        .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 50px; }
        .sign div { border-top: 1px solid #333; padding-top: 6px; font-size: 12px; color: #666; }
        .check { margin-top: 24px; padding: 12px; border: 1px dashed #aaa; border-radius: 8px; }
        @media print { body { padding: 0; } .noprint { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="top">
        <div>
            <h1>{{ \App\Models\Setting::get('site_name') }} — xonça icarəsi</h1>
            <div>{{ \App\Models\Setting::get('phone') }}</div>
        </div>
        <div style="text-align:right">
            <div class="code">{{ $booking->code }}</div>
            <div>{{ $booking->statusLabel() }}</div>
        </div>
    </div>

    <div class="grid">
        <div><b>Müştəri</b><span class="big">{{ $booking->customer_name }}</span></div>
        <div><b>Telefon</b><span class="big">{{ $booking->phone }}</span></div>
        <div><b>Məclis</b>{{ $booking->event_date->translatedFormat('j F Y, l') }}{{ $booking->event_time ? ', ' . $booking->event_time : '' }}{{ $booking->occasion ? ' · ' . $booking->occasion->name : '' }}</div>
        <div><b>Çatdırılma</b>{{ $booking->delivery_type === 'pickup' ? 'Özü götürür' : trim($booking->district . ', ' . $booking->address, ', ') }}</div>
        @php($before = (int) \App\Models\Setting::get('days_before'))
        @php($after = (int) \App\Models\Setting::get('days_after'))
        <div><b>Aparılacaq</b>{{ $booking->event_date->copy()->subDays($before)->translatedFormat('j F, l') }}</div>
        <div><b>Geri alınacaq</b>{{ $booking->event_date->copy()->addDays($after)->translatedFormat('j F, l') }}</div>
    </div>

    <table>
        <tr><th>#</th><th>Xonça</th><th>Dolduruş</th><th>Lent</th><th style="text-align:right">Məbləğ</th></tr>
        @foreach ($booking->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><b>{{ $item->xonca_name }}</b>@if ($item->note)<br><small>{{ $item->note }}</small>@endif</td>
                <td>{{ \App\Models\BookingItem::FILL_MODES[$item->fill_mode] ?? '' }}@if ($item->fill)<br><small>{{ $item->fillSummary() }}</small>@endif</td>
                <td><span class="dot" style="background: {{ $item->ribbon_color }}"></span></td>
                <td style="text-align:right">{{ $money($item->price + $item->fill_total) }}</td>
            </tr>
        @endforeach
    </table>
    <table class="tot" style="width:auto;margin-left:auto">
        <tr><td>Çatdırılma</td><td style="text-align:right">{{ $money($booking->delivery_fee) }}</td></tr>
        <tr><td><b>Cəmi</b></td><td style="text-align:right"><b>{{ $money($booking->total) }}</b></td></tr>
        @if ($booking->deposit_total > 0)
            <tr><td>Depozit (geri qaytarılır)</td><td style="text-align:right">{{ $money($booking->deposit_total) }}</td></tr>
        @endif
    </table>

    @if ($booking->note)
        <div class="check"><b>Müştərinin qeydi:</b> {{ $booking->note }}</div>
    @endif

    <div class="check">☐ Xonçalar tam təhvil verildi &nbsp;&nbsp; ☐ Ödəniş alındı &nbsp;&nbsp; ☐ Depozit alındı</div>

    <div class="sign">
        <div>Təhvil verdi (kuryer)</div>
        <div>Təhvil aldı (müştəri)</div>
    </div>
</body>
</html>
