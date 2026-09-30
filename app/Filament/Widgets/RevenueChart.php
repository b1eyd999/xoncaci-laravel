<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Gəlir və sifarişlər (12 ay)';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $months = collect(range(11, 0))->map(fn ($i) => now()->startOfMonth()->subMonthsNoOverflow($i));
        $rows = Booking::where('status', '!=', 'cancelled')
            ->where('event_date', '>=', $months->first()->toDateString())
            ->get(['event_date', 'total'])
            ->groupBy(fn ($b) => $b->event_date->format('Y-m'));

        return [
            'datasets' => [
                [
                    'label' => 'Gəlir (AZN)',
                    'data' => $months->map(fn ($m) => round((float) ($rows->get($m->format('Y-m'))?->sum('total') ?? 0)))->all(),
                    'borderColor' => '#8a1c2b',
                    'backgroundColor' => 'rgba(138,28,43,.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Sifariş',
                    'data' => $months->map(fn ($m) => $rows->get($m->format('Y-m'))?->count() ?? 0)->all(),
                    'borderColor' => '#c9a14a',
                    'backgroundColor' => '#c9a14a',
                    'type' => 'bar',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['position' => 'left', 'beginAtZero' => true],
                'y1' => ['position' => 'right', 'beginAtZero' => true, 'grid' => ['drawOnChartArea' => false]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
