<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Setting;
use App\Models\Xonca;
use Carbon\CarbonImmutable;

/**
 * A rented xonça leaves the shelf the day before the event and comes back the
 * day after (both are settings). Two events clash when those windows
 * overlap, i.e. when they are closer than before + after days.
 */
class Availability
{
    public static function gap(): int
    {
        return max(0, (int) Setting::get('days_before')) + max(0, (int) Setting::get('days_after'));
    }

    public static function taken(Xonca $xonca, string $date, ?int $ignoreBooking = null): int
    {
        $day = CarbonImmutable::parse($date);
        $gap = self::gap();

        return BookingItem::query()
            ->where('xonca_id', $xonca->id)
            ->whereHas('booking', function ($q) use ($day, $gap, $ignoreBooking) {
                $q->whereIn('status', Booking::HOLDING)
                    ->whereBetween('event_date', [$day->subDays($gap)->toDateString(), $day->addDays($gap)->toDateString()]);
                if ($ignoreBooking) {
                    $q->where('id', '!=', $ignoreBooking);
                }
            })
            ->count();
    }

    public static function free(Xonca $xonca, string $date): int
    {
        return max(0, (int) $xonca->stock - self::taken($xonca, $date));
    }

    /** The first day a customer may book for. */
    public static function earliest(): CarbonImmutable
    {
        return CarbonImmutable::today()->addDays(max(0, (int) Setting::get('min_days_ahead')));
    }

    /**
     * Days of the next months on which this xonça is fully booked — the
     * calendar greys them out before the customer even picks one.
     *
     * @return list<string>
     */
    public static function busyDays(Xonca $xonca, int $days = 120): array
    {
        $gap = self::gap();
        $from = CarbonImmutable::today();
        $to = $from->addDays($days);

        $events = BookingItem::query()
            ->where('xonca_id', $xonca->id)
            ->whereHas('booking', fn ($q) => $q->whereIn('status', Booking::HOLDING)
                ->whereBetween('event_date', [$from->subDays($gap)->toDateString(), $to->addDays($gap)->toDateString()]))
            ->with('booking:id,event_date')
            ->get()
            ->map(fn ($i) => $i->booking->event_date->toDateString())
            ->countBy();

        if ($events->isEmpty()) {
            return [];
        }

        $busy = [];
        for ($d = $from; $d->lte($to); $d = $d->addDay()) {
            $count = 0;
            foreach ($events as $day => $n) {
                if (abs(CarbonImmutable::parse($day)->diffInDays($d, false)) <= $gap) {
                    $count += $n;
                }
            }
            if ($count >= $xonca->stock) {
                $busy[] = $d->toDateString();
            }
        }

        return $busy;
    }
}
