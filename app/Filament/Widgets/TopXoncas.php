<?php

namespace App\Filament\Widgets;

use App\Models\BookingItem;
use Filament\Widgets\ChartWidget;

class TopXoncas extends ChartWidget
{
    protected static ?string $heading = 'Ən çox icarəyə gedən xonçalar';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $top = BookingItem::query()
            ->whereHas('booking', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->selectRaw('xonca_name, count(*) as n')
            ->groupBy('xonca_name')->orderByDesc('n')->limit(8)->get();

        return [
            'datasets' => [[
                'label' => 'Sifariş',
                'data' => $top->pluck('n')->all(),
                'backgroundColor' => ['#8a1c2b', '#a8822f', '#c2566a', '#c9a14a', '#5a101b', '#e3c47f', '#731523', '#d4ad5a'],
                'borderRadius' => 6,
            ]],
            'labels' => $top->pluck('xonca_name')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]]];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
