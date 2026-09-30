<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Http\Controllers\SiteController;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Support\Money;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    public function getTitle(): string
    {
        return 'Sifariş ' . $this->record->code;
    }

    protected function getHeaderActions(): array
    {
        // The next step is one tap: no dialog to confirm.
        $next = [
            'new' => ['confirmed', 'Təsdiqlə', 'heroicon-m-check'],
            'confirmed' => ['preparing', 'Hazırlanır', 'heroicon-m-sparkles'],
            'preparing' => ['delivered', 'Çatdırıldı', 'heroicon-m-truck'],
            'delivered' => ['returned', 'Geri qaytarıldı', 'heroicon-m-arrow-uturn-left'],
        ][$this->record->status] ?? null;

        return array_filter([
            $next ? Actions\Action::make('advance')->label($next[1])->icon($next[2])->color('success')
                ->action(function () use ($next) {
                    $this->record->update(['status' => $next[0]]);
                    Notification::make()->title('Status: ' . Booking::STATUSES[$next[0]])->success()->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }) : null,
            Actions\Action::make('whatsapp')->label('WhatsApp')->icon('heroicon-m-chat-bubble-oval-left')->color('gray')
                ->url($this->record->whatsappUrl(), shouldOpenInNewTab: true),
            Actions\Action::make('print')->label('Çap')->icon('heroicon-m-printer')->color('gray')
                ->url(route('admin.booking.print', $this->record), shouldOpenInNewTab: true),
            Actions\EditAction::make()->label('Dəyiş'),
            $this->record->status !== 'cancelled' ? Actions\Action::make('cancel')->label('Ləğv et')->color('danger')->icon('heroicon-m-x-mark')
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);
                    Notification::make()->title('Sifariş ləğv edildi')->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }) : null,
        ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $items = SiteController::itemCatalog();

        return $infolist->columns(3)->schema([
            Infolists\Components\Group::make()->columnSpan(['default' => 3, 'lg' => 2])->schema([
                Infolists\Components\RepeatableEntry::make('items')->label('Xonçalar')->schema([
                    Infolists\Components\TextEntry::make('preview')->hiddenLabel()->columnSpan(1)
                        ->state(function (BookingItem $item) use ($items) {
                            if (! $item->xonca) {
                                return '—';
                            }
                            $cfg = $item->xonca->toViewer();
                            $cfg['ribbonColor'] = $item->ribbon_color ?: $cfg['ribbonColor'];
                            $cfg['fill'] = collect($item->fill ?? [])->map(fn ($f) => ['id' => (int) $f['id'], 'qty' => (int) $f['qty']])->values()->all();

                            return new HtmlString('<div wire:ignore><xonca-preview data-mode="static" data-height="260" data-config="' . e(json_encode($cfg)) . '" data-items="' . e(json_encode($items)) . '"></xonca-preview></div>');
                        }),
                    Infolists\Components\Group::make()->columnSpan(1)->schema([
                        Infolists\Components\TextEntry::make('xonca_name')->label('Xonça')->weight('bold')->size('lg')
                            ->url(fn (BookingItem $i) => $i->xonca ? route('xonca', $i->xonca) : null, true),
                        Infolists\Components\TextEntry::make('fill_mode')->label('Dolduruş')->formatStateUsing(fn ($state) => BookingItem::FILL_MODES[$state] ?? $state)->badge(),
                        Infolists\Components\ColorEntry::make('ribbon_color')->label('Lent'),
                        Infolists\Components\TextEntry::make('price')->label('İcarə')->money('AZN'),
                        Infolists\Components\TextEntry::make('deposit')->label('Depozit')->money('AZN'),
                        Infolists\Components\TextEntry::make('note')->label('Qeyd')->placeholder('—'),
                    ]),
                    Infolists\Components\TextEntry::make('fill')->label('İçindəkilər')->columnSpan(1)
                        ->state(fn (BookingItem $i) => collect($i->fill ?? [])->map(fn ($f) => $f['name'] . ' × ' . $f['qty'] . ' — ' . Money::format($f['price'] * $f['qty']))->all())
                        ->listWithLineBreaks()->bulleted()->placeholder('Boş xonça'),
                ])->columns(3),
                Infolists\Components\Section::make('Qeydlər')->columns(2)->schema([
                    Infolists\Components\TextEntry::make('note')->label('Müştərinin qeydi')->placeholder('—'),
                    Infolists\Components\TextEntry::make('admin_note')->label('Daxili qeyd')->placeholder('—'),
                ]),
            ]),
            Infolists\Components\Group::make()->columnSpan(['default' => 3, 'lg' => 1])->schema([
                Infolists\Components\Section::make()->schema([
                    Infolists\Components\TextEntry::make('status')->label('Status')->badge()
                        ->formatStateUsing(fn ($state) => Booking::STATUSES[$state] ?? $state)
                        ->color(fn ($state) => Booking::STATUS_COLORS[$state] ?? 'gray'),
                    Infolists\Components\TextEntry::make('event_date')->label('Məclis')->date('d.m.Y, l')
                        ->suffix(fn (Booking $r) => $r->event_time ? ', ' . $r->event_time : '')
                        ->helperText(fn (Booking $r) => $r->occasion?->name),
                    Infolists\Components\TextEntry::make('customer_name')->label('Müştəri')->weight('bold'),
                    Infolists\Components\TextEntry::make('phone')->label('Telefon')->url(fn (Booking $r) => 'tel:' . preg_replace('/\s+/', '', $r->phone))->copyable(),
                    Infolists\Components\TextEntry::make('delivery')->label('Çatdırılma')
                        ->state(fn (Booking $r) => $r->delivery_type === 'pickup' ? 'Özü götürür' : trim($r->district . ', ' . $r->address, ', ')),
                ]),
                Infolists\Components\Section::make('Hesab')->schema([
                    Infolists\Components\TextEntry::make('xonca_total')->label('Xonçalar')->money('AZN')->inlineLabel(),
                    Infolists\Components\TextEntry::make('fill_total')->label('Dolduruş')->money('AZN')->inlineLabel(),
                    Infolists\Components\TextEntry::make('delivery_fee')->label('Çatdırılma')->money('AZN')->inlineLabel(),
                    Infolists\Components\TextEntry::make('total')->label('Cəmi')->money('AZN')->inlineLabel()->weight('bold')->size('lg')->color('primary'),
                    Infolists\Components\TextEntry::make('deposit_total')->label('Depozit')->money('AZN')->inlineLabel()
                        ->helperText(fn (Booking $r) => $r->deposit_total > 0 ? ($r->deposit_returned ? '✓ geri verilib' : 'hələ geri verilməyib') : null),
                    Infolists\Components\TextEntry::make('created_at')->label('Sifariş vaxtı')->dateTime('d.m.Y H:i')->inlineLabel(),
                ]),
            ]),
        ]);
    }
}
