<?php

namespace App\Filament\Resources\FillCategoryResource\Pages;

use App\Filament\Resources\FillCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFillCategory extends EditRecord
{
    protected static string $resource = FillCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
