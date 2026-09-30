<?php

namespace App\Filament\Resources\FillItemResource\Pages;

use App\Filament\Resources\FillItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFillItem extends EditRecord
{
    protected static string $resource = FillItemResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
