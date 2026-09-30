<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Every booking lands in the owner's Telegram the moment it is placed. The
 * bot and the chat are set in admin → Tənzimləmələr.
 */
class Telegram
{
    public static function on(): bool
    {
        return filled(Setting::get('telegram_token')) && filled(Setting::get('telegram_chat'));
    }

    public static function send(string $text): bool
    {
        if (! self::on()) {
            return false;
        }

        try {
            $answer = Http::timeout(10)->post('https://api.telegram.org/bot' . trim(Setting::get('telegram_token')) . '/sendMessage', [
                'chat_id' => trim(Setting::get('telegram_chat')),
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
            if ($answer->successful()) {
                return true;
            }
            Log::error('Telegram refused: ' . $answer->body());
        } catch (Throwable $e) {
            Log::error('Telegram is unreachable: ' . $e->getMessage());
        }

        return false;
    }

    public static function booking(Booking $booking): void
    {
        $booking->loadMissing('items', 'occasion');

        $lines = ['🎀 <b>Yeni sifariş ' . e($booking->code) . '</b>', ''];
        foreach ($booking->items as $item) {
            $lines[] = '• <b>' . e($item->xonca_name) . '</b> — ' . Money::format($item->price)
                . "\n   " . e(BookingItem::FILL_MODES[$item->fill_mode] ?? $item->fill_mode)
                . ($item->fill ? "\n   <i>" . e($item->fillSummary()) . '</i>' : '');
        }
        $lines[] = '';
        $lines[] = '💰 <b>Cəmi: ' . Money::format($booking->total) . '</b>' . ($booking->deposit_total > 0 ? ' + depozit ' . Money::format($booking->deposit_total) : '');
        $lines[] = '🗓 ' . $booking->event_date->format('d.m.Y') . ($booking->event_time ? ', ' . e($booking->event_time) : '')
            . ($booking->occasion ? ' · ' . e($booking->occasion->name) : '');
        $lines[] = '👤 ' . e($booking->customer_name) . ' · 📞 ' . e($booking->phone);
        $lines[] = $booking->delivery_type === 'pickup'
            ? '🏠 Özü götürəcək'
            : '🚚 ' . e(trim(($booking->district ? $booking->district . ', ' : '') . $booking->address));
        if ($booking->note) {
            $lines[] = '📝 ' . e($booking->note);
        }
        $lines[] = '';
        $lines[] = url('/admin/bookings/' . $booking->code);

        self::send(implode("\n", $lines));
    }
}
