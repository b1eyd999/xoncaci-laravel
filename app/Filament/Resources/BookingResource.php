<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\FillItem;
use App\Models\Setting;
use App\Models\Xonca;
use App\Support\Availability;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Sifarişlər';

    protected static ?string $modelLabel = 'sifariş';

    protected static ?string $pluralModelLabel = 'Sifarişlər';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationBadge(): ?string
    {
        $n = Booking::where('status', 'new')->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'customer_name', 'phone'];
    }

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema([
            Forms\Components\Group::make()->columnSpan(['default' => 3, 'lg' => 2])->schema([
                Forms\Components\Section::make('Xonçalar')->icon('heroicon-m-gift')->schema([
                    Forms\Components\Repeater::make('items')->relationship()->hiddenLabel()
                        ->schema(self::itemSchema())
                        ->columns(4)
                        ->itemLabel(fn (array $state) => ($state['xonca_name'] ?? 'Xonça') . (isset($state['price']) ? ' — ' . Money::format((float) $state['price']) : ''))
                        ->collapsible()
                        ->addActionLabel('Xonça əlavə et')
                        ->minItems(1)
                        ->defaultItems(1),
                ]),
                Forms\Components\Section::make('Qeydlər')->icon('heroicon-m-pencil-square')->columns(2)->schema([
                    Forms\Components\Textarea::make('note')->label('Müştərinin qeydi')->rows(3),
                    Forms\Components\Textarea::make('admin_note')->label('Daxili qeyd (müştəri görmür)')->rows(3),
                ]),
            ]),
            Forms\Components\Group::make()->columnSpan(['default' => 3, 'lg' => 1])->schema([
                Forms\Components\Section::make('Status')->icon('heroicon-m-flag')->schema([
                    Forms\Components\ToggleButtons::make('status')->hiddenLabel()->options(Booking::STATUSES)->colors(Booking::STATUS_COLORS)
                        ->default('confirmed')->required()->columns(2),
                    Forms\Components\Toggle::make('deposit_returned')->label('Depozit geri verildi'),
                ]),
                Forms\Components\Section::make('Müştəri')->icon('heroicon-m-user')->schema([
                    Forms\Components\TextInput::make('customer_name')->label('Ad')->required(),
                    Forms\Components\TextInput::make('phone')->label('Telefon')->tel()->required(),
                    Forms\Components\TextInput::make('email')->label('E-poçt')->email(),
                ]),
                Forms\Components\Section::make('Məclis')->icon('heroicon-m-calendar-days')->columns(2)->schema([
                    Forms\Components\DatePicker::make('event_date')->label('Tarix')->required()->native(false)->displayFormat('d.m.Y')->live()->columnSpanFull(),
                    Forms\Components\TextInput::make('event_time')->label('Saat')->placeholder('18:00'),
                    Forms\Components\Select::make('occasion_id')->label('Növ')->relationship('occasion', 'name'),
                ]),
                Forms\Components\Section::make('Çatdırılma')->icon('heroicon-m-truck')->schema([
                    Forms\Components\ToggleButtons::make('delivery_type')->hiddenLabel()->inline()->live()
                        ->options(['delivery' => 'Ünvana', 'pickup' => 'Özü götürür'])->default('delivery')->required(),
                    Forms\Components\Select::make('district')->label('Rayon')->options(fn () => array_combine(Setting::districts(), Setting::districts()))
                        ->searchable()->visible(fn (Get $get) => $get('delivery_type') === 'delivery'),
                    Forms\Components\TextInput::make('address')->label('Ünvan')->visible(fn (Get $get) => $get('delivery_type') === 'delivery'),
                    Forms\Components\TextInput::make('delivery_fee')->label('Çatdırılma haqqı')->numeric()->prefix('₼')->default(0),
                ]),
                Forms\Components\Section::make('Hesab')->icon('heroicon-m-calculator')->schema([
                    Forms\Components\Placeholder::make('sum')->hiddenLabel()->content(function (Get $get) {
                        $items = collect($get('items') ?? []);
                        $xonca = $items->sum(fn ($i) => (float) ($i['price'] ?? 0));
                        $fill = $items->sum(fn ($i) => collect($i['fill'] ?? [])->sum(fn ($f) => (float) ($f['price'] ?? 0) * (int) ($f['qty'] ?? 1)));
                        $deposit = $items->sum(fn ($i) => (float) ($i['deposit'] ?? 0));
                        $total = $xonca + $fill + (float) $get('delivery_fee');

                        return new \Illuminate\Support\HtmlString(
                            '<div class="space-y-1 text-sm">'
                            . '<div class="flex justify-between"><span>Xonçalar</span><b>' . Money::format($xonca) . '</b></div>'
                            . '<div class="flex justify-between"><span>Dolduruş</span><b>' . Money::format($fill) . '</b></div>'
                            . '<div class="flex justify-between"><span>Çatdırılma</span><b>' . Money::format((float) $get('delivery_fee')) . '</b></div>'
                            . '<div class="flex justify-between border-t pt-2 text-base"><span>Cəmi</span><b class="text-primary-600">' . Money::format($total) . '</b></div>'
                            . '<div class="flex justify-between text-gray-500"><span>+ depozit</span><span>' . Money::format($deposit) . '</span></div></div>'
                        );
                    }),
                ]),
            ]),
        ]);
    }

    /** Whether the chosen xonça is still free on the chosen day. */
    protected static function availability(Get $get, ?BookingItem $record): array
    {
        $date = $get('../../event_date');
        $x = Xonca::find($get('xonca_id'));
        if (! $date || ! $x) {
            return [null, 'gray'];
        }
        $taken = Availability::taken($x, Carbon::parse($date)->toDateString(), $record?->booking_id);

        return $taken >= $x->stock
            ? ["⚠ Bu tarixə tutulub ({$taken}/{$x->stock})", 'danger']
            : ['✓ Boşdur (' . ($x->stock - $taken) . '/' . $x->stock . ')', 'success'];
    }

    /** One xonça inside an order, as the admin edits it. */
    protected static function itemSchema(): array
    {
        return [
            Forms\Components\Select::make('xonca_id')->label('Xonça')->columnSpan(2)
                ->options(fn () => Xonca::orderBy('sort')->get()->mapWithKeys(fn ($x) => [$x->id => "{$x->code} · {$x->name}"]))
                ->searchable()->required()->live()
                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                    $x = Xonca::with('defaultFills.item')->find($state);
                    if (! $x) {
                        return;
                    }
                    $set('xonca_name', $x->name);
                    $set('price', $x->price);
                    $set('deposit', $x->deposit);
                    $set('ribbon_color', $x->ribbon_color);
                    if (($get('fill_mode') ?? 'ours') === 'ours') {
                        $set('fill', $x->defaultFills->filter(fn ($f) => $f->item)->map(fn ($f) => [
                            'id' => $f->fill_item_id, 'name' => $f->item->name, 'price' => (float) $f->item->price, 'qty' => $f->qty,
                        ])->values()->all());
                    }
                })
                ->hint(fn (Get $get, ?BookingItem $record) => self::availability($get, $record)[0])
                ->hintColor(fn (Get $get, ?BookingItem $record) => self::availability($get, $record)[1]),
            Forms\Components\Select::make('fill_mode')->label('Dolduruş')->options(BookingItem::FILL_MODES)->default('ours')->required(),
            Forms\Components\ColorPicker::make('ribbon_color')->label('Lent'),
            Forms\Components\TextInput::make('price')->label('İcarə')->numeric()->prefix('₼')->required()->live(onBlur: true),
            Forms\Components\TextInput::make('deposit')->label('Depozit')->numeric()->prefix('₼')->default(0),
            Forms\Components\TextInput::make('note')->label('Qeyd')->columnSpan(2),
            Forms\Components\Hidden::make('xonca_name'),
            Forms\Components\Repeater::make('fill')->label('İçindəkilər')->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('id')->label('Əşya')->columnSpan(2)
                        ->options(fn () => FillItem::orderBy('sort')->pluck('name', 'id'))
                        ->searchable()->required()->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $i = FillItem::find($state);
                            $set('name', $i?->name);
                            $set('price', (float) $i?->price);
                        }),
                    Forms\Components\TextInput::make('qty')->label('Say')->numeric()->minValue(1)->default(1)->required()->live(onBlur: true),
                    Forms\Components\TextInput::make('price')->label('Qiymət')->numeric()->prefix('₼')->live(onBlur: true),
                    Forms\Components\Hidden::make('name'),
                ])
                ->columns(4)->defaultItems(0)->addActionLabel('Əşya əlavə et')->reorderable(false)->collapsed(false),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('event_date')
            ->recordUrl(fn (Booking $r) => static::getUrl('view', ['record' => $r]))
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kod')->searchable()->fontFamily('mono')->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('event_date')->label('Tarix')->date('d.m.Y')->sortable()
                    ->description(function (Booking $r) {
                        $d = $r->daysLeft();

                        return match (true) {
                            $d === 0 => 'Bu gün',
                            $d === 1 => 'Sabah',
                            $d > 1 => "{$d} gün qalıb",
                            default => abs($d) . ' gün əvvəl',
                        };
                    }),
                Tables\Columns\TextColumn::make('customer_name')->label('Müştəri')->searchable()->description(fn (Booking $r) => $r->phone),
                Tables\Columns\TextColumn::make('items.xonca_name')->label('Xonçalar')->listWithLineBreaks()->limitList(3)->expandableLimitedList(),
                Tables\Columns\TextColumn::make('occasion.name')->label('Məclis')->badge()->color('gray')->toggleable(),
                Tables\Columns\TextColumn::make('total')->label('Cəmi')->money('AZN')->sortable()->weight('bold')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('AZN')->label('')),
                Tables\Columns\TextColumn::make('delivery_type')->label('Çatdırılma')->toggleable()
                    ->formatStateUsing(fn ($state, Booking $r) => $state === 'pickup' ? 'Özü götürür' : ($r->district ?: 'Ünvana'))
                    ->icon(fn ($state) => $state === 'pickup' ? 'heroicon-m-building-storefront' : 'heroicon-m-truck'),
                Tables\Columns\SelectColumn::make('status')->label('Status')->options(Booking::STATUSES)->selectablePlaceholder(false),
                Tables\Columns\TextColumn::make('created_at')->label('Gəlib')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(Booking::STATUSES)->multiple(),
                Tables\Filters\SelectFilter::make('occasion_id')->label('Məclis')->relationship('occasion', 'name'),
                Tables\Filters\Filter::make('event_date')->form([
                    Forms\Components\DatePicker::make('from')->label('Tarixdən')->native(false),
                    Forms\Components\DatePicker::make('until')->label('Tarixədək')->native(false),
                ])->query(fn (Builder $query, array $data) => $query
                    ->when($data['from'] ?? null, fn ($q, $v) => $q->whereDate('event_date', '>=', $v))
                    ->when($data['until'] ?? null, fn ($q, $v) => $q->whereDate('event_date', '<=', $v)))
                    ->columns(2),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Bax'),
                    Tables\Actions\EditAction::make()->label('Dəyiş'),
                    Tables\Actions\Action::make('whatsapp')->label('WhatsApp')->icon('heroicon-m-chat-bubble-oval-left')->color('success')
                        ->url(fn (Booking $r) => $r->whatsappUrl(), shouldOpenInNewTab: true),
                    Tables\Actions\Action::make('call')->label('Zəng et')->icon('heroicon-m-phone')->color('gray')
                        ->url(fn (Booking $r) => 'tel:' . preg_replace('/\s+/', '', $r->phone)),
                    Tables\Actions\Action::make('print')->label('Çap (kuryer vərəqi)')->icon('heroicon-m-printer')->color('gray')
                        ->url(fn (Booking $r) => route('admin.booking.print', $r), shouldOpenInNewTab: true),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('confirm')->label('Təsdiqlə')->icon('heroicon-m-check')
                        ->action(function ($records) {
                            $records->each->update(['status' => 'confirmed']);
                            Notification::make()->title('Təsdiqləndi')->success()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Hələ sifariş yoxdur')
            ->emptyStateDescription('Saytdan gələn sifarişlər burada görünəcək. Telefonla gələni «Yeni sifariş» ilə əlavə edin.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['items', 'occasion']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
