<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $month = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
        $live = fn () => Booking::where('status', '!=', 'cancelled');

        $revenue = (float) $live()->whereBetween('event_date', $month)->sum('total');
        $prev = (float) $live()->whereBetween('event_date', [
            now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
            now()->subMonthNoOverflow()->endOfMonth()->toDateString(),
        ])->sum('total');
        $trend = $prev > 0 ? (int) round(($revenue - $prev) / $prev * 100) : null;

        $spark = collect(range(6, 0))->map(fn ($i) => (float) Booking::whereDate('created_at', now()->subDays($i)->toDateString())->count())->all();

        return [
            Stat::make('Yeni sifarişlər', Booking::where('status', 'new')->count())
                ->description('cavab gözləyir')->descriptionIcon('heroicon-m-bell-alert')
                ->color('warning')->chart($spark)
                ->url(BookingResource::getUrl('index', ['activeTab' => 'new'])),
            Stat::make('Bu ayın gəliri', Money::format($revenue))
                ->description($trend === null ? 'məclis tarixinə görə' : ($trend >= 0 ? '+' : '') . $trend . '% keçən aya görə')
                ->descriptionIcon($trend !== null && $trend < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($trend !== null && $trend < 0 ? 'danger' : 'success'),
            Stat::make('7 gündə məclis', $live()->whereBetween('event_date', [now()->toDateString(), now()->addDays(7)->toDateString()])->count())
                ->description('çatdırılacaq')->descriptionIcon('heroicon-m-truck')->color('info')
                ->url(BookingResource::getUrl('index', ['activeTab' => 'week'])),
            Stat::make('Geri alınacaq', Booking::where('status', 'delivered')->count())
                ->description('müştəridə olan xonçalar')->descriptionIcon('heroicon-m-arrow-uturn-left')->color('gray')
                ->url(BookingResource::getUrl('index', ['activeTab' => 'return'])),
        ];
    }
}
