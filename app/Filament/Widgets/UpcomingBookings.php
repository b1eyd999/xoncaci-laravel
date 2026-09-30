<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingBookings extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Yaxın məclislər';

    public function table(Table $table): Table
    {
        return $table
            ->query(Booking::query()->with('items')
                ->whereDate('event_date', '>=', now()->toDateString())
                ->whereNotIn('status', ['cancelled', 'returned'])
                ->orderBy('event_date'))
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->recordUrl(fn (Booking $r) => BookingResource::getUrl('view', ['record' => $r]))
            ->columns([
                Tables\Columns\TextColumn::make('event_date')->label('Tarix')->date('d.m.Y')
                    ->description(fn (Booking $r) => match (true) {
                        $r->daysLeft() === 0 => 'Bu gün',
                        $r->daysLeft() === 1 => 'Sabah',
                        default => $r->daysLeft() . ' gün qalıb',
                    }),
                Tables\Columns\TextColumn::make('customer_name')->label('Müştəri')->description(fn (Booking $r) => $r->phone),
                Tables\Columns\TextColumn::make('items.xonca_name')->label('Xonçalar')->listWithLineBreaks()->limitList(2),
                Tables\Columns\TextColumn::make('district')->label('Rayon')->placeholder('Özü götürür'),
                Tables\Columns\TextColumn::make('total')->label('Cəmi')->money('AZN'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => Booking::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => Booking::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->emptyStateHeading('Yaxın günlərdə məclis yoxdur');
    }
}
