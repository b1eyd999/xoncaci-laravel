<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Sayt';

    protected static ?string $modelLabel = 'sual';

    protected static ?string $pluralModelLabel = 'Suallar (FAQ)';

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Forms\Components\TextInput::make('question')->label('Sual')->required(),
            Forms\Components\Textarea::make('answer')->label('Cavab')->required()->rows(4),
            Forms\Components\Toggle::make('is_active')->label('Saytda görünsün')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('question')->label('Sual')->weight('bold')->description(fn (Faq $r) => \Illuminate\Support\Str::limit($r->answer, 90))->wrap(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktiv'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
