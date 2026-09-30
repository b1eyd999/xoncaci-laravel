<?php

namespace App\Filament\Resources\XoncaResource\Pages;

use App\Filament\Resources\XoncaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditXonca extends EditRecord
{
    protected static string $resource = XoncaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('site')->label('Saytda bax')->icon('heroicon-m-eye')->color('gray')
                ->url(fn () => route('xonca', $this->record), shouldOpenInNewTab: true),
            Actions\DeleteAction::make(),
        ];
    }
}
