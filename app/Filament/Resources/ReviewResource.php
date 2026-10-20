<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use App\Filament\Forms\Translations;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Sayt';

    protected static ?string $modelLabel = 'rəy';

    protected static ?string $pluralModelLabel = 'Rəylər';

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema([
            Forms\Components\TextInput::make('name')->label('Ad')->required(),
            Forms\Components\TextInput::make('occasion')->label('Məclis')->placeholder('Nişan'),
            Forms\Components\Select::make('rating')->label('Ulduz')->options([5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★'])->default(5)->required(),
            Forms\Components\Textarea::make('text')->label('Rəy')->required()->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('sort')->label('Sıra')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Saytda görünsün')->default(true),
            Translations::make(['occasion' => 'Məclis', 'text' => ['Rəy', 'textarea']])->columnSpanFull()->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Ad')->weight('bold')->description(fn (Review $r) => $r->occasion),
                Tables\Columns\TextColumn::make('text')->label('Rəy')->limit(80)->wrap(),
                Tables\Columns\TextColumn::make('rating')->label('Ulduz')->formatStateUsing(fn ($state) => str_repeat('★', $state))->color('warning'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktiv'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
