<?php

namespace App\Filament\Resources\OccasionResource\Pages;

use App\Filament\Resources\OccasionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOccasion extends EditRecord
{
    protected static string $resource = OccasionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
