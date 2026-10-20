<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FillCategoryResource\Pages;
use App\Models\FillCategory;
use App\Filament\Forms\Translations;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FillCategoryResource extends Resource
{
    protected static ?string $model = FillCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Kataloq';

    protected static ?string $modelLabel = 'kateqoriya';

    protected static ?string $pluralModelLabel = 'Dolduruş kateqoriyaları';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Ad')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set, ?FillCategory $record) => $record ? null : $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('sort')->label('Sıra')->numeric()->default(0),
            Translations::make(['name' => 'Ad'])->columnSpanFull()->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Ad')->weight('bold'),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Əşya sayı')->badge(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFillCategories::route('/'),
            'create' => Pages\CreateFillCategory::route('/create'),
            'edit' => Pages\EditFillCategory::route('/{record}/edit'),
        ];
    }
}
