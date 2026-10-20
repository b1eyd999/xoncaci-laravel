<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FillItemResource\Pages;
use App\Models\FillItem;
use App\Filament\Forms\Translations;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FillItemResource extends Resource
{
    protected static ?string $model = FillItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';

    protected static ?string $navigationGroup = 'Kataloq';

    protected static ?string $modelLabel = 'dolduruş əşyası';

    protected static ?string $pluralModelLabel = 'Dolduruş əşyaları';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->columns(12)->schema([
            Forms\Components\Section::make()->columnSpanFull()->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Ad')->required()->maxLength(120)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set, ?FillItem $record) => $record ? null : $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->label('Kod (slug)')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('fill_category_id')->label('Kateqoriya')->relationship('category', 'name')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->label('Ad')->required(),
                        Forms\Components\TextInput::make('slug')->required(),
                    ])->preload(),
                Forms\Components\TextInput::make('price')->label('Qiymət (1 ədəd)')->numeric()->prefix('₼')->required()->minValue(0),
                Forms\Components\Select::make('model')->label('Rəsm (ikon)')->options(FillItem::MODELS)->required()->searchable()
                    ->helperText('Şəkil yüklənməyibsə, saytda bu qızılı xətt rəsmi göstərilir'),
                Forms\Components\TextInput::make('max_qty')->label('Bir xonçada ən çox')->numeric()->minValue(1)->default(12)->required(),
                Forms\Components\ColorPicker::make('color')->label('Rəng (admin siyahısı üçün)')->required(),
                Forms\Components\TextInput::make('sort')->label('Sıra')->numeric()->default(0),
                Forms\Components\TextInput::make('description')->label('Qısa izah')->columnSpanFull(),
                Forms\Components\FileUpload::make('image')->label('Şəkil (istəyə görə)')->image()->directory('items')->imageResizeTargetWidth(600)->columnSpanFull()
                    ->helperText('Kvadrat şəkil — saytda dairəvi kiçik şəkil kimi göstərilir və rəsmi əvəz edir.'),
                Forms\Components\Toggle::make('is_active')->label('Saytda görünsün')->default(true),
            ]),
            Translations::make(['name' => 'Ad', 'description' => 'Qısa izah'])->columnSpanFull()->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->defaultGroup('category.name')
            ->columns([
                Tables\Columns\ColorColumn::make('color')->label(''),
                Tables\Columns\TextColumn::make('name')->label('Ad')->searchable()->weight('bold')
                    ->description(fn (FillItem $r) => FillItem::MODELS[$r->model] ?? $r->model),
                Tables\Columns\TextInputColumn::make('price')->label('Qiymət ₼')->type('number')->rules(['numeric', 'min:0'])->width('120px'),
                Tables\Columns\TextColumn::make('max_qty')->label('Maks.')->suffix(' əd.'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktiv'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('fill_category_id')->label('Kateqoriya')->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Dəyiş'),
                Tables\Actions\ReplicateAction::make()->label('Kopyala')->color('gray')
                    ->beforeReplicaSaved(function (FillItem $replica) {
                        $replica->name .= ' (kopya)';
                        $replica->slug = Str::slug($replica->name) . '-' . Str::lower(Str::random(4));
                    }),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFillItems::route('/'),
            'create' => Pages\CreateFillItem::route('/create'),
            'edit' => Pages\EditFillItem::route('/{record}/edit'),
        ];
    }
}
