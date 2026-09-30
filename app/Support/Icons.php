<?php

namespace App\Support;

/**
 * Hairline drawings of the things that go on a xonça, one per FillItem
 * model. Drawn on a 24 grid with a 1px champagne stroke; the owner's own
 * photo of an item replaces its drawing when he uploads one.
 */
class Icons
{
    public const PATHS = [
        'kelle_qend' => '<path d="M12 3L7.5 19h9z"/><path d="M8.7 15h6.6"/><path d="M9.4 19v1.6h5.2V19"/><path d="M10.6 5.2c-1.4-.9-2.6-.4-2.4.6.2.9 1.6.8 3.8.2m.6-.8c1.4-.9 2.6-.4 2.4.6-.2.9-1.6.8-3.8.2"/>',
        'candle' => '<path d="M10 9.5h4V20h-4z"/><path d="M12 9.5V8"/><path d="M12 7.8c-1.3-1.2-1.1-3 0-4.6 1.1 1.6 1.3 3.4 0 4.6z"/><path d="M7.5 20h9"/>',
        'candles3' => '<path d="M5 12.5h3V20H5zm5.5-4h3V20h-3zm5.5 3h3V20h-3z"/><path d="M6.5 12.5V11m5.5-2.5V7m5.5 4.5V10"/><path d="M12 6.8c-.8-.7-.7-1.8 0-2.8.7 1 .8 2.1 0 2.8z"/><path d="M3.5 20h17"/>',
        'pakhlava' => '<path d="M12 3.5l5 5-5 5-5-5z"/><path d="M7 12.5l5 5 5-5"/><path d="M7 16l5 5 5-5"/><circle cx="12" cy="8.5" r=".9"/>',
        'shekerbura' => '<path d="M3.5 16a8.5 8.5 0 0 1 17 0z"/><path d="M6 13.2l1.2-1.3 1.2 1.3 1.2-1.3 1.2 1.3 1.2-1.3 1.2 1.3 1.2-1.3 1.2 1.3 1.2-1.3 1.2 1.3"/>',
        'sweets_bowl' => '<path d="M3.5 11.5h17a8.5 7 0 0 1-17 0z"/><path d="M9 21h6m-3-2v2"/><circle cx="9" cy="9.3" r="1.6"/><circle cx="12.8" cy="8.4" r="1.6"/><circle cx="15.8" cy="10" r="1.1"/>',
        'gift_box' => '<path d="M4.5 10.5h15V20h-15zM3.5 7.5h17v3h-17zM12 7.5V20"/><path d="M12 7.5C10 4 7 4.4 7 6s3 1.5 5 1.5zm0 0c2-3.5 5-3.1 5-1.5s-3 1.5-5 1.5z"/>',
        'ring_box' => '<path d="M4 13h16v7H4z"/><path d="M4 13l1.8-5h12.4L20 13"/><circle cx="12" cy="11.2" r="2.4"/><path d="M11 8.8L12 7.4l1 1.4"/>',
        'henna_bowl' => '<path d="M3 12.5h18a9 6.5 0 0 1-18 0z"/><path d="M8 12.5V8.5m4 4V7m4 5.5V8.5"/><path d="M8 7.6c-.6-.5-.6-1.3 0-2 .6.7.6 1.5 0 2zm4-1.5c-.6-.5-.6-1.3 0-2 .6.7.6 1.5 0 2zm4 1.5c-.6-.5-.6-1.3 0-2 .6.7.6 1.5 0 2z"/>',
        'mirror' => '<ellipse cx="12" cy="9.5" rx="5.2" ry="6.6"/><ellipse cx="12" cy="9.5" rx="3.5" ry="4.9"/><path d="M12 16.1V20m-3.5 1h7"/>',
        'perfume' => '<rect x="6.5" y="10" width="11" height="10.5" rx="2.2"/><path d="M10 10V7.6h4V10M9.4 5h5.2v2.6H9.4z"/><path d="M9 14.5h6"/>',
        'fabric' => '<ellipse cx="6.5" cy="12" rx="2.6" ry="5"/><path d="M6.5 7h11m-11 10h11"/><path d="M17.5 7a2.6 5 0 0 1 0 10"/><path d="M11 7v10m3.5-10v10"/>',
        'shawl' => '<path d="M3.5 9L12 5l8.5 4L12 13z"/><path d="M3.5 9v5.2L12 18.5l8.5-4.3V9"/><path d="M12 13v5.5"/><path d="M9 9.2c0-1.2 1.4-1.7 2-.8.4.7-.3 1.4-1 1"/>',
        'apple' => '<path d="M12 7.3c-1.6-1.2-6-1.3-6 3.9 0 4 2.6 8 4.3 8 .8 0 1.1-.5 1.7-.5s.9.5 1.7.5c1.7 0 4.3-4 4.3-8 0-5.2-4.4-5.1-6-3.9z"/><path d="M12 7.3c0-1.6.6-3 2.2-4"/>',
        'pomegranate' => '<circle cx="12" cy="13.2" r="6.9"/><path d="M9.6 6.8L10 4l2 1.6L14 4l.4 2.8"/><path d="M9.5 12.5a3 3 0 0 1 2.5-2"/>',
        'orange' => '<circle cx="12" cy="13.2" r="6.9"/><path d="M12 6.3c.5-1.6 2-2.6 3.6-2.6-.3 1.6-1.9 2.6-3.6 2.6z"/><circle cx="12" cy="13.2" r=".7"/>',
        'grapes' => '<circle cx="9" cy="8.8" r="2"/><circle cx="13" cy="8.8" r="2"/><circle cx="11" cy="12.3" r="2"/><circle cx="15" cy="12.3" r="2"/><circle cx="13" cy="15.8" r="2"/><circle cx="11" cy="19.2" r="1.6"/><path d="M11 6.8V4.2l3-1.2"/>',
        'nuts_bowl' => '<path d="M3 12.5h18a9 6.5 0 0 1-18 0z"/><ellipse cx="8" cy="10.7" rx="2.1" ry="1.3"/><ellipse cx="12" cy="9.6" rx="2.1" ry="1.3"/><ellipse cx="16" cy="10.7" rx="2.1" ry="1.3"/>',
        'flowers' => '<circle cx="12" cy="5.8" r="2.3"/><circle cx="7.8" cy="8.6" r="2.3"/><circle cx="16.2" cy="8.6" r="2.3"/><path d="M12 8.1V20M8 10.9l4 5.2 4-5.2"/><path d="M9 20.5h6"/>',
        'cake' => '<path d="M4.5 20.5h15v-6h-15zM7 14.5v-4h10v4M9.2 10.5V7.3h5.6v3.2"/><path d="M12 7.3V5.2"/><path d="M4.5 17c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0 1.5-1 1.5-1"/>',
        'chocolate' => '<path d="M12 20s-7-4.3-7-9.6A3.8 3.8 0 0 1 12 8.2a3.8 3.8 0 0 1 7 2.2C19 15.7 12 20 12 20z"/><path d="M8.3 11.5h7.4"/>',
        'necklace' => '<path d="M5 3.5c0 7.2 3 10.5 7 10.5s7-3.3 7-10.5"/><path d="M12 14l-2 2.6 2 3.4 2-3.4z"/>',
        'shoes' => '<path d="M3 17.5h9.5c2.5 0 4-2 5.5-3.5.9-.9 3-.8 3 .8v2.7h-3.5l-.8 3"/><path d="M3 17.5c.3-3.3 2.2-6 4.8-6 2 0 3.2 2.5 4.7 6"/><path d="M5 17.5v3"/>',
        'tea' => '<path d="M8 5h8l-1.2 6c-.4 1-.4 2 0 3l1.2 5.5H8L9.2 14c.4-1 .4-2 0-3z"/><path d="M5 20.5h14"/><path d="M9.3 9h5.4"/>',
    ];

    public static function svg(?string $model, string $class = 'size-7'): string
    {
        $paths = self::PATHS[$model] ?? self::PATHS['gift_box'];

        return '<svg viewBox="0 0 24 24" class="' . e($class) . '" fill="none" stroke="currentColor" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
    }
}
