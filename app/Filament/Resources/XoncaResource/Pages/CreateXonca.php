<?php

namespace App\Filament\Resources\XoncaResource\Pages;

use App\Filament\Resources\XoncaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateXonca extends CreateRecord
{
    protected static string $resource = XoncaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
