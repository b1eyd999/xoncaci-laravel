<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Xonca;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/**
 * The month at a glance: which events are on which day, and — per xonça —
 * how many are out on each day, so the owner sees at once what he can still
 * promise when someone rings.
 */
class Calendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Sifarişlər';

    protected static ?string $navigationLabel = 'Təqvim';

    protected static ?string $title = 'Təqvim';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.calendar';

    #[Url]
    public string $month = '';

    #[Url]
    public string $view_mode = 'events';

    public function mount(): void
    {
        $this->month = $this->month ?: now()->format('Y-m');
    }

    public function shift(int $by): void
    {
        $this->month = CarbonImmutable::createFromFormat('Y-m-d', $this->month . '-01')->addMonths($by)->format('Y-m');
    }

    public function today(): void
    {
        $this->month = now()->format('Y-m');
    }

    protected function getViewData(): array
    {
        $first = CarbonImmutable::createFromFormat('Y-m-d', $this->month . '-01')->startOfDay();
        $start = $first->startOfWeek(CarbonImmutable::MONDAY);
        $end = $first->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY);

        $bookings = Booking::with('items')
            ->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->orderBy('event_time')
            ->get()
            ->groupBy(fn ($b) => $b->event_date->toDateString());

        $days = [];
        for ($d = $start; $d->lte($end); $d = $d->addDay()) {
            $days[] = [
                'date' => $d,
                'in' => $d->month === $first->month,
                'today' => $d->isToday(),
                'bookings' => $bookings->get($d->toDateString(), collect()),
            ];
        }

        // Occupancy: for each xonça and day, how many are out of stock.
        $matrix = [];
        if ($this->view_mode === 'stock') {
            // A tray is physically out from the day before to the day after.
            $before = max(0, (int) \App\Models\Setting::get('days_before'));
            $after = max(0, (int) \App\Models\Setting::get('days_after'));
            $gap = $before + $after;
            $monthDays = [];
            for ($d = $first; $d->month === $first->month; $d = $d->addDay()) {
                $monthDays[] = $d;
            }
            $events = Booking::query()
                ->whereIn('status', Booking::HOLDING)
                ->whereBetween('event_date', [$first->subDays($gap)->toDateString(), $first->endOfMonth()->addDays($gap)->toDateString()])
                ->with('items:id,booking_id,xonca_id')
                ->get(['id', 'event_date']);
            foreach (Xonca::orderBy('sort')->get() as $x) {
                $row = [];
                foreach ($monthDays as $d) {
                    $n = 0;
                    foreach ($events as $e) {
                        $diff = (int) round($e->event_date->diffInDays($d, false));
                        if ($diff >= -$before && $diff <= $after) {
                            $n += $e->items->where('xonca_id', $x->id)->count();
                        }
                    }
                    $row[] = $n;
                }
                $matrix[] = ['xonca' => $x, 'row' => $row];
            }

            return compact('days', 'first', 'matrix', 'monthDays') + ['url' => fn ($b) => BookingResource::getUrl('view', ['record' => $b])];
        }

        return compact('days', 'first', 'matrix') + ['monthDays' => [], 'url' => fn ($b) => BookingResource::getUrl('view', ['record' => $b])];
    }
}
