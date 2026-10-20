<?php

namespace App\Filament\Resources;

use App\Filament\Resources\XoncaResource\Pages;
use App\Models\FillItem;
use App\Models\Xonca;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class XoncaResource extends Resource
{
    protected static ?string $model = Xonca::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Kataloq';

    protected static ?string $modelLabel = 'xonça';

    protected static ?string $pluralModelLabel = 'Xonçalar';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->columns(12)->schema([
            Forms\Components\Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                Forms\Components\Tabs\Tab::make('Əsas')->icon('heroicon-m-information-circle')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')->label('Ad')->required()->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set, ?Xonca $record) => $record ? null : $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('code')->label('Kod')->required()->maxLength(30)->unique(ignoreRecord: true)
                            ->default(fn () => 'XN-' . str_pad((string) ((Xonca::max('id') ?? 0) + 1), 3, '0', STR_PAD_LEFT)),
                        Forms\Components\TextInput::make('slug')->label('Link (slug)')->required()->unique(ignoreRecord: true)
                            ->prefix('/xonca/')->helperText('Saytdakı ünvanı. Dəyişsəniz köhnə link işləməyəcək.'),
                        Forms\Components\Select::make('occasions')->label('Bayramlar')->relationship('occasions', 'name')->multiple()->preload(),
                        Forms\Components\Select::make('style')->label('Stil')->options(Xonca::STYLES)->required()->default('klassik'),
                        Forms\Components\Toggle::make('is_active')->label('Saytda görünsün')->default(true)->inline(false),
                    ]),
                    Forms\Components\TextInput::make('short_description')->label('Qısa təsvir (kartda)')->maxLength(160),
                    Forms\Components\Textarea::make('description')->label('Ətraflı təsvir')->rows(4),
                ]),

                Forms\Components\Tabs\Tab::make('Görünüş')->icon('heroicon-m-swatch')->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\ToggleButtons::make('shape')->label('Sini forması')->options(Xonca::SHAPES)->inline()->required()->default('round')->columnSpanFull(),
                        Forms\Components\TextInput::make('size_cm')->label('Diametr / uzunluq')->numeric()->suffix('sm')->default(60)->required(),
                        Forms\Components\ColorPicker::make('tray_color')->label('Sini rəngi')->default('#c9a14a')->required(),
                        Forms\Components\ColorPicker::make('rim_color')->label('Kənar (haşiyə)')->default('#e8c77a')->required(),
                        Forms\Components\ToggleButtons::make('cover_type')->label('Örtük')->options(Xonca::COVERS)->inline()->default('tulle')->required()->columnSpan(2),
                        Forms\Components\ColorPicker::make('cover_color')->label('Tül rəngi')->default('#fff4f4'),
                        Forms\Components\ColorPicker::make('ribbon_color')->label('Əsas lent rəngi')->default('#b3122e')->required(),
                        Forms\Components\TagsInput::make('ribbon_choices')->label('Müştərinin seçə biləcəyi lent rəngləri')
                            ->placeholder('#b3122e')->columnSpan(2)
                            ->helperText('HEX kodları. Boş qalsa, standart 6 rəng təklif olunur.'),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make('Qiymət və anbar')->icon('heroicon-m-banknotes')->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('price')->label('İcarə qiyməti')->numeric()->prefix('₼')->required()->minValue(0),
                        Forms\Components\TextInput::make('deposit')->label('Depozit')->numeric()->prefix('₼')->default(0)->helperText('Qaytarılanda geri verilir'),
                        Forms\Components\TextInput::make('stock')->label('Neçə ədəd var')->numeric()->minValue(0)->default(1)->required()
                            ->helperText('Eyni gündə neçə müştəriyə verə bilərsiniz'),
                        Forms\Components\TextInput::make('empty_discount')->label('Boş götürəndə endirim')->numeric()->prefix('₼')->default(0),
                        Forms\Components\Toggle::make('fill_allowed')->label('Müştəri içini özü seçə bilsin')->default(true)->inline(false),
                        Forms\Components\Toggle::make('is_featured')->label('Ana səhifədə göstər')->inline(false),
                        Forms\Components\TextInput::make('sort')->label('Sıra')->numeric()->default(0),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make('Hazır dolduruş')->icon('heroicon-m-sparkles')->schema([
                    Forms\Components\Placeholder::make('fill_hint')->hiddenLabel()
                        ->content('Müştəri «Ustalarımızın dolduruşu»nu seçəndə xonça bu əşyalarla gəlir.'),
                    Forms\Components\Repeater::make('defaultFills')->relationship()->hiddenLabel()
                        ->schema([
                            Forms\Components\Select::make('fill_item_id')->label('Əşya')
                                ->options(fn () => FillItem::query()->orderBy('sort')->get()->mapWithKeys(fn ($i) => [$i->id => $i->name . ' — ' . Money::format($i->price)]))
                                ->searchable()->required()->columnSpan(3),
                            Forms\Components\TextInput::make('qty')->label('Say')->numeric()->minValue(1)->default(1)->required(),
                        ])
                        ->columns(4)->addActionLabel('Əşya əlavə et')->reorderable(false)->defaultItems(0),
                ]),

                Forms\Components\Tabs\Tab::make('3D və AR')->icon('heroicon-m-cube-transparent')->schema([
                    Forms\Components\Placeholder::make('ar_help')->hiddenLabel()->content(new \Illuminate\Support\HtmlString(
                        '<div class="space-y-2 text-sm leading-relaxed">'
                        . '<p><b>Müştəri telefonun kamerası ilə xonçanı öz evində, real ölçüdə görür.</b> Bunun üçün xonçanın 3D skanı lazımdır.</p>'
                        . '<p>1) Telefona pulsuz <b>Polycam</b>, <b>KIRI Engine</b> və ya <b>RealityScan</b> tətbiqini yükləyin. '
                        . '2) Hazır xonçanı işıqlı, sadə fonlu masaya qoyun. 3) «Object / Photo mode» seçin və xonçanın ətrafında yavaş-yavaş 3 dövrə vurun: aşağıdan, ortadan, yuxarıdan (60–120 şəkil). '
                        . '4) Hazır olanda <b>GLB</b> formatında ixrac edin və bura yükləyin.</p>'
                        . '<p>Ölçü avtomatik düzəldilir: saytda xonça «Görünüş» bölməsindəki diametrə (sm) uyğun göstərilir. iPhone üçün USDZ faylı vacib deyil — sayt özü hazırlayır; tətbiq USDZ də verirsə, onu da yükləyin.</p></div>'
                    )),
                    Forms\Components\FileUpload::make('model_glb')->label('3D model (.glb)')
                        ->disk('public')->directory('models')->maxSize(51200)
                        ->acceptedFileTypes(['model/gltf-binary', 'application/octet-stream', '.glb'])
                        ->helperText('50 MB-a qədər. 5–15 MB olanda telefonda daha tez açılır.'),
                    Forms\Components\FileUpload::make('model_usdz')->label('iPhone üçün (.usdz) — istəyə görə')
                        ->disk('public')->directory('models')->maxSize(51200)
                        ->acceptedFileTypes(['model/vnd.usdz+zip', 'model/usd', 'application/zip', 'application/octet-stream', '.usdz']),
                ]),
                Forms\Components\Tabs\Tab::make('Şəkillər')->icon('heroicon-m-photo')->schema([
                    Forms\Components\FileUpload::make('images')->label('Real şəkillər')->image()->multiple()->reorderable()->maxFiles(12)
                        ->directory('xoncas')->imageResizeMode('contain')->imageResizeTargetWidth(1600)->maxSize(8192)
                        ->helperText('Birinci şəkil kataloqda göstərilir, ikincisi kartın üzərinə gələndə. Şaquli (4:5) şəkillər ən gözəl görünür. Şəkil yoxdursa, xonçanın qızılı xətt rəsmi göstərilir.'),
                ]),
            ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\ImageColumn::make('images')->label('')->limit(1)->circular()->defaultImageUrl(null)->toggleable(),
                Tables\Columns\TextColumn::make('code')->label('Kod')->searchable()->badge()->color('gray'),
                Tables\Columns\TextColumn::make('name')->label('Ad')->searchable()->weight('bold')
                    ->description(fn (Xonca $r) => (Xonca::SHAPES[$r->shape] ?? '') . ' · ' . (Xonca::COVERS[$r->cover_type] ?? '')),
                Tables\Columns\ColorColumn::make('ribbon_color')->label('Lent'),
                Tables\Columns\IconColumn::make('model_glb')->label('AR')->boolean()->getStateUsing(fn (Xonca $r) => $r->hasAr()),
                Tables\Columns\TextColumn::make('occasions.name')->label('Bayramlar')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('price')->label('Qiymət')->money('AZN')->sortable(),
                Tables\Columns\TextColumn::make('stock')->label('Anbar')->suffix(' əd.')->sortable(),
                Tables\Columns\TextColumn::make('booking_items_count')->counts('bookingItems')->label('Sifariş')->sortable()->badge()->color('info'),
                Tables\Columns\ToggleColumn::make('is_featured')->label('Ana səhifə'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktiv'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('shape')->label('Forma')->options(Xonca::SHAPES),
                Tables\Filters\SelectFilter::make('occasions')->label('Bayram')->relationship('occasions', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktiv'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_site')->label('Saytda')->icon('heroicon-m-eye')->color('gray')
                    ->url(fn (Xonca $r) => route('xonca', $r), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make()->label('Dəyiş'),
                Tables\Actions\ReplicateAction::make()->label('Kopyala')->color('gray')
                    ->excludeAttributes(['booking_items_count'])
                    ->beforeReplicaSaved(function (Xonca $replica) {
                        $replica->name .= ' (kopya)';
                        $replica->slug = Str::slug($replica->name) . '-' . Str::lower(Str::random(4));
                        $replica->code = 'XN-' . strtoupper(Str::random(4));
                        $replica->is_active = false;
                    })
                    ->after(function (Xonca $replica, Xonca $record) {
                        $replica->occasions()->sync($record->occasions->pluck('id'));
                        foreach ($record->defaultFills as $f) {
                            $replica->defaultFills()->create(['fill_item_id' => $f->fill_item_id, 'qty' => $f->qty]);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('occasions');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListXoncas::route('/'),
            'create' => Pages\CreateXonca::route('/create'),
            'edit' => Pages\EditXonca::route('/{record}/edit'),
        ];
    }
}
