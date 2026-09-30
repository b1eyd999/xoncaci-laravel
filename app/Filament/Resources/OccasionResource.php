<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OccasionResource\Pages;
use App\Models\Occasion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OccasionResource extends Resource
{
    protected static ?string $model = Occasion::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Kataloq';

    protected static ?string $modelLabel = 'bayram';

    protected static ?string $pluralModelLabel = 'Bayramlar (toy, nişan...)';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('name')->label('Ad')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set, ?Occasion $record) => $record ? null : $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->label('Link')->prefix('/xoncalar/')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('tagline')->label('Qısa şüar')->columnSpanFull(),
            Forms\Components\Textarea::make('description')->label('Təsvir')->columnSpanFull(),
            Forms\Components\ColorPicker::make('accent')->label('Kartın rəngi')->required()->default('#8a1c2b'),
            Forms\Components\TextInput::make('sort')->label('Sıra')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Saytda görünsün')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')
            ->columns([
                Tables\Columns\ColorColumn::make('accent')->label(''),
                Tables\Columns\TextColumn::make('name')->label('Ad')->weight('bold')->description(fn (Occasion $r) => $r->tagline),
                Tables\Columns\TextColumn::make('xoncas_count')->counts('xoncas')->label('Xonça')->badge(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktiv'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOccasions::route('/'),
            'create' => Pages\CreateOccasion::route('/create'),
            'edit' => Pages\EditOccasion::route('/{record}/edit'),
        ];
    }
}
