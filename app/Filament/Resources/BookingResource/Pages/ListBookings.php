<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Yeni sifariş')->icon('heroicon-m-plus')];
    }

    public function getTabs(): array
    {
        $today = now()->toDateString();
        $week = now()->addDays(7)->toDateString();

        return [
            'upcoming' => Tab::make('Qarşıdakılar')->icon('heroicon-m-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('event_date', '>=', $today)->whereNotIn('status', ['cancelled', 'returned']))
                ->badge(Booking::whereDate('event_date', '>=', $today)->whereNotIn('status', ['cancelled', 'returned'])->count()),
            'new' => Tab::make('Yeni')->icon('heroicon-m-bell-alert')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'new'))
                ->badge(Booking::where('status', 'new')->count())->badgeColor('warning'),
            'week' => Tab::make('Bu həftə')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('event_date', [$today, $week])->whereNotIn('status', ['cancelled'])),
            'return' => Tab::make('Geri alınacaq')->icon('heroicon-m-arrow-uturn-left')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'delivered'))
                ->badge(Booking::where('status', 'delivered')->count()),
            'deposit' => Tab::make('Depozit qaytarılmayıb')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'returned')->where('deposit_returned', false)->where('deposit_total', '>', 0)),
            'all' => Tab::make('Hamısı'),
        ];
    }
}
